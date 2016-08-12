<?php

function to_minutes($xpath,$element) {
	$result = $element->xpath($xpath);

	while(list( ,$node) = each($result)) {
		$value = $node;
	}

	return $value/60;
}

// Convert from rfc3435 to SNOM digitmap format
function to_rfc3435($xpath,$element) {
	/* SNOMs replace:
	 * 'x' -> '.'
	 * don't use the 'T'
	 * can't put all the dialplan expressions in one value (they are all
	 * in seperate lines
	 */
	$replace[0] = array("search" => "x","replace" => ".");
	$replace[1] = array("search" => "T","replace" => "");
	$result = $element->xpath($xpath);
	while(list( ,$node) = each($result)) {
		$valueset = explode("|",$node);
		foreach($valueset as $value) {
			for($count=0;$count<count($replace);$count++) {
				$value = str_ireplace($replace[$count]["search"], $replace[$count]["replace"], $value);
			}
			$value = "<template match=\"".$value."\" user=\"phone\" rewrite=\"\" />\n";
			$returnstring .= $value;
		}
	}
	
	return $returnstring;
}

#$element->devicesetting->regtimeout = to_minutes("devicesetting/regtimeout",$element);

$element->devicesetting->dialplan = to_rfc3435("devicesetting[linenum=1]/dialplan",$element);

return $element;
?>
