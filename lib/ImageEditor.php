<?php

/**
 * ImageEditor is a oop wrapper for php image-copy functions
 * adding resizing fillin and fitin style
 *
 * @access public
 * @author Kai Rautenberg, <mail@kairautenberg.de>
 */
class ImageEditor
{

	/**
	 * dimension x of imagedata ( gdImg )
	 *
	 * @var int
	 */
    public $width = 0;

    /**
     * dimension y of imagedata ( gdImg )
     *
     * @var unknown_type
     */
    public $height = 0;

    /**
     * possible image types to load
     *
     */
    const JPG = 'JPG';
    const PNG = 'PNG';
    const GIF = 'GIF';
    
    /**
     * type of image : 'JPG', 'PNG', 'GIF'
     *
     * @var string
     */
    public $type = null;
    
    /**
     * holds the gdImagedata
     *
     * @var resource
     */
    private $gdImg = null;
    
    public $bgColor = 0x000000; // 0x0033CC;

    /**
     * empty untill now
     *
     */
    function __construct()
    {
		;
    }
	
    /**
     * fill gdImg with an empty GD-Image of size (width/height)
     *
     * @param unknown_type $x
     * @param unknown_type $y
     */
    public function createCanvas($width, $height)
    {
       	$this->gdImg = imagecreatetruecolor ( $width, $height );
       	$this->loadImageData();
    }
    
