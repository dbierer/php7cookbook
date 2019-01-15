<?php
namespace Application\Security;

use ReflectionClass;
use InvalidArgumentsException;
class SessOptions
{

	const ERROR_PARAMS              = 'ERROR: invalid parameters; session options must be included in the list of pre-defined options';

	const SESS_OP_NAME  			= 'name';				// default	"PHPSESSID"
	const SESS_OP_LAZY_WRITE 		= 'lazy_write'; 		// default "1" 	AVAILABLE SINCE PHP 7.0.0.
	const SESS_OP_SAVE_PATH 		= 'save_path';			// default	""
	const SESS_OP_SAVE_HANDLER 		= 'save_handler';		// default	"FILES"
	const SESS_OP_GC_PROBABILITY 	= 'gc_probability';		// default 	"1"
	const SESS_OP_GC_DIVISOR 		= 'gc_divisor';			// default	"100" 	AVAILABLE SINCE PHP 4.3.2.
	const SESS_OP_GC_MAXLIFETIME 	= 'gc_maxlifetime';		// default 	"1440"
	const SESS_OP_SERIALIZE_HANDLER = 'serialize_hander';	// default 	"PHP"
	const SESS_OP_COOKIE_PATH 		= 'cookie_path';		// default	"/"
	const SESS_OP_COOKIE_DOMAIN 	= 'cookie_domain';		// default	""
	const SESS_OP_COOKIE_SECURE 	= 'cookie_secure';		// default 	"" 	AVAILABLE SINCE PHP 4.0.4.
	const SESS_OP_COOKIE_HTTPONLY 	= 'cookie_httponly';	// default	"" 	AVAILABLE SINCE PHP 5.2.0.
	const SESS_OP_COOKIE_LIFETIME 	= 'cookie_lifetime';	// default 	"0"
	const SESS_OP_USE_COOKIES 		= 'use_cookies';		// default	"1"
	const SESS_OP_USE_ONLY_COOKIES 	= 'use_only_cookies';	// default	"1" 	AVAILABLE SINCE PHP 4.3.0.
	const SESS_OP_USE_STRICT_MODE 	= 'use_strict_mode';	// default	"0" 	AVAILABLE SINCE PHP 5.5.2.
	const SESS_OP_REFERER_CHECK 	= 'referer_check';		// default	""
	const SESS_OP_ENTROPY_FILE 		= 'entropy_file';		// default	""
	const SESS_OP_ENTROPY_LENGTH 	= 'entropy_length';		// default	"0"
	const SESS_OP_CACHE_EXPIRE 		= 'cache_expire';		// default	"180"
	const SESS_OP_CACHE_LIMITER 	= 'cache_limiter';		// default	"NOCACHE"
	const SESS_OP_USE_TRANS_SID 	= 'use_trans_sid';		// default	"0" IN PHP <= 4.2.3. PHP_INI_PERDIR IN PHP < 5. AVAILABLE SINCE PHP 4.0.3.
	const SESS_OP_HASH_FUNCTION 	= 'hash_function';		// default	"0" 	AVAILABLE SINCE PHP 5.0.0.
	const SESS_OP_URL_REWRITER_TAGS = 'url_rewriter.tags';	// default	"A=HREF,AREA=HREF,FRAME=SRC,FORM=,FIELDSET=" 	AVAILABLE SINCE PHP 4.0.4.
	const SESS_OP_HASH_BITS_PER_CHARACTER = 'hash_bits_per_character';	// default	"4" 	AVAILABLE SINCE PHP 5.0.0.

	protected $options;
	protected $allowed;

	public function __construct(array $options)
	{
		$reflect = new ReflectionClass(get_class($this));
		$this->allowed = $reflect->getConstants();
		$this->allowed = array_flip($this->allowed);
		unset($this->allowed[self::ERROR_PARAMS]);
		// make sure options are allowed
		foreach ($options as $key => $value) {
			if(!isset($this->allowed[$key])) {
				error_log(__METHOD__ . ':' . self::ERROR_PARAMS);
				throw new InvalidArgumentsException(self::ERROR_PARAMS);
			}
		}
		$this->options = $options;
	}

	public function getAllowed()
	{
		return $this->allowed;
	}

	public function start()
	{
		session_start($this->options);
	}

}
