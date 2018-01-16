<?php
# Convert from codec names to codec numbers like the way Aastra wants:
#
#                         : For customized codec list use the
#                         : following payloads:
#                         : payload 9 = g722
#                         : payload 8 = g711a (8k)
#                         : payload 0 = g711u (8k)
#                         : payload 111 = g711a (16k)
#                         : payload 110 = g711u (16k)
#                         : payload 18 = g729
#
# Aastra does their codec preferences positionally.  First payload code
# on the line is the preference moving left->right.
# 
# This function adds a new custom XML element: "customcodec" to the
# codecgroup element retrieved from the model.
# Inside customcodec is a simple "payload" element that is the codec
# string as Aastra wants it.

function to_customcodec($element) {
	$returnstring = "";
	/* $element is the XML-thing we got from the model.  We called this
	 * template in the "codecgroup" context so the $element is a chunk of
	 * XML type codecgroup
	 */
	$codectable = array(
		"g711a" => "8",
		"g711u" => "0",
		"g711a16khz" => "111",
		"g711u16khz" => "110",
		"g722" => "9",
		"g729" => "18"
	);
	
	$payloadtable = array();
	
	foreach($codectable as $codecname=>$payload) {
		$result = $element->xpath("codecgroup[codec_id='".$codecname."']/priority");
		if($result) {
			while(list( ,$node) = each($result)) {
				$priority = strip_tags($node->asXML());
				$payloadtable[$priority] = $codectable[$codecname];
			}
		}
	}

	ksort($payloadtable);
	
	foreach($payloadtable as $payload) {
		$returnstring .= "payload=".$payload.";ptime=20;silsupp=off,";
	}
	
	$returnstring = rtrim($returnstring,",");

	/* Now let's add a custom XML element so we can look it up easily in 
	 * the template
	 * We will make a "customcodec" element with a child element of
	 * "payload".  We need to make our template (the thing that called
	 * this) do a search for customcodec/payload to get the value set 
	 * here.
	 */
	
	$customcodec = $element->addChild('customcodec');
	$customcodec->addChild("payload", $returnstring);
	
	return $element;
}

return to_customcodec($element);

?>
