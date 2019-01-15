<?php
namespace Application\Acl;

use Application\MiddleWare\ { Response, TextStream };
use Psr\Http\Message\ { RequestInterface, ResponseInterface };

class Authenticate
{

    const ERROR_AUTH = 'ERROR: invalid token';
    const DEFAULT_KEY = 'auth';

    protected $adapter;
    protected $token;

    /**
     * Builds core authentication mechanism
     *
     * @param AuthenticateInterface $adapter = provides login()
     * @param string $key = $_SESSION[$key] to store login info
     */
    public function __construct(AuthenticateInterface $adapter, $key)
    {
        $this->key = $key;
        $this->adapter = $adapter;
    }

    public function getToken()
    {
        $this->token = bin2hex(random_bytes(16));
        $_SESSION['token'] = $this->token;
        return $this->token;
    }

    public function matchToken($token)
    {
        $sessToken = $_SESSION['token'] ?? date('Ymd');
        return ($token == $sessToken);
    }

    public function getLoginForm($action = NULL)
    {
        $action = ($action) ? 'action="' . $action . '" ' : '';
        $output = '<form method="post" ' . $action . '>';
        $output .= '<table><tr><th>Username</th><td>';
        $output .= '<input type="text" name="username" /></td>';
        $output .= '</tr><tr><th>Password</th><td>';
        $output .= '<input type="password" name="password" /></td>';
        $output .= '</tr><tr><th>&nbsp;</th>';
        $output .= '<td><input type="submit" /></td>';
        $output .= '</tr></table>';
        $output .= '<input type="hidden" name="token" value="';
        $output .= $this->getToken() . '" />';
        $output .= '</form>';
        return $output;
    }

    /**
     * Calls login() from adapter; expects a 2xx return code for success
     * @param RequestInterface $request
     * @return ResponseInterface $response = where body = database info (if successful)
     *         If not successful, returns status code = 400
     */
    public function login(RequestInterface $request) : ResponseInterface
    {
        // token provides CSRF protection
        $params = json_decode($request->getBody()->getContents());
        $token = $params->token ?? FALSE;
        if (!($token && $this->matchToken($token))) {
            $code = 400;
            $body = new TextStream(self::ERROR_AUTH);
            $response = new Response($code, $body);
        } else {
            $response = $this->adapter->login($request);
        }
        if ($response->getStatusCode() >= 200
            && $response->getStatusCode() < 300) {
            $_SESSION[$this->key] =
                json_decode($response->getBody()->getContents());
        } else {
            $_SESSION[$this->key] = NULL;
        }
        return $response;
    }

}
