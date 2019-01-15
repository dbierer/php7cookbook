<?php
namespace Application\Acl;

use Psr\Http\Message\ { RequestInterface, ResponseInterface };

interface AuthenticateInterface
{

    /**
     * @param RequestInterface $request
     * @return ResponseInterface $response = where body = database info (if successful)
     *         If not successful, returns status code = 400
     */
    public function login(RequestInterface $request) : ResponseInterface;
}
