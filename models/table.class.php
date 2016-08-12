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

/**************** table Object Class *****************
 *
 * GETs a given table from DB's through the DB object
 *
 */

class table {
	var $myname = "Table Model";
	var $controller;
	protected $db;
	protected $queries = array(
		"default" => "select * from $1",

		"subscriber" => "select * from $1 order by username ASC",
		"line" => "select * from $1 order by device_id ASC",
		"devicetype" => "select * from $1 order by make, model ASC",
		"devicesetting" => "select * from line join subscriber on line.subscriber_id=subscriber.id join device on line.device_id=device.id join devicetype on device.devicetype_id=devicetype.id join domain on device.domain_id=domain.id order by line.linenum ASC",
		"sipdevice" => "select device.id, devicetype.make, devicetype.model, domain.domain, device.description, count(line.linenum) as totallines from device join devicetype on device.devicetype_id=devicetype.id join domain on device.domain_id=domain.id left join line on device.id=line.device_id group by device.id, devicetype.make, devicetype.model, domain.domain, device.description order by device.id",
		"codec" => "select * from $1 order by codec ASC",
		"codecgroup" => "select codecgroup.id, domain_id, domain, priority, codec_id, parameters, codec.description from codecgroup left join domain on codecgroup.domain_id=domain.id left join codec on codecgroup.codec_id=codec.id order by domain ASC, priority ASC",

		"create_subscriber" => "insert into subscriber ",
		"create_device" => "insert into device ",
		"create_devicetype" => "insert into devicetype ",
		"create_domain" => "insert into domain ",
		"create_line" => "insert into line ",
		"create_codec" => "insert into codec ",
		"create_codecgroup" => "insert into codecgroup ",

		"edit_subscriber" => "update subscriber set ",
		"edit_device" => "update device set ",
		"edit_devicetype" => "update devicetype set ",
		"edit_domain" => "update domain set ",
		"edit_line" => "update line set ",
		"edit_codec" => "update codec set ",
		"edit_codecgroup" => "update codecgroup set ",

		"delete_subscriber" => "delete from subscriber where ",
		"delete_device" => "delete from device where ",
		"delete_devicetype" => "delete from devicetype where ",
		"delete_domain" => "delete from domain where ",
		"delete_line" => "delete from line where ",
		"delete_codec" => "delete from codec where ",
		"delete_codecgroup" => "delete from codecgroup where ",
	);
	protected $request;
	protected $resultset;
	protected $truevalues = array("t","true","1","yes","y");
	var $xpathregex;

	function __construct($request,& $controller) {
		/* The '&' is a reference so we don't get a 'copy' of the
		 * controller but instead get a pointer to the existing one.
		 */
		$this->controller = $controller;
		$modelpath = $this->controller->getregistryentry("config/modelpath");
		$dbclass = $this->controller->getregistryentry("config/db");
		$mysettings = $this->controller->getregistryentry("config/dbsetting");
		require_once (__SITE_PATH."/".$modelpath.$dbclass.".class.php");
		$this->db = new $dbclass($mysettings);
		$this->xpathregex = $this->controller->getregistryentry("config/xpathregex");
		$this->request = $request;
	}

