<?php
	/*
		(mg) Framework SMTP

		Copyright (c) 1999-2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		SMTP Controller (SMTP Protocol 3)
		Version		4.0.0 Generation BN-2010
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	define("EOL","\r\n");
	define("MSG_END","\r\n.\r\n");
	define("EOH","\r\n\r\n");
	define("SEP","\r\n--#BOUNDARY#--");
	define("EOM","QUIT\r\n\0");		
	
	define("BODY_PART_HTML", "html");
	define("BODY_PART_TEXT", "text");
	
	define("SMTP_LOGFILE", false);
	
	define("SMTP_SSL", "ssl");
	define("SMTP_TLS", "tls");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgSMTPController	
	class mgSMTPController{
	
		# -------------------------------------------------------------------------------------------------------------
		# (local) private stack
		// storage
		private $attachements = Array();
		private $recipients   = Array();
		private $headers      = Array();
		// message details
		private	$replyname   	        = "";
		private	$replymail   			= "";
		private	$fromname    			= "";
		private	$frommail    			= "";
		private $msgsubject           = "";
		private $msgbody              = Array();
		// options
		private $mimeenabled          = false;	// mime enabled
		private $debugenabled         = false;	// debug enabled
		private $htmlenabled          = false;	// html in body enabled
		private $loginenabled			= true;		// login enabled
		// socket
		private $socket               = 0;		// socket
		// connection details
		private $hostname				= "";
		private $port					= 25;
		private $authusername			= "";
		private $authpassword			= "";
		private $encryption				= false;
		
		public $transaction				= Array();

		# -------------------------------------------------------------------------------------------------------------
		# constructor construct
		#  param0 = hostname
		#  param1 = port
		#  param2 = auth username
		#  param3 = auth password
		public function __construct($param0, $param1=25, $param2=false, $param3=false) {
			// initialize header
			$this->AddHeader("to"); 
			$this->AddHeader("cc");  
			$this->AddHeader("bcc"); 
			// initialize connection info
			$this->hostname = $param0;
			$this->port = $param1;
			$this->authusername = $param2;
			$this->authpassword = $param3;
			
			// enable message properties
			$this->SetImportance();
			$this->SetSensitivity();
			$this->SetPriority();
			
		}	
		
		# -------------------------------------------------------------------------------------------------------------
		# property bags
		
		# SetSubject
		public function SetSubject($param){$this->msgsubject = $param;}
		# SetBody
		public function SetBody($text, $html = false){
			$this->msgbody = $text;
			if($html!==false) {
				$this->EnableHTML();
			}
		}
		# SetFrom
		public function SetFrom($param0, $param1){$this->fromname = $param0; $this->frommail = $param1;}
		# SetReplyTo
		public function SetReplyTo($param0, $param1){$this->replyname = $param0; $this->replymail = $param1;}
		# SetImportance,  low|normal|high
		public function SetImportance($param="normal"){$this->AddHeader("X-Importance", $param);}
		# SetPriority,  low|normal|high
		public function SetPriority($param="normal"){$this->AddHeader("X-Priority", $param);}
		# SetSensitivity, personal|private|company-confidential
		public function SetSensitivity($param="Personal"){$this->AddHeader("X-Sensitivity", $param, "X");}
		
		# EnableMime
		public function EnableMime($value=true){$this->mimeenabled=$value;}
		# EnableDebug
		public function EnableDebug($value=true){$this->debugenabled=$value;}
		# EnableHTML
		public function EnableHTML($value=true){$this->htmlenabled=$value;}
		# EnableAuth
		public function EnableAuth($value=true){$this->loginenabled=$value;}		
		# EnableEncryption
		public function EnableEncryption($value=false){$this->encryption=$value;}	

		# -------------------------------------------------------------------------------------------------------------
		# Message Management	

		# -------------------------------------------------------------------------------------------------------------
		# AddHeader, adds an raw header
		public function AddHeader($name, $value=""){$this->header[$name]=$value;}
		
		# -------------------------------------------------------------------------------------------------------------
		# AddAttachment
		#  localfile  = local filename
		#  remotefile = attachement filename, autoname
		#  mime       = autodetect mime type or specify mime time
		public function AddAttachment($localfile, $remotefile=false, $mime=false) {
			// check if local file exists
			if(!file_exists($localfile)){return false;}
		
			// prepare local filename
			$_localfile=str_replace("\\", "/", $localfile);
			
			// prepare remote filename
			if($remotefile===false){$remotefile=substr(strrchr($_localfile,'/'),1);}
			
			// prepare file suffix
			$suffix = substr(strrchr($remotefile,'.'),1);
			
			// prepare mime
			if($mime===false){$mime=mime_content_type($localfile);}
			
			// attach 
			$this->attachements[] = array("type"=>"file", "filename"=>$localfile, "name"=>$remotefile, "mime"=>$mime);
			
			// return
			return true;
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# AddAttachmentBuffer
		#  localfile  = local filename
		#  remotefile = attachement filename, autoname
		#  mime       = autodetect mime type or specify mime time
		public function AddAttachmentBuffer($filename, $buffer, $mime) {
			$this->attachements[] = array("type"=>"buffer", "name"=>$filename, "content"=>$buffer, "mime"=>$mime);
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# AddRecipient
		#  name     = name
		#  address  = email adddress
		#  type     = to/cc/bcc
		public function AddRecipient($name, $address, $type="to"){
			// prepare type
			$type = strtolower($type);
			if(strlen($name)==0){$name=$address;}
			
			// prepare type array
			if(isset($this->recipients[$type])&&!is_array($this->recipients[$type])){$this->recipients[$type]=Array();}
				
			// add
			$this->recipients[$type][$address]=$name;
		}
		

		# -------------------------------------------------------------------------------------------------------------
		# SMTP Processing Functions
		
		# -------------------------------------------------------------------------------------------------------------
		# debugoutput, writes an debug string to the console
		private function debugoutput($line,$sent=1){
			$line=trim($line);
			// write log
			if(SMTP_LOGFILE) {
				@file_put_contents(SMTP_LOGFILE, $line."\n", FILE_APPEND);
			}
			// add to transaction
			$this->transaction[] = $line;
			
			if(!$this->debugenabled) return;
			//echo nl2br(htmlentities($line)."<br />");
			echo $line."\n";
		}


		# -------------------------------------------------------------------------------------------------------------
		# authlogin, authentification login
		private function authLogin(){
			$buf="AUTH LOGIN";
			$this->sendlines($buf);
			if($this->getanswer()!=334){ fclose($this->socket); return false;}
			$buf=sprintf("%s",base64_encode($this->authusername));
			$this->sendlines($buf);
			if($this->getanswer()!=334){
				fclose($this->socket);
				return false;
			}
			$buf=sprintf("%s",base64_encode($this->authpassword));
			$this->sendlines($buf);
			if($this->getanswer()!=235){
				fclose($this->socket);
				return false;
			}
			return true;
		}

		# -------------------------------------------------------------------------------------------------------------
		# Connect, public function, Connect to smtp server
		public function Connect($timeout=15){
			// prepare
			$errno        = "";
			$errstr        = "";
			
			// open connection
			$this->socket = @fsockopen ($this->hostname, $this->port, $errno, $errstr, $timeout);
			
			// check status of socket
			if (!$this->socket){$this->debugoutput($errno.":".$errstr."\r\n");return false;}
			
			// check status message of server
			if($this->getanswer()!=220){fclose($this->socket);return false;}
			
			// begin introduction
			$buf=sprintf("HELO %s","localhost");
			$this->sendlines($buf);
			
			// get answer
			$hiReply    = $this->getanswer();
			if($hiReply == 250){
				// enable encryption 
				if($this->encryption) {
					switch($this->encryption) {
						case SMTP_TLS:
							// start TLS
							$this->sendlines("STARTTLS");
							// run
							if($this->getanswer() != 220) return false;
							// open encryption
							if(false == stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
								$this->debugoutput("Unable to start TLS Encryption\r\n");
								fclose($this->socket);
								return false;
							}
							break;
					}
					// hello again
					$buf=sprintf("EHLO %s", "localhost");
					$this->sendlines($buf);
					// asnwer
					if($this->getanswer() != 250) return false;				
				}
				// run authentification
				if($this->loginenabled){
					// process login
					return $this->authLogin();
				}
				return true;
			}
			if($this->getanswer()!=250){fclose($this->socket);return false;}
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# Disconnect, public function, Disconnect from smtp server		
		public function Disconnect(){
			$this->sendlines("QUIT");
			$quitReply = $this->getanswer();
			fclose($this->socket);
			if($quitReply==221) {return true;}
			return true;
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# Send, sends the message
		public function Send($connect=false,$disconnect=false){
			if($connect){if(!$this->connect()) return false;}
			
			// prepare header
			$buf=sprintf("MAIL FROM:<%s>",$this->frommail);
			$this->sendlines($buf);
			if($this->getanswer()!=250){ fclose($this->socket); return false; }
			// send header
			if(!$this->sendrecipients()){ fclose($this->socket); return false; }
			
			// prepare data
			$this->sendlines("DATA");
			if($this->getanswer()!=354){ fclose($this->socket); return false; }
			// send data
			if(!$this->sendheaders()){ fclose($this->socket); return false; }
			if(!$this->sendmessage()){ fclose($this->socket); return false; }
			if(!$this->sendattachments()){ fclose($this->socket); return false; }
			
			// end message
			$this->sendlines(MSG_END);
			if($this->getanswer()!=250){ fclose($this->socket); return false; }
			
			// end connection
			if($disconnect){ $this->disconnect(); }
			return true;
		}		
		
		# -------------------------------------------------------------------------------------------------------------
		# sendrecipients, sends the recipients list to the server			
		private function sendrecipients(){
			$result        = 0;
			$mails        = array();
			$mailsErr    = array();
			while(list($type,$list)=each($this->recipients)){
				while(list($mail,$name)=each($list)){
					if(in_array($mail,$mails)) continue;
					$buf    =sprintf("RCPT TO:<%s>",$mail);
					$this->sendlines($buf);
					$rez    =$this->getanswer();
					array_push($mails,$mail);
					if($rez==250) continue;
					array_push($mailsErr,$mail);
					unset($this->recipients[$type][$mail]);
				}
			}
			return ((count($mails)-count($mailsErr))>0);
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# sendheaders, sends the header of the message
		private function sendheaders(){
			// prepare headers
			reset($this->headers);
			while(list($name,$value)=each($this->headers)){
				$buf    ="$name: $value";
				$this->sendlines($buf);
			}
			reset($this->recipients);
			while(list($type,$list)=each($this->recipients)){
				$mails    = array();
				while(list($mail,$name)=each($list)){
					array_push($mails,"$name <$mail>");
				}
				$type[0]    =strtoupper($type[0]);
				if(isset($this->headers[$type])) continue;
				$buf        ="$type: ".implode(",",$mails)."";
				$this->sendlines($buf);
			}
			$buf=sprintf("From: %s <%s>",$this->fromname,$this->frommail);
			$this->sendlines($buf);
			if(strlen($this->replymail)){
				$buf=sprintf("Reply-to: %s <%s>",$this->replyname,$this->replymail);
				$this->sendlines($buf);
			}
			$buf=sprintf("Subject: %s",$this->msgsubject);
			$this->sendlines($buf);
			return true;
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# _sendmessage , sends the message depending on encoding: HTML, TExt
		private function sendmessage(){
			// prepare auto detect of text style
			if(!$this->mimeenabled){
				$this->mimeenabled = ($this->htmlenabled) || (count($this->attachements));
			}
			
			// send message
			if($this->mimeenabled){
				$buf =
   				    "MIME-Version: 1.0\r\n".
					"Content-type: multipart/mixed; boundary=\"#BOUNDARY#\"\r\n\r\n";
				$this->sendlines($buf);
				$buf=
					"\r\n--#BOUNDARY#\r\n".
					"Content-Type: text/".($this->htmlenabled ? "html" : "plain")."; charset=us-ascii\r\n";
				$this->sendlines($buf);
			}else{
				$buf="\r\n";
				$this->sendlines($buf);
			}
			$this->sendlines($this->msgbody,1);
			return true;
		}
		
		
		# -------------------------------------------------------------------------------------------------------------
		# _sendattachments , sends the attachments
		private function sendattachments(){
			if(!$this->mimeenabled) return true;
			if(!count($this->attachements)) return true;
			foreach($this->attachements as $index=>$current) {
				// check type
				switch($current["type"]) {
					// (file)
					case "file":
						// read file and get content
						$content = base64_encode(file_get_contents($current["filename"]));
						break;
					// (buffer)
					default:
						$content = $current["content"];
						break;
				}
				// assign variables
				$name = $current["name"];
				// create header
				$header = sprintf("\r\n\r\n--#BOUNDARY#\r\n".
					"Content-Type: %s;&;nbsp;name=%s\r\n".
					"Content-Length: %s\r\n".
					"Content-Transfer-Encoding: base64\r\n".
					"Content-Disposition: attachment; filename=%s\r\n".
					"Content-ID: <%s>\r\n\r\n",
					$current["mime"], $name, strlen($content), $name, $name);
				// send header and content
				$this->sendlines($header);
				$this->sendlines($content, 1);
			}
			return true;
		}
		
		
		
		# -------------------------------------------------------------------------------------------------------------
		# sendlines, sends line data to server
		private function sendlines($data){
			// prepare data
			if(!is_array($data)){
				$data = str_replace("\r","",$data);
				$data = explode("\n",$data);
				if(!count($data))$data=array($data);
			}
			// send data
			foreach($data as $line){
				$line = trim($line);
				$this->debugoutput($line);
				if(!fputs($this->socket,$line."\r\n")){return false;}
			}
			// exit
			return true;
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# _getanswer, receive replies from smtp server
		private function getanswer(){
			$line="";
			while(!feof($this->socket)){
				$ch    = fgetc($this->socket);
				if(!strlen($ch)) return false;
				if($ch=="\n"){
					$this->debugoutput($line,0);
					if($line[3]==" ") return (int)substr($line,0,3);
					$line    = ""; continue;
				}
				if($ch!="\r") $line.=$ch;
			}
			return false;
		}		

		//--
	}
?>