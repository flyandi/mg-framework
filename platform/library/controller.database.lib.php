<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Database Controller
	*/
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	// Configuration Options
	define("DB_CONFIG", "@DB_CONFIG");
	define("DB_OPTION_SHOWSQL", false);
	define("DB_OPTION_LOGSQL", false);
	
	// Table Definitions
	define("DB_TABLE_USERS", "users");
	define("DB_TABLE_SESSION", "sessions");
	define("DB_TABLE_PERSONALITY", "personality");
	define("DB_TABLE_API", "api");
	define("DB_TABLE_APIAUTH", "apiauth");
	define("DB_TABLE_STATISTICS", "statistics");
	define("DB_TABLE_OPTIONS", "options");
	define("DB_TABLE_ASSETS", "assets");
	
	// Field Definitions
	define("DB_FIELD_IDSTRING", "idstring");
	define("DB_FIELD_IDADDRESS", "idaddress");
	define("DB_FIELD_TEMPLATE", "idtemplate");
	define("DB_FIELD_RELATED", "idrelated");
	define("DB_FIELD_LOCALIZED", "idlocalized");
	define("DB_FIELD_USERID", "iduser");
	define("DB_FIELD_USER", "iduser");
	define("DB_FIELD_CUSTOMERID", "idcustomer");
	define("DB_FIELD_TOKEN", "idtoken");
	define("DB_FIELD_TYPE", "idtype");
	define("DB_FIELD_SESSION", "idsession");
	define("DB_FIELD_ADDRESS", "idaddress");
	define("DB_FIELD_STAMP", "idstamp");
	define("DB_FIELD_ENABLED", "active");
	define("DB_FIELD_USERNAME", "idusername");
	define("DB_FIELD_PASSWORD", "idpassword");
	define("DB_FIELD_ROLE", "role");
	define("DB_FIELD_STATUS", "status");
	define("DB_FIELD_VERIFY", "verify");
	define("DB_FIELD_META", "meta");
	define("DB_FIELD_STATSCODE", "code");
	define("DB_FIELD_SOURCE", "source");
	define("DB_FIELD_IDSOURCE", "idsource");
	define("DB_FIELD_MODULE", "module");
	define("DB_FIELD_NAME", "name");
	define("DB_FIELD_VALUE", "value");
	define("DB_FIELD_MODE", "mode");
	define("DB_FIELD_SECURED", "secured");
	define("DB_FIELD_CREATED", "created");
	define("DB_FIELD_UPDATED", "updated");
	define("DB_FIELD_HISTORY", "history");
	define("DB_FIELD_PATH", "path");
	define("DB_FIELD_AUTHOR", "idauthor");
	define("DB_FIELD_CATEGORY", "idcategory");
	define("DB_FIELD_SERVICE", "service");
	define("DB_FIELD_DESCRIPTION", "description");
	
	// Request Constants 
	define("DB_CREATE", -1);
	define("DB_SELECTALL", -2);
	define("DB_SELECT", -3);
	define("DB_SELECTOR", -4);
	define("DB_SELECTSQL", -5);
	define("DB_SELECTLIKE", -11);
	define("DB_DELETE", -6);
	define("DB_FROMSESSION", -7);
	define("DB_FROMUID", -8);
	define("DB_FROMUSERID", -8);
	define("DB_SELECTRAW", -10);
	define("DB_FULLTEXTSEARCH", -12);
	define("DB_SELECTNOT", -13);
	define("DB_SELECTRANDOM", -14);

		
	# MG Global Results
	define("DB_OK", 2);
	define("DB_NOTEXISTS", -1);
	define("DB_ERROR", -2);	
	define("DB_NOTSUPPORTED", -3);
	define("DB_DISABLED", -4);
	define("DB_CUSTOMRESULT0", -100);
	define("DB_CUSTOMRESULT1", -101);
	define("DB_CUSTOMRESULT2", -102);
	define("DB_USERDISABLED", -103);
	define("DB_INVALIDPASSWORD", -104);
	define("DB_INVALIDTOKEN", -110);
	define("DB_LARGETABLE_LIMITS", -200);
	define("DB_NOTAUTHORIZED", -300);
	
	// DBM Sort Patterns
	define("DB_SORTCOLUMN_ASC", "ASC");
	define("DB_SORTCOLUMN_DESC", "DESC");	
	
	// Enabled/Disabled for DB
	define("ENABLED", 1);
	define("DISABLED", 0);
	
	// maxs
	define("DB_MAXBUFFER", 1000);
	
	// value
	define("VALUE_NULL", "null");
	
	// history constants
	define("DB_HISTORY_CREATE", 0);
	define("DB_HISTORY_UPDATE", 1);
	define("DB_HISTORY_CUSTOM", 2);
	
	// simple file db constants
	define("DB_FORMAT_BLOB", 0);
	define("DB_FORMAT_JSON", 1);
	
	// search modes
	define("DB_SEARCHMODE_SOUNDEX", 1);
	define("DB_SEARCHMODE_FIELDS", "fields");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgDatabase, manages the database service
	class mgDatabase {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (local)
		private $framework;
		
		# ---------------------------------------------------------------------------------------------------------------
		# Constructor
		public function __construct($framework=false) {
			// set framework reference
			$this->framework = $framework;
			// get connection
			$connection = GetVar(CONNECTION, false);
			// check connection
			if(!$connection) {
				DieCriticalError("Database Connection");
				exit;
			}
			// get parameters
			$params = $connection->parameters;
			// run database
			@mysql_connect($params->host, $params->user, $params->password) or die($this->error(mysql_error()));
			@mysql_select_db($params->database) or DieCriticalError($this->error(mysql_error()));
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# error (work required here)
		public function error($e) {
			// set database error
			DieCriticalError("Database Error", $e, "Database");
		}
		

	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgDatabaseStream, base class for database	
	class mgDatabaseStream {
	
		# -------------------------------------------------------------------------------------------------------------------
		# (local stack)
		private $connection;			// connection
		private $totalcount = false;	// rowcount
		private $table;					// storage for table
		private $fields;				// storage for fields
		private $request = false;		// request, holds the request
		private $references = false;	// references
		private $limit = false;			// limits
		private $sort = false;			// sorts
		private $search = false;		// search
		private $largetable = false;	// indicates a large table
		
		# public
		public $unique = false;			// sets unique mode
		public $result = DB_ERROR;		// result, status of the last operation
		public $lastsql = "";			// last sql 
		public $forcewrite = Array();	// force write flag
		public $rows;					// storage for all current rows	
		public $searchmode;				// search mode
		public $searchmodeparams;		// search mode params
	
		# -------------------------------------------------------------------------------------------------------------------
		# (construct)
		public function __construct($table, $request=DB_CREATE, $references=false, $sort = false, $limit = false, $search = false) {
			// initialize parameters
			$this->connection = GetVar(CONNECTION);
			// initialize 			
			$this->table = sprintf("%s_%s", DefaultValue(@$this->connection->parameters->root, "mg"), $table);
			$this->request = $request;	
			$this->references = $references;
			$this->limit = $limit;
			$this->sort = $sort;
			$this->search = $search;
			// check request
			if($this->request == DB_CREATE) {
				$this->__create();
			}
			// initialize
			$this->__initialize();
		}		
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__initialize) initializes the intelligent driver
		private function __initialize() {
			// only apply to a stream
			if($this->unique===false) {
				// get total count
				$this->totalcount = $this->totalcount();
				// check for large table
				if($this->totalcount > DB_MAXBUFFER) {
					// set dynamic
					$this->largetable = true;
				}
			} 
			// read data
			$this->__read();		
		}
	
		# -------------------------------------------------------------------------------------------------------------------
		# (__create) creates an new item within the table context
		private function __create(){
			// create new guid
			$guid = CreateGUID();
			// set new references
			$this->references = Array(DB_FIELD_IDSTRING=>$guid);
			// change request mode
			$this->request = DB_SELECT;
			$this->unique = true;
			// execute statement
			return $this->__execute("INSERT INTO {table} (idstring) VALUES ('%s')", $guid);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__execute) builds the sql and executes it
		private function __execute(){
			// initialize
			$query = func_get_arg(0);
			// parse string
			foreach(Array("table"=>$this->table) as $n=>$v) {
				$query = str_replace(sprintf("{%s}", $n), $v, $query);
			}
			// get fields 
			$fields = func_get_args();
			// sanity check
			if(count($fields)>1) {
				// prepare
				array_shift($fields);
				// create
				$query = vsprintf($query, $fields);
			}
			// show query
			if(DB_OPTION_SHOWSQL) {
				echo sprintf("%s\n", $query);
			}
			if(DB_OPTION_LOGSQL) {
				DebugWrite($query);
			}
			// lastsql
			$this->lastsql = $query;
			// prepare query
			$rsql = @mysql_query($query) or DieCriticalError("Error: ".mysql_error(), $query);
			// parse result
			if(is_bool($rsql)) {
				$result = $rsql;
			} else {
				// get number of rows
				$result = mysql_num_rows($rsql);
				// check result, if less than 1000 rows, than it's ok to load the buffer
				if($result > DB_MAXBUFFER) {return $result;}
				// reset fields
				$this->fields = Array();
				// get fields
				for($i=0;$i<mysql_num_fields($rsql);$i++) {
					// get field
					$field = mysql_fetch_field($rsql, $i);
					// assign field
					$this->fields[] = (string)$field->name;
				}
				// reset internal rows
				$this->rows = Array();
				// get rows
				while($row = mysql_fetch_assoc($rsql)) {
					// pre process data
					switch(true) {
						case isset($row[DB_FIELD_HISTORY]): 
							$row[DB_FIELD_HISTORY] = $this->GetHistory(false, $row[DB_FIELD_HISTORY]);
							break;
					}
					$this->rows[] = $row;
				}				
			}			
			// return result
			return $result;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__buildquery) builds a query from an array
		private function __buildquery($values, $filter = " AND ", $compare = "=") {
			// check values
			if(!is_array($values)) return "";
			// initialize result
			$result = Array();
			// cycle values
			foreach($values as $n=>$v) {
				// pre format
				$p = true;
				switch($n) {
					case DB_FIELD_HISTORY: 
						// process correct
						if(is_array($v)) $v = serialize($v);
						$p = !is_string($v)||strlen($v)==0?false:true; 
						break;
				}
				// format data
				if($p===true) {
					$result[] = sprintf("%s%s'%s'", $n, $compare, @mysql_real_escape_string($v));
				}
			}
			// return result
			return implode($filter, $result);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__buildrequest) builds the fields based on references
		private function __buildrequest() {
			// initialize fields
			$fields = "";
			// build fields
			switch($this->request) {
				// (DB_SELECTRAW), raw
				case DB_SELECTRAW:
				// (DB_SELECTSQL), selects rows with custom sql
				case DB_SELECTSQL: $fields = is_string($this->references)?$this->references: null; break;
				// (DB_SELECTOR), selects using OR
				case DB_SELECTOR: $fields = $this->__buildquery($this->references, " OR "); break;
				// (DB_SELECTLIKE), selects using like
				case DB_SELECTLIKE: $fields = $this->__buildquery($this->references, " AND ", " LIKE "); break;
				// (DB_FULLTEXTSEARCH)
				case DB_FULLTEXTSEARCH:
					// build string
					$f = Array();
					if(is_string($this->search)&&strlen($this->search)) {
						// get fields
						$this->__readfields();
						// process fields
						if(is_array($this->fields)) {
							foreach($this->fields as $fn) {
								if(!in_array($fn, Array(DB_FIELD_PASSWORD, DB_FIELD_SERVICE, DB_FIELD_HISTORY))) {
									// build by searchmode
									switch($this->searchmode) {
										// (soundex)
										case DB_SEARCHMODE_SOUNDEX:
											// init
											$allow = true;
											// check range
											if(is_array($this->searchmodeparams)) {
												// switch by true
												switch(true) {
													case isset($this->searchmodeparams[DB_SEARCHMODE_FIELDS]):
														// allowed fields
														$allow = in_array($fn, $this->searchmodeparams[DB_SEARCHMODE_FIELDS]);
														break;
												}
											}
											// assign
											if($allow) {
												$f[] = sprintf("mgSearchSoundsLike('%s', %s, ' ')", $this->search, $fn);
											}
											break;
											
										// (normal/like)
										default:
											$f[$fn] = sprintf("%%%s%%", $this->search);
											break;
									}
								}
							}
						}
					} else if(is_array($this->search)) {
						$f = $this->search;
					}
					// test f
					if(is_array($f)&&count($f)!=0) {
						// prepare references
						$ref = is_array($this->references)&&count($this->references)!=0?sprintf(" AND (%s)", $this->__buildquery($this->references)):"";
						// switch by mode
						switch($this->searchmode) {
							// (soundex)
							case DB_SEARCHMODE_SOUNDEX:
								$fields = sprintf("(%s)%s", 
									implode(" OR ", $f),
									$ref
								);	
								break;
							// default
							default:
								$fields = sprintf("(%s)%s", 
									$this->__buildquery($f, " OR ", " LIKE "), 
									$ref
								);
								break;
						}
						// finalize
						break;
					}
				// (DB_SELECT), selects rows using AND
				case DB_SELECTRANDOM:
				case DB_SELECT: $fields = $this->__buildquery($this->references); break;
				// (DB_SELECTALL), no filter is set
				default: break;
			}
			// validate fields
			if($fields == null && $fields != false) {return false; } // statement failed
			// return fields
			return $fields;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__read) reads from the database
		private function __read($limits = false) {
			// check if this is a raw statement
			if($this->request == DB_SELECTRAW) {
				$result = $this->__execute($this->references);	
			} else {
				// get fields
				$fields = $this->__buildrequest();
				// check fields
				if($fields === false) return false;
				// check length
				if(strlen(trim($fields))==0) $fields = false;
				// create sort
				$sort = Array();
				if($this->sort!==false&&is_array($this->sort)) {
					foreach($this->sort as $c=>$s) {$sort[] = sprintf("%s %s", $c, $s);}
				}			
				// execute query
				$result = $this->__execute("SELECT %s FROM {table} %s %s %s", 
					"*",
					($fields!==false?"WHERE {$fields}":""),
					(count($sort)>0?sprintf("ORDER BY %s", implode(",", $sort)):""),
					$this->unique?"LIMIT 1":(is_array($limits)?sprintf("LIMIT %s, %s", $limits[0], $limits[1]):"")
				);
			}
			// validate result
			return $this->result = ($result===false?DB_ERROR:($result==0)?DB_NOTEXISTS:DB_OK);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__write) writes to the database
		private function __write($index=false) {	
			// sanity chheck
			if ($this->result!=DB_OK) return false;
			// prepare index
			if($this->unique){$index = 0;}
			// prepare object
			if (!isset($this->rows[$index])) return false;
			// get row
			$row  = $this->rows[$index];
			// get row id
			$id = DefaultValue(@$row[DB_FIELD_IDSTRING], false);
			// sanity check
			if(!$id) return false;
			// unset system fields
			unset($row[DB_FIELD_IDSTRING]);
			// execute query
			return $this->__execute("UPDATE {table} SET %s WHERE %s", $this->__buildquery($row, ","), $this->__buildquery(Array(DB_FIELD_IDSTRING=>$id)));
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__remove) deletes a row in the database
		private function __remove($index=false, $filters=false) {	
			// sanity chheck
			if ($this->result!=DB_OK) return false;
			// prepare index
			if($this->unique){$index = 0;}
			// prepare object
			if (!isset($this->rows[$index])) return false;
			// get row
			$row  = $this->rows[$index];
			// get row id
			$filters = is_array($filters)?$filters:Array(DB_FIELD_IDSTRING=>DefaultValue(@$row[DB_FIELD_IDSTRING], false));
			// execute query
			return $this->__execute("DELETE FROM {table} WHERE %s", $this->__buildquery($filters));
		}
		
		# (__readfields) 
		private function __readfields() {
			// execute query
			$l = @mysql_query(sprintf("SHOW COLUMNS FROM %s", $this->table));
			// check result
			if($l&&mysql_num_rows($l)>0) {
				// initialize
				$this->fields = Array();
				// cycle
				while ($row = mysql_fetch_assoc($l)) {
					$this->fields[] = (string)$row["Field"];
				}
			}
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# Read/Write Operations
		
		# Delete, deletes a row
		public function Delete($row=false, $filters=false){
			// test
			if(!$this->unique&&$row===false) {
				// loop
				for($i=0;$i<$this->rowcount();$i++) {
					$this->__remove($i, $filters);		
				}
				// return
				return true;
			} else {
				// execute
				return $this->__remove($row, $filters);		
			}
		}
		
		# Read, reads an field value from a row
		public function Read($row, $name = false, $default = false){
			// reasign fields if unique
			if($this->unique) {$default = $name; $name = $row; $row = 0;}
			// check big table
			if($this->largetable) {
				// reset rows
				$this->rows = Array();
				// execute read row
				$this->__read(Array($row, 1));
				// set result row
				$row = 0;
			} 
			// return row
			return isset($this->rows[$row])?($name===false?$this->rows[$row]:(isset($this->rows[$row][$name])?$this->rows[$row][$name]:$default)):$default;
		}
		
		# ReadAll, reads all parsed
		public function ReadAll($row = false, $combine = true, $default = Array()) {
			// init
			$result = $default;
			// set row
			if($this->unique) $row = 0;
			// check row
			$data = false;
			if(isset($this->rows[$row])) {
				$data = $this->rows[$row];
			} else {
				$data = $this->Read($row);
			}
			// check
			if($data !== false) {
				// get data and set result;
				$result = $data;
				// run data
				foreach($data as $name=>$value) {
					// check if string is serialized
					if($v = @unserialize($value)) {
						if($combine) {
							unset($result[$name]);
							$result = array_merge($result, $v);
						}  else {
							$result[$name] = $v;
						}
					}
				}
			}
			return $result;
		}
		
		# Write, writes an field value to the row
		public function Write($row, $name=false, $value=false, $publish=false){
			// reasign fields if unique
			if($this->unique) {$publish = $value; $value = $name; $name = $row; $row = 0;}
			// sanity check
			if(!isset($this->rows[$row])) {
				return false; // failed, row does not exists
			}
			// publish fix
			if(is_array($name)) {$publish = $value;}
			// initialize
			$values = is_array($name)?$name:Array($name=>$value);
			// write values
			
			foreach($values as $k=>$v) {
				// pre processing
				switch($k) {
					// do not write
					/*case DB_FIELD_HISTORY: 
						unset($values[$k]);
						break;*/
					// write
					default:
						if($this->isfield($k)) {
							$this->rows[$row][$k] = $v;		
						}
						break;
				}
			}
			// publish
			if($publish) {$this->Publish($row);}
			// return result
			return true;
		}
		
		# Publish, publishes the data to the database
		public function Publish($row=false){
			// unique check
			if($this->unique){$row=0;}
			// write row
			if($row!==false) {
				$this->__write($row);
			}
		}
		
		# RemoveFieldsData, removes any field data
		public function RemoveFieldsData($data) {
			// cycle
			foreach($this->fields as $field) {
				if(isset($data[$field])) unset($data[$field]);
			}
			// return
			return $data;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# Handling Functions
		public function isfield($name) {
			// check fields if name exists
			return in_array($name, $this->fields);
		}
		
		
		# -------------------------------------------------------------------------------------------------------------------
		# Property Bags
				
		# rowcount, returns the count of the rows
		public function rowcount(){
			if($this->largetable) {
				return $this->totalcount;
			} 
			return $this->unique?1:count($this->rows);
		}
		
		# totalcount, returns the total count of the rows
		public function totalcount() {
			// exit
			if(in_array($this->request, Array(DB_SELECTRAW, DB_SELECTSQL))) return 0;
			// get fields
			$fields = $this->__buildrequest();
			// check fields
			if(strlen($fields)==0) $fields = false;
			// create query
			$query = sprintf("SELECT COUNT(*) as cnt FROM %s %s", 
				$this->table,
				$fields!==false?sprintf("WHERE %s", $fields):""
			);
			// execute statement
			$sql = mysql_query($query) or DieCriticalError("Error: ".mysql_error(), $query);
			// check result
			if(mysql_num_rows($sql)==0) return 0;
			// return
			return mysql_result($sql, 0, "cnt");
		}
		
		# getrow, returns a full row
		public function getrow($index=0){if($this->unique){$index=0;} return isset($this->rows[$index])?$this->rows[$index]:false;}
		
		# getall, returns all rows
		public function getall($param0=false, $limit = false){
			if(is_array($this->rows)&&count($this->rows)!=0) {
				return $this->unique?$this->rows[0]:$this->rows;
			}
			// load all
			$result = Array();
			for($i=0;$i<$this->rowcount();$i++) {
				$result[] = $this->Read($i);
				if($limit!==false&&$i>= $limit) return $result;
			}
			return $result;
		}
		
		
		# -------------------------------------------------------------------------------------------------------------------
		# Slice Functions
		public function	getsliced($start, $count) {
			// create page
			$start = ($start-1) * $count;
			// check large table
			if($this->largetable) {
				// read date with limits
				$this->__read(Array($start, $count));
				// return rows
				return $this->rows;
			} else {	
				// return paged result
				return array_slice($this->getall(), $start<0?0:$start, $count); 
			}
		}
	
		# -------------------------------------------------------------------------------------------------------------------
		# FieldValue Support
	
		# (private) __processfieldvalue, processes a field value
		private function __processfieldvalue($mode, $row, $field, $name = false, $value=false, $publish=false){
			// initialize result
			$result = false;
			// get row
			$data = @trim($this->Read($row, $this->unique?false:$field));
			// get fields
			try {
				$fields = is_string($data)&&strlen($data)!=0?unserialize($data):false;
			} catch(Exception $e) {
				$fields = false;
			}
			// sanity check
			if(!is_array($fields)) {$fields = Array();}
			// transform unique
			if($this->unique){$value = $name;$name = $field;}
			// switch by mode
			switch($mode) {
				// write
				case 1: 
					// initialize values
					$values = is_array($name)?$name:Array($name=>$value);
					// store values
					foreach($values as $k=>$v) { 
						$fields[$k] = $v; 
					}
					// confirm
					$result = true;
					break;
				// read
				case 2: 
					// assign result
					$result = isset($fields[$name])?$fields[$name]:$value;
					break;					
				// delete
				case 3:
					if(isset($fields[$name])) {
						// delete field
						unset($fields[$name]);
						// confirm
						$result = true;
					}
					break;
				// append
				case 4: 
					break;
					
			}
			// check write
			if($mode!=2&&$result) {
				// exclude list
				$exclude = array_merge($this->fields, Array(DB_FIELD_SESSION, DB_FIELD_TOKEN, DB_FIELD_IDSTRING, DB_FIELD_RELATED, DB_FIELD_HISTORY, DB_FIELD_META, DB_FIELD_PASSWORD, DB_FIELD_USERNAME, DB_FIELD_USERID));
				foreach($fields as $name=>$value) {
					if(in_array($name, $exclude)) {
						unset($fields[$name]);
					}
				}
				// serialize data
				$data = serialize($fields);
				// write
				if($this->unique) {
					$result = $this->Write($row, $data, $name);
				} else {
					$result = $this->Write($row, $field, $data, $publish);
				}
			}
			// return result
			return $result;
		}
		
		# WriteFieldValue, sets an database value item
		public function WriteFieldValue($row, $field, $name=false, $value = false, $publish = false){
			return $this->__processfieldvalue(1, $row, $field, $name, $value, $publish);
		}
		
		# ReadFieldValue, gets an database value item
		public function ReadFieldValue($row, $field, $name = false, $default = false){
			return $this->__processfieldvalue(2, $row, $field, $name, $default);
		}

		# DeleteFieldValue, deletes and field value from the stack
		public function DeleteFieldValue($row, $field, $name = false, $publish=false){
			return $this->__processfieldvalue(3, $row, $field, $name, $publish);			
		}
		
		# AppendFieldData
		public function AppendFieldData($row, $field, $name = false, $publish = false) {
			return $this->__processfieldvalue(4, $row, $field, $name, $publish);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__get) magic
		public function __get($name) {
			// switch by name
			switch($name) {
				case "id": return $this->Read(DB_FIELD_IDSTRING); break;
				case "fields": return $this->fields; break;
				case "count": return $this->rowcount(); break;
				case "largetable": return $this->largetable; break;
			}
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (__toString) magic
		public function __toString() {
			return $this->status;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		#(CreateHistoryItem)			
		public function CreateHistoryItem($name, $description = false, $data = false, $type = DB_HISTORY_CUSTOM, $publish = true) {
			// check
			if(!$this->isfield(DB_FIELD_HISTORY)||!$this->unique) return false;
			// build replace
			$framework = GetVar(FRAMEWORK);
			$rowdata = $this->getall();
			// unset history
			unset($rowdata[DB_FIELD_HISTORY]);
			// check rowdata
			if(isset($rowdata[DB_FIELD_META])) {
				$meta = @unserialize($rowdata[DB_FIELD_META]);
				if(is_array($meta)) {
					unset($rowdata[DB_FIELD_META]);
					$rowdata = array_merge($meta, $rowdata);
				}					
			}			
			// replace
			$username = sprintf("%s %s", $framework->user->ReadMeta("user_firstname"), $framework->user->ReadMeta("user_lastname"));
			if(trim($username)=="") $username = $framework->user->Read(DB_FIELD_USERNAME);
			$replace = array_merge(is_array($data)?$data:Array(), is_array($rowdata)?$rowdata:Array(), Array(
				"loginuser"=>$framework->user->username,
				"loginuserfirstname"=>$framework->user->ReadMeta("user_firstname"),
				"loginuserlastname"=>$framework->user->ReadMeta("user_lastname"),
				"loginuserfullname"=>$username,
				"loginusername"=> $framework->user->Read(DB_FIELD_USERNAME)
			));
			// add
			$history = $this->Read(DB_FIELD_HISTORY);
			if(!is_array($history)) $history = Array();
			$t = time();
			$history[] = Array(
				"stamp"=>$t,
				"time"=>date("h:i:s a", $t),
				"date"=>date("Y-m-d", $t),
				"type"=>$type,
				"name"=>$name,
				"description"=>mgFillVariableString($description, $replace),
				"data"=>$data
			);
			// result
			
			return $this->Write(DB_FIELD_HISTORY, $history, $publish);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		#(CreateHistory)			
		public function GetHistory($row = false, $value = false) {
			// check
			if($value===false||!$this->isfield(DB_FIELD_HISTORY)) return false;
			// get
			if($this->unique) $row = 0;
			// get value
			$value = $value!==false?$value:$this->Read($row, DB_FIELD_HISTORY);
			// initialize
			$result = false;
			// get history
			$list = DefaultValue(@unserialize($value), false);
			// check
			if(is_array($list)) {
				// sort
				sort($list);
				// add
				$result = $list;
			}
			// return
			return $result;
		}		
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgDatabaseObject, base class for a single row	
	class mgDatabaseObject extends mgDatabaseStream {
	
		# -------------------------------------------------------------------------------------------------------------------
		#(constructor)			
		public function __construct($table, $request=DB_CREATE, $references=false) {
			// initialize request
			switch($request) {
				// (DB_FROMUSERID) reads from iduser
				case DB_FROMUSERID: 
					// assign new request
					$request = DB_SELECT;
					// build references
					$references = Array(
						DB_FIELD_USERID=>$references
					);
					break;
			
				// (DB_FORMSESSION), assigns from the session field
				case DB_FROMSESSION: 
					// assign new request
					$request = DB_SELECT;
					// build references
					$references = Array(
						DB_FIELD_SESSION=>$references, 
						DB_FIELD_ADDRESS=>GetRemoteAddress()
					); 
					break;
				// (DB_SELECTSQL), pass through
				case DB_SELECTSQL: break;
				// (DB_CREATE), pass through
				case DB_CREATE: break;
				// (DB_SELECT), select
				case DB_SELECTOR:
				case DB_SELECTLIKE:
				case DB_SELECT: 
					// check
					if(is_array($references)) {
						break;
					}
				// (default) select by id
				default: 
					// set references
					$references = Array(DB_FIELD_IDSTRING=>$request);
					// request
					$request = DB_SELECT;
					break;
					
			}
			// set unique
			$this->unique = true;
			// perform constructor
			parent::__construct($table, $request, $references);
		}

		
		
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgDatabaseSearch, performs a search on a database stream
	class mgDatabaseSearch extends mgDatabaseStream {
	
		# -------------------------------------------------------------------------------------------------------------------
		#(constructor)			
		public function __construct($table, $search, $references = false, $sort = false, $limit = false, $searchmode = false, $searchparams = false) {
			// set searchmode
			$this->searchmode = $searchmode;
			$this->searchmodeparams = $searchparams;
			// perform constructor
			parent::__construct($table, DB_FULLTEXTSEARCH, $references, $sort, $limit, $search);
		}		
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgDataExists, checks if a certain data exists
	function mgDataExists($table, $conditions) {
		
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgDatabaseHistoryItem, adds a history item to the row
	function mgDatabaseHistoryItem($table, $mode, $rel, $name, $description, $data = false) {
		// create object
		$db = new mgDatabaseObject($table, $mode, $rel);
		if($db->result == DB_OK) {
			return $db->CreateHistoryItem($name, $description, $data);
		}
		return false;
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgSimpleFileDatabase, manages a simple file based datbase
	class mgSimpleFileDatabase {
		# -------------------------------------------------------------------------------------------------------------------
		#(private)	
		private $database = Array();
		private $filename = false;
		private $format = DB_FORMAT_BLOB;
		
		# -------------------------------------------------------------------------------------------------------------------
		# (constructor)			
		public function __construct($filename, $format = DB_FORMAT_BLOB) {
			// assign filename
			$this->filename = $filename;
			$this->format = $format;
			// load
			if(file_exists($filename)) {
				// load data
				$data = file_get_contents($filename);
				// load database
				switch($format) {
					case DB_FORMAT_JSON: $this->database = @json_decode($data, true); break;
					default: $this->database = @unserialize($data); break;
				}
				// check
				if(!is_array($this->database)) {
					$this->database = Array();
				}
			}			
		}
	
		
		# -------------------------------------------------------------------------------------------------------------------
		# (Create) creates a item in the database
		public function Create($data = false) {
			// format data
			if(is_object($data)) $data = (array)$data; 
			// validate
			if(!is_array($data)) $data = Array();
			// create id
			$id = CreateGUID();
			// merge data
			$data = array_merge($data, Array(
				DB_FIELD_IDSTRING=>$id
			));
			// write data
			$this->database[$id] = $data;
			// run update
			$this->__update();
			// return id
			return $id;				
		}
				
		# -------------------------------------------------------------------------------------------------------------------
		# (Query) Runs an query, that is slow, so index based search with get is better
		public function Query($conditions, $first = false, $compact = false) {
			// initialize result
			$result = Array();
			// run conditions
			foreach($this->database as $id=>$row) {
				foreach($row as $name=>$value) {
					foreach($conditions as $key=>$pattern) {
						if(mgCompare($name, $key) && preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $value)) {
							// add index
							$row = array_merge($row, array(DB_FIELD_IDSTRING=>$id));
							// first occurance
							if($first) return $compact?$row:Array($id=>$row);
							// add to result
							if($compact) {
								$result[] = $row;
							} else {
								$result[$id]  = $row;
							}
						}
					}
				}
			}
			// return 
			return count($result)!=0?$result:false;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (Write) Writes a data set or all
		public function Write($id, $value = false, $param = false) {
			// initialize
			$data = false;
			// check id
			if(is_array($id)&&isset($id[DB_FIELD_IDSTRING])) {
				$data = $id;
				$id = $id[DB_FIELD_IDSTRING];
			} else {
				// get data
				$data = $this->Read($id);
				// verify
				$data = is_array($data)?$data:Array();
				if(is_array($value)) {
					$data = array_merge($data, $value);
				} else {
					$data[$value] = $param;
				}
			}
			// write
			if($data&&$this->Exists($id)) {
				$this->database[$id] = $data;
				$this->__update();
				return true;
			} 
			return false;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# __call
		public function __call($name, $args) {
			switch(strtolower($name)) {	
				// (getall)
				case "getall": return $this->database; break;
				// toArray() 
				case "toarray": return is_array($this->database)?$this->database:Array(); break;
				// Exists, checks if the item exists
				case "exists": return isset($this->database[$args[0]]); break;
				// Read/Get, returns the item
				case "read": case "get": return isset($this->database[$args[0]])?$this->database[$args[0]]:false; break;
				// Delete, deletes the item
				case "delete": 
					if($this->Exists($args[0])) {
						// delete
						unset($this->database[$args[0]]);
						// remove
						return $this->__update();
					} 
					return false;
					break;
					
				// update, creates and/or writes a data set
				case "update":
					// parse data
					$data = $args[0];
					// get id
					$id = is_array($data)&&isset($data[DB_FIELD_IDSTRING])?$data[DB_FIELD_IDSTRING]:false;
					// create or write
					if(!$id || !$this->Exists($id)) {
						$id = $this->Create($data);
					} else {
						// save data
						$this->Write($id, $data);
					}
					// return id
					return $id;
					break;
			}
		}
		

		# -------------------------------------------------------------------------------------------------------------------
		# (__update) writes the database
		private function __update($filename = false, $format = DB_FORMAT_BLOB) {
			// initialize
			if(!$filename) $filename = $this->filename;
			if(!$format) $format = $this->format;
			// create data
			switch($format) {
				case DB_FORMAT_JSON: $data = @json_encode($this->database); break;
				default: $data = @serialize($this->database); break;
			}
			// write data			
			return @file_put_contents($filename, $data);
		}		
	
	}
