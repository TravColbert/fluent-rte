<?php
# Convert from Fluent's codec names to Cisco's codec names (Cisco wants 
# the names in a specific mixed-case format :
#
# $element is the XML-thing we got from the model.  We called this
# template in the "codecgroup" context so the $element is an XML chunk 
# of codecgroups.
#
# Note the use of lambda-style functions here.  This is because you may 
# be re-including this file several times and PHP doesn't like having 
# functions redefined.  In this way the functions are anonymous and no 
# errors are thrown.

$fc_convert = create_function('$xmlobj',
'	$codectable = array(
		"g711a" => "G711a",
		"g711u" => "G771u",
		"g711a16khz" => "G711a",
		"g711u16khz" => "G711u",
		"g722" => "G722",
		"g729" => "G729a"
	);
	
	$xmlobj->codecgroup[0]->codec_id = $codectable[trim($xmlobj->codecgroup[0]->codec_id)];
	
	return $xmlobj;');

$element = $fc_convert($element);
?>
