<?php
	/*
		(mg) Framework XML

		Copyright (c) 2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
	*/
	# -------------------------------------------------------------------------------------------------------------------
	# Constants
	define("XML_EMPTY", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>");

	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgXML, reads/writes an xml file
	class mgXML extends SimpleXMLElement {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetAttribute, searches for a node with the attribute and value, optional returns another attribute
		public function GetAttribute($attribute, $value, $returnattribute=false, $default = null) {
			foreach($this as $node) {
				if(strtolower((string)$node[$attribute])==strtolower((string)$value)) {
					return ($returnattribute!==false)?(string)$node[$returnattribute]:$node;
				}
			}
			return $default;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetAttributeAll, returns all matching attributes
		public function GetAttributeAll($attribute, $value, $default = null) {
			$result = Array();
			foreach($this as $node) {
				if(strtolower((string)$node[$attribute])==strtolower((string)$value)) {
					$result[] = $node;
				}
			}
			return count($result)==0?$default:$result;
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) MergeFrom, merges from another simple XML
		public function MergeFrom($source, $reverse = false) {
			// cycle source
			foreach($source->children() as $child) {
				$node = $this->AddObject($child);
				$node->MergeFrom($child, true);
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AddObject, adds a node as object
		public function AddObject($object) {	
			$node = $this->addChild($object->getName(), (string)$object);
			foreach($object->attributes() as $key=>$value){
				$node->addAttribute($key, $value);
			}
			return $node;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) IndexOf, 
		public function IndexOf($attribute, $value) {
			for($i=0;$i<$this->count();$i++){
				if((string)$this[$i][$attribute]==(string)$value){
					return $i;
				}
			}
			return null;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) asBeautifulXML, formats the output of an xml file
		public function asBeautifulXML() {
			$dom = dom_import_simplexml($this)->ownerDocument;
			$dom->formatOutput = true;
			return $dom->saveXML();
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) toArray, formats the XML node as array
		public function toArray() {
			$result = Array();
			foreach($this as $node) {
				$result[] = (array)$node;
			}
			return $result;
		}
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgLoadXML, loads an xml file and creates a new mgXML class
	function mgLoadXML($filename) {if(file_exists($filename)) { return new mgXML(file_get_contents($filename));} return false;}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgXMLEntityDecode
	function mgXMLEntityDecode($text, $charset = 'Windows-1252'){
		return base64_decode($text);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgXMLEntities
	function mgXMLEntities($text, $charset = 'Windows-1252'){ 
		return str_replace(array ( '&', '"', "'", '<', '>', '?' ), array( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;'), $text);
	} 
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgXMLToArray
	function mgXMLToArray($xml) {
		if (@get_class($xml) == 'SimpleXMLElement') {
			$attributes = $xml->attributes();
			foreach($attributes as $k=>$v) {
				if ($v) $a[$k] = (string) $v;
			}
			$x = $xml;
			$xml = get_object_vars($xml);
		}
		if (is_array($xml)) {
			if (count($xml) == 0) return (string) $x; // for CDATA
			foreach($xml as $key=>$value) {
				$r[$key] = mgXMLToArray($value);
			}
			if (isset($a)) $r['@attributes'] = $a;    // Attributes
			return $r;
		}
		return (string) $xml;
	}

?>