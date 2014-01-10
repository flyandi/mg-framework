<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		File Controller
	*/

   
    # -------------------------------------------------------------------------------------------------------------------
    # Constants Declaration
    define("FILE_ZIP", "application/zip,application/x-zip-compressed,application/binary ");
    define("FILE_PNG", "image/png,image/x-png");
    define("FILE_GIF", "image/gif");
    define("FILE_JPEG", "image/jpeg,image/pjpeg");
	define("FILE_ANY", true);
   
	define("FILE_ERROR", false);
    define("FILE_ERROR_NOTACCEPTED_FILETYPE", -200);
    define("FILE_ERROR_NOTACCEPTED_FILESIZE", -201);
    define("FILE_ERROR_GENERALERROR", -202);
    define("FILE_ERROR_NOTUPLOADED", -203);
    define("FILE_ERROR_USERSECURITY", -204);
    define("FILE_ERROR_MOVEUPLOADED", -205);
    define("FILE_ERROR_REGISTERMEDIA", -206);
    define("FILE_ERROR_IMAGESIZES", -207);
    define("FILE_ERROR_OK", -210);
   
    define("FILESIZE_1MB", 1048576);
    define("FILESIZE_2MB", 2097152);
    define("FILESIZE_5MB", 5242880);
	define("FILESIZE_25MB", 26214400);
	define("FILESIZE_ANY", true);
   
    define("IMAGE_MAXWIDTH", 1500);
    define("IMAGE_MAXHEIGHT", 1500);
	
	# -------------------------------------------------------------------------------------------------------------------
	# MIME Types
	define("MIME_JAVASCRIPT", "application/javascript");
	define("MIME_JSON", "application/json");
	define("MIME_CSS", "text/css");
	define("MIME_XML", "application/xml");
	define("MIME_SWF", "application/x-shockwave-flash");
	define("MIME_HTML", "text/html");
	define("MIME_PNG", "image/png");
   
   
    # -------------------------------------------------------------------------------------------------------------------
    # (function) mgIsMime  
    function mgIsMime($mimetype, $accepttypes=true) {
		if($accepttypes===true){return true;}
		$accepttypes=is_array($accepttypes)?$accepttypes:Array($accepttypes);
		foreach($accepttypes as $types){
			$typelist = explode(",", $types);
			foreach($typelist as $type) {
				if(mgCompare($mimetype, $type)){
					return true;
				}
			}
		}
		return false;
	}   
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgIsBase64
	function mgIsBase64($f) {
		if(strlen($f)<512&&file_exists($f)) {
			$f = @file_get_contents($f);
		}
		return strpos(substr($f, 0, 128), ";base64,") !== false;
	}
   
    # -------------------------------------------------------------------------------------------------------------------
    # (function) mgOutputFile
    function mgOutputFile($f, $m="text/plain"){
        if (file_exists($f)){
            header("Content-type: $m");
            header("Content-length: ".filesize($f));
            @readfile($f);
            exit;
        }
    }
   
    # -------------------------------------------------------------------------------------------------------------------
    # (function) mgOutputBuffer      
    function mgOutputBuffer($b, $m="text/plain"){
        header("Content-type: $m");
        header("Content-length: ".strlen($b));
        echo $b;
        exit;
    }
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgOutputHeaders, outputs an array of headers
    function mgOutputHeaders($headers, $preventcaching = false) {
		// caching headers
		if($preventcaching === true) {
			$headers = array_merge($headers, array(
				//"Expires"=> date(DATE_RFC822, time() - (3600 * 24 * 365)),
				"Last-Modified" => date(DATE_RFC822, time() - 120),
				"Pragma" => "no-cache",
				"Cache-Control" => "no-store, no-cache, must-revalidate",
				"Cache-Control" => "post-check=0, pre-check=0",
				"ZTag" => date(DATE_RFC822, time())
			));
		} else {
			$headers = array_merge($headers, Array(
				//"Expires"=>date(DATE_RFC822, time() + (3600 * 24 * 30)),
				"Cache-Control"=> sprintf("public, max-age=%s", 3600 * 24 * 30)
			));
		}
		// get framework
		$framework = GetVar(FRAMEWORK);
		// general
		$headers = array_merge($headers, Array(
			"Content-Language"=>GetVar(FRAMEWORK_LOCALIZED, "en")
		));
	
		// output headers
		foreach($headers as $n=>$v) {
			@header(sprintf("%s: %s", $n, $v));
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgOutput, outputs a buffer with headers, caching and compression    
    function mgOutput($buffer, $contenttype = MIME_HTML, $compress = true, $terminate = false) {
		// disable compression
		@ini_set('zlib.output_compression','Off');
		// output headers
		@header(sprintf("Content-Type: %s%s", $contenttype, $contenttype==MIME_HTML?"; charset=utf-8":""));
		// get accepted encoding
		$ae = @$_SERVER["HTTP_ACCEPT_ENCODING"]; 
		// reset encoding
		if(IfAppVar("CONTENT_GZIP", true)) {
			// retrieve proper enconding
			if(strpos($ae, 'x-gzip') !== false ){
				$encoding = 'x-gzip';
			} elseif( strpos($ae,'gzip') !== false ){
				$encoding = 'gzip';
			}
			// check compress
			if($encoding && $compress) {
				// create compressed
				$buffer = gzencode($buffer, 6);
				// set headers
				@header('Content-Encoding: '.$encoding);
			}
		}
		// set length
		@header('Content-Length: '.strlen($buffer));
		// set output
		echo $buffer;
		
		// terminate
		if($terminate) exit;
	}
   
    # -------------------------------------------------------------------------------------------------------------------
    # (function) mgForceDownloadBuffer, Sends request to browser to download the buffer file
    function mgForceDownloadBuffer($buffer, $filename, $mime){
        $ua = strtolower ($_SERVER["HTTP_USER_AGENT"]);
        $size = strlen($buffer);
		
		// headers
		$header = Array(
			"Pragma"=> "no-cache",
			"Last-Modified"=> date(DATE_RFC822, time() - 120),
			"Cache-Control"=> "no-store, no-cache, must-revalidate",
			"Cache-Control"=> "post-check=0, pre-check=0",
			"Content-Type"=> $mime,
			"Content-Length"=>$size,
			"Content-Transfer-Encoding"=>"binary",
			"ZTag"=>date(DATE_RFC822, time())
		);
		
		// output headers
		foreach($headers as $n=>$v) {
			@header(sprintf("%s: %s", $n, $v));
		}
		
		// content disposition
	    if ((is_integer(strpos($ua, "msie"))) && (is_integer(strpos($ua, "win")))) {
            header( "Content-Disposition: filename=$filename;" );
        } else {
           header( "Content-Disposition: attachment; filename=$filename;" );
		}
     
		// buffer		
        echo $buffer;
        exit();
   }       
   
    # -------------------------------------------------------------------------------------------------------------------
    # (function) mgFileInfo    
    function mgFileInfo($fileid, $param) {
        return $_FILES[$fileid][$param];
    }   
	
	
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgFileInfo    
    function mgIsUploaded($fileid) {return is_uploaded_file(mgFileInfo($fileid, "tmp_name"));}   	
   
   
    # -------------------------------------------------------------------------------------------------------------------
    # (function) mgUploadeBase64File, uploads a base64 encoded file
	function mgUploadeBase64File($data, $filename = false) {
		// create filename
		$filename = $filename?$filename:tempnam(sys_get_temp_dir(), 'mg');
		// parse data
		$temp = explode(",", $data); if(count($temp)==2){$data = $temp[1];}
		// put into file
		file_put_contents($filename, base64_decode($data));
		// return filename
		return $filename;
	}
	
	
    # -------------------------------------------------------------------------------------------------------------------
    # (function) mgUploadFile, uploads a file and checks if file is valid
    function mgUploadFile($fileid, $filename=false, $directory=false, $accept=false, $acceptsize=FILESIZE_ANY, $fullerror=false) {
		// Initiate
        if (!isset($_FILES[$fileid])) return false;

        // Detect if an error encountered
        if (mgFileInfo($fileid, "error") != UPLOAD_ERR_OK) return $fullerror?FILE_ERROR_GENERALERROR:FILE_ERROR;
		
		
        // Detect File Size
        if($acceptsize!=FILESIZE_ANY&&mgFileInfo($fileid, "size")>$acceptsize) return $fullerror?FILE_ERROR_NOTACCEPTED_FILESIZE:FILE_ERROR;
       
        // Detect File Type
		if($accept!==false&&!mgIsMime(mgFileInfo($fileid, "type"), $accept)) return $fullerror?FILE_ERROR_NOTACCEPTED_FILETYPE:FILE_ERROR;
       
        // Check uploaded file
		$tempfilename = mgFileInfo($fileid, "tmp_name");
        if (!is_uploaded_file($tempfilename)) return $fullerror?FILE_ERROR_NOTUPLOADED:FILE_ERROR;
		
        // check if directory exists, otherwise create
		$directory = dirname($directory?$directory:$tempfilename);
        if(!file_exists($directory)){mgCreateDirectory($directory);}
       
        // process file upload
		$filename=$directory."/".basename($filename?$filename:CreateGUID()); //mgFileInfo($fileid, "name")
		if (!move_uploaded_file($tempfilename, $filename)) return $fullerror?FILE_ERROR_MOVEUPLOADED:FILE_ERROR;

		// return valid filename
		return $filename;
    }   
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgDeflateArchive, deflates an zip archive to the given directory or same directory
	function mgDeflateArchive($filename, $directory=false){
		// initialize
		$zip = zip_open($filename);
		// sanity check
		if($zip){
			// cycle archive
			while ($zip_entry = zip_read($zip)) {
				if(zip_entry_open($zip, $zip_entry, "r")) {
					$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$fna = ($directory?$directory:dirname($filename)).zip_entry_name($zip_entry);
					// get directory contents
					$fnd = dirname($fna."/");
					if(!file_exists($fnd)){mgCreateDirectory($fnd);}
					if(basename($fna)!="") {
						$fh = @fopen($fna, "w");
						@fwrite($fh, $buf);
						@fclose($fh);
					}
				}
				zip_entry_close($zip_entry);
			}
			zip_close($zip);
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgCreateArchive, creates an zip archive
	function mgCreateArchive($source, $filename) {
		$zip = new ZipArchive;
		$b = false;
		if($zip->open($filename, ZipArchive::CREATE)===true)  {
			$dir = preg_replace('/[\/]{2,}/', '/', $source."/");
			$dirs = array($dir);
			while (count($dirs)) {
				$dir = current($dirs);
				if($b) {$zip->addEmptyDir(str_replace($source, "", $dir));}
				$dh = opendir($dir);
				while($file = readdir($dh)) {
					if ($file != '.' && $file != '..') {
						if (is_file($dir.$file)) {
							$zip->addFile($dir.$file, str_replace($source, "", $dir).$file);
						} elseif (is_dir($dir.$file)) {
							$dirs[] = $dir.$file."/";
						} 
					}
				}
				$b = true;
				closedir($dh);
				array_shift($dirs);
			}
			$zip->close();
			return true;
		}
		return false;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgDeleteFile, delets a file (save way)
	function mgDeleteFile($filename) {if(file_exists($filename)){@unlink($filename);}}
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgDeleteDirectory, deletes a directory
	function mgDeleteDirectory($dir, $deleteroot = true){
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") mgDeleteDirectory($dir."/".$object, true); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			if($deleteroot) @rmdir($dir);
		}
 	}
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgTrailSlash, adds or removes trailing slash
	function mgTrailSlash($s, $add = false) {
		$has = substr($s, -1) == "/";
		if($add&&!$has) {
			return $s."/";
		} else if (!$add&&$has) {
			return substr($s, 0, -1);
		}
		return $s;
	}

	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgGetDirectoryFiles, returns the content of the directory as array
	function mgGetDirectoryFiles($directory, $recursive=false, $onlydirectories = false) {
		// initialize
		$result = Array();
		// sanity checl
		if(!is_dir($directory)) return $result;		
		// add trailing slash
		if(substr($directory, -1)!="/") $directory .= "/";
		// run open dir
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				if(!in_array($file, Array(".", ".."))) {
					$result[] = $directory.$file;
					if(is_dir($directory.$file) && $recursive) {
						$result = array_merge(mgGetDirectoryFiles($directory.$file, true), $result);
					}
				}
			}
		}
		closedir($handle);
		// sort
		sort($result);
		// return
		return $result;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgGetDirectoryFiles, returns the content of the directory as array
	function mgGetDirectory($directory, $includebasepath = false) {
		// initialize
		$result = Array();
		// sanity check
		if(!is_dir($directory)) return $result;		
		// run open dir
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				if($file!="."&&$file!="..") {
					$result[] = $includebasepath?$directory.$file:$file;
				}
			}
		}
		closedir($handle);
		// sort
		sort($result);
		// return
		return $result;
	}

	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgCopyDirectory
	function mgCopyDirectory($src,$dst) {
		$dir = opendir($src);
		mgCreateDirectory($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					mgCopyDirectory($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	} 
	
	
	# -------------------------------------------------------------------------------------------------------------------
    # (function) mgCreateDirectory
	function mgCreateDirectory($path, $recursive = true, $mode = 0777) {
		// retain mask
		$o = umask(0);
		// create dir
		@mkdir($path, $mode, $recursive);
		// reset mask
		umask($o);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# File functions
	# -------------------------------------------------------------------------------------------------------------------
	
	# (function) mgGetFileSuffix, returns the suffix of the filename
	function mgGetFileSuffix($filename) {
		preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix); return strtolower(@$fileSuffix[1]);
	}
	
	# (function) mgGetFileWithoutSuffix
	function mgGetFileWithoutSuffix($filename) {
		return current(explode(".", $filename));
	}
	
	# (function) mgGetFileSize, returns the file size as number
	function mgGetFileSize($filename) { return filesize($filename);}
	
	# (function) mgGetFileSizeString, returnst he file size as string
	function mgGetFileSizeString($filename) {
		$size = filesize($filename);
		$units = array(' bytes', ' kb', ' MB', ' GB', ' TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2).$units[$i];
	}
	
	# (function) mgGetSuffixName, returns the name based on the suffix 
	function mgGetSuffixName($filename) {
		// initialize suffix
		preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
		// switch
		switch(strtolower(@$fileSuffix[1])) {	
			case "js" :return "JavaScript";
			case "json" : return "JSON";
            case "jpg" : case "jpeg" : case "jpe" : return "JPEG Image";
			case "png":	 return "PNG Image"; 
            case "gif" : return "GIF Image";
			case "bmp" : return "Bitmap";
			case "tiff" : return "TIFF Image";
            case "css" : return "Stylesheet";
            case "xml" : return "XML Document";
            case "doc" : case "docx" : return "Word Document";
            case "xls" : case "xlt" : case "xlm" : case "xld" : case "xla" : case "xlc" : case "xlw" : case "xll" : return "Excel Document";
            case "ppt" : case "pps" : return "Powerpoint Document";
            case "rtf" : return "Rich Text";
            case "pdf" : return "Adobe PDF";
            case "html" : case "htm" : case "php" : return "HTML";
			case "txt" : return "Text";
            case "wmv": case "mov": case "avi": case "mpeg" : case "mpg" : case "mpe" : return "Video";
            case "mp3" : return "MP3 Audio";
            case "wav" : return "Audio";
            case "aiff" : case "aif" : return "Audio";
            case "zip" : return "ZIP Archive";
			case "rar" : return "RAR Archive";
            case "tar" : return "TAR Archive";
            case "swf" : return "Flash";
			case "svg":  return "Scalable Vector Graphics";
			case "woff": return "Web Open Font Format";
            default :return strtoupper(@$fileSuffix[1]). " File";
		}
	}
		
	# (function) mgGetMime, returns the mime type of the given file
	function mgGetMime($filename) {
		// initialize suffix
		preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
		// switch
		switch(strtolower(@$fileSuffix[1])) {
			case "gz": return "application/x-gzip";
			case "js" :return "text/javascript";
			case "json" : return "application/json";
            case "jpg" : case "jpeg" : case "jpe" : return "image/jpg";
            case "png" : case "gif" : case "bmp" : case "tiff" : return "image/".strtolower($fileSuffix[1]);
            case "css" : return "text/css";
            case "xml" : return "application/xml";
            case "doc" : case "docx" : return "application/msword";
            case "xls" : case "xlt" : case "xlm" : case "xld" : case "xla" : case "xlc" : case "xlw" : case "xll" : return "application/vnd.ms-excel";
            case "ppt" : case "pps" : return "application/vnd.ms-powerpoint";
            case "rtf" : return "application/rtf";
            case "pdf" : return "application/pdf";
            case "html" : case "htm" : case "php" : return "text/html";
			case "txt" : return "text/plain";
            case "mpeg" : case "mpg" : case "mpe" : return "video/mpeg";
            case "mp3" : return "audio/mpeg";
            case "wav" : return "audio/wav";
            case "aiff" : case "aif" : return "audio/aiff";
            case "avi" : return "video/msvideo";
            case "wmv" : return "video/x-ms-wmv";
            case "mov" : return "video/quicktime";
            case "zip" : return "application/zip";
            case "tar" : return "application/x-tar";
            case "swf" : return "application/x-shockwave-flash";
			case "svg" : return "image/svg+xml";
			case "woff": return "application/font-woff";
            default :return "unknown/" . trim(isset($filesuffix[0])?$fileSuffix[0]:"unknown");	
		}
	}
	
	# (function) mgGetMimeSimple, returns a simple version of mime types
	function mgGetMimeSimple($filename) {
		switch(mgGetFileSuffix($filename)) {
			case "js" : return "script";
			case "xml" : case "json" : return "data";
			case "css" : return "css";
            case "png" : case "gif" : case "bmp" : case "tiff" : case "jpg" : case "jpeg" : case "jpe" : return "image";
            case "html" : case "htm" : case "php" : return "html";
			case "txt" : return "text";
			default: return "unknown";
		}
	}

	# (function) mgGetMimeCategory, returns a mime category
	function mgGetMimeCategory($filename) {
		switch(mgGetFileSuffix($filename)) {
			case "zip": case "rar": case "tar" : case "gz" : return "archives";
			case "wav": case "mp3" : case "aiff" : return "music";
			case "avi": case "mov" : case "wmv" : case "mpg": case "mpeg" : return "videos";
            case "png" : case "gif" : case "bmp" : case "tiff" : case "jpg" : case "jpeg" : case "jpe" : return "images";
            case "js" : case "xml" : case "json" : case "css" : case "html" : case "htm" : case "php" : return "web";
			case "swf": return "media";
			case "rtf" : case "pdf" : case "ppt" : case "pps" : case "doc" : case "docx" : case "xls" : case "xlt" : case "xlm" : case "xld" : case "xla" : case "xlc" : case "xlw" : case "xll" : case "txt" : return "documents";
			default: return "unknown";
		}
	}	

	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgIsFileExpired
	function mgIsFileExpired($filename, $timeout) {
		// init
		$result = false;
		// check
		if(file_exists($filename)) {
			// get file modified
			$t = filemtime($filename);
			// check
			$result = $t!==false && ($t + $timeout) < time()?true:false;
		}
		return $result;		
	}
