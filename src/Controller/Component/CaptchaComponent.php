<?php
namespace CakephpCaptcha\Controller\Component;

use Cake\Controller\Component;
use CakephpCaptcha\PhpCaptcha;
/**
 * Captcha Component
 *
 */
class CaptchaComponent extends Component {
	var $controller;

	function image($count = null) {
		if (!$count || $count < 3) {
			$count = 5;
		}
		
		$imagesPath = APP . 'Vendor' . DS . 'phpcaptcha' . DS . 'fonts' . DS;
		
		$aFonts = array(
			$imagesPath . 'VeraBd.ttf',
			$imagesPath . 'VeraIt.ttf',
			$imagesPath . 'Vera.ttf' 
		);
		
		$oVisualCaptcha = new PhpCaptcha($aFonts, 200, 60);
		
		$oVisualCaptcha->UseColour(true);
		// $oVisualCaptcha->SetOwnerText('Source:
		// '.FULL_BASE_URL);
		$oVisualCaptcha->SetNumChars($count);
		return $oVisualCaptcha->Create();
	}

	function audio() {
		$oAudioCaptcha = new AudioPhpCaptcha('/usr/bin/flite', '/tmp/');
		$oAudioCaptcha->Create();
	}

	function check($userCode, $caseInsensitive = true) {
		if ($caseInsensitive) {
			$userCode = strtoupper($userCode);
		}
		
		if ($userCode && $userCode == $this->request->session()->consume(CAPTCHA_SESSION_ID)) {
			return true;
		}
		
		return false;
	}
}