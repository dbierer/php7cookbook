<?php
namespace Application\Captcha;

interface CaptchaInterface
{
    public function getLabel();
    public function getImage();
    public function getPhrase();
}