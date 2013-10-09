<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Google Analytics Controller
	*/

	

	# -------------------------------------------------------------------------------------------------------------------
	# (constants) 
	include "ga/autoload.php";
	use UnitedPrototype\GoogleAnalytics;

	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgGoogleAnalytics, provides interface to google analytics
	class mgGoogleAnalytics {
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $ga;
		private $visitor;
		private $session;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($uid = false, $domain = BASEPATH, $ip = false, $ua = false) {
			// init tracker
			$this->ga = new GoogleAnalytics\Tracker($uid, $domain);
			// init visitor
			$this->visitor = new GoogleAnalytics\Visitor();
			$this->visitor->setIpAddress($ip===false?GetRemoteAddress():$ip);
			$this->visitor->setUserAgent($ua);
			// init session
			$this->session = new GoogleAnalytics\Session();
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (Event)
		public function Event($category, $action, $label, $value = false, $interaction = false) {
			$event = new GoogleAnalytics\Event();
			$event->setCategory($category);
			$event->setAction($action);
			$event->setLabel($label);
			$event->setValue(is_numeric($value)?$value:0);
			$event->setNonInteraction($interaction?"false":"true");
			$this->ga->trackEvent($event, $this->session, $this->visitor);
		}
	}
?>