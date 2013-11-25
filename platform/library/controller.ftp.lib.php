<?php
	/*
		MobilesiteGuru project (mg)	Version 6.0
		
		Copyright (c) 1998 - 2009 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		FTP Controller
		Version		1.0
		
		Last Changes
		2009/02/19	Andi		Initial
	*/
	

	define("FTP_NORMAL", 0);
	define("FTP_SECURE", 1);
	define("FTP_DIRECTORYDEPTH", 10);
	
	define("FTP_ERROR_GENERAL", 100);
	define("FTP_ERROR_HOSTNOTFOUND", 102);
	define("FTP_ERROR_AUTH", 103);
	
	define("FTP_PATH_DIRS", 0);
	define("FTP_PATH_FILES", 1);
	
	define("FTP_DEFAULTPORT", 21);

	class mgFTPController {

		# --
		# local variables
		
		// Session Information
		private $s_host;
		private $s_port;
		private $s_username;
		private $s_password;
		
		
		// Internal Storages
		private $m_mode;			// connect mode 
		
		// Socket Storage
		private $s_socket;			// socket id

		# ------------------------------------------------------------------------------------------------------
		# __construct
		#   param0 - host
		#   param1 - username
		#   param2 - password
		#   param3 - mode
		#   param4 - port
		public function __construct($param0, $param1, $param2, $param3=FTP_NORMAL, $param4=FTP_DEFAULTPORT) {
			// assign variables
			$this->s_host = $param0;
			$this->s_port = $param4;
			$this->s_username = $param1;
			$this->s_password = $param2;
			$this->m_mode = $param3;
			// initialize
			$this->s_socket = false;
		}
		
		# ------------------------------------------------------------------------------------------------------
		# Connect
		public function Connect() {
			// prepare
			if($this->s_socket===false){$this->Disconnect();}
			// execute
			switch($this->m_mode){
				// secure FTP
				case FTP_SECURE: $this->socket = @ftp_ssl_connect($this->s_host, $this->s_port); break;
				// normal FTP
				default:	$this->s_socket = @ftp_connect($this->s_host, $this->s_port); break;
			}
			// evaluate
			if($this->s_socket===false){return FTP_ERROR_HOSTNOTFOUND;}
			
			// start login
			$l = ftp_login($this->s_socket, $this->s_username, $this->s_password);
			if(!$l){return FTP_ERROR_AUTH;}
			
			return true;
		}
		
		# ------------------------------------------------------------------------------------------------------
		# Disconnect
		public function Disconnect() {
			@ftp_close($this->s_socket);
			$this->s_socket = false;
			return true;
		}
		
		# ------------------------------------------------------------------------------------------------------
		# Passive
		public function Passive($param=true) {
			if($this->s_socket){return @ftp_pasv($this->s_socket, $param);}
			return false;
		}	
		
		# ------------------------------------------------------------------------------------------------------
		# Macro(IsDirectory())		
		public function IsDirectory($resource) {
			if(!$this->s_socket){return false;}
			return (ftp_size($this->s_socket, $resource) == '-1');
		}			
		
		# ------------------------------------------------------------------------------------------------------
		# Macro(Get())			
		public function Get($remotefile, $localfile) {
			if(!$this->s_socket){return false;}
			return @ftp_get($this->s_socket, $localfile, $remotefile, FTP_BINARY);
		}
		
		# ------------------------------------------------------------------------------------------------------
		# Macro(Put())			
		public function Put($remotefile, $localfile) {
			if(!$this->s_socket){return false;}
			return @ftp_get($this->s_socket, $remotefile, $localfile, FTP_BINARY);
		}		
		
		
		# ------------------------------------------------------------------------------------------------------
		# Macro(GetDirectoryEx()), returns extended information about the current directory	
		public function GetDirectoryEx($directory="/") {
			if(!$this->s_socket){return false;}
			$items = Array();
			
			$filetypes = array('-'=>'file', 'd'=>'directory', 'l'=>'link');
			
			$data = @ftp_rawlist($this->s_socket, $directory);
			
			foreach($data as $line) {
				if (substr(strtolower($line), 0, 5) == 'total') continue; # first line, skip it
				preg_match('/'. str_repeat('([^\s]+)\s+', 7) .'([^\s]+) (.*)/', $line, $matches); # Here be Dragons
				list($permissions, $children, $owner, $group, $size, $month, $day, $time, $name) = array_slice($matches, 1);
				# if it's not a file, directory or link, I don't really care to know about it :-) comment out the next line if you do
				if (! in_array($permissions[0], array_keys($filetypes))) continue;
				$type = $filetypes[$permissions[0]];
				$files[$name] = array('type'=>$type, 'permissions'=>substr($permissions, 1), 'children'=>$children, 'owner'=>$owner, 'group'=>$group, 'size'=>$size);
			}

			
	/*
			$list   = @ftp_rawlist($this->s_socket, $directory);

			foreach ($list as $_) {
				
				preg_replace('`^(.{10}+)(\s*)(\d{1})(\s*)(\d*|\w*)(\s*)(\d*|\w*)(\s*)(\d*)\s([a-zA-Z]{3}+)(\s*)([0-9]{1,2}+)(\s*)([0-9]{2}+):([0-9]{2}+)(\s*)(.*)$`Ue',
						'$items[]=array(
							"rights"=>"$1", 
							"number"=>"$3", 
							"owner"=>"$5", 
							"group"=>"$7", 
							"file_size"=>"$9", 
							"mod_time"=>"$10 $12 $14:$15", 
							"file"=>"$17", 
							"type"=>(preg_match("/^d/","$1"))?"dir":"file",1);',
							$_);
			}
			// transform 
			$result = Array(FTP_PATH_DIRS=>Array(), FTP_PATH_FILES=>Array());
			reset($items);
			foreach($items as $_) {
				
				$cap = trim($_["file"]);
				if($_["type"]=="dir"&&($cap!=".."&&$cap!=".")){
					$result[FTP_PATH_DIRS][] = $_;
				} else if($_["type"]=="file"){
					$result[FTP_PATH_FILES][] = $_;
				}
			}
			
			echo "<pre>";
			print_r($result);
			
			exit;*/
			
			return $items;
		}
		
		
		# ------------------------------------------------------------------------------------------------------
		# Macro(GetDirectory())		
		public function GetDirectory($directory="/") {
			if(!$this->s_socket){return false;}
			$result = Array(FTP_PATH_DIRS=>Array(), FTP_PATH_FILES=>Array());
			$newdir = @ftp_nlist($this->s_socket, $directory);
			foreach ($newdir as $key => $x) {
				// filter dir's and files
				if(ftp_size($this->s_socket, $x)!='-1') {
					$result[FTP_PATH_FILES][] = $x;
				} else if ((strpos($x,".")) || (strpos($x,".") === 0)){
					unset($newdir[$key]); 	
				} else {
					$result[FTP_PATH_DIRS][] = $x;
				}
			}
			return $result;
		}

		# ------------------------------------------------------------------------------------------------------
		# Macro(GetDirectories())
		public function GetDirectories($depth=FTP_DIRECTORYDEPTH) {
			if(!$this->s_socket){return false;}
			$result = array(".");
			$a = count($result);
			$i = 0; $b = -1;
			while (($a != $b) && ($i < $depth)) {
				$i++;
				$a = count($result) ;
				foreach ($result as $d) {
					$ftp_dir = $d."/" ;
					$newdir = @ftp_nlist($this->s_socket, $ftp_dir);
					foreach ($newdir as $key => $x) {
						if ((strpos($x,".")) || (strpos($x,".") === 0)) { 
							unset($newdir[$key]); 
						} elseif (!in_array($x,$result)) { 
							$result[] = $x ; 
						}
					}
				}
				$b = count($result) ;
			}
			return $result;
		}
		
		# ------------------------------------------------------------------------------------------------------
		# __login
		public function Login() {
		
		}
		
		# ------------------------------------------------------------------------------------------------------
		# __error
		private function __error($v, $result=false) {		
			if($v!==false){return true;}
			return $result;
		}

	}



	
	/*
$conn_id = ftp_connect($ftp_server) or (die("Couldn't connect to $ftp_server"));
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
if (!$login_result) { die("Couldn't log in to FTP account."); }


print_r($dir) ;

ftp_close($conn_id);


$host = 'ftp.example.org';
$usr = 'example_user';
$pwd = 'example_password';
 
// file to move:
$local_file = './example.txt';
$ftp_path = '/data/example.txt';
 
// connect to FTP server (port 21)
$conn_id = ftp_connect($host, 21) or die ("Cannot connect to host");
 
// send access parameters
ftp_login($conn_id, $usr, $pwd) or die("Cannot login");
 
// turn on passive mode transfers (some servers need this)
// ftp_pasv ($conn_id, true);
 
// perform file upload
$upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_ASCII);
 
// check upload status:
print (!$upload) ? 'Cannot upload' : 'Upload complete';
print "\n";
 
** Chmod the file (just as example)

// If you are using PHP4 then you need to use this code:
// (because the "ftp_chmod" command is just available in PHP5+)
if (!function_exists('ftp_chmod')) {
   function ftp_chmod($ftp_stream, $mode, $filename){
        return ftp_site($ftp_stream, sprintf('CHMOD %o %s', $mode, $filename));
   }
}
 
// try to chmod the new file to 666 (writeable)
if (ftp_chmod($conn_id, 0666, $ftp_path) !== false) {
    print $ftp_path . " chmoded successfully to 666\n";
} else {
    print "could not chmod $file\n";
}
 
// close the FTP stream
ftp_close($conn_id);*/


