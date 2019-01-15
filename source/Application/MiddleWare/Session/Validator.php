<?php
namespace Application\MiddleWare\Session;

use InvalidArgumentException;
use Psr\Http\Message\ { ServerRequestInterface, ResponseInterface };
use Application\MiddleWare\ { Constants, Response, TextStream };

class Validator
{
    const KEY_TEXT = 'text';
    const KEY_SESSION = 'thumbprint';
    const KEY_STATUS_CODE = 'code';
    const KEY_STATUS_REASON = 'reason';
    const KEY_STOP_TIME = 'stop_time';

    const ERROR_TIME = 'ERROR: session has exceeded stop time';
    const ERROR_SESSION = 'ERROR: session thumbprint does not match';
    const SUCCESS_SESSION = 'SUCCESS: session validates OK';

    protected $sessionKey;
    protected $currentPrint;
    protected $storedPrint;
    protected $currentTime;
    protected $storedTime;

    /**
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param int $stopTime = usually some value based on time()
     */
    public function __construct(ServerRequestInterface $request, $stopTime = NULL)
    {
        // session thumbprint
        $this->currentTime  = time();
        $this->storedTime   = $_SESSION[self::KEY_STOP_TIME] ?? 0;
        $this->currentPrint = md5($request->getServerParams()['REMOTE_ADDR']
                                . $request->getServerParams()['HTTP_USER_AGENT']
                                . $request->getServerParams()['HTTP_ACCEPT_LANGUAGE']);
        $this->storedPrint  = $_SESSION[self::KEY_SESSION] ?? NULL;
        // 1st time
        if (empty($this->storedPrint)) {
            $this->storedPrint = $this->currentPrint;
            $_SESSION[self::KEY_SESSION] = $this->storedPrint;
            if ($stopTime) {
                $this->storedTime = $stopTime;
                $_SESSION[self::KEY_STOP_TIME] = $stopTime;
            }
        }
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $code = 401;  // unauthorized
        if ($this->currentPrint != $this->storedPrint) {
            $text[self::KEY_TEXT] = self::ERROR_SESSION;
            $text[self::KEY_STATUS_REASON] = Constants::STATUS_CODES[401];
        } elseif ($this->storedTime) {
            if ($this->currentTime > $this->storedTime) {
                $text[self::KEY_TEXT] = self::ERROR_TIME;
                $text[self::KEY_STATUS_REASON] = Constants::STATUS_CODES[401];
            } else {
                $code = 200; // success
            }
        }
        if ($code == 200) {
            $text[self::KEY_TEXT] = self::SUCCESS_SESSION;
            $text[self::KEY_STATUS_REASON] = Constants::STATUS_CODES[200];
        }
        $text[self::KEY_STATUS_CODE] = $code;
        $body = new TextStream(json_encode($text));
        return $response->withStatus($code)->withBody($body);

    }

}