    /**
     * load image from disk
     *
     * @param string $path
     * @param string $type 'JPG', 'PNG', 'GIF'
     * @return boolean
     */
    public function loadImageFile( $path, $type = null)
    {
    	// grep imagetype from filename
    	if($type == null)
    	{
    		if(preg_match('/(\.jpg|\.jpeg)$/i', $path))
    			$this->type = ImageEditor::JPG;
    		elseif(preg_match('/(\.png)$/i', $path))
    			$this->type = ImageEditor::PNG;
    		elseif(preg_match('/(\.gif)$/i', $path))
    			$this->type = ImageEditor::GIF;
    		else 
    			return false;
    	}
    	else 
    		$this->type = $type;
    	
    	$this->gdImg = null;

    	switch($this->type)
    	{
    		case ImageEditor::JPG :
	    		$this->gdImg = @imagecreatefromjpeg($path);
	    		break;
    		case ImageEditor::PNG :
	    		$this->gdImg = @imagecreatefrompng($path);
	    		break;
    		case ImageEditor::GIF :
	    		$this->gdImg = @imagecreatefromgif($path);
	    		break;
    	}

    	if($this->gdImg)
    	{
    		$this->loadImageData();
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }
    
    /**
     * fill an area of image with another image, scaled and drifted
     *
     * @param ImageEditor $fillImg
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $driftX
     * @param int $driftY
     */
	public function fillinArea(ImageEditor $fillImg, $x, $y , $width, $height, $driftX = 0, $driftY = 0 )
	{
		// create a mask for area
		$maskImg = new ImageEditor();
		$maskImg->createCanvas($width, $height);
		// fill mask
		$maskImg->fillin($fillImg, $driftX, $driftY);
		// drop masked image into image
		$this->dropin($maskImg, $x, $y);
	}

	/**
	 * drop image into another image option offset (x/y)
	 *
	 * @param ImageEditor $dropImg
	 * @param int $x
	 * @param int $y
	 */
	public function dropin(ImageEditor $dropImg, $x = 0, $y = 0)
	{
    	imagecopy ($this->gdImg, $dropImg->gdImg, $x,$y, 0, 0, $dropImg->width, $dropImg->height);		
	}
	
	/**
	 * fills canvas with fillImage,
	 * centers fillImage , applies drift in x and y direction to the mask of the crop
	 *
	 * @param ImageEditor $fillImg
	 * @param int $driftX
	 * @param int $driftY
	 */
	public function fillin( ImageEditor $fillImg, $driftX = 0, $driftY = 0)
    {
    	// scale canvas to fill image size max	
    	$this->width = ($fillImg->width < $this->width)? $fillImg->width : $this->width;
    	$this->height = ($fillImg->height < $this->height)? $fillImg->height : $this->height;

    	$this->widthRatio = $fillImg->width / $this->width;
    	$this->heightRatio = $fillImg->height / $this->height;
 
    	if( $this->widthRatio > 1 ||  $this->heightRatio > 1 ){
    		if( $this->widthRatio > $this->heightRatio){
    			$cropWidth = round( $this->width /$this->height * $fillImg->height);
    			$cropHeight = $fillImg->height;
    		}
    		else{
    			$cropWidth = $fillImg->width;
    			$cropHeight = round( $this->height / $this->width * $fillImg->width);
    		}

    		$cropMarginX = round(($fillImg->width - $cropWidth) / 2);
    		$cropMarginY = round(($fillImg->height - $cropHeight) /2);
    		
    		/**
    		 * add drift to margin
    		 * if drifting out of fillImg move to edge
    		 */
    		if($driftX > 0)
	    		$cropMarginX = ( ($cropMarginX + $cropWidth + $driftX) > $fillImg->width )?
	    			 ($fillImg->width - $cropWidth) :  ($cropMarginX + $driftX);
    		elseif($driftX < 0)		    		
    			$cropMarginX = ( ($cropMarginX + $driftX) < 0 )? 0 : ($cropMarginX + $driftX) ;
    		
    		if($driftY > 0)
	    		$cropMarginY = ( ($cropMarginY + $cropHeight + $driftY) > $fillImg->height )? 
	    			($fillImg->height - $cropHeight) :  ($cropMarginY + $driftY);
    		elseif($driftY < 0)
	    		$cropMarginY = ( ($cropMarginY + $driftY) < 0 )? 0 : ($cropMarginY + $driftY);	    	

			$this->gdImg =  imagecreatetruecolor ($this->width, $this->height);

    		imagecopyresampled( $this->gdImg, $fillImg->gdImg, 0, 0,
    		$cropMarginX, $cropMarginY,
    		$this->width, $this->height, 
    		$cropWidth, $cropHeight);
    	}
    	else{
			$this->gdImg =  imagecreatetruecolor ($this->width, $this->height);
    		imagecopy ($this->gdImg, $fillImg->gdImg, 0,0,0,0, $fillImg->width, $fillImg->height);
    	}
    	// reload the image dimensions
    	$this->loadImageData();
    }

    /**
     * fits an image into canvas  with maximum size of canvas (width/height),
     * scales down canvas size if fitin image doens't fill canvas
     *
     * @param ImageEditor $fitImg
     */
    public function fitin( ImageEditor $fitImg)
    {
    	if($this->gdImg)
    	{
			$width = ($this->width < $fitImg->width)? $this->width : $fitImg->width ;
			$height = ($this->height < $fitImg->height)? $this->height : $fitImg->height ;
			
			$widthRatio = $fitImg->width / $width;
			$heightRatio =$fitImg->height / $height;
		
			/**
			 * just scale if width or height of fitImg greater than canvas
			 * else : copy only
			 */
			if( $widthRatio > 1 ||  $heightRatio > 1 ){
				
				// start calculate size from the greater sidelength
				if( $widthRatio > $heightRatio){
					$cropWidth = round( $width /$height * $fitImg->height);
					$cropHeight = $fitImg->height;
				}
				else{
					$cropWidth = $fitImg->width;
					$cropHeight = round( $height / $width * $fitImg->width);		
				}
		
				// get the margin to apply on the copyprocess to center the image
				$cropMarginX = round(($fitImg->width - $cropWidth) / 2);
				$cropMarginY = round(($fitImg->height - $cropHeight) /2);
				
				$this->gdImg =  imagecreatetruecolor ($width, $height );
		
			 	imagecopyresampled($this->gdImg, $fitImg->gdImg, 0, 0, 
											$cropMarginX, $cropMarginY, 
											$width, $height,
											$cropWidth, $cropHeight);
			}
			else{
				$this->gdImg =  imagecreatetruecolor ($width, $height );
				imagecopy ($this->gdImg, $fitImg->gdImg, 0,0,0,0, $width, $height);
			}
			$this->loadImageData();
    	}   	
    }
    
    /**
     * loads the parameter of an image from source
     *
     */
    public function loadImageData()
    {
    	if($this->gdImg)
    	{
	    	$this->width = imagesx($this->gdImg);
	    	$this->height = imagesy($this->gdImg);
    	}
    }

    /**
     * writes the image to disk
     *
     * @param string $path
     * @param string $type
     * @param int $jpgQuality
     * @return boolean
     */
    public function writeImageFile( $path, $type = null, $jpgQuality = 75)
    {
    	// overwrite imagetype
    	if($type == null)
    		$type = $this->type;
    
    	// write image to disk
    	switch($type)
    	{
    		case ImageEditor::JPG :
				imagejpeg($this->gdImg, $path, $jpgQuality);
	    		break;
    		case ImageEditor::PNG :
    			imagepng($this->gdImg,$path);
	    		break;
    		case ImageEditor::GIF :
				imagegif($this->gdImg, $path);
				break;
    		default:
    		 // no type set 
    		 return false;
    	}
    	return true;
    }

    /**
     * displays Image to browser
     * sends httpHeader according to type of image
     *
     * @param string $type
     * @param boolean $displayHttpHeader
     * @param int $jpgQuality
     * @return boolean
     */
    public function displayImage($type = null, $displayHttpHeader = true, $jpgQuality = 75)
    {
    	// overwrite imagetype
    	if($type == null)
    		$type = $this->type;
    		
    	// display http header
    	if($displayHttpHeader)
    	{
    		switch ($type)
    		{
    		    case ImageEditor::JPG :
                    header('Content-type: image/jpeg');
                	break;
                case ImageEditor::PNG :
                    header('Content-type: image/png');
                	break;
                case ImageEditor::GIF :
                    header('Content-type: image/gif');
                    break;
                default:
                	return false;
    		}
    	}

    	// display image
    	switch($type)
    	{
    		case ImageEditor::JPG :
				imagejpeg($this->gdImg, '', $jpgQuality);
	    		break;
    		case ImageEditor::PNG :
    			imagepng($this->gdImg);
	    		break;
    		case ImageEditor::GIF :
				imagegif($this->gdImg);
				break;
    		default:
    		 // no type set 
    		 return false;
    	}
    	return true;
    }
     
    /**
     * fixes the lack of debian php5.1 compiled with a gd-library not supporting rotate function
     *
     * @param unknown_type $angle
     */
	public function rotate($angle = 0) 
	{

		// convert degrees to radians
		$angle = $angle + 180;
		$angle = deg2rad($angle);

		$src_x = imagesx($this->gdImg);
		$src_y = imagesy($this->gdImg);

		$center_x = floor($src_x/2);
		$center_y = floor($src_y/2);

		$cosangle = cos($angle);
		$sinangle = sin($angle);

		$corners=array(array(0,0), array($src_x,0), array($src_x,$src_y), array(0,$src_y));

		foreach($corners as $key=>$value) 
		{
			$value[0]-=$center_x;        //Translate coords to center for rotation
			$value[1]-=$center_y;
			$temp=array();
			$temp[0]=$value[0]*$cosangle+$value[1]*$sinangle;
			$temp[1]=$value[1]*$cosangle-$value[0]*$sinangle;
			$corners[$key]=$temp;
		}

		$min_x=1000000000000000;
		$max_x=-1000000000000000;
		$min_y=1000000000000000;
		$max_y=-1000000000000000;

		foreach($corners as $key => $value) 
		{
			if($value[0]<$min_x)
			$min_x=$value[0];
			if($value[0]>$max_x)
			$max_x=$value[0];

			if($value[1]<$min_y)
			$min_y=$value[1];
			if($value[1]>$max_y)
			$max_y=$value[1];
		}

		$rotate_width=round($max_x-$min_x);
		$rotate_height=round($max_y-$min_y);

		$rotate=imagecreatetruecolor($rotate_width,$rotate_height);
		imagealphablending($rotate, false);
		imagesavealpha($rotate, true);

		//Reset center to center of our image
		$newcenter_x = ($rotate_width)/2;
		$newcenter_y = ($rotate_height)/2;

		for ($y = 0; $y < ($rotate_height); $y++) 
		{
			for ($x = 0; $x < ($rotate_width); $x++) 
			{
				// rotate...
				$old_x = round((($newcenter_x-$x) * $cosangle + ($newcenter_y-$y) * $sinangle))
				+ $center_x;
				$old_y = round((($newcenter_y-$y) * $cosangle - ($newcenter_x-$x) * $sinangle))
				+ $center_y;

				if ( $old_x >= 0 && $old_x < $src_x && $old_y >= 0 && $old_y < $src_y ) 
				{

					$color = imagecolorat($this->gdImg, $old_x, $old_y);
				} else 
				{
					// this line sets the background colour
					$color = imagecolorallocatealpha($this->gdImg, 255, 255, 255, 127);
				}
				imagesetpixel($rotate, $x, $y, $color);
			}
		}


		imagedestroy($this->gdImg);
		$this->gdImg = $rotate;
		$this->loadImageData();

	}
}

?>
