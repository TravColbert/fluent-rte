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

class template {
	protected $templatepath = "templates/";
	protected $resultarray = array();
	protected $resultset;
	protected $maxlinenum;
	protected $placeholderregex = "|\{([^\}%]+)\}|";
	protected $subtemplateregex = "|\{%([^\{\}]+)%\}|";
	protected $directiveregex = "|^\/\*(.*)\*\/$|";
	protected $preprocessor;

	function __construct() {

	}

	public function get($string,$customtemplate) {
		if(!$string) {
			return "Empty record. Possibly an object deleted elsewhere.";
		}
		$this->resultset = new SimpleXMLElement($string);
		$elementtype = $this->resultset->children()->getName();

		if(!$customtemplate) {
			$this->templatefile = $this->templatepath.$this->retrieve_element("devicesetting/template",$this->resultset);
		} else {
			$this->templatefile = $this->templatepath.$customtemplate;
		}

		$templatefilepath = trim($this->templatefile);

		if(!file_exists($templatefilepath)) {
			echo "Templatefile ".$templatefilepath." not found";
			return false;
		}

		// Get the template
		$templatecontents = file($templatefilepath);
		$returnstring = $this->apply_template($templatecontents,$this->resultset);

		return $returnstring;
	}

	protected function retrieve_element($request,$element) {
		$result = $element->xpath($request);
		if($result) {
			while(list( , $node) = each($result)) {
				$item = $node->asXML()."\n";
			}
			$returnstring = strip_tags($item);

			return $returnstring;
		}
		return false;
	}

	protected function apply_template($templatecontents,$element) {
		$objectdata['objecttype'] = $element->children()->getName();
		if($directive = $this->get_directiveline(trim($templatecontents[0]))) array_shift($templatecontents);
		$fst_stage = array();
		$snd_stage = array();

		if(array_key_exists("placeholderregex",$directive)) 
			$this->placeholderregex = $directive["placeholderregex"];

		if(array_key_exists("subtemplateregex",$directive)) 
			$this->subtemplateregex = $directive["subtemplateregex"];

		if(array_key_exists("preprocessor",$directive)) {
			$this->preprocessor = trim(__SITE_PATH . "/" . $directive["preprocessor"]);
			if(!file_exists($this->preprocessor)) {
				echo "Preprocessor file ".$this->preprocessor." not found.\n";
				return false;
			}
			include($this->preprocessor);
		}

		for($count=0;$count<count($templatecontents);$count++) {
			if(preg_match_all($this->placeholderregex,$templatecontents[$count],$matches)) {
				/* Take out duplicates of the same key (e.g. same lookup
				 *  several times in the same line)
				 */
				$matches[0] = array_unique($matches[0]);
				$matches[1] = array_unique($matches[1]);
				$slots = array_keys($matches[0]);
				if(array_key_exists("repeat",$directive)) {
					foreach($element->children() as $child) {
						$workline = $templatecontents[$count];
						foreach($slots as $slot) {
							if(strpos($matches[1][$slot],"@")!==false && strpos($matches[1][$slot],"@")==0) {
								$workline = str_replace($matches[0][$slot],trim($objectdata[substr($matches[1][$slot],1)]),$workline);
							} else {
								$workline = str_replace($matches[0][$slot],trim($this->retrieve_element($matches[1][$slot],$child)),$workline);
							}
						}
						$fst_stage[] = $workline;
					}
				} else {
					$workline = $templatecontents[$count];
					foreach($slots as $slot) {
						if(strpos($matches[1][$slot],"@")!==false && strpos($matches[1][$slot],"@")==0) {
							$workline = str_replace($matches[0][$slot],trim($objectdata[substr($matches[1][$slot],1)]),$workline);
						} else {
							$workline = str_replace($matches[0][$slot],trim($this->retrieve_element($matches[1][$slot],$element)),$workline);
						}
					}
					$fst_stage[] = $workline;
				}
			} else {
				$fst_stage[] = $templatecontents[$count];
			}
		}

		/* For our second stage of pattern-matching we work from the
		 * fst_stage array
		 */

		for($count=0;$count<count($fst_stage);$count++) {
			if(preg_match($this->subtemplateregex,$fst_stage[$count],$include)) {
				$subtemplatefile = explode(":",$include[1]);
				/* Syntax for the include directive {%...%}:
				 * {%template_file:query:repeat_mode:startloop%}
				 * where:
				 * template_file = the template file
				 * query = basically the resource being looked up
				 * repeat_mode = block repeats the file as a block
				 * startloopat = loop this block starting at element X
				 *
				 * You can omit either field 2 and/or 3 by doing like
				 * this:  {%blah.template::block%}
				 * which means run blah.template in current resource
				 * context and repeat the template as a config block
				 */
				$subquery["template"] = $subtemplatefile[0];
				$subquery["q"] = $subtemplatefile[1];
				$subquery["repeat"] = $subtemplatefile[2];
				$subquery["startloopat"] = $subtemplatefile[3];
				if(isset($subquery) && $subquery["q"]!="") {
					$controller = new controller();
					$string = $controller->get($subquery);
					$snd_stage[] = str_replace($include[0],$string,$fst_stage[$count]);
				} elseif(!isset($subquery["q"]) || $subquery["q"]=="") {
					$subtemplatefilepath = trim($this->templatepath.$subquery["template"]);
					$subtemplatecontents = file($subtemplatefilepath);
					if(trim($subquery["repeat"])=="block") {
						$subcount=1;
						$startofloop=0;
						if($subquery["startloopat"]) $startofloop=$subquery["startloopat"];
						foreach($element->children() as $child) {
							if($subcount>=$startofloop) {
								$snd_stage[] = $this->apply_template($subtemplatecontents,$child);
							}
							$subcount++;
						}
					} else {
						$snd_stage[] = $this->apply_template($subtemplatecontents,$element);
					}
				}
			} else {
				$snd_stage[] = $fst_stage[$count];
			}
		}
		return implode($snd_stage);
	}

	protected function get_directiveline($string) {
		$returnarray = array();
		if(preg_match($this->directiveregex,$string,$directiveline)) {
			$temparray = explode(" ",trim($directiveline[1]));
			foreach ($temparray as $element) {
				$directive = explode("=",$element,2);
				$returnarray[trim($directive[0])] = trim($directive[1]);
			}
		}
		return $returnarray;
	}

	protected function header() {
		header ("Content-Type:text/xml");
		return true;
	}
}
