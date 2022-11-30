<?php
	function do404(){
	global $xmlFile, $pageName, $site;
		$xmlFile =  $_SERVER['CONTEXT_DOCUMENT_ROOT'] . $site['xmldir'] . "/_404.xml";
		$pageName = "_404";
	    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	}

	function doError($code){
	global $xmlFile, $pageName, $site;
		$xmlFile =  $_SERVER['CONTEXT_DOCUMENT_ROOT'] . $site['xmldir'] . "/_404.xml";
		$pageName = "_404";
	    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	    http_response_code($code);
	}

	function dbConnect(){
	global $db, $mysqli;
		if($mysqli != null)
			return $mysqli;

		if(strlen(trim($db['port']))>0)
			$servername = $db['server'].':'.$db['port'];
		else
			$servername = $db['server'];

		$mysqli = new mysqli($servername, $db['user'], $db['pass'], $db['database']);

		if ($mysqli->connect_errno || !$mysqli->set_charset("utf8")){
			return null;
		}

		return $mysqli;
	}

	function loadXML($data){
		$xml = new DOMDocument();
	    if(!$xml->loadXML($data)){ //Debug any XML errors
			$errors = libxml_get_errors();
			foreach ($errors as $error) {
				echo display_xml_error($error, $xml);
			}
			libxml_clear_errors();
			die();
		}
		return $xml;
	}
	function loadXMLFile($fileName){
		$xml = new DOMDocument();
	    if(!$xml->load($fileName)){ //Debug any XML errors
			$errors = libxml_get_errors();
			foreach ($errors as $error) {
				echo display_xml_error($error, $xml);
			}
			libxml_clear_errors();
			die();
		}
		return $xml;
	}

	function loadTemplate($templateid){
		$sql = "SELECT * from templates WHERE templateid = $templateid";
		$rs = doQuery($sql, $dbLink);
		if(!$rs)
			die(mysql_error());
		$row = mysql_fetch_array($rs);
		if(!$row)
			do404();

		$overrides = $row['overrides'];
		$templatexml = $row['templatexml'];
		$templatedoc = loadXML($templatexml); //Load Template XML
		$templateXP = new DOMXPath($templatedoc);

		$row = NULL;
		$rs = NULL;

		if(!is_null($overrides)){
			$parentTemplate = loadTemplate($overrides);
			$parentXP = new DOMXPath($parentTemplate);
			$templateXP->query("");
		}
	}

	function fixVoidTags($html){
		$voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'menuitem', 'meta', 'param', 'source', 'track', 'wbr'];
		foreach ($voidTags as $tagName) {
			$html = preg_replace('/\<'.$tagName.'(.*?)\>\<\/'.$tagName.'\>/i', '<'.$tagName.'$1 />' , $html);
		}

		return $html;
	}

	function getBaseURL($url){
		$buff = parse_url($url);
		return $buff['scheme']."://".$buff['host'];
	}


	function doLogin(){
		if(strlen(trim($_SESSION['user'])) > 0)
			return true;

		$_SESSION['user'] = 1;
	}


	$modules = array(); //Array of include files

	function modRequire($modname){
	//modRequire: Loads dependency modules if not yet loaded - module names must match file names
	global $site, $modules;

		if(!isset($modules[$modname]))
			$modules[$modname] = null;

		if(!is_object($modules[$modname]) && $modules[$modname] != "loading"){
			$modules[$modname] = "loading";
			include_once($_SERVER['CONTEXT_DOCUMENT_ROOT'] . $site['moddir'] . "/$modname.php");
		}
	}


	function ddie($text){
	//Debug Die: Outputs as text
		header("Content-Type: text/plain; charset=utf-8"); //Send page as HTML
		die($text);
	}

	function getRequestProtocol(){
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			return "https";

		return "http";
	}


	function getSPXML($spName, $params){
	global $mysqli;
		$fail = false; //Fail flag
		$xml = new DOMDocument(); //Return XML

		$outputNode = $xp->query("output", $actionNode)->item(0);

		//Set document element to action name
		$xml->appendChild($xml->createElement($outputNode->getAttribute("rootName")));

		//Connect
		if(!($mysqli = dbConnect())){
			$xml->documentElement->setAttribute("fail", "1");
			return $xml;
		}

		//Exexute
		$query = "CALL $spName (" . implode(",", $params) . ")";
		if(!($result = $mysqli->query($query)))
			$fail = true;

		//If we failed return failure
		if($fail){
			$xml->documentElement->setAttribute("fail", "1");
			$xml->documentElement->setAttribute("err", $mysqli->error);
			$xml->documentElement->setAttribute("sql", $query);
			return $xml;
		}

		//Loop through result rows
		while($row = $result->fetch_assoc()){
			$rowNode = $xml->documentElement->appendChild($xml->createElement($outputNode->getAttribute("nodeName")));

			//Get output fields from definition XML
			$fieldNodes = $xp->query("output/field", $actionNode);
			//Loop through output fields
			foreach ($fieldNodes as $fieldNode) {
				$dbField = $row[$fieldNode->getAttribute("dbName")];
				if($dbField){
					switch ($fieldNode->getAttribute("type")) {
						case 'attribute':
							$rowNode->setAttribute($fieldNode->getAttribute("name"), $dbField);
							break;

						case 'element':
							$rowNode->appendChild($xml->createElement($fieldNode->getAttribute("name")))->appendChild($xml->createTextNode($dbField));
							break;
					}
				}
			}
		}

		//Free result-set
		$result->free();

		$xml->documentElement->setAttribute("fail", "0");

		return $xml;
	}

	function doSPAction($actionNode, $inputParams){
		global $mysqli, $xp;

		//Array for parameters
		$params = array();

		//Add params to array
		foreach($xp->query("params/param", $actionNode) as $param){
			if(isset($inputParams[$param->getAttribute("name")]))
				$paramValue = $mysqli->real_escape_string($inputParams[$param->getAttribute("name")]);
			else
				$paramValue = "";

			switch ($param->getAttribute("dataType")) {
				case 'string':
					$paramValue = "'" . $paramValue . "'";
					break;
			}
			array_push($params, $paramValue);
		}

		//Stored Procedure name
		$spName = $actionNode->getAttribute("dbName");

		return getSPXML($spName, $params);
	}

	function doModAction($actionNode, $inputParams){
		$modName = $actionNode->getAttribute("moduleName");
		modRequire($modName);
		$funcName = $actionNode->getAttribute("functionName");
		return call_user_func(__NAMESPACE__ . '\\' . $modName . '::' . $funcName, $actionNode, $inputParams);
	}


	function humanTiming ($time){
		//Returns how much time has passed (in largest increment) since a particular time in history (credit: arnorhs at http://bit.ly/1hDSMtR)
	    $time = time() - $time; // to get the time since that moment

	    $tokens = array (
	        31536000 => 'year',
	        2592000 => 'month',
	        604800 => 'week',
	        86400 => 'day',
	        3600 => 'hour',
	        60 => 'minute',
	        1 => 'second'
	    );

	    foreach ($tokens as $unit => $text) {
	        if ($time < $unit) continue;
	        $numberOfUnits = floor($time / $unit);
	        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
	    }
	}

	function currentDate(){
		return date("Y-m-d");
	}

	function futureDate(){
		return date('Y-m-d', strtotime('+15 years'));
	}

	function formatStringDate($date, $format, $tz){
		$date = date_create($date, timezone_open($tz));
		return $date->format($format);
	}

	function compareDates($date1, $date2, $tz){
		if($date1 == "now")
			$date1 = date_create(null, timezone_open($tz));
		else
			$date1 = date_create($date1, timezone_open($tz));

		if($date2 == "now")
			$date2 = date_create(null, timezone_open($tz));
		else
			$date2 = date_create($date2, timezone_open($tz));

		return $date1->getTimestamp() - $date2->getTimestamp();
	}


?>
