<?php
namespace Api\Controller;

use Think\Controller;
use Common\Service\CaptchaService as Captcha;

class CaptchaController extends Controller
{
	private $height;
	private $weight;
	private $frontSize;
	private $codeLength;

	//return captcha
	public function code()
	{
		
	}
}
