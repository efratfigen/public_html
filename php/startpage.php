<?php
error_reporting(E_ALL);
session_start();

$spetNS = 'http://www.spetnik.com/2013/spetml';

require("inc/config.php");
require("inc/functions.php");

$debug = false; //Set to true to debug XML result

$mysqli = null;

	//Return 404 if missing pagename argument (shouldn't happen if site is properly configured) or if
	//pagename begins with a blocked prefix
	if(!isset($_GET["pagename"]) || substr($_GET["pagename"], 0, strlen($site['blockprefix'])) == $site['blockprefix'])
		do404();
	else
		$pageName = $_GET["pagename"];

	//If pagename argument is empty, then we want the default (typically home) page
	if(trim($pageName) == "")
		$pageName = $site['defpage'];

	$xmlFile =  $_SERVER['CONTEXT_DOCUMENT_ROOT'] . $site['xmldir'] . "/$pageName.xml"; //Set XML file path based on pagename var

	if(!file_exists($xmlFile)) //if xml file is non-existent then 404
    	do404();

    $xml = loadXMLFile($xmlFile);

	//If XML document root element is not <page />, then return 404 because it is not supposed to display
	if($xml->documentElement->nodeName != "spetml:page"){
		do404();
		$xml = loadXMLFile($xmlFile);
	}

	//BEGIN Request element for HTTP request data
	$elemRequest = $xml->documentElement->insertBefore($xml->createElementNS($spetNS, "spetml:request"), $xml->documentElement->firstChild);
	if(!isset($_GET["other"]))
		$other = "";
	else
		$other = $_GET["other"];

	$getVars = preg_split("/\//", $other, NULL, PREG_SPLIT_NO_EMPTY);
	$elemGetVars = $elemRequest->appendChild($xml->createElementNS($spetNS, "spetml:getVars"));
	foreach ($getVars as $id => $value) {
		$elemGetVars->appendChild($xml->createElementNS($spetNS, "spetml:var"))->setAttribute("id", $id)->ownerElement->appendChild($xml->createTextNode($value));
	}

	// - Add HTTP headers to the request element as well
	$elemHeaders = $elemRequest->appendChild($xml->createElementNS($spetNS, "spetml:headers"));
	foreach (getallheaders() as $name => $value) {
		$elemHeaders->appendChild($xml->createElementNS($spetNS, "spetml:header"))->setAttribute("name", $name)->ownerElement->appendChild($xml->createTextNode($value));
	}
	//END request element

	//Handle redirect for <spetml:page redirect='...' />
	if(strlen(trim($xml->documentElement->getAttribute("redirect"))) > 0){
		header("Location: " . $xml->documentElement->getAttribute("redirect"));
		die();
	}

	//Make sure XML has a pagename
	if($xml->documentElement->getAttribute("name") == "")
		$xml->documentElement->setAttribute("name", $pageName);

	$xp = new DOMXPath($xml);
	$xp->registerNamespace("spetml", "http://www.spetnik.com/2013/spetml");

	//If page requires SSL, redirect to SSL version
	if(trim(strtolower($xp->query("/spetml:page")->item(0)->getAttribute("ssl")))=="yes" && getRequestProtocol() != "https"){
		header("location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		die();
	}

	/*Get Site Data and append to doc*/
	$siteXML = loadXMLFile($_SERVER['CONTEXT_DOCUMENT_ROOT'] . $site['xmldir'] . "/" . $site['structfile']);

	$siteNode = $xp->query("/spetml:page/spetml:site")->item(0); //Query <site> node in existing XML
	if(!$siteNode){ //If <site> node doesn't exist, create it
		$siteNode = $xml->createElementNS('http://www.spetnik.com/2013/spetml', 'spetml:site');
		$xml->documentElement->appendChild($siteNode);
	}

	foreach($siteXML->documentElement->childNodes as $curNode){
		$siteNode->appendChild($xml->importNode($curNode, true)); //Import site data into page XML
	}


	/*********************************/


	/* BEGIN Load modules and process functions*/
	$modCalls = $xp->query("/spetml:page/spetml:modules/spetml:module | /spetml:page/spetml:site/spetml:modules/spetml:module");
	foreach($modCalls as $curMod){
		$modName = $curMod->getAttribute("name");
		modRequire($modName);
		foreach($xp->query("function", $curMod) as $curFunction){
			$argXML = new DOMDocument();
			$argXML->appendChild($argXML->createElementNS($spetNS, 'spetml:args'));

			foreach($xp->query("argument", $curFunction) as $curArg){
				$argNode = $argXML->importNode($curArg, true);
				$argXML->documentElement->appendChild($argNode); //Import site data into page XML

				if(substr($argNode->getAttribute('value'), 0, 1) == "{" && substr($argNode->getAttribute('value'), -1, 1) == "}"){ //value is an XPath query
					$refNodes = $xp->query(substr($argNode->getAttribute('value'), 1, strlen($argNode->getAttribute('value'))-2)); //Query the value
					foreach($refNodes as $curNode){ //Loop through any nodes in the result
						$argNode->appendChild($argXML->importNode($curNode, true)); //And append to argument
					}
				}
			}

			//Call function
			$tempXML = call_user_func(__NAMESPACE__ . '\\' . $modName . '::' . $curFunction->getAttribute("name"), $argXML);
			//Append function results to XML
			$curFunction->appendChild($xml->importNode($tempXML->documentElement, true));
		}
	}
	/*** END Load modules and process functions ***/

	/* BEGIN Get XML Externals and append */
	$externals = $xp->query("/spetml:page/spetml:externals/spetml:reference[@type=\"xml\"]");
	foreach($externals as $curNode){
		$siteXML->load($curNode->getAttribute("src"));
		$curNode->appendChild($xml->importNode($siteXML->documentElement, true));
	}
	/* END Get XML Externals and append */


	//If this is a POST request, import POST functions and end (no transformation)
	if($_SERVER['REQUEST_METHOD'] == "POST"){
		require("post.php");
		die();
	}

	/* BEGIN XSL TRANSFORM AND OUTPUT SEQUENCE */
	header("Content-Type: text/html; charset=utf-8");

	//Load template file specified in page XML
	$templateName = $xp->query("/spetml:page")->item(0)->getAttribute("templateName");
	$xslFile = $_SERVER['CONTEXT_DOCUMENT_ROOT'] . $site['xsldir'] . "/$templateName.xslt";
	$xsl = new DOMDocument();
	$xsl->load($xslFile);
	$xslp = new DOMXPath($xsl);
	$xslp->registerNamespace("spet", "http://www.spetnik.com/2013/spetcms");

	// Get Includes and append to XSL
	$includes = $xslp->query("//spet:includes/spet:include");
	foreach($includes as $include){
		$incDoc = new DOMDocument();
		$incDoc->load($_SERVER['CONTEXT_DOCUMENT_ROOT'] . $site['xsldir'] ."/".$include->getAttribute('name').".inc.xslt");
		$xslInc = new DOMXPath($incDoc);
		$xslInc->registerNamespace("xsl", "http://www.w3.org/1999/XSL/Transform");

		foreach ($xslInc->query("//xsl:template") as $node) {
			$xsl->documentElement->appendChild($xsl->importNode($node, true));
		}
	}

	//If $debug == true then output raw, untransformed XML
	if($debug){
		header("Content-Type: application/xml; charset=utf-8");
		die($xml->saveXml());
	}

	//Register any PHP XSLT functions
	$processor = new XSLTProcessor();
	$processor->registerPHPFunctions($site['phpxsltfunctions']);
	$processor->importStylesheet($xsl);

	//Do transformation
	if($xml = $processor->transformToDoc($xml)){ //Make sure transformation goes ok
		echo(fixVoidTags($xml->saveHTML())); //Dump HTML to browser
	}else{
		echo("An error occured.");
	}

	/*** END XSL transform and output sequence ***/

	//Close DB connection, if used
	if($mysqli)
		$mysqli->close();
?>
