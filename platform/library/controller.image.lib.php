<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Image Controller
	*/

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants

	define("IMAGE_LANDSCAPE", 0);
	define("IMAGE_PORTRAIT", 1);
	define("IMAGE_SQUARE", 2);
	
	define("IMAGETYPE_STRING", "string");
	define("IMAGETYPE_BASE64", "base64");
	
	define("BACKGROUND_NOREPEAT", "no-repeat");
	define("BACKGROUND_REPEATX", "repeat-x");
	define("BACKGROUND_REPEATY", "repeat-y");
	define("BACKGROUND_TILE", "tile");
	define("BACKGROUND_STRETCH", "stretch");
	
	define("PARAM_MARGIN", "margin");
	define("PARAM_PADDING", "padding");
	
	define("FONT_PATH", "fonts/");
	define("FONT_EXTENSION", ".ttf");
	
	define("TEXTMODE_SINGLE", "singleline");
	define("TEXTMODE_MULTI", "multiline");
	
	define("COLOR_TRANSPARENT", "none");
	
	define("MAX_WIDTH", 1500);
	define("MAX_HEIGHT", 1500);
	
	define("ROTATE_M90", -1);
	define("ROTATE_ZERO", 0);
	define("ROTATE_90", 1);
	define("ROTATE_180", 2);

	
	# -------------------------------------------------------------------------------------------------------------------
	# Helper Functions
	
	// mgCreateSizeFromParam
	function mgCreateSizeFromParam($param0=false){if($param0!=false){return explode("x",$param0);}}
	function mgParsePosition($params){$params=explode(";", $params);if(is_array($params)&&count($params)==4){return $params;}return false;}
	
	// mgResizeBase64Image, resizes a base64 image
	function mgResizeBase64Image($image, $width, $height, $html=true) {
		$img = new mgImage($image, is_file($image)?false:IMAGETYPE_BASE64);
		$img->ResizeAndCrop($width, $height, 'topcenter');
		return $img->Base64($html);
	}
	
	// mgBase64Image, transforms a image from/to base64
	function mgBase64Image($image, $asbinary = false) {
		$img = new mgImage($image, $asbinary?IMAGETYPE_BASE64:false);
		$img->Alpha();
		return $asbinary?$img->EmitRaw():$img->base64(true);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgImage
	class mgImage {
	
		public $s_uid;					// storage user data id
		public $image;					// Buffer for the working Image
		public $original_image;			// Buffer for store a copy of the image
		public $parameters;				// Internal Parameter Storage

		# ------------------------------------------------------------------------------------------------------------
		# __Construct
		public function __construct($param0=false, $param1=false){
			// get image type
			if($param1==IMAGETYPE_BASE64){
				$n = IMAGETYPE_STRING; 
				$data = explode(",", $param0);
				$param0 = base64_decode(count($data)==2?$data[1]:$data[0]);
			} else if (($param0!==false)&&(file_exists($param0))){
				$rect = GetImageSize($param0); $n = $rect[2]; 
			} else { 
				$n = false; 
			}
			// execute
			switch ($n) {
				// base64 Support
				case IMAGETYPE_STRING: $this->image = ImageCreateFromString($param0); break;
				// Gif Support
				case IMAGETYPE_GIF: $this->image = ImageCreateFromGIF($param0);break;
				// JPEG Support
				case IMAGETYPE_JPEG: $this->image = ImageCreateFromJPEG($param0);break;
				// PNG Support
				case IMAGETYPE_PNG: $this->image = ImageCreateFromPNG($param0);break;
				// Default, create new image
				default: $this->image = ImageCreateTrueColor(DefaultValue($param0, 150), DefaultValue($param1, 150));break;
			}
			// result
			$this->original_image = $this->image;
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# Output, outputs an image		
		public function EmitRaw() {$this->__Output($this->original_image);}
		public function Emit() {$this->__Output($this->image);}
		public function EmitNoCache() {$this->__Output($this->image, false);}
		
		# ------------------------------------------------------------------------------------------------------------
		# Returns the image as Base64
		public function Base64($html=true, $format = IMAGETYPE_PNG) {
			// create image
			ob_start();
			switch($format) {
				case IMAGETYPE_PNG: @ImagePNG($this->image); break;
				case IMAGETYPE_JPEG: @ImageJPEG($this->image, false, 85); break;
			}
			$image =  base64_encode(ob_get_contents());
			ob_end_clean();
			// return
			return ($html==true)?"data:image/png;base64,".$image:$image;
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# Parameter
		public function Parameter($param0=false, $param1=false){if($param0===false){return "";}if($param1===false){return @$this->parameters[$param0];} else {$this->parameters[$param0]=$param1;}}

		# ------------------------------------------------------------------------------------------------------------
		# asJPG
		public function asJPG($filename, $quality=100) {
			@ImageJPEG($this->image, $filename, $quality);
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# asPNG
		public function asPNG($filename) {
			@ImagePNG($this->image, $filename);
		}

		
		# ------------------------------------------------------------------------------------------------------------
		# __Output, outputs an image
		public function __Output($im, $cache = true, $param0=IMAGETYPE_PNG) {

			ob_start(); // start a new output buffer
			switch($param0){
				// PNG
				default: @ImagePNG($im); $m = "png"; break;
			}
			
			$cd = ob_get_contents();
			$cl = ob_get_length();
			ob_end_clean(); // stop this output buffer
			header("HTTP/1.1 200 OK"); 
			header("Content-type: image/$m");
			header("Content-Length: $cl");
			// cache headers
			header("Expires: " . date(DATE_RFC822, time() - (3600 * 24 * 365)));	
			header("Last-Modified: " . date(DATE_RFC822, time() - 120));
			header("Pragma: no-cache");	
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			print $cd;
			exit;
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# Property Ba
		
		
		# ------------------------------------------------------------------------------------------------------------
		# Property Bag
		public function Width(){return ImageSX($this->image);}
		public function Height(){return ImageSY($this->image);}
		public function Reset(){$this->image=$this->original_image;}
		public function Alpha($p=true){imagealphablending($this->image,$p);imagesavealpha($this->image,$p);}
		public function SaveAlpha($p=true){imagealphablending($this->image,!$p);imagesavealpha($this->image,$p);}
		public function AlphaBlending($p=true){imagealphablending($this->image,$p);}
		public function FullTransparent($p=true){imagealphablending($this->image,$p);imagesavealpha($this->image,$p);}
		
		# ------------------------------------------------------------------------------------------------------------
		# Filter
		public function FilterNegative(){return imagefilter($this->image, IMG_FILTER_NEGATE);}
		public function FilterGrayscale(){return imagefilter($this->image, IMG_FILTER_GRAYSCALE);}
		public function FilterGaussianBlur(){return imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);}
		public function FilterMeanRemoval(){return imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);}
		public function FilterSmooth($param0=100){return imagefilter($this->image, IMG_FILTER_SMOOTH, $param0);}
		public function FilterAntialias($param=true){return ImageAntialias($this->image, $param);}
		public function FilterColorize($r,$g,$b){return imagefilter($this->image, IMG_FILTER_COLORIZE, $r, $g, $b);}
		
		# ------------------------------------------------------------------------------------------------------------
		# Orientation
		public function Orientation(){if ($this->Height()>$this->Width())return IMAGE_PORTRAIT;return IMAGE_LANDSCAPE;}
		
		# ------------------------------------------------------------------------------------------------------------
		# Resize	
		public function Resize($param0=false,$param1=false){
			if (($param0==false)||($param1==false)){return false;}
			$dst = ImageCreateTrueColor($param0,$param1);
			$o   = ImageCopyReSampled($dst, $this->image, 0, 0, 0, 0, $param0, $param1, $this->Width(), $this->Height());
			if($o){$this->image = $dst;}
			return $o;
		}
		
		// resize Width
		public function ResizeWidth($param0=false){
			if($param0==false){return false;}
			return $this->Resize($param0, (($param0*$this->Height())/$this->Width()));
		}
		
		// resize Height
		public function ResizeHeight($param0=false){
			if($param0==false){return false;}
			return $this->Resize((($param0*$this->Width())/$this->Height()), $param0);
		}	
		
		// shrink to 
		public function Shrink($param0=false) {
			// execute
			if($this->Width()>=$this->Height()&&$this->Width()>$param0){return $this->ResizeWidth($param0);}
			if($this->Height()>=$this->Width()&&$this->Height()>$param0){return $this->ResizeHeight($param0);}
			// return false
			return false;
		}
		

		// resize Letterbox
		public function ResizeAndCrop($w,$h, $cm=false) {
			// prepare
			$nw=($h*$this->Width())/$this->Height();
			$nh=($w*$this->Height())/$this->Width();
			// execute
			if($w>=$h){$this->Resize($nw, $h);}
			if($h>$w){$this->Resize($w, $nh);}
			$this->Crop(0, 0, $w, $h, $cm);
		}
		
		// centers
		public function AutoCrop($w,$h, $cm=false) {
			// resize
			if($this->width() > $this->height()) {
				$this->ResizeHeight($h);
			} else {
				$this->ResizeWidth($w);
			}
			// prepare
			$centreX = round($this->width()/ 2);
			$centreY = round($this->height() / 2);
			$cropWidthHalf  = round($w / 2);
			$cropHeightHalf = round($h / 2);
			// get coordinates
			$x1 = max(0, $centreX - $cropWidthHalf);
			$y1 = max(0, $centreY - $cropHeightHalf);
			$x2 = min($this->width(), $centreX + $cropWidthHalf);
			$y2 = min($this->height(), $centreY + $cropHeightHalf);
			// center y
			$cy = (integer)round(($h-$this->height())/2);
			$cx = (integer)round(($w-$this->width())/2);

			
			// create image
			$dst = ImageCreateTrueColor($w, $h);
			$this->fillfull(255, 255, 255, $dst);
			// resample
			//var_dump(sprintf("%s %s %s %s", $x1, $y1, $x2, $y2));
			$o = ImageCopyResampled($dst, $this->image, $cx, $cy, $x1, $y1, $x2, $y2, $this->width(), $this->height());
			//$this->fill(255, 255, 255, $dst);
			if($o){$this->image=$dst;}
			return $o;
		}
		
		
		
		// auto resize
		public function AutoResize($sa){
			if($sa[0]=="auto"){
				$this->ResizeHeight($sa[1]);
			} else if ($sa[1]=="auto") {
				$this->ResizeWidth($sa[0]);
			} else {
				$this->ResizeAndCrop($sa[0], $sa[1]);
			}
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# Rotate
		public function Rotate($r, $a=false) {
			if($a) {
				switch($a) {
					case ROTATE_M90: $r = 270; break;
					case ROTATE_90: $r= 90; break;
					case ROTATE_180: $r= 180; break;
					default: return; break;
				}
			}
			$this->image = imagerotate($this->image, $r, 0);
			$this->FullTransparent();
		}
		
		
		# ------------------------------------------------------------------------------------------------------------
		# Inside Crop
		public function InsideCrop($x, $y, $w, $h) {
			$dst = ImageCreateTrueColor($w, $h);
			ImageCopyResampled($dst, $this->image, 0, 0, $x, $y, $w, $h, $w, $h);
			$this->image = $dst;
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# Crop
		public function Crop($x,$y,$w,$h,$cm=false){
			// test if w or h is lower than image size
			//if($this->Width()<$w){$w=$this->Width();}
			//if($this->Height()<$h){$h=$this->Height();}
			// create image
			$dst = ImageCreateTrueColor($w, $h);
			$this->FillFull(255, 255, 255, $dst);
			// run			
			switch($cm){
				case "topcenter": $x = ($this->Width()-$w) /2; break;
				case "center": $y = ($this->Height()-$h)/2;  $x = ($this->Width()-$w) /2; break;
			}	
			$o   = ImageCopyReSampled($dst, $this->image, $x, $y, 0, 0, $w, $h, $this->Width(), $this->Height());
			$this->fill(255, 255, 255, $dst);
			// return
			if($o){$this->image=$dst;}
			// make it white
			
			return $o;
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# CropRect
		public function CropRect($x, $y, $w, $h, $nw = false, $nh = false) {
			// reset image
			$this->Reset();
			// set size
			if($nw!==false&&$nh!==false) {
				$this->Resize($nw, $nh);
			}
			// crop
			$dst = ImageCreateTrueColor($w, $h);
			$this->FillFull(255, 255, 255, $dst);
			$o = ImageCopyResampled($dst, $this->image, 0, 0, $x, $y, $w, $h, $w, $h);
			if($o) {$this->image = $dst;}
			return $o;
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# Color
		public function Color($r,$g,$b){
			return ImageColorAllocate($this->image, $r,$g,$b);
		}
		
		public function ColorTransparent($r,$g,$b){
			imagecolortransparent($this->image, $this->Color($r, $g, $b));
		}
		
		# ------------------------------------------------------------------------------------------------------------
		# CanvasRect, returns the rect of the canvas
		public function CanvasRect(){return Array(0, 0, $this->Width(), $this->Height());}

		
		# ------------------------------------------------------------------------------------------------------------
		# Overlay, adds an transparent overlay to the image
		public function Overlay($overlayimage, $x, $y, $w, $h) {
			// Create Overlay Picture
			$o = new mgImage($overlayimage);
			$o->Alpha();
			// Create Transparent Image and activate alpha blending
			$t = ImageCreateTrueColor($o->Width(), $o->Height());
			ImageAlphaBlending($t, false);
			$color = ImageColorTransparent($t, imagecolorallocatealpha($t, 0, 0, 0, 127));
			ImageFill($t, 0, 0, $color);
			ImageSaveAlpha($t, true);			
			//imagecolortransparent($t, imagecolorallocate($t, 0, 0, 0));
			ImageAlphaBlending($t, True);
			// copy source image
			ImageCopyReSampled($t, $this->image, $x, $y, 0, 0, $w, $h, $this->Width(), $this->Height());
			// copy overlay image
			ImageCopy($t, $o->image, 0, 0, 0, 0, $o->Width(), $o->Height());
			//sset output buffer
			$this->image = $t;
		}	

		# ------------------------------------------------------------------------------------------------------------
		# FullOverlay, adds a picture on top of the picture
		public function FullOverlay($overlayimage, $x=0, $y=0) {
			// Create Overlay Picture
			$o = new mgImage($overlayimage);
			ImageAlphaBlending($o->image, false)	;
			ImageSaveAlpha($o->image, true);
			// add image
			ImageCopy($this->image, $o->image, $x, $y, 0, 0, $o->Width(), $o->Height());
		}	

		# ------------------------------------------------------------------------------------------------------------
		# MergeFromImage, merges image into canvas
		public function MergeFromImage($image, $x=0, $y=0, $w=0, $h=0) {ImageCopyMerge($this->image, $image, $x, $y, 0, 0, $w, $h, 100);}	
		
		# ------------------------------------------------------------------------------------------------------------
		# Fill
		public function Fill($r, $g, $b, $im = false) {
			if(!$im) $im = $this->image;
			imagefill($im, 0, 0, imagecolorallocate($im, $r, $g, $b));
		}
		
		public function FillFull($r, $g, $b, $im = false) {
			if(!$im) $im = $this->image;
			imagefilledrectangle($im, 0, 0, ImageSX($im), ImageSY($im), imagecolorallocate($im, $r, $g, $b));
		}
	}
	
