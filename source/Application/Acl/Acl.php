<?php
namespace Application\Acl;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Application\MiddleWare\ { Constants, Response, TextStream };

class Acl
{

    const DEFAULT_STATUS = '';
    const DEFAULT_LEVEL  = 0;
    const DEFAULT_PAGE   = 0;

    const ERROR_ACL = 'ERROR: authorization error';
    const ERROR_APP = 'ERROR: requested page not listed';
    const ERROR_DEF = 'ERROR: must assign keys "levels", "pages" and "allowed"';

    protected $default;
    protected $levels;
    protected $pages;
    protected $allowed;    // [status => ['inherits' => xxx, 'pages' => [level => [pages allowed], etc.]

    public function __construct(array $assignments)
    {
        $this->default = $assignments['default'] ?? self::DEFAULT_PAGE;
        $this->pages   = $assignments['pages'] ?? FALSE;
        $this->levels  = $assignments['levels'] ?? FALSE;
        $this->allowed = $assignments['allowed'] ?? FALSE;
        if (!($this->pages && $this->levels && $this->allowed)) {
            throw new InvalidArgumentException(self::ERROR_DEF);
        }
    }

    protected function mergeInherited($status, $level)
    {
        $allowed = $this->allowed[$status]['pages'][$level] ?? array();
        for ($x = $status; $x > 0; $x--) {
            $inherits = $this->allowed[$x]['inherits'];
            if ($inherits) {
                $subArray = $this->allowed[$inherits]['pages'][$level] ?? array();
                $allowed = array_merge($allowed, $subArray);
            }
        }
        return $allowed;
    }

    public function isAuthorized(RequestInterface $request)
    {

        $code = 401;    // unauthorized
        $text['page'] = $this->pages[$this->default];
        $text['authorized'] = FALSE;

        // check to see if request is on the list of pages
        $page = $request->getUri()->getQueryParams()['page'] ?? FALSE;

        if ($page === FALSE) {

            $code = 400;    // bad request

        } else {

            $params = json_decode($request->getBody()->getContents());
            $status = $params->status ?? self::DEFAULT_LEVEL;
            $level  = $params->level  ?? '*';
            $allowed = $this->mergeInherited($status, $level);

            if (in_array($page, $allowed)) {
                $code = 200;    // OK
                $text['authorized'] = TRUE;
                $text['page'] = $this->pages[$page];
            } else {
                $code = 401;    // unauthorized
            }

        }

        $body = new TextStream(json_encode($text));
        return (new Response())->withStatus($code)->withBody($body);

    }

}
