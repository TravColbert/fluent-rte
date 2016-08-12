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

/**************** xmlfile Object Class *****************
 *
 * Model that uses XML files as datasource
 *
 */

class xmlfile {
	var $myname = "XML File Model";
	var $controller;
	protected $modelpath;
	protected $xmldatapath;
	protected $xmldata;
	protected $request;
	protected $truevalues = array("t","true","1","yes","y");
	var $xpathregex;

    /* * * * * * * __construct * * * * * * * *
     *
     * Accepts XPATH queries. 
     * Returns relevant XML chunk from XML 
     * file
     *
     */
	function __construct($request,& $controller) {
		/* The '&' is a reference so we don't get a 'copy' of the
		 * controller but instead get a pointer to the existing one.
		 */
		$this->controller = $controller;
		$this->modelpath = $this->controller->getregistryentry("config/modelpath");
		$this->xmldatapath = $this->controller->getregistryentry("config/xmldatapath");
		$this->xpathregex = $this->controller->getregistryentry("config/xpathregex");
		$this->request = $request;
	}

	/* * * * * * * get * * * * * * * *
	 *
	 * Returns a SimpleXML object to caller
	 *
	 */
	function get() {
		if(!preg_match($this->xpathregex,$this->request,$resource)) {
			$this->controller->log($this->myname . ": Couldn't parse GET string.",LOG_ERR);
			return false;
		}
		/* At this point $resource[1] should contain a valid Fluent
		 * object type (e.g. subscriber, domain, devicesetting, etc).
		 * 
		 * You can give the registry a new registry file to load and 
		 * search for info.  This will be a resource.
		 * Fall back to the main registry file if you can't find the 
		 * requested 'resource.xml' file.
		 */

		if(isset($resource[1]) && is_readable($this->xmldatapath . $resource[1]. ".xml")) {
			$this->controller->log($this->myname . ": Found XML data file: " . $this->xmldatapath . $resource[1] . ".xml",LOG_DEBUG);
			// get the right XML file
			$this->xmldata = $this->controller->load($this->xmldatapath . $resource[1] . ".xml");
			$result = $this->xmldata->xpath($this->request);
		} else {
			$this->controller->log($this->myname . ": Couldn't get XML file: " . $this->xmldatapath . $resource[1] . ".xml",LOG_ERR);
			return false;
		}
		if($result) {
			$this->controller->log($this->myname . ": Results found",LOG_DEBUG);
			//$returnstring = "<".$resource[1]."s>\n";
			$returnstring = "<root>\n";
			while(list( , $node) = each($result)) {
				$returnstring .= $node->asXML()."\n";
			}
			$returnstring .= "</root>\n";
			//$returnstring .= "</".$resource[1]."s>\n";
			return $returnstring;
		}
		return false;
	}
}
