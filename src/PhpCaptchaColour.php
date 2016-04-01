<?php
namespace CakephpCaptcha;

use CakephpCaptcha\PhpCaptcha;

// example sub class
class PhpCaptchaColour extends PhpCaptcha {

	function PhpCaptchaColour($aFonts, $iWidth = CAPTCHA_WIDTH, $iHeight = CAPTCHA_HEIGHT) {
		parent::PhpCaptcha($aFonts, $iWidth, $iHeight);
		
		$this->UseColour(true);
	}
}