	public function get() {
		if(!preg_match($this->xpathregex,$this->request,$resource)) {
				$this->controller->log($this->myname . ": Couldn't parse GET string.",LOG_ERR);
				return false;
		}
		if(!$this->retrieve_obj($resource[1])) return false;
		$this->resultset = $this->to_xml($resource[1]);
		$result = $this->resultset->xpath(stripslashes(strtolower($this->request)));
		if($result) {
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

	public function post() {
		if(array_key_exists("action",$this->request)) {
			// action: create, import, delete, edit, retrieve
			$action = $this->request["action"]."_obj";
			/* Thus we end up with creat_obj, delete_obj...
			 * This will correspond to a func below.
			 */
			$this->request = $this->$action();
			return $this->get();
		} else {
			$this->controller->log($this->myname . ": No action specified for this POST",LOG_ERR);
			return false;
		}
	}

	/* Finds the correct SELECT query based on object(resource) type
	 * Queries the data-source (e.g. SQL) and returnes the data to
	 * an object property as a typical PHP array
	 */
	protected function retrieve_obj($resource) {
		$query = $this->get_query($resource);
		$resultset = $this->db->get($query);
		if(!$resultset) return false;
		$this->resultset = pg_fetch_all($resultset);
		return true;
	}

	protected function create_obj() {
		$resource = $this->request["resource"];
		//$db = new db($this->mydb);
		$query = $this->get_query("create_".$resource);
		$keyfield = strip_tags($this->controller->getregistryentry($resource."/keyfield",$resource));
		$fields = explode(",",strip_tags($this->controller->getregistryentry($resource."/fields",$resource)));

		$columns = " (";
		$values = " values(";
		for($count=0;$count<count($fields);$count++) {
			$field = explode(":",$fields[$count]);
			$columns .= $field[0];
			if($count < (count($fields)-1)) $columns .= ", ";
			if(array_key_exists($field[0],$this->request)) {
				if($field[0]==$keyfield && strlen($this->request[$field[0]])==0) {
					$this->request[$field[0]] = "DEFAULT";
				}
				if(!$field[1] || $field[1]=="string") {
					$values .= "'".$this->request[$field[0]]."'";
				} elseif($field[1]=="bool" || $field[1]=="boolean") {
					if($this->request[$field[0]]='t') {
						$values .= "true";
					} else {
						$values .= "false";
					}
				} else {
					$values .= $this->request[$field[0]];
				}
				if($count != (count($fields)-1)) $values .= ", ";
			} else {
				$values .= "DEFAULT";
				if($count != (count($fields)-1)) $values .= ", ";
			}
		}
		$columns .= ")";
		$values .= ")";
		$query .= $columns.$values;
		$this->controller->log($this->myname . ": create query is: ".$query ,LOG_DEBUG);
		$resultset = $this->db->get($query);
		return strip_tags($this->controller->getregistryentry($resource."/successresult",$resource));
	}

	/* import_obj creates objects in the database similar to create
	 * exept it uses SimpleXMLObjects as its input.
	 * This makes it good for file import/restore uses.
	 */
	protected function import_obj() {
		/*
		echo "<pre>";
		var_dump($this->request);
		echo "</pre>";
		*/
		$resource = $this->request["resource"];
		//$db = new db($this->mydb);
		if(!empty($this->request["importfile"])) {
			$importfile = $this->request["importfile"];
			$importdata = $this->controller->load($importfile);
		} elseif(!empty($this->request["importdata"])) {
			$importdata = new SimpleXMLElement($this->request["importdata"]);
		} else {
			$this->controller->log($this->myname . ": no usable XML data to import - quitting.",LOG_DEBUG);
			return false;
		}

		$keyfield = strip_tags($this->controller->getregistryentry($resource."/keyfield",$resource));
		$fields = explode(",",strip_tags($this->controller->getregistryentry($resource."/fields",$resource)));

		for($count=0;$count<count($fields);$count++) {
			$field = explode(":",$fields[$count]);
			$fields[$count] = $field[0];
			$fieldtypes[$field[0]] = $field[1];
		}

		if(!count($importdata->xpath($resource))) {
			echo "No valid objects to import<br>\n";
			return false;
		}
		
		foreach($importdata->children() as $importobject) {
			$query = $this->get_query("create_".$resource);
			$columns = " (";
			$values = " values(";
			
			$elementcount = count($importobject->children());
			
			$elementnum = 1;
			
			foreach($importobject->children() as $importelement) {
				if(in_array($importelement->getName(),$fields)) {
					if($importelement->getName()==$keyfield && $fieldtypes[$importelement->getName()]=="int") {
						$elementnum++;
						continue;
					}
					$columns .= $importelement->getName();
					if(trim(strip_tags($importelement->asXML()))!=null) {
						if($fieldtypes[$importelement->getName()]==null || $fieldtypes[$importelement->getName()]=="string" || $fieldtypes[$importelement->getName()]=="") {
							$values .= "'" . trim(strip_tags($importelement->asXML())) . "'";
						} elseif($fieldtypes[$importelement->getName()]=="bool" || $fieldtypes[$importelement->getName()]=="boolean" ) {
							if(in_array(trim(strip_tags($importelement->asXML())),$this->truevalues)) {
								$values .= "true";
							} else {
								$values .= "false";
							}
						} else {
							$values .= trim(strip_tags($importelement->asXML()));
						}
					} else {
						$values .= "DEFAULT";
					}

					if($elementnum < $elementcount) {
						$columns .= ", ";
						$values .= ", ";
					}
				}
				$elementnum++;
			}

			$columns .= ")";
			$values .= ")";
			$query .= $columns.$values;

			$this->controller->log($this->myname . ": create query is: ".$query ,LOG_DEBUG);
			$resultset = $this->db->get($query);
		}
		//echo "Resource is: ".$resource."<br>\n";
		return strip_tags($this->controller->getregistryentry($resource."/successresult",$resource));
	}

	protected function edit_obj() {
		$resource = $this->request["resource"];
		$query = $this->get_query("edit_".$resource);

		/* The trick with edit is determining which field changed
		 * We will use a hidden field in the originating form that will
		 * hold the original value.  So, this will progress through the
		 * fields and compare the new value with the old.
		 * If there are differences then we update the record.
		 */
		$keyfield = strip_tags($this->controller->getregistryentry($resource."/keyfield",$resource));
		$fields = explode(",",strip_tags($this->controller->getregistryentry($resource."/fields",$resource)));
		$column_to_change = array();
		for($count=0;$count<count($fields);$count++) {
			$field = explode(":",$fields[$count]);
			if($field[0] != $keyfield && $this->request[$field[0]]!=$this->request["orig_".$field[0]]) {
				switch($field[1]) {
					case "int":
						$column_to_change[] = " ".$field[0]."=".$this->request[$field[0]];
						break;
					case "bool":
						if(in_array(strtolower($this->request[$field[0]]),$this->truevalues)) {
							$column_to_change[] = " ".$field[0]."='t'";
						} else {
							$column_to_change[] = " ".$field[0]."='f'";
						}
						break;
					default:
						$column_to_change[] = " ".$field[0]."='".$this->request[$field[0]]."'";
						break;
				}
			}
		}

		for($count=0;$count<count($column_to_change);$count++) {
			$query .= $column_to_change[$count];
			if($count==(count($column_to_change)-1)) {
				/* if "id" is not an INT we need to quote it
				 * 2147483647 is limit of an int
				 */
				if(is_numeric($this->request["id"]) && $this->request["id"]<2147483647) {
					$query .= " where ".$keyfield."=".$this->request["id"];
				} else {
					$query .= " where ".$keyfield."='".$this->request["id"]."'";
				}
			} else {
				$query .= ", ";
			}
		}
		$this->controller->log($this->myname . ": edit query is: ".$query ,LOG_DEBUG);
		$resultset = $this->db->get($query);
		if(!$resultset) return false;
		return strip_tags($this->controller->getregistryentry($resource."/successresult",$resource));
	}

	protected function delete_obj() {
		$resource = $this->request["resource"];
		$query = $this->get_query("delete_".$resource);
		$keyfield = strip_tags($this->controller->getregistryentry($resource."/keyfield",$resource));
		$fieldlist = array();
		$fields = explode(",",strip_tags($this->controller->getregistryentry($resource."/fields",$resource)));
		foreach($fields as $field) {
			$f = explode(":",$field);
			$fieldlist[$f[0]] = $f[1];
		}
		if(isset($fieldlist[$keyfield]) && $fieldlist[$keyfield]=='int') {
			$query .= $keyfield."=".$this->request[$keyfield];
		} else {
			$query .= $keyfield."='".$this->request[$keyfield]."'";
		}

		$this->controller->log($this->myname . ": delete query is: ".$query ,LOG_DEBUG);
		$resultset = $this->db->get($query);
		if(!$resultset) return false;
		return strip_tags($this->controller->getregistryentry($resource."/successresult",$resource));
	}

	/* Input an object type (resource) and this will take whatever is in
	 * the object's $resultset and turn it into an XML object
	 * and returns the XML object
	 */
	protected function to_xml($table) {
		//$resultarray[$table] = pg_fetch_all($resultset);
		$resultarray[$table] = $this->resultset;
		$returnstring = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$returnstring .= "<root>\n";
		//$returnstring .= "<".$table."s>\n";
		for($count=0;$count<count($resultarray[$table]);$count++) {
			$returnstring .= "<".$table.">\n";
			foreach($resultarray[$table][$count] as $key => $value) {
				if(!$value) {
					$returnstring .= "\t<".$key."/>\n";
				} else {
					$returnstring .= "\t<".$key.">".$value."</".$key.">\n";
				}
			}
			$returnstring .= "</".$table.">\n";
		}
		//return new SimpleXMLElement($returnstring."</".$table."s>");
		return new SimpleXMLElement($returnstring."</root>");
	}

	/* Tries to return an appropriate DB query based on object
	 * (resource) requested
	 */
	protected function get_query($resource) {
		if(array_key_exists($resource,$this->queries)) {
			$query = $this->queries[$resource];
		} else {
			$query = $this->queries["default"];
		}
		$query = str_replace("$1",$resource,$query);
		$this->controller->log($this->myname . ": Using query: " . $query ,LOG_DEBUG);
		return $query;
	}
}
