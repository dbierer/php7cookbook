<?php
namespace Application\MiddleWare;

use InvalidArgumentException;
use Psr\Http\Message\ { RequestInterface, StreamInterface, UriInterface };

class Request extends Message implements RequestInterface
{

    protected $uri;
    protected $method;      // HTTP method
    protected $uriObj;      // Psr\Http\Message\UriInterface instance

    /**
     * Initializes a request to be sent
     * If you leave the params blank, is designed to read from an incoming request
     *
     * @param string $uri
     * @param string $method = get | put | post | delete
     * @param StreamInterface $body
     * @param array $headers
     * @param string $version
     */
    public function __construct($uri = NULL,
                                $method = NULL,
                                StreamInterface $body = NULL,
                                $headers = NULL,
                                $version = NULL)
    {
        $this->uri = $uri;
        $this->body = $body;
        $this->method = $this->checkMethod($method);
        $this->httpHeaders = $headers;
        $this->version = $this->onlyVersion($version);
    }

    // defined in Psr\Http\Message\RequestInterface
    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        return $this->uri ?? Constants::DEFAULT_REQUEST_TARGET;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        $this->uri = $requestTarget;
        $this->getUri();
        return $this;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $this->method = $this->checkMethod($method);
        return $this;
    }

    protected function checkMethod($method)
    {
        if (!$method === NULL) {
            if (!in_array(strtolower($method), Constants::HTTP_METHODS)) {
                throw new InvalidArgumentException(Constants::ERROR_HTTP_METHOD);
            }
        }
        return $method;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri()
    {
        if (!$this->uriObj) {
            $this->uriObj = new Uri($this->uri);
        }
        return $this->uriObj;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($preserveHost) {
            $found = $this->findHeader(Constants::HEADER_HOST);
            if (!$found && $uri->getHost()) {
                $this->httpHeaders[Constants::HEADER_HOST] = $uri->getHost();
            }
        } elseif ($uri->getHost()) {
            $this->httpHeaders[Constants::HEADER_HOST] = $uri->getHost();
        }
        $this->uri = $uri->__toString();
        return $this;
    }
}
