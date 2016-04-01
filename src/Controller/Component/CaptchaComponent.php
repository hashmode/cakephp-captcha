<?php
namespace CakephpCaptcha\Controller\Component;

use Cake\Controller\Component;
use CakephpCaptcha\Lib\PhpCaptcha;

/**
 * Captcha Component
 *
 */
class CaptchaComponent extends Component {

	function image($count = null) {
		if (!$count || $count < 3) {
			$count = 5;
		}
		
		$oVisualCaptcha = new PhpCaptcha();
		
		$oVisualCaptcha->UseColour(true);
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