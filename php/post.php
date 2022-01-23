<?php

	if($pageName == "404"){
		header("Location: " . getRequestProtocol() . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); //Redirect to GET version of page
		
	}

	if(isset($_GET["other"]) && strlen(trim($_GET["other"])) > 0 )
		$actionNode = $xp->query("/spetml:page/spetml:post/spetml:actions/spetml:action[@name='" . $_GET["other"] . "']")->item(0);
	else
		$actionNode = $xp->query("/spetml:page/spetml:post/spetml:actions/spetml:action[@default='true']")->item(0);

	if(!$actionNode){
		header("Location: " . getRequestProtocol() . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); //Redirect to GET version of page
		die();
	}
	
	$output = "";

	switch ($actionNode->getAttribute("type")) {
		case 'sp':
			$output = doSPAction($actionNode, $_POST)->saveXML();
			header("Content-Type: application/xml; charset=utf-8");
			break;
		case 'module':
			$output = doModAction($actionNode, $_POST)->saveXML();
			header("Content-Type: application/xml; charset=utf-8");
			break;
		case 'php':
			require($actionNode->getAttribute("file"));
			die();
			break;
	}

	if($mysqli)
		$mysqli->close();

	echo($output);
?>