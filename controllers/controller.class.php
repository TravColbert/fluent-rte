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

/**************** controller Object Class *****************
 *
 * queries from a datasource and passes to a renderer
 *
 */

class controller {

	var $myname;
	var $modelpath;
	var $viewpath;
	var $logobject;

	private $resultset;
	var $registry;
	var $logger;
	var $xpathregex;

	function __construct() {
		require_once (__SITE_PATH . "/configs/registry.class.php");
		$this->registry = new registry(__SITE_PATH . "/configs/registry.xml",$this);
		$this->myname = $this->getregistryentry("config/appname");
		$this->modelpath = $this->getregistryentry("config/modelpath");
		$this->viewpath = $this->getregistryentry("config/viewpath");
		$this->logobject = $this->getregistryentry("config/logger");
		require_once (__SITE_PATH . "/" . $this->modelpath . $this->logobject . ".class.php");
		$this->logger = new $this->logobject($this);
		$this->xpathregex = $this->getregistryentry("config/xpathregex");
	}

    /* * * * * * * get * * * * * * * *
     *
     * Accepts XPATH queries.
     * From the query it figures out where the
     * datasource shall be, what the view is then
     * returns output from a view
     *
     */
	public function get($querystring) {
		// 'q' specifies the xpath 'query' part
		if(array_key_exists("q",$querystring)) {
			if(!preg_match($this->xpathregex,$querystring["q"],$resource)) {
				$this->log($this->myname . ": No applicable query found",LOG_ERR);
				return false;
			};
			// Let's get the actual data that feeds the template:
			$modelinfo = $this->select_model($resource[1],"GET");
			// be sure to include the query in the model's data
			$modelinfo["querystring"] = $querystring["q"];
		} else {
			$this->log($this->myname . ": No querystring found",LOG_ERR);
			return false;
		}
		$modelinfo = $this->readmodel($modelinfo);
		$template = null;
		// Some queries (like 'devicesetting') have a template value embedded in it
		// let's ask for that template if no specific template is asked for in the
		// query...
		//if(array_key_exists("template",$modelinfo)) $template = $modelinfo["template"];
		if(array_key_exists("template",$querystring)) $template = $querystring["template"];
		if($template=="auto") {
			$this->log($this->myname . "get(): model-specified auto-template: " + $modelinfo["template"], LOG_DEBUG);
			//$viewinfo["customtemplate"] = $modelinfo["template"];
			$viewinfo = $this->select_view("GET",$modelinfo["template"]);
		} else {
			$viewinfo = $this->select_view("GET",$template);
		}
		return $this->start_chain($modelinfo,$viewinfo);
	}

	public function post($request) {
		if(!array_key_exists("resource",$request)) {
			$this->log($this->myname . ": No datasource specified",LOG_ERR);
			return false;
		}
		$this->log("post(): " . $request["resource"], LOG_DEBUG);
		$modelinfo = $this->select_model($request["resource"],"POST");
		// be sure to include the query in the model's data
		$modelinfo["querystring"] = $request;
		$modelinfo = $this->readmodel($modelinfo);

		$viewinfo = $this->select_view("GET",$request["template"]);
		return $this->start_chain($modelinfo,$viewinfo);
	}

	protected function readmodel($modelinfo) {
		if(file_exists($modelinfo["url"])) {
			require_once $modelinfo["url"];
		} else {
			$this->log($this->myname . ": Can't find specified model class",LOG_ERR);
			return false;
		}
		$model = new $modelinfo["datasource"]($modelinfo["querystring"],$this);
		$modelAction = $modelinfo["verb"];
		$modelinfo["data"] = $model->$modelAction();
		$this->log("start_chain(): Model Action: " . $modelAction,LOG_DEBUG);
		$this->log("start_chain(): Model: " . $modelinfo["data"],LOG_DEBUG);
		$this->log("start_chain(): Model: " . $modelinfo["data"],LOG_ERR);
		$modelinfo["XML"] = new SimpleXMLElement($modelinfo["data"]);
		$templates = $modelinfo["XML"]->xpath('devicesetting/template');
		if($templates) {
			if(count($templates)>0) {
				$this->log("start_chain(): >>>>" . $templates[0], LOG_DEBUG);
				$modelinfo["template"] = $templates[0];
			}
		}
		return $modelinfo;
	}

	public function getregistryentry($searchstring,$resource=null) {
		return $this->registry->get($searchstring,$resource);
	}

	public function log($string,$loglevel=null) {
		return $this->logger->log($string,$loglevel);
	}

	protected function start_chain($modelinfo,$viewinfo) {
		if(file_exists($viewinfo["outputsource"])) {
			require_once $viewinfo["outputsource"];
		} else {
			$this->log($this->myname . ": Can't find specified template",LOG_ERR);
			return false;
		}
		$this->log("start_chain(): View outputtype(): " . $viewinfo["outputtype"]);
		$view = new $viewinfo["outputtype"]();
		$this->log($this->myname . ": Starting model -> view chain",LOG_DEBUG);
		$this->log("start_chain(): View: " . $viewinfo["customtemplate"],LOG_DEBUG);
		$modelAction = $modelinfo["verb"];
		return $view->get($modelinfo["data"],$viewinfo["customtemplate"]);
		//return $view->get($modelData,$viewinfo["customtemplate"]);
	}

	/* select_model is used to determine which model object will be
	 * employed.  Give this function the "type" of resource you have
	 * requested (e.g. subscriber, line, devicesetting...)
	 *
	 * $verb - we are building-in the notion of different models
	 * depending on the HTTP "verb" used (think "REST" systems). So, for
	 * verb we pass on either POST, GET, PUT, DELETE
	 */
	protected function select_model($resource,$verb) {
		$model["datasource"] = $this->getregistryentry($resource . "/datasource[@verb='" . $verb . "']/object",$resource);
		$model["url"] = $this->modelpath . $this->getregistryentry($resource . "/datasource[@verb='" . $verb . "']/url",$resource);
		$model["verb"] = strtolower($verb);
		$arraystring = join(", ",array_keys($model));
		$this->log($this->myname . ": Model class is: " . $model["url"],LOG_DEBUG);
		$this->log($this->myname . ": Model: " . $arraystring, LOG_DEBUG);
		return $model;
	}

	protected function select_view($verb,$resource=null) {
		/* We have 3 possibilities for view setting:
		 * 1. The default view set in the registry for the resource/verb
		 * 2. the default view for the whole system (generally raw xml)
		 * 3. something special you specify
		 */
		$customtemplate = null;
		if(isset($resource)) {
			// views are always verb type GET
			$outputtype = $this->getregistryentry("template[@verb='GET']/output");
			if($resource!="") {
				$customtemplate = $resource;
			}
		} else {
			$outputtype = "xmlview";
		}

		$outputsource = $this->viewpath.$outputtype.".class.php";

		$view["outputsource"] = $outputsource;
		$view["outputtype"] = $outputtype;
		$view["customtemplate"] = $customtemplate;

		$this->log($this->myname . ": View class is: " . $view["outputsource"],LOG_DEBUG);
		return $view;
	}

    /* * * * * * * load * * * * * * * *
     *
     * Reads xml file and creates a
     * SimpleXML element.
     *
     */

	function load($registryloc) {
		$string = file_get_contents($registryloc);
		$xmlfile = new SimpleXMLElement($string);
		return $xmlfile;
	}
}
