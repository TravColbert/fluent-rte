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

/**************** logger Object Class *****************
 *
 * accepts strings to be logged to syslog and or console
 * Can be configured by log importance level
 *
 */

class logger {
	
	var $controller;

	function __construct($controller) {
		$this->controller = $controller;
		//$this->controller->getregistryentry();
	}
	
	function log($string, $loglevel=null) {
		if(!isset($loglevel)) $loglevel=LOG_ERR;
		syslog($loglevel,$string);
		return true;
	}

	/* 
		LOG_EMERG system is unusable
		LOG_ALERT action must be taken immediately
		LOG_CRIT critical conditions
		LOG_ERR error conditions
		LOG_WARNING warning conditions
		LOG_NOTICE normal, but significant, condition
		LOG_INFO informational message
		LOG_DEBUG debug-level message
	*/
}
