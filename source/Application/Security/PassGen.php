<?php
namespace Application\Security;

/**
 * Generates passwords
 *
 * Things which make is more difficult to crack:
 * 1. Overall length MUST be > 6; each character above length 6 exponentially increases difficulty to crack
 * 2. Combinations of UPPER lower 9999 and $%%^^&&
 * 3. Dictionary attacks will guess words ... but mixing UPPER and lower will help to obscure
 * 4. Random placement of words, digits + special chars
 */
class PassGen
{

	const SOURCE_SUFFIX = 'src';
	const SPECIAL_CHARS = '\`¬|!"£$%^&*()_-+={}[]:@~;\'#<>?,./|\\';

	protected $algorithm;
	protected $sourceList;
	protected $word;
	protected $list;

	public function __construct(array $wordSource, $minWordLength, $cacheDir)
	{
		$this->processSource($wordSource, $minWordLength, $cacheDir);
		$this->initAlgorithm();
	}

	public function initAlgorithm()
	{
		$this->algorithm = [
			['word', 'digits', 'word', 'special'],
			['digits', 'word', 'special', 'word'],
			['word', 'word', 'special', 'digits'],
			['special', 'word', 'special', 'digits'],
			['word', 'special', 'digits', 'word', 'special'],
			['special', 'word', 'special', 'digits', 'special', 'word', 'special'],
		];
	}

	public function generate()
	{
		$pwd = '';
		$key = random_int(0, count($this->algorithm) - 1);
		foreach ($this->algorithm[$key] as $method) {
			$pwd .= $this->$method();
		}
		return str_replace("\n", '', $pwd);
	}

	/**
	 * Returns random int between 1 and $max
	 *
	 * @param int $max
	 * @return int $random
	 */
	public function digits($max = 999)
	{
		return random_int(1, $max);
	}

	/**
	 * Returns 1 special character
	 *
	 * @return string $char
	 */
	 public function special()
	 {
		$maxSpecial = strlen(self::SPECIAL_CHARS) - 1;
		return self::SPECIAL_CHARS[random_int(0, $maxSpecial)];
	 }

	/**
	 * Flips random characters to UPPERcase
	 *
	 * @param string $word
	 * @return string $flipped
	 */
	public function flipUpper($word)
	{
		$maxLen   = strlen($word);
		$numFlips = random_int(1, $maxLen - 1);
		$flipped  = strtolower($word);
		for ($x = 0; $x < $numFlips; $x++) {
			$pos = random_int(0, $maxLen - 1);
			$word[$pos] = strtoupper($word[$pos]);
		}
		return $word;
	}

	/**
	 * Chooses word from $wordSource
	 *
	 * @return string $word
	 */
	 public function word()
	 {
		$wsKey   = random_int(0, count($this->sourceList) - 1);
		$list    = file($this->sourceList[$wsKey]);
		$maxList = count($list) - 1;
		$word    = $list[random_int(0, $maxList)];
		return $this->flipUpper($word);
	}

	/**
	 * Processes $wordSource[$key]
	 * Creates simple hash of source URL
	 * Stores contents into file indicated by $cacheDir
	 *
	 * @param array $wordSource = list of URLs containing source for words
	 * @param int $minWordLength = minimum length accepted for words
	 * @param string $cacheDir = directory to store processed source files
	 */
	 public function processSource($wordSource, $minWordLength, $cacheDir)
	 {
		foreach ($wordSource as $html) {
			$hashKey = md5($html);
			$sourceFile = $cacheDir . '/' . $hashKey . '.' . self::SOURCE_SUFFIX;
			$this->sourceList[] = $sourceFile;
			if (!file_exists($sourceFile) || filesize($sourceFile) == 0) {
				echo 'Processing: ' . $html . PHP_EOL;
				$contents = file_get_contents($html);
				if (preg_match('/<body>(.*)<\/body>/i', $contents, $matches)) {
					$contents = $matches[1];
				}
				$list = str_word_count(strip_tags($contents), 1);
				// get rid of words which are too short
				foreach ($list as $key => $value) {
					if (strlen($value) < $minWordLength) {
						$list[$key] = 'xxxxxx';
					} else {
						$list[$key] = trim($value);
					}
				}
				$list = array_unique($list);
				file_put_contents($sourceFile, implode("\n",$list));
			}
		}
		return TRUE;
	}

}
