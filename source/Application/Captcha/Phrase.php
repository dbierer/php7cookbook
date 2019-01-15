<?php
namespace Application\Captcha;

/**
 * Generates random phrase
 *
 */
class Phrase
{
	const DEFAULT_LENGTH   = 5;
	const DEFAULT_NUMBERS  = '0123456789';
	const DEFAULT_UPPER    = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
	const DEFAULT_LOWER    = 'abcdefghijklmnopqrstuvwxyz';
	const DEFAULT_SPECIAL  = '¬\`|!"£$%^&*()_-+={}[]:;@\'~#<,>.?/|\\';
	const DEFAULT_SUPPRESS = ['O','l'];

    protected $phrase;
    protected $includeNumbers;
    protected $includeUpper;
    protected $includeLower;
    protected $includeSpecial;
    protected $otherChars;
    protected $suppressChars;
    protected $string;
    protected $length;

    /**
     * Builds phrase
     *
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
        $length = NULL,
        $includeNumbers = TRUE,
        $includeUpper= TRUE,
        $includeLower= TRUE,
        $includeSpecial = FALSE,
        $otherChars = NULL,
        array $suppressChars = NULL)
    {
        $this->length = $length ?? self::DEFAULT_LENGTH;
        $this->includeNumbers = $includeNumbers;
        $this->includeUpper = $includeUpper;
        $this->includeLower = $includeLower;
        $this->includeSpecial = $includeSpecial;
        $this->otherChars = $otherChars;
        $this->suppressChars = $suppressChars ?? self::DEFAULT_SUPPRESS;
        $this->phrase = $this->generatePhrase();
    }

    public function generatePhrase()
    {
        $phrase = '';
        $this->string = $this->initString();
        $max = strlen($this->string) - 1;
        for ($x = 0; $x < $this->length; $x++) {
            $phrase .= substr($this->string, random_int(0, $max), 1);
        }
        return $phrase;
    }

    public function initString()
    {
        $string = '';
        if ($this->includeNumbers) {
            $string .= self::DEFAULT_NUMBERS;
        }
        // NOTE: we get rid of letters which can be confused with numbers
        if ($this->includeUpper) {
            $string .= self::DEFAULT_UPPER;
        }
        // NOTE: we get rid of letters which can be confused with numbers
        if ($this->includeLower) {
            $string .= self::DEFAULT_LOWER;
        }
        if ($this->includeSpecial) {
            $string .= self::DEFAULT_SPECIAL;
        }
        if ($this->otherChars) {
            $string .= $this->otherChars;
        }
        if ($this->suppressChars) {
			$string = str_replace($this->suppressChars, '', $string);
		}
        return $string;
    }

    public function __toString()
    {
        return $this->phrase;
    }

    public function getString()
    {
        return $this->string;
    }

    public function setString($string)
    {
        $this->string = $string;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setLength($length)
    {
        $this->length = $length;
    }

    public function getPhrase()
    {
        return $this->phrase;
    }

    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;
    }

    public function getIncludeNumbers()
    {
        return $this->includeNumbers;
    }

    public function setIncludeNumbers($includeNumbers)
    {
        $this->includeNumbers = $includeNumbers;
    }

    public function getIncludeUpper()
    {
        return $this->includeUpper;
    }

    public function setIncludeUpper($includeUpper)
    {
        $this->includeUpper = $includeUpper;
    }

    public function getIncludeLower()
    {
        return $this->includeLower;
    }

    public function setIncludeLower($includeLower)
    {
        $this->includeLower = $includeLower;
    }

    public function getIncludeSpecial()
    {
        return $this->includeSpecial;
    }

    public function setIncludeSpecial($includeSpecial)
    {
        $this->includeSpecial = $includeSpecial;
    }
}
