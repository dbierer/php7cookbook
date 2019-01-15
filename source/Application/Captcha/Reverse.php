<?php
namespace Application\Captcha;

class Reverse implements CaptchaInterface
{
    const DEFAULT_LABEL = 'Type this in reverse';
    protected $phrase;
    /**
     * Builds phrase
     *
     * @param string $label == what to show on screen
     * @param int $length == length of phrase
     * @param bool $includeNumbers       : (0 - 9)
     * @param string $includeUpper       : (A - Z)
     * @param string $includeLower       : (a - z)
     * @param string $includeSpecial     : (!"£$ etc.)
     * @param unknown $otherChars        : anything else!
     * @param array $suppressChars       : include an array of characters to be suppressed
     *                                     i.e. "O" or "l"
     */
    public function __construct(
        $label = self::DEFAULT_LABEL,
        $length = 6,
        $includeNumbers = TRUE,
        $includeUpper= TRUE,
        $includeLower= TRUE,
        $includeSpecial = FALSE,
        $otherChars = NULL,
        array $suppressChars = NULL)
    {
        $this->label  = $label;
        $this->phrase = new Phrase($length, $includeNumbers, $includeUpper,
								   $includeLower, $includeSpecial, $otherChars, $suppressChars);
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getImage()
    {
        return strrev($this->phrase->getPhrase());
    }

    public function getPhrase()
    {
        return $this->phrase->getPhrase();
    }

}
