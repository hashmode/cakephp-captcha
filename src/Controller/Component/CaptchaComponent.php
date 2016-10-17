<?php

namespace CakephpCaptcha\Controller\Component;

use Cake\Controller\Component;
use CakephpCaptcha\Lib\PhpCaptcha;
use Cake\Event\Event;

/**
 * Captcha Component
 */
class CaptchaComponent extends Component
{
    /**
     * holds PhpCaptcha object
     * @var object
     */
    private $visualCaptcha = null;

    /**
     * startup method
     * 
     * @param Event $event
     */
    public function startup(Event $event)
    {
        $this->request->session()->read();
        $this->visualCaptcha = new PhpCaptcha();
    }

    /**
     * image method
     *
     * @param int $count            
     * @return string - the image's content
     */
    function image($count = 5)
    {
        $this->visualCaptcha->SetNumChars($count);
        return $this->visualCaptcha->Create();
    }

    /**
     * check method - checks if the provided code matches the previosly generated catpcha
     *
     * @param string $userCode            
     * @param boolean $caseInsensitive            
     * @return boolean
     */
    function check($userCode, $caseInsensitive = true)
    {
        return $this->visualCaptcha->Validate($userCode, $caseInsensitive);
    }

    /**
     * @TODO
     */
    function audio()
    {
        $oAudioCaptcha = new AudioPhpCaptcha('/usr/bin/flite', '/tmp/');
        $oAudioCaptcha->Create();
    }
}