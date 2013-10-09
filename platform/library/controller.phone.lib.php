<?php
	/*
		(mg) BabyNotify 1.0
		
		Copyright (c) 2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Main Controller		()
		Version		1.0
	*/

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	

	# define debug
	define("DEBUG", false);
	
	# XML Gateway
	define("PHONELOOKUP_XML_GATEWAY", "http://www.localcallingguide.com/xmlprefix.php?npa=%s&nxx=%s&blocks=1");

	# Provider EMail to SMS Gateways
	define("PROVIDER_VERIZON", "vtext.com");
	define("PROVIDER_ATT", "txt.att.net");
	define("PROVIDER_VIRGIN", "vmobl.com");
	define("PROVIDER_BELLSOUTH", "bellsouth.cl");
	define("PROVIDER_BOOST", "myboostmobile.com");
	define("PROVIDER_SPRINT", "messaging.sprintpcs.com");
	define("PROVIDER_TMOBILE", "tmomail.net");
	define("PROVIDER_METROPCS", "mymetropcs.com");
	define("PROVIDER_NEXTEL", "messaging.nextel.com");
	define("PROVIDER_QWEST", "qwestmp.com");
	define("PROVIDER_ROGERS", "pcs.rogers.com");

	# Provider Cache
	define("PROVIDER_CACHE_TABLE", "providercache");

	
	# -------------------------------------------------------------------------------------------------------------------
	# function:			QueryProviderCache
	#	querys the provider cache table
	function QueryProviderCache($npa, $nxx) {
		if(DEBUG) return false;
		$rSql = @mysql_query(sprintf("SELECT * FROM %s WHERE npa='%s' AND nxx='%s'", PROVIDER_CACHE_TABLE, $npa, $nxx	));
		return (@mysql_num_rows($rSql)!=0)?Array(mysql_result($rSql,0,"pn"),mysql_result($rSql,0,"pg")):false;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# function:			PublishProviderCache
	#	adds the provider to the cache table
	function PublishProviderCache($npa, $nxx, $pn, $pg) {
		if(DEBUG) return true;
		@mysql_query(sprintf("INSERT INTO %s (npa,nxx,pn,pg) VALUES ('%s','%s','%s','%s')", PROVIDER_CACHE_TABLE, $npa, $nxx, $pn, $pg));
	}	
	
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# function:			GetWirelessProvider
	#	returns the provider for the given phone number
	function GetWirelessProvider($phone=false) {
		# sanity check, US only at the moment
		if($phone===false||strlen($phone)!=10) return false;
		
		# get phone parts
		$npa = substr($phone, 0, 3);
		$nxx = substr($phone, 3, 3);
		$blc = intval(substr($phone, 6, 1));
		
		try {
			# query cache
			$cache = QueryProviderCache($npa, $nxx);
			# check
			if($cache===false) {
				# load xml stream
				$xml = @simplexml_load_file(sprintf(PHONELOOKUP_XML_GATEWAY, $npa, $nxx));
				# check if xml was fetched
				if(!isset($xml->prefixdata)||count($xml->prefixdata)==0) return false;
				# assign data
				$data = $xml->prefixdata;
				# look if there are multiple providers in this area
				if(count($xml->prefixdata)>1) {foreach($xml->prefixdata as $p){if($p->x==$blc){$data = $p; break;}}}
				
				# get name and type
				$name = strtolower($data->{'company-name'});
				$type = strtolower($data->{'company-type'});
				# sanity check, if wireless provider
				if($type!=="w") return false;
				# initialize provider
				$provider = false;
				# check 
				if(preg_match("/verizon/", $name)!=0){ $provider = PROVIDER_VERIZON; } else 
				if(preg_match("/cingular|at&t/", $name)!=0){ $provider = PROVIDER_ATT; } else 
				if(preg_match("/bellsouth/", $name)!=0) { $provider = PROVIDER_BELLSOUTH; } else 
				if(preg_match("/boost/", $name)!=0) { $provider = PROVIDER_BOOST; } else
				if(preg_match("/sprint/", $name)!=0) { $provider = PROVIDER_SPRINT; } else 
				if(preg_match("/t-mobile|tmobile/", $name)!=0) { $provider = PROVIDER_TMOBILE; } else
				if(preg_match("/metropcs|metro pcs/", $name)!=0){ $provider = PROVIDER_METROPCS; } else
				if(preg_match("/nextel/", $name)!=0){ $provider = PROVIDER_NEXTEL; } else
				if(preg_match("/qwest/", $name)!=0){ $provider = PROVIDER_QWEST; } else
				if(preg_match("/rogers/", $name)!=0){ $provider = PROVIDER_ROGERS; }
		
				# cache result
				if($provider!==false){PublishProviderCache($npa, $nxx, $name, $provider);}
			} else {
				# return provider from cache
				$provider = $cache[1];
			}
			
			# return provider, if not found, not supported
			return $provider;
			
		} catch(Exception $e) {}
		
		return false;
	}
?>