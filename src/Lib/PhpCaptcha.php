<?php
/**
 * PhpCaptcha - A visual and audio CAPTCHA generation library
 * 
 * @author Edward Eliot
 * @copyright 2005-2006, Edward Eliot
 * @license BSD License
 * @link http://www.ejeliot.com/pages/2
 * 
 */
namespace CakephpCaptcha\Lib;

class PhpCaptcha {

	/** 
	 * session's var name to save the code
	 * @var string
	 */
	protected $sessionId = 'php_captcha';

	/**
	 * captcha's image
	 * @var string
	 */
	private $oImage = '';
	
	/**
	 * List of fonts 
	 * @var array
	 */
	protected $aFonts = array();
	
	/**
	 * image's width 
	 * @var int
	 */
	protected $iWidth = 300;
	
	/**
	 * image's height 
	 * @var int
	 */
	protected $iHeight = 70;
	
	/**
	 * number of characters to show 
	 * @var int
	 */
	protected $iNumChars = 5;
	
	/** 
	 * number of lines to draw
	 * @var int
	 */
	protected $iNumLines = 70;
	
	/**
	 * space between characters 
	 * @var int
	 */
	private $iSpacing = null;

	/**
	 * whether to show shadows of letters
	 * @var boolean
	 */
	protected $bCharShadow = false;
	
	/**
	 * to show some text under image
	 * @var boolean
	 */
	protected $sOwnerText = '';
	
	/**
	 * list of characters to use
	 * @var string|array
	 */
	protected $aCharSet = '';
	
	/**
	 * whenter compare input in a case insensitive manner
	 * @var boolean
	 */
	protected $bCaseInsensitive = true;
	
	/** 
	 * image(s) to use as background images
	 * @var string|array
	 */
	protected $vBackgroundImages = '';
	
	/** 
	 * mininum font size(px) of the characters
	 * @var int
	 */
	protected $iMinFontSize = 16;

	/** 
	 * maximum font size(px) of the characters
	 * @var int
	 */
	protected $iMaxFontSize = 25;
	
	/**
	 * whether to use colors
	 * @var boolean
	 */
	protected $bUseColour = true;
	
	/** 
	 * output image type
	 * @var string
	 */
	protected $sFileType = 'jpeg';
	
	/** 
	 * the current text of the captcha
	 * @var string
	 */
	private $sCode = '';

	
    function __construct()
    {
        $imagesPath = dirname(__FILE__) . DS . 'fonts' . DS;
        
        $aFonts = array(
            $imagesPath . 'VeraBd.ttf',
            $imagesPath . 'VeraIt.ttf',
            $imagesPath . 'Vera.ttf',
            $imagesPath . 'VeraBI.ttf',
            $imagesPath . 'VeraMoBd.ttf',
            $imagesPath . 'VeraMoBI.ttf',
            $imagesPath . 'VeraMoIt.ttf',
            $imagesPath . 'VeraMono.ttf',
            $imagesPath . 'VeraSe.ttf',
            $imagesPath . 'VeraSeBd.ttf'
        );
        
        $this->aFonts = $aFonts;
        
        $vCharSet = array_merge(range('A', 'Z'), range(1, 9));
        $this->SetCharSet($vCharSet);
    }

    function CalculateSpacing()
    {
        $this->iSpacing = (int) ($this->iWidth / $this->iNumChars);
    }

    function SetWidth($iWidth)
    {
        $this->iWidth = $iWidth;
        if ($this->iWidth > 500) {
            $this->iWidth = 500;
        }
        
        $this->CalculateSpacing();
    }

    function SetHeight($iHeight)
    {
        $this->iHeight = $iHeight;
        if ($this->iHeight > 200)
            $this->iHeight = 200; // to prevent performance impact
    }

    function SetNumChars($iNumChars)
    {
        $this->iNumChars = $iNumChars;
        $this->CalculateSpacing();
    }

    function SetNumLines($iNumLines)
    {
        $this->iNumLines = $iNumLines;
    }

    function DisplayShadow($bCharShadow)
    {
        $this->bCharShadow = $bCharShadow;
    }

    function SetOwnerText($sOwnerText)
    {
        $this->sOwnerText = $sOwnerText;
    }

