<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Assets Controller
	*/
	

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	// AS Type Definitions
	define("ASSET_ASURL", 0);
	define("ASSET_ASINFO", 2);
	define("ASSET_ASSEOURL", 3);
	
	// Request
	define("ASSET_REQUEST_USER", "user");
	define("ASSET_REQUEST_GET", "get");
	define("ASSET_REQUEST_SET", "set");
	define("ASSET_REQUEST_UPLOAD", "upload");
	define("ASSET_REQUEST_DELETE", "delete");
	
	define("ASSET_ROLE", ROLE_MANAGER);	// Minimum Role Required
	

	define("ASSET_META_FILENAME", "filename");
	define("ASSET_META_SIZE", "filesize");
	define("ASSET_META_MIME", "filemime");
	define("ASSET_META_TYPE", "filetype");
	define("ASSET_META_TYPENAME", "filetypename");
	define("ASSET_META_SUFFIX", "filesuffix");
	define("ASSET_META_CATEGORY", "category");
	define("ASSET_META_URL", "url");
	define("ASSET_META_SEOURL", "seourl");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Asset Management
	# -------------------------------------------------------------------------------------------------------------------
	
	// (mgAssetFilename) 
	function mgAssetFilename($id, $name, $default = false, $path = FRAMEWORK_ASSETS_PATH) {
		// form path
		return sprintf("%s%s/%s.%s", $path, $id, $id, $name);
	}
	
	// (mgWriteAsset), writes an asset
	function mgWriteAsset($id, $name, $data, $path = FRAMEWORK_ASSETS_PATH) {
		// form path
		$path = sprintf("%s%s/", $path, $id);
		// check if path exists
		if(!is_dir($path)) {@mkdir($path);}
		// write file
		@file_put_contents(sprintf("%s%s.%s", $path, $id, $name), $data);
	}
		
	// (mgReadAsset, reads an asset
	function mgReadAsset($id, $name, $default = false, $path = FRAMEWORK_ASSETS_PATH) {
		// form filename
		$fn = sprintf("%s%s/%s.%s", $path, $id, $id, $name);
		// check if file exists
		if(file_exists($fn)) {return file_get_contents($fn);}
		// return default
		return $default;
	}		
	
	// (mgDecodeAsset)
	function mgDecodeAsset($data) {
		// prepare data
		$data = explode(",", $data);
		// return
		return base64_decode(count($data)==2?$data[1]:$data[0]);
	}
	
	// (mgRegisterFileAsset)
	function mgRegisterFileAsset($user, $filename) {
		// initialize
		$result = false;
		// register asset
		$asset = new mgAsset(DB_CREATE);
		// check
		if($asset->result == DB_OK) {
			// register user
			$asset->Write(Array(
				DB_FIELD_USERID=>$user
			));
			// register file
			$asset->copyfile($filename);
			// publish
			$asset->Publish();
			// register 
			$result = $asset->asformatted(ASSET_ASINFO);;
		}
		// return result
		return $result;		
	}

	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgAssets, manages assets
	class mgAssets {
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $framework = false;
		private $secured = false;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($framework, $secured) {
			$this->framework = $framework;
			$this->secured = $secured;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (request) processes a request
		public function request($request = false, $execute = true) {
			// get automatic request
			if($request===false) $request = GetDirVar(1);
			// initialize result
			$result = false;
			// process request
			switch($request) {
				// delete
				case ASSET_REQUEST_DELETE: 
					if($this->secured) {
						// get items
						$items = GetVar("items");
						// check if this is an array
						if(!is_array($items)) { $items = Array($items);}
						// cycle items
						foreach($items as $id=>$p) {
							// create asset
							$asset = new mgAsset($id);
							// check id
							if($asset->result == DB_OK) {
								// check if this is the same user
								if($this->framework->user->role >= ROLE_MANAGER || 	$this->framework->user->userid == $asset->Read(DB_FIELD_USERID)) {
									// can remove
									$asset->remove();
								}
							}
						}
						// return result
						$result = true;
					}				
					break;
				// user
				case ASSET_REQUEST_USER:
					if($this->secured) {
						// run sql
						$db = new mgDatabaseStream(DB_TABLE_ASSETS, DB_SELECT, Array(DB_FIELD_USERID=>GetVar("user"), DB_FIELD_ENABLED=>ENABLED));
						// check sql
						if($db->result==DB_OK) {
							// initialize result
							$result = Array();
							// cycle db
							foreach($db->getall() as $index=>$row) {
								// process meta
								$meta = @unserialize(DefaultValue($row[DB_FIELD_META], false));
								unset($row[DB_FIELD_META]);
								$result[] = array_merge($row, is_array($meta)?$meta:Array());
							}
						}
					}
					break;
					
				// upload
				case ASSET_REQUEST_UPLOAD:	
					// get data
					$userid = GetVar("user", false);
					// request access level
					if($this->secured && ($this->framework->user->id == $userid || $this->framework->requirerole(ASSET_ROLE, false))) {
						// initialize
						$fileinfo = false;					
						// upload file
						if(GetVar("form", false)) {
							// upload from form, construct header
							if(isset($_FILES['selectfile'])&&is_uploaded_file($_FILES['selectfile']['tmp_name'])) {
								// get filename
								$fn = file_get_contents($_FILES['selectfile']['tmp_name']);
								$name = $_FILES['selectfile']['name'];
								$mime = mgGetMime($name);
								// build fileinfo
								$fileinfo = (object) Array(
									"data"=>sprintf("data:%s;base64,%s", $mime, base64_encode($fn)),
									"name"=>$name,
									"type"=>$mime,
									"size"=>count($fn)
								);
							}
						} else {
							$fileinfo = (object)GetVar("file");
						}			
						// check
						if($fileinfo !== false) {
							// register asset
							$asset = new mgAsset(DB_CREATE);
							// check
							if($asset->result == DB_OK) {
								// register user
								$asset->Write(Array(
									DB_FIELD_USERID=>$userid
								));
								// replace file
								$asset->replacefile($fileinfo);
								// publish
								$asset->Publish();
								// register 
								$result = $asset->asformatted(ASSET_ASINFO);
							}
						}
					}
					break;
					
				// get the asset
				case ASSET_REQUEST_GET: 
					// create asset
					$asset = new mgAsset(GetDirVar(3));
					// check asset
					if($asset->result == DB_OK) {
						// format output
						$result = $asset->asformatted(GetVar("format", ASSET_ASINFO));
					}
					break;
					
				// publish
				default:
					// run sql
					$db = new mgDatabaseStream(DB_TABLE_ASSETS, DB_SELECTSQL, sprintf("idstring='%s' OR '%s' LIKE CONCAT('%%', idrelated, '%%')", 
						GetDirVar(1),
						GetDirVar(1)
					));
					// check result
					if($db->result == DB_OK) {
						// create asset
						$asset = new mgAsset($db->Read(0, DB_FIELD_IDSTRING));
						// show asset
						$asset->output();
					} else {
						// send 404 error
						DieCriticalError("The requested asset was not found.");
					}
					exit;
						
			}
			// execute
			if($execute) {
				// emit headers
				mgOutputBuffer(json_encode(Array("result"=>$result, "timestamp"=>time())));
				// exit (not needed)
				exit;
			}
			// return result
			return $result;
		}
	
	
	}
	
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgAsset, manages a single asset
	class mgAsset extends mgDatabaseObject {
	
		# -------------------------------------------------------------------------------------------------------------------
		# (private)
		private $__fn = false;
		
		
		# -------------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($id = DB_CREATE) {
			// prepare id
			$id = str_replace("/assets/", "", $id);
			// execute query
			parent::__construct(DB_TABLE_ASSETS, $id);
			// result
			if ($this->result === DB_OK){
				if($id == DB_CREATE) {
					$this->Write(DB_FIELD_RELATED, uniqid());
				}
			}
				
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (Output)
		public function Output($query = false, $astype = false) {
			// get meta
			$meta = (object)@unserialize($this->Read(DB_FIELD_META));
			// get filename
			$filename = $this->ContentFilename();
			// get query
			$query = is_array($query)?$query:mgGetURLQueryParameters();
			// switch by type
			switch($astype?$astype:DefaultValue(@$meta->category, false)) {
				// image
				case "images":
					// get image
					$img = new mgImage($filename);
					// magic
					foreach($query as $name=>$value) {
						$isfinal = false;
						switch($name) {
							// (max) 
							case "max":
								$v = explode("x", $value);
								if(count($v)==2) {
									if($img->Width()>$v[0]) {
										$img->ResizeWidth($v[0]);
									}
									if($img->Height()>$v[1])  {
										$img->ResizeHeight($v[1]);
									}
								}
								break;
								
							// (croprect) 
							case "croprect":
								$v = explode("x", $value);
								if(count($v)>=6) {
									$img->CropRect($v[0], $v[1], $v[2], $v[3], $v[4], $v[5]);
									$isfinal = isset($v[6])?false:true;
								}
								break;
								
							// (autocrop)
							case "autocrop":
								// split by x
								$v = explode("x", $value);
								if(count($v)==2) {
									$img->AutoCrop($v[0], $v[1]);
								}
								break;								
						
							// (crop) 
							case "crop":
								// split by x
								$v = explode("x", $value);
								if(count($v)==2) {
									$img->ResizeAndCrop($v[0], $v[1]);
								}
								break;
								
							// (resize)
							case "resize": 
								// split by x
								$v = explode("x", $value);
								if(count($v)==2) {
									$img->Resize($v[0], $v[1]);
								}
								break;
							// (width)
							case "width": $img->ResizeWidth(is_numeric($value)?$value:120); break;
							// (height)
							case "height": $img->ResizeHeight(is_numeric($value)?$value:120); break;
							// (filter)
							case "filter": 
								switch($value) {
									case "negative": $img->FilterNegative(); break;
									case "gray": $img->FilterGrayscale(); break;
								}
								break;
							// (rotate)
							case "rotate": if(is_numeric($value)){$img->Rotate($value);} break;
						}
						// check final
						if($isfinal) break;
					}
					// output
					switch(GetVar("output", false)) {
						// no cache
						case "nocache": $img->EmitNoCache(); break;
						// raw
						case "raw": $img->EmitRaw(); break;
						// default
						default: $img->Emit(); break;
					}
					break;
					
				// any other file
				default:
					// execute force download
					mgForceDownloadBuffer($this->Content(), $meta->filename, $meta->filemime);
					break;
			}
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (Content)
		public function Content() {
			return mgReadAsset($this->Read(DB_FIELD_USERID), $this->Read(DB_FIELD_IDSTRING));
		}
		
		public function ContentFilename() {
			return mgAssetFilename($this->Read(DB_FIELD_USERID), $this->Read(DB_FIELD_IDSTRING));
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (Remove)
		public function Remove($soft = true) {
			// soft delete
			if($soft) {
				$this->Write(DB_FIELD_ENABLED, DISABLED, true);
			}  else {
				@unlink($this->ContentFilename());
				$this->Delete();
			}
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (AsFormatted)
		public function AsFormatted($format, $optional = false) {
			switch($format) {
				// Returns the asset as URL
				case ASSET_ASURL:
					return $this->ReadFieldValue(DB_FIELD_META, ASSET_META_URL);
					break;
					
				// Returns the asset as SEO URL
				case ASSET_ASSEOURL:
					return $this->ReadFieldValue(DB_FIELD_META, ASSET_META_SEOURL);
					break;
			
				// Returns the asset as JSON info
				case ASSET_ASINFO: default:	
					return @unserialize($this->Read(DB_FIELD_META));
					break;
			}
			// return error
			return false;
		}
		
		
		# -------------------------------------------------------------------------------------------------------------------
		# (CopyFile)
		public function CopyFile($filename) {
			// check
			if(file_exists($filename)) {
				// create fileinfo
				$fileinfo = (object) Array(
					"size"=>filesize($filename),
					"name"=>basename($filename),
					"data"=>@file_get_contents($filename),
					"type"=>mgGetMime($filename)
				);
				// replace file
				return $this->ReplaceFile($fileinfo);
				
			}
			// return
			return false;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (ReplaceFile)
		public function ReplaceFile($fileinfo, $form = false) {
			// check fileinfo
			if(isset($fileinfo->data)) {
				// check if this is a base64 encoded file
				if(mgIsBase64($fileinfo->data)) {
					$fileinfo->data = mgDecodeAsset($fileinfo->data);
				}
				// upload file				
				mgWriteAsset($this->Read(DB_FIELD_USERID), $this->Read(DB_FIELD_IDSTRING), $fileinfo->data);
				// write name
				$this->Write(Array(
					DB_FIELD_NAME=>@$fileinfo->name,
					DB_FIELD_ENABLED=>ENABLED,
				));
				// write meta information
				$this->WriteFieldValue(DB_FIELD_META, Array(
					ASSET_META_FILENAME=>@$fileinfo->name,
					ASSET_META_SIZE=>@$fileinfo->size,
					ASSET_META_MIME=>@$fileinfo->type,
					ASSET_META_TYPE=>mgGetMimeSimple(@$fileinfo->name),
					ASSET_META_TYPENAME=>mgGetSuffixName(@$fileinfo->name),
					ASSET_META_SUFFIX=>mgGetFileSuffix(@$fileinfo->name),
					ASSET_META_CATEGORY=>mgGetMimeCategory(@$fileinfo->name),
					ASSET_META_URL=>sprintf("/%s/%s", REQUEST_ASSETS, $this->Read(DB_FIELD_IDSTRING)),
					ASSET_META_SEOURL=>sprintf("/%s/%s-%s.%s", REQUEST_ASSETS, mgFormatSEOFriendly(mgGetFileWithoutSuffix(@$fileinfo->name)), $this->Read(DB_FIELD_RELATED), mgGetFileSuffix(@$fileinfo->name))
				));
				// add special meta
				switch(mgGetMimeSimple(@$fileinfo->name)) {
					// (image)
					case "image":
						$img = new mgImage($this->ContentFilename());
						// save image data
						$this->WriteFieldValue(DB_FIELD_META, Array(
							"width"=>$img->Width(),
							"height"=>$img->Height()
						));
						// read exif if jpeg
						$f = @exif_read_data($this->ContentFilename());
						// write
						if(is_array($f)) {
							$this->WriteFieldValue(DB_FIELD_META, "exif", $f);
						}
						break;
				}
				// publish
				$this->Publish();
			}
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgGetAsset, returns quickly the asset with information
	function mgGetAsset($id, $asobject = true) {
		// get asset
		$a = new mgAsset($id);
		// check
		if($a->result == DB_OK) {
			// create result
			$result = @unserialize($a->Read(DB_FIELD_META));
			// add content
			$result['data'] = $a->Content();
			// return
			return $asobject?(object)$result:$result;
		}
		// error
		return false;
	}
	
