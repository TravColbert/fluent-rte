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

/**************** registry Object Class *****************
 *
 * Maintains a tree of references to other objects that
 * can be referred to to perform things.
 *
 */

class registry {
	var $myname = "Registry Model";
	var $controller;
	public $registry;

    /* * * * * * * __construct * * * * * * * *
     *
     * Accepts XPATH queries. 
     * Returns relevant XML chunk from registry 
     * file
     *
     */
    function __construct($registryfile,& $controller) {
		$this->controller = $controller;
    	// This gets the full registry
    	//$this->registry = $this->load($registryfile);
    	
    	// The registry's config will be put in $this->regconfig
    	$this->registry = $this->controller->load($registryfile);
    }

    /* * * * * * * get * * * * * * * *
     *
     * Accepts XPATH queries. 
     * Returns a simple string to caller
     *
     */
    function get($search,$resource=null) {
		/* You can give the registry a new registry file to load and 
		 * search for info.  This will be a resource.
		 * Fall back to the main registry file if you can't find the 
		 * requested 'resource.xml' file.
		 */
		if(isset($resource) && is_readable("configs/".$resource.".xml")) {
			// get the right registry file
			$tmpregistry = $this->controller->load("configs/" . $resource . ".xml");
			$result = $tmpregistry->xpath($search);
		} else {
			$result = $this->registry->xpath($search);
		}
		//$result = $this->registry->xpath($search);
		if($result) {
			while(list( , $node) = each($result)) {
				$returnstring .= $node->asXML()."\n";
			}
			return trim(strip_tags($returnstring));
		}
		return false;
	}
}
