<?php
namespace Application\Captcha;

/**
 * Generates an Image from the CAPTCHA phrase
 * NOTE: *requires* the GD extension
 */
 use DirectoryIterator;

class Image implements CaptchaInterface
{

	const DEFAULT_WIDTH = 200;
	const DEFAULT_HEIGHT = 50;
    const DEFAULT_LABEL = 'Enter this phrase';
	const DEFAULT_BG_COLOR = [255,255,255];
	const DEFAULT_URL = '/captcha';
	const IMAGE_PREFIX = 'CAPTCHA_';
	const IMAGE_SUFFIX = '.jpg';
	const IMAGE_EXP_TIME = 300;				// seconds
	const ERROR_REQUIRES_GD = 'Requires the GD extension + the JPEG library';
	const ERROR_IMAGE = 'Unable to generate image';

    protected $phrase;
    protected $imageFn;
    protected $label;
    protected $imageWidth;
    protected $imageHeight;
    protected $imageRGB;
    protected $imageDir;
    protected $imageUrl;

    /**
     * Builds phrase
     *
     * @param string $imageDir			 : directory where image file will be written
     * @param string $imageUrl			 : base URL for image
     * @param string $imageFont          : True Type Font file
     * @param string $label == what to show on screen
     * @param int $length == length of phrase
     * @param bool $includeNumbers       : (0 - 9)
     * @param string $includeUpper       : (A - Z)
     * @param string $includeLower       : (a - z)
     * @param string $includeSpecial     : (!"£$ etc.)
     * @param unknown $otherChars        : anything else!
     * @param array $suppressChars       : include an array of characters to be suppressed
     *                                     i.e. "O" or "l"
     * @param int $imageWidth			 : image width in px
     * @param int $imageHeight			 : image height in px
     * @param array $imageRGB			 : image RGB as [red,green,blue] where red,green,blue == (int) 0-255
     */
    public function __construct(
        $imageDir,
        $imageUrl,
        $imageFont = NULL,
        $label = NULL,
        $length = NULL,
        $includeNumbers = TRUE,
        $includeUpper= TRUE,
        $includeLower= TRUE,
        $includeSpecial = FALSE,
        $otherChars = NULL,
        array $suppressChars = NULL,
        $imageWidth = NULL,
        $imageHeight = NULL,
        array $imageRGB = NULL
        )
    {
		if (!function_exists('imagecreatetruecolor')) {
			throw new \Exception(self::ERROR_REQUIRES_GD);
		}
        $this->imageDir = $imageDir;
        $this->imageUrl = $imageUrl;
        $this->imageFont = $imageFont;
        $this->label  = $label ?? self::DEFAULT_LABEL;
        $this->imageWidth = $imageWidth ?? self::DEFAULT_WIDTH;
        $this->imageHeight = $imageHeight ?? self::DEFAULT_HEIGHT;
        $this->imageRGB = $imageRGB ?? self::DEFAULT_BG_COLOR;
        if (substr($imageUrl, -1, 1) == '/') {
            $imageUrl = substr($imageUrl, 0, -1);
        }
        $this->imageUrl = $imageUrl;
        if (substr($imageDir, -1, 1) == DIRECTORY_SEPARATOR) {
            $imageDir = substr($imageDir, 0, -1);
        }

		// generate phrase
        $this->phrase = new Phrase($length, $includeNumbers, $includeUpper,
                                   $includeLower, $includeSpecial, $otherChars, $suppressChars);

		// clean up old image files
		$this->removeOldImages();

		// generate CAPTCHA image
        $this->generateJpg();
    }

	public function generateJpg()
	{
		try {
			list($red,$green,$blue) = $this->imageRGB;
			$im = imagecreatetruecolor($this->imageWidth, $this->imageHeight);
			$black = imagecolorallocate($im, 0, 0, 0);
			$imageBgColor = imagecolorallocate($im, $red, $green, $blue);

			// Make the background $imageBgColor
			imagefilledrectangle($im, 0, 0, $this->imageWidth, $this->imageHeight, $imageBgColor);

			// define margins
			$xMargin = (int) ($this->imageWidth * .1 + .5);
			$yMargin = (int) ($this->imageHeight * .3 + .5);

			// print phrase onto image
			$phrase = $this->getPhrase();
			$max = strlen($phrase);
			$count = 0;
			$x = $xMargin;
			$size = 5;
			for ($i = 0; $i < $max; $i++) {
				if ($this->imageFont) {
					$size = rand(12, 32);
					$angle = rand(0, 30);
					$y = rand($yMargin + $size, $this->imageHeight);
					imagettftext($im, $size, $angle, $x, $y, $black, $this->imageFont, $phrase[$i]);
					// adjust $x
					$x += (int) ($size  + rand(0,5));
				} else {
					$y = rand(0, ($this->imageHeight - $yMargin));
					if ($count++ & 1) {
						// NOTE: using PHP 7 string dereferencing $phrase[$i]
						imagechar($im, 5, $x, $y, $phrase[$i], $black);
					} else {
						imagecharup($im, 5, $x, $y, $phrase[$i], $black);
					}
					// adjust $x
					$x += (int) ($size * 1.2);
				}
			}

			// add random dots
			$numDots = rand(10, 999);
			for ($i = 0; $i < $numDots; $i++) {
				imagesetpixel($im, rand(0, $this->imageWidth), rand(0, $this->imageHeight), $black);
			}

			// generate random image filename
			$this->imageFn = self::IMAGE_PREFIX . md5(date('YmdHis') . rand(0,9999)) . self::IMAGE_SUFFIX;
			imagejpeg($im, $this->imageDir . DIRECTORY_SEPARATOR . $this->imageFn);
			imagedestroy($im);
		} catch (\Throwable $e) {
			error_log(__METHOD__ . ':' . $e->getMessage());
			throw new \Exception(self::ERROR_IMAGE);
		}
	}

	public function removeOldImages()
	{
		$old = time() - self::IMAGE_EXP_TIME;
		foreach (new DirectoryIterator($this->imageDir) as $fileInfo) {
			if($fileInfo->isDot()) continue;
			if ($fileInfo->getATime() < $old) {
				unlink($this->imageDir . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
			}
		}
	}

    public function getLabel()
    {
        return $this->label;
    }

    public function getImage()
    {
        return sprintf('<img src="%s/%s" />', $this->imageUrl, $this->imageFn);
    }

    public function getPhrase()
    {
        return $this->phrase->getPhrase();
    }

}
