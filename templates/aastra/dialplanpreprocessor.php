<?php

function to_minutes($xpath,$element) {
	$result = $element->xpath($xpath);

	while(list( ,$node) = each($result)) {
		$value = $node;
	}

	return $value/60;
}

// Convert from rfc3435 to Aastra digitmap format
function to_rfc3435($xpath,$element) {
	/* Aastras:
	 * replace "." (0 or more of the previous construct) with "+"
	 * don't use the 'T'
	 * use big X's only
	 */
	$replace[] = array("search" => "T","replace" => "");
	$replace[] = array("search" => ".","replace" => "+");
	$replace[] = array("search" => "x","replace" => "X");
	
	$result = $element->xpath($xpath);
	while(list( ,$node) = each($result)) {
		$value = $node;
		for($count=0;$count<count($replace);$count++) {
			$value = str_ireplace($replace[$count]["search"], $replace[$count]["replace"], $value);
		}
		$returnstring .= $value;
	}
	
	if(stristr($returnstring,"#"))
		$returnstring = "\"".$returnstring."\"";
	
	return $returnstring;
}

$element->devicesetting->dialplan = to_rfc3435("devicesetting[linenum=1]/dialplan",$element);

return $element;
?>