    function SetCharSet($vCharSet)
    {
        // check for input type
        if (is_array($vCharSet)) {
            $this->aCharSet = $vCharSet;
        } else {
            if ($vCharSet != '') {
                // split items on commas
                $aCharSet = explode(',', $vCharSet);
                
                // initialise array
                $this->aCharSet = array();
                
                // loop through items
                foreach ($aCharSet as $sCurrentItem) {
                    // a range should have 3 characters, otherwise is normal character
                    if (strlen($sCurrentItem) == 3) {
                        // split on range character
                        $aRange = explode('-', $sCurrentItem);
                        
                        // check for valid range
                        if (count($aRange) == 2 && $aRange[0] < $aRange[1]) {
                            // create array of characters from range
                            $aRange = range($aRange[0], $aRange[1]);
                            
                            // add to charset array
                            $this->aCharSet = array_merge($this->aCharSet, $aRange);
                        }
                    } else {
                        $this->aCharSet[] = $sCurrentItem;
                    }
                }
            }
        }
    }

    function CaseInsensitive($bCaseInsensitive)
    {
        $this->bCaseInsensitive = $bCaseInsensitive;
    }

    function SetBackgroundImages($vBackgroundImages)
    {
        $this->vBackgroundImages = $vBackgroundImages;
    }

    function SetMinFontSize($iMinFontSize)
    {
        $this->iMinFontSize = $iMinFontSize;
    }

    function SetMaxFontSize($iMaxFontSize)
    {
        $this->iMaxFontSize = $iMaxFontSize;
    }

    function UseColour($bUseColour)
    {
        $this->bUseColour = $bUseColour;
    }

    function SetFileType($sFileType)
    {
        
        // check for valid file type
        if (in_array($sFileType, array(
            'gif',
            'png',
            'jpeg'
        ))) {
            $this->sFileType = $sFileType;
        } else {
            $this->sFileType = 'jpeg';
        }
    }

    function DrawLines()
    {
        for ($i = 0; $i < $this->iNumLines; $i ++) {
            // allocate colour
            if ($this->bUseColour) {
                $iLineColour = imagecolorallocate($this->oImage, rand(100, 250), rand(100, 250), rand(100, 250));
            } else {
                $iRandColour = rand(100, 250);
                $iLineColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
            }
            
            // draw line
            imageline($this->oImage, rand(0, $this->iWidth), rand(0, $this->iHeight), rand(0, $this->iWidth), rand(0, $this->iHeight), $iLineColour);
        }
    }


    function DrawOwnerText()
    {
        // allocate owner text colour
        $iBlack = imagecolorallocate($this->oImage, 0, 0, 0);
        // get height of selected font
        $iOwnerTextHeight = imagefontheight(2);
        // calculate overall height
        $iLineHeight = $this->iHeight - $iOwnerTextHeight - 4;
        
        // draw line above text to separate from CAPTCHA
        imageline($this->oImage, 0, $iLineHeight, $this->iWidth, $iLineHeight, $iBlack);
        
        // write owner text
        imagestring($this->oImage, 2, 3, $this->iHeight - $iOwnerTextHeight - 3, $this->sOwnerText, $iBlack);
        
        // reduce available height for drawing CAPTCHA
        $this->iHeight = $this->iHeight - $iOwnerTextHeight - 5;
    }


    function GenerateCode()
    {
        // reset code
        $this->sCode = '';
        
        // loop through and generate the code letter by letter
        for ($i = 0; $i < $this->iNumChars; $i ++) {
            if (count($this->aCharSet) > 0) {
                // select random character and add to code string
                $this->sCode .= $this->aCharSet[array_rand($this->aCharSet)];
            } else {
                // select random character and add to code string
                $this->sCode .= chr(rand(65, 90));
            }
        }
        
        // save code in session variable
        if ($this->bCaseInsensitive) {
            $_SESSION[$this->sessionId] = strtoupper($this->sCode);
        } else {
            $_SESSION[$this->sessionId] = $this->sCode;
        }
    }


