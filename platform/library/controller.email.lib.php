<?php
	/*
		(mg) Framework E-Mail

		Copyright (c) 1999-2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Simple E-Mail Controller
		Version		4.0.0 Generation BN-2010
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgEMailController, formats and sends an email
	class mgSimpleEMailController extends mgSMTPController {
	
		private $bodyhtml = false;
		private $bodytext = false;
	
		# -------------------------------------------------------------------------------------------------------------
		# constructor __construct
		#  param0   = from
		#  param1   = subject
		#  param2/3 = body
		public function __construct($fromemail, $subject) {
			// initialize parent 
			parent::__construct(SMTP_HOST, SMTP_PORT, SMTP_AUTH_USER, SMTP_AUTH_PASSWORD);
			// initialize smpt auth
			$this->EnableAuth(defined("SMTP_AUTH")&&SMTP_AUTH=="true"?true:false);
			// initialize message
			if(is_array($fromemail)) {
				$this->SetFrom($fromemail[0], $fromemail[1]);
			} else {
				$this->SetFrom(EMAIL_FROM_NAME, $fromemail);
			}
			// set subject
			$this->SetSubject($subject);
		}
		
		# -------------------------------------------------------------------------------------------------------------
		# Execute, sends the email
		public function Execute($text, $html=true) {
			// set body
			$this->SetBody($text, $html);
			// send message
			if ($this->Connect()) {
				return $this->Send(false, true);
			}
			return false;
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgSendTemplatedMail, sends a template email
	function mgSendTemplatedMail($adr, $name, $data = Array(), $localized = false) {
		// initialize
		$result = false; 
		// get framework 
		$framework = GetVar(FRAMEWORK);
		// get content
		$email = $framework->personality->GetEMail($name);
		// sanity check
		if($email) {
			// prepare email recipients
			$to = Array();
			// prepare
			if(!is_array($adr)) $adr = Array($adr);
			// check
			foreach($adr as $em) {
				if(ValidateEMail($em)) $to[] = $em;
			}
			// initialize localization
			if(!$localized) $localized = $framework->localized;
			// get subject and content
			$subject = mgLocalizedArray($email->subject, $localized, "source");
			$content = mgLocalizedArray($email->content, $localized, "source");
			// create template
			$template = new mgTemplate($framework->translate, false, $framework);
			// assign template
			$template->AssignFromText($framework->__template($email->template?$email->template:defined("TEMPLATE_EMAIL")?TEMPLATE_EMAIL:"{CONTENT}"));
			// buffer 
			$template->Buffer("CONTENT", $content);
			// write data
			$template->Write($data);
			// send email to each recipient
			foreach($to as $toemail) {
				// prepare
				$toname = is_array($toemail)?$toemail[0]:"";
				$toemail = is_array($toemail)?$toemail[1]:$toemail;
				// set data
				$data = array_merge($data, Array(
					"toemailaddress"=>$toemail,
					"toemailname"=>$toname
				));			
				// create controller
				$e = new mgSimpleEMailController($email->sender?$email->sender:EMAIL_FROM_NOREPLY, mgFillVariableString($subject, $data));
				// enable html
				if(!isset($email->type)||!$email->type||$email->type==0) {
					$e->EnableHTML();
				}
				// prepare controller
				$e->AddRecipient($toname, $toemail);
				// send email
				if(!$e->Execute($template->GetParsed(true), true)) {
					// check
					$result = $e->transaction;
					break;
				} else {
					$result = true;
				}
			}
		}
		// all good
		return $result;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgSendContentMail, sends email with 
	function mgSendContentMail($adr, $subject, $content, $data = Array(), $sender = false, $attach = false) {
		// initialize
		$result = false;
		// get framework
		$framework = GetVar(FRAMEWORK);
		// prepare email recipients
		$to = Array();
		// prepare
		if(!is_array($adr)) $adr = Array($adr);
		// check
		foreach($adr as $em) {
			if(ValidateEMail($em)) $to[] = $em;
		}
		// get subject and content
		// create template
		$template = new mgTemplate($framework->translate, false, $framework);
		// assign template
		$template->AssignFromText($content);
		// write data
		$template->Write($data);
		// send email to each recipient
		foreach($to as $toemail) {
			// prepare
			$toname = is_array($toemail)?$toemail[0]:"";
			$toemail = is_array($toemail)?$toemail[1]:$toemail;
			// set data
			$data = array_merge($data, Array(
				"toemailaddress"=>$toemail,
				"toemailname"=>$toname
			));			
			// create controller
			$e = new mgSimpleEMailController($sender?$sender:EMAIL_FROM_NOREPLY, mgFillVariableString($subject, $data));
			// enable html
			if(!isset($email->type)||!$email->type||$email->type==0) {
				$e->EnableHTML();
			}
			// prepare controller
			$e->AddRecipient($toname, $toemail);
			// attachments
			if(is_array($attach)) {
				foreach($attach as $index=>$params) {
					if(count($params)==3) {
						$e->AddAttachment($params[0], $params[1], $params[2]);
					}
				}
			}		
			// send email
			if(!$e->Execute($template->GetParsed(true), true)) {
				// check
				$result = $e->transaction;
				break;
			} else {
				$result = true;
			}
		}
		// all good
		return $result;
	}	
?>
