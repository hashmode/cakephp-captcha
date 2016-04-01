<?php 
namespace CakephpCaptcha\Lib;

// this class will only work correctly if a visual CAPTCHA has been created first using PhpCaptcha
class AudioPhpCaptcha {
	var $sFlitePath;
	var $sAudioPath;
	var $sCode;

	function AudioPhpCaptcha(
			$sFlitePath = CAPTCHA_FLITE_PATH, // path to flite binary
			$sAudioPath = CAPTCHA_AUDIO_PATH // the location to temporarily store the generated audio CAPTCHA
	) {
		$this->SetFlitePath($sFlitePath);
		$this->SetAudioPath($sAudioPath);
		 
		// retrieve code if already set by previous instance of visual PhpCaptcha
		if (isset($_SESSION[CAPTCHA_SESSION_ID])) {
			$this->sCode = $_SESSION[CAPTCHA_SESSION_ID];
		}
	}

	function SetFlitePath($sFlitePath) {
		$this->sFlitePath = $sFlitePath;
	}

	function SetAudioPath($sAudioPath) {
		$this->sAudioPath = $sAudioPath;
	}

	function Mask($sText) {
		$iLength = strlen($sText);
		 
		// loop through characters in code and format
		$sFormattedText = '';
		for ($i = 0; $i < $iLength; $i++) {
			// comma separate all but first and last characters
			if ($i > 0 && $i < $iLength - 1) {
				$sFormattedText .= ', ';
			} elseif ($i == $iLength - 1) { // precede last character with "and"
				$sFormattedText .= ' and ';
			}
			$sFormattedText .= $sText[$i];
		}
		 
		$aPhrases = array(
			"The %1\$s characters are as follows: %2\$s",
			"%2\$s, are the %1\$s letters",
			"Here are the %1\$s characters: %2\$s",
			"%1\$s characters are: %2\$s",
			"%1\$s letters: %2\$s"
		);
		 
		$iPhrase = array_rand($aPhrases);
		 
		return sprintf($aPhrases[$iPhrase], $iLength, $sFormattedText);
	}

	function Create() {
		$sText = $this->Mask($this->sCode);
		$sFile = md5($this->sCode.time());

		// create file with flite
		shell_exec("$this->sFlitePath -t \"$sText\" -o $this->sAudioPath$sFile.wav");
		 
		// set headers
		header('Content-type: audio/x-wav');
		header("Content-Disposition: attachment;filename=$sFile.wav");
		 
		// output to browser
		echo file_get_contents("$this->sAudioPath$sFile.wav");
		 
		// delete temporary file
		@unlink("$this->sAudioPath$sFile.wav");
	}
}
