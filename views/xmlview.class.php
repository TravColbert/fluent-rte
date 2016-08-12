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

/**************** table_to_xml Object Class *****************
 *
 * converts table data to XML
 *
 */

class xmlview {
	
	protected $resultarray;

    function __construct() {
    	
    }

    public function get($string,$customtemplate) {
        $this->header();
		//if($string)
        //	return "<view ".$modifier.">".$string."</view>";
        return $string;
        //return false;
    }
    
    protected function header() {
    	header ("Content-Type:text/xml");
    	return true;
    }

}
