<?php
	/*
		(mg) Framework

		Copyright (c) 1999-2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		User Controller
		Version		4.0.0 Generation BN-2010
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# (constants)
	define("USER_MODULENAME", "user");
	
	// (statistics)
	define("USER_STATS_LOGIN", 1);
	define("USER_STATS_LOGOUT", 2);
	define("USER_STATS_AUTHFAILED", 10);
	define("USER_STATS_AUTHFAILED_USERDISABLED", 11);
	define("USER_STATS_AUTHFAILED_INVALIDPASSWORD", 12);
	define("USER_STATS_AUTHTOKENFAILED", 20);
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgUserController, user controller
	class mgUserController extends mgDatabaseObject {
	
		# -------------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($param0=false,$param1=false) {
			if ($param0==false)return;
		
			// execute query
			parent::__construct(DB_TABLE_USERS, $param0, $param1);
			// result
			if($this->result === DB_OK){
				// set temp password
				if($param0 == DB_CREATE) {
					$this->Write(DB_FIELD_PASSWORD, CreateRandomPassword(20), true);
				}
				
			}
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) AuthToken
		public function AuthenticateToken($param0, $param1=false) {
			// check
			if(strlen($param0)==0) return false;
			// execute query
			parent::__construct(DB_TABLE_USERS, DB_SELECT, Array(DB_FIELD_TOKEN=>$param0));
			if ($this->result==DB_OK){
				// login user
				if($param1) {
					$this->Write(DB_FIELD_SESSION, $param1);
					$this->Write(DB_FIELD_ADDRESS, GetRemoteAddress());
					$this->Publish();
					// register statistics
					$this->__stats(USER_STATS_LOGIN, Array("method"=>"token"));
				}
			} else {
				// return result
				$this->result = DB_INVALIDTOKEN;
				// register statistics
				$this->__stats(USER_STATS_AUTHTOKENFAILED, Array("token"=>$param0));
			}
			return $this->result;
			
		}
		
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) AuthenticateCredentials, autentifcate as user credentials
		public function AuthenticateCredentials($param0,$param1,$param2=false) {
			// create result
			$result = false;
			// execute query
			parent::__construct(DB_TABLE_USERS, DB_SELECT, Array(DB_FIELD_USERNAME=>$param0));
			// check result
			if ($this->result==DB_OK){
				if ($this->Read(DB_FIELD_PASSWORD)!=$param1) {
					$result = DB_INVALIDPASSWORD;
				} else if ($this->Read(DB_FIELD_ENABLED)==DISABLED){
					$result = DB_USERDISABLED;
				} else {
					// login user
					if($param2) {
						// login user
						$this->Write(DB_FIELD_SESSION, $param2);
						$this->Write(DB_FIELD_ADDRESS, GetRemoteAddress());
						$this->Publish();
					}
					$this->__updatelogin();
					// register statistics
					$result = $this->result;
				}
			} else {
				$result = $this->result;
			}
			// get statistic code
			switch($result) {
				case DB_INVALIDPASSWORD: $statscode = USER_STATS_AUTHFAILED_INVALIDPASSWORD; break;
				case DB_USERDISABLED: $statscode = USER_STATS_AUTHFAILED_USERDISABLED; break; 
				default: $statscode = $result==DB_OK?USER_STATS_LOGIN:USER_STATS_AUTHFAILED; break;
			}			
			// statistics
			$this->__stats($statscode, array_merge(Array("method"=>"credentials"), $result!=DB_OK?Array("username"=>$param0, "password"=>$param1):Array()));
			// return result
			return $result;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (private) __updatelogin information
		private function __updatelogin() {
			$this->WriteMeta("logincounter", (integer)$this->ReadMeta("logincounter", 0) + 1, false);
			$this->WriteMeta("lastlogin", date("F j, Y h:i a"));
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) Login, sign in the user by session
		public function Login($session){
			// login user
			$this->Write(DB_FIELD_SESSION, $session);
			$this->Write(DB_FIELD_ADDRESS, GetRemoteAddress());
			$this->Publish();
			$this->__updatelogin();
			// register statistics event
			$this->__stats(USER_STATS_LOGIN, Array("method"=>"credentials"));
		}
		
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) Verify, verifies the user session
		public function Verify($param0){
			parent::__construct(DB_TABLE_USERS, DB_FROMSESSION, $param0);
			return ($this->result===DB_OK);
		}

		# -------------------------------------------------------------------------------------------------------------------
		# (public) Logout, logout the user
		public function Logout() {
			$this->Write(DB_FIELD_SESSION, "");
			$this->Write(DB_FIELD_ADDRESS, "");
			$this->Publish();
			// update stats
			$this->__stats(USER_STATS_LOGOUT, Array("method"=>"credentials"));
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) IsVerified, returns true of the user has completed the verification
		public function IsVerified() {
			// check string
			return $this->Read("verify")==VERIFY_CONFIRMED?true:false;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (property) __get
		public function __get($name) {return $this->__property($name);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (property) __set
		public function __set($name, $value) {return $this->__property($name, $value);}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __property, reads/write property (global/meta)
		private function __property($name, $value=null) {
			// sanity check
			if(!is_string($name)) return;
			// initialize global properties
			$globalproperties = Array("role"=>DB_FIELD_ROLE, "session"=>DB_FIELD_SESSION, "id"=>DB_FIELD_IDSTRING, "userid"=>DB_FIELD_IDSTRING, "username"=>DB_FIELD_USERNAME);
			// check against global properties
			if(array_key_exists($name, $globalproperties)){return ($value==null)?$this->Read(@$globalproperties[$name]):$this->Write(@$globalproperties[$name], $value, true);}
			// return meta property
			return ($value==null)?$this->ReadMeta($name):$this->WriteMeta($name, $value);
		}		
				
		# -------------------------------------------------------------------------------------------------------------------
		# (public) WriteMeta, writes user meta data
		public function WriteMeta($name, $value = false, $publish=true){
			$this->WriteFieldValue("meta", $name, $value);
			$this->WriteFieldValue("meta", USER_META_UPDATE, time());
			if($publish) {$this->Publish();}
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) ReadMeta, reads user meta data
		public function ReadMeta($name, $default = ""){
			return $this->ReadFieldValue("meta", $name, $default);
		}	
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) WriteAsset, writes an asset (file storage)
		public function WriteAsset($name, $data) {
			return mgWriteAsset($this->id, $name, $data);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) ReadAsset, reads an asset (file storage)
		public function ReadAsset($name, $default = false) {
			return mgReadAsset($this->id, $name, $default);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (private) __stats, registers into statistics
		private function __stats($code, $more = Array()) {
			if(!function_exists("mgStatisticEvent")) return;
			// create meta
			$meta = Array(DB_FIELD_USERID=>$this->id);
			// create extra meta
			switch($code) {
				case USER_STATS_LOGIN:
					$meta[DB_FIELD_SESSION]=$this->session;
					break;
			}
			// write event
			mgStatisticEvent(USER_MODULENAME, $code, array_merge($meta, $more));
		}
		
	}

	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgGetActiveUsername, returns the active username
	function mgGetActiveUsername($returnobject=false) {
		$m = new mgUserController(DB_FROMSESSION, GetVar("session"));
		if($m->result==DB_OK) {
			return $returnobject?$m:$m->username;
		}
		return false;
	}

	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgUserExists, checks if the given user exists by ID
	function mgUserExists($param0){$p=new mgUserController($param0); return ($p->result==DB_OK);}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgUserNameExists, checks if the given user exists by NAME
	function mgUserNameExists($param0){$p=new mgDatabaseObject(DB_TABLE_USERS, DB_SELECT, Array(DB_FIELD_USERNAME=>$param0)); return ($p->result==DB_OK);}	

	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgVerifyUser, verifies the user e-mail
	function mgVerifyUser($verify) {
		$p = new mgDatabaseObject(DB_TABLE_USERS, DB_SELECT, Array("verify"=>$verify));
		if($p->result==DB_OK){
			$p->Write("verify", VERIFY_CONFIRMED, true); 
			return true;
		}
		return false;
	}
	
	
?>