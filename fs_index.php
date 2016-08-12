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

/*** define the site path constant ***/
$site_path = realpath(dirname(__FILE__));
define ('__SITE_PATH', $site_path);
 
require_once(__SITE_PATH . "/controllers/controller.class.php");
$controller = new controller();

switch($_SERVER['REQUEST_METHOD']) {
	case "GET":
		handle_get($controller);
	break;
	case "POST":
		handle_post($controller);
	break;
}

function handle_get($controller) {
	if(!array_key_exists("q",$_REQUEST)) {
		echo "No query found";
		return false;
	}
	$string = $controller->get($_REQUEST);
	echo $string;
	return true;
}

// We convert FS's POSTs into GETs and add the necessary elements to complete
// a request.

function handle_post($controller) {
	if(array_key_exists("action",$_REQUEST) && $_REQUEST["action"]=="sip_auth" && array_key_exists("user",$_REQUEST)) {
		// Add the all-important 'q' string:
		$_REQUEST["q"]="subscriber[username='".$_REQUEST["user"]."']";
		$_REQUEST["template"]="freeswitch.account.template";
	}
	// We use the controller's GET method to retrieve data for Freeswitch
	$string = $controller->get($_REQUEST);
	header('Content-Type: text/xml');
	echo $string;
	return true;
}
