# Visual and Audio captcha for Cakephp 3

The **Cakephp 3.x** implementation of the following captcha
http://www.ejeliot.com/pages/2

installation should be done by composer

```
composer require hashmode/cakephp-captcha:~1.0
```

## How to use
Load from bootstrap
```
Plugin::load('CakephpCaptcha');
```

Load component in your controller's initialize function by
```
$this->loadComponent('CakephpCaptcha.Captcha');
```

Add some function in your controller to call from view
```
	public function image() {
	    $this->autoRender = false;
	    echo $this->Captcha->image(5);
	}

```

From view 
```
<img src="<?php echo $this->Url->build('/users/image');?>" />
```
You can concatenate some random chars by js to the url if the users refresh the captcha - to prevent cache-related issues


To check if the provided value is correct 
```
$this->Captcha->check($userSubmittedData)
```