    function DrawCharacters()
    {
        // loop through and write out selected number of characters
        for ($i = 0; $i < strlen($this->sCode); $i ++) {
            // select random font
            $sCurrentFont = $this->aFonts[array_rand($this->aFonts)];
            
            // select random colour
            if ($this->bUseColour) {
                $iTextColour = imagecolorallocate($this->oImage, rand(0, 100), rand(0, 100), rand(0, 100));
                
                if ($this->bCharShadow) {
                    // shadow colour
                    $iShadowColour = imagecolorallocate($this->oImage, rand(0, 100), rand(0, 100), rand(0, 100));
                }
            } else {
                $iRandColour = rand(0, 100);
                $iTextColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
                
                if ($this->bCharShadow) {
                    // shadow colour
                    $iRandColour = rand(0, 100);
                    $iShadowColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
                }
            }
            
            // select random font size
            $iFontSize = rand($this->iMinFontSize, $this->iMaxFontSize);
            
            // select random angle
            $iAngle = rand(- 33, 33);
            
            // get dimensions of character in selected font and text size
            $aCharDetails = imageftbbox($iFontSize, $iAngle, $sCurrentFont, $this->sCode[$i], array());
            
            // calculate character starting coordinates
            $iX = $this->iSpacing / 4 + $i * $this->iSpacing;
            $iCharHeight = $aCharDetails[2] - $aCharDetails[5];
            $iY = $this->iHeight / 2 + $iCharHeight / 4;
            
            // write text to image
            imagefttext($this->oImage, $iFontSize, $iAngle, $iX, $iY, $iTextColour, $sCurrentFont, $this->sCode[$i], array());
            
            if ($this->bCharShadow) {
                $iOffsetAngle = rand(- 30, 30);
                
                $iRandOffsetX = rand(- 5, 5);
                $iRandOffsetY = rand(- 5, 5);
                
                imagefttext($this->oImage, $iFontSize, $iOffsetAngle, $iX + $iRandOffsetX, $iY + $iRandOffsetY, $iShadowColour, $sCurrentFont, $this->sCode[$i], array());
            }
        }
    }

      
    function WriteFile($sFilename)
    {
        if ($sFilename == '') {
            // tell browser that data is jpeg
            header("Content-type: image/$this->sFileType");
        }
        
        switch ($this->sFileType) {
            case 'gif':
                $sFilename != '' ? imagegif($this->oImage, $sFilename) : imagegif($this->oImage);
                break;
            case 'png':
                $sFilename != '' ? imagepng($this->oImage, $sFilename) : imagepng($this->oImage);
                break;
            default:
                $sFilename != '' ? imagejpeg($this->oImage, $sFilename) : imagejpeg($this->oImage);
        }
    }

      
    function Create($sFilename = '')
    {
        try {
            if (! function_exists('imagecreate') || ! function_exists("image".$this->sFileType) || ($this->vBackgroundImages != '' && ! function_exists('imagecreatetruecolor'))) {
                throw new \Exception('GD Library not found');
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        // get background image if specified and copy to CAPTCHA
        if (is_array($this->vBackgroundImages) || $this->vBackgroundImages != '') {
            // create new image
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
            
            // create background image
            if (is_array($this->vBackgroundImages)) {
                $iRandImage = array_rand($this->vBackgroundImages);
                $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages[$iRandImage]);
            } else {
                $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages);
            }
            
            // copy background image
            imagecopy($this->oImage, $oBackgroundImage, 0, 0, 0, 0, $this->iWidth, $this->iHeight);
            
            // free memory used to create background image
            imagedestroy($oBackgroundImage);
        } else {
            // create new image
            $this->oImage = imagecreate($this->iWidth, $this->iHeight);
        }
        
        // allocate white background colour
        imagecolorallocate($this->oImage, 255, 255, 255);
        
        // check for owner text
        if ($this->sOwnerText != '') {
            $this->DrawOwnerText();
        }
        
        // check for background image before drawing lines
        if (! is_array($this->vBackgroundImages) && $this->vBackgroundImages == '') {
            $this->DrawLines();
        }
        
        $this->GenerateCode();
        $this->DrawCharacters();
        
        // write out image to file or browser
        $this->WriteFile($sFilename);
        
        // free memory used in creating image
        imagedestroy($this->oImage);
        
        return true;
    }


    function Validate($sUserCode, $bCaseInsensitive = true)
    {
        if ($bCaseInsensitive) {
            $sUserCode = strtoupper($sUserCode);
        }
        
        if (! empty($_SESSION[$this->sessionId]) && $sUserCode == $_SESSION[$this->sessionId]) {
            unset($_SESSION[$this->sessionId]);
            
            return true;
        }
        
        return false;
    }
	
}
