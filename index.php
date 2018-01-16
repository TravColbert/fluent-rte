<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 retab: */

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
	echo $controller->get($_REQUEST);
	return true;
}

function handle_post($controller) {
	// For object creation and updates(at the moment)
	echo $controller->post($_REQUEST);
	return true;
}
