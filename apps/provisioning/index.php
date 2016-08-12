<?php
/* vim: set expandtab tabstop=3 shiftwidth=3: */

/*
Fluent - a suite of tools for the management of VoIP networks
Copyright (C) 2010 trav dot colbert at gmail dot com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

?>
<html>
<head>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<style type="text/css" title="currentStyle">
	@import "css/basic.css";
</style>
<?php 
	$applocation = "/apps/provisioning";
	$dirname = dirname($_SERVER["SCRIPT_NAME"]);
	$fluentlocation = str_replace($applocation, "", $dirname);
?>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.livequery.js"></script>
<script type="text/javascript">
var fluentbase = "<?php echo $fluentlocation; ?>";
$(document).ready(function() {
	$('a.menulink').livequery('click', function(event){
		var target = $(this).attr("target");
		$('#submenu').load(target);
		return false;
	});
	$('a.menulink').hover(function () {
		$(this).addClass("mainmenuhover");
	},
	function () {
		$(this).removeClass("mainmenuhover");
	});
	$('a.link').livequery('click', function(event){
		var target = fluentbase + $(this).attr("target");
		$('#container').load(target, function() {
			$('table.reporttable:first thead tr').prepend('<th id=\"action\">Action</th>');
			$('table.reporttable tbody tr.reportrecord').prepend('<td id=\"action\"><a class=\"editrecord\"><img src=\"images/edit-find-replace.png\" alt=\"Edit this record\" title=\"Edit this record\"/></a> <a class=\"deleterecord\"><img src=\"images/edit-delete.png\" alt=\"Delete this record\" title=\"Delete this record\"/></a></td>');
			$('tr:odd').addClass("oddrow");
		});
		return false;
	});
	$('.editrecord').livequery('click', function(event){
		var resource = $(this).parents('.reporttable').attr("id");
		var id = $(this).parents('tr').attr("id");
		if (!IsNumeric(id)) {
			var target = fluentbase + "/index.php?q=" + resource + "[id='" + id + "']&template=" + resource + ".edit.template";
		} else {
			var target = fluentbase + "/index.php?q=" + resource + "[id=" + id + "]&template=" + resource + ".edit.template";
		};
		$('#container').load(target, function() {
			$('tr:odd').addClass("oddrow");
			$('input.origselectedval').each( function(i){
				var origval = $(this).val();
				origval = origval+'';
				var origfieldid = $(this).attr("id");
				var fieldid = origfieldid.replace("orig_","");
				$('select[name=' + fieldid + '] option[value=\'' + origval + '\']').attr('selected','selected')
			});
			
		});
		
		return false;
	});
	$('.deleterecord').livequery('click', function(event){
		var resource = $(this).parents('.reporttable').attr("id");
		var id = $(this).parents('tr').attr("id");
		if (!IsNumeric(id)) {
			var target = fluentbase + "/index.php?q=" + resource + "[id='" + id + "']&template=" + resource + ".delete.template";
		} else {
			var target = fluentbase + "/index.php?q=" + resource + "[id=" + id + "]&template=" + resource + ".delete.template";
		};
		$('#container').load(target, function() {
			$('tr:odd').addClass("oddrow");
		});
		return false;
	});
	$('#actionbutton').livequery('click', function(event){
		var resource = $('form input#resource').attr("value");
		var id = $('form input#id').val();
		var target = fluentbase + "/index.php?q=" + resource + "&template=" + resource + ".table.template";
		$.post(fluentbase + "/index.php", $('form').serialize());
		$('#container').load(target, function() {
			$('tr:odd').addClass("oddrow");
		});
		return false;
	});
} );
function IsNumeric(sText) {
	var ValidChars = "0123456789.";
	var IsNumber=true;
	var Char;
	for (i = 0; i < sText.length && IsNumber == true; i++) { 
		Char = sText.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) {
			IsNumber = false;
		}
	}
	return IsNumber;  
};
</script>
</head>
<body>
	<div id="maintitle"><span>Fluent VoIP Provisioning</span></div>
	<div id="menubar">
		<div id="mainmenu">
			<a class="menulink" target="domains.html">Domains</a><a class="menulink" target="devicetypes.html">Device Types</a><a class="menulink" target="accounts.html">Accounts</a><a class="menulink" target="devices.html">Devices</a><a class="menulink" target="lines.html">Lines</a><a class="menulink" id="help" target="help.html">Help</a>
		</div>
		<div id="submenu"></div>
	</div>
	<div id="container"></div>
</body>
</html>
