<?php
class modCore{
	public static function sendEmail($actionNode, $inputParams){

		function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
			$header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
			return mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header, '-f"no-reply@solarlogistix.com" -F"Solar Logistix"');
		}

		$outputXML = new DomDocument();
		$outputXML->appendChild($outputXML->createElement("sendEmail"));

		try{
				
			$xp = new DOMXPath($actionNode->ownerDocument);
			$sender = $xp->query("spetml:params/spetml:param[@name='fromAddress']", $actionNode)->item(0)->getAttribute("value");
			$subjectNode = $xp->query("spetml:params/spetml:param[@name='subject']", $actionNode)->item(0);
			$subject = $subjectNode->getAttribute("value");
			$replyTo = $inputParams[$xp->query("spetml:params/spetml:param[@name='replyTo']", $actionNode)->item(0)->getAttribute("fieldName")];
			foreach ($xp->query("spetml:value", $subjectNode) as $valueNode) {
				switch($valueNode->getAttribute("type")){
					case "text":
						$subject .= $valueNode->getAttribute("text");
						break;
					case "field":
						$subject .= $inputParams[$valueNode->getAttribute("name")];
						break;
				}
			}

			foreach ($xp->query("spetml:params/spetml:param[@name='recipients']/spetml:value", $actionNode) as $recipient) {
				$body = $xp->query("spetml:params/spetml:param[@name='message']", $actionNode)->item(0)->getAttribute("value") . "\r\n\r\n";
				
				foreach ($xp->query("spetml:params/spetml:param[@name='field']", $actionNode) as $field) {
					$body .= $field->getAttribute("title") . $inputParams[$field->getAttribute("fieldName")] . "\r\n";
				}
				
				$body = mb_convert_encoding($body, "UTF-8","AUTO");
				$extra = "From: $sender\r\n" . "Return-Path: $sender\r\n" . "Reply-To: ".$replyTo." \r\n" . "X-Mailer: PHP/" . phpversion();
				mail_utf8($recipient->getAttribute("text"), $subject, $body, $extra);	
			}
			
			
		
			$outputXML->documentElement->setAttribute("fail", "0");
		}catch(Exception $e){
			$outputXML->documentElement->setAttribute("fail", "1");
		}
		
		
		return $outputXML;
	}
}

?>
