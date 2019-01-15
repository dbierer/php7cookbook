<?php
namespace Application\MiddleWare;

use Psr\Http\Message\ { ResponseInterface, StreamInterface };

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * Responses are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Response extends Message implements ResponseInterface
{

    protected $statusCode;

    /**
     * Initializes a request to be sent
     * If you leave the params blank, is designed to read from an incoming request
     *
     * @param int $statusCode
     * @param StreamInterface $body
     * @param array $headers
     * @param string $version
     */
    public function __construct($statusCode = NULL,
                                StreamInterface $body = NULL,
                                $headers = NULL,
                                $version = NULL)
    {
        $this->body = $body;
        $this->status['code'] = $statusCode ?? Constants::DEFAULT_STATUS_CODE;
        $this->status['reason'] = Constants::STATUS_CODES[$statusCode] ?? '';
        $this->httpHeaders = $headers;
        $this->version = $this->onlyVersion($version);
        if ($statusCode) $this->setStatusCode();
    }

    /**
     * sets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     */
    public function setStatusCode()
    {
        http_response_code($this->getStatusCode());
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->status['code'];
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($statusCode, $reasonPhrase = '')
    {
        if (!isset(Constants::STATUS_CODES[$statusCode])) {
            throw new InvalidArgumentException(Constants::ERROR_INVALID_STATUS);
        }
        $this->status['code'] = $statusCode;
        $this->status['reason'] = ($reasonPhrase) ? Constants::STATUS_CODES[$statusCode] : NULL;
        $this->setStatusCode();
        return $this;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default      recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->status['reason']
                ?? Constants::STATUS_CODES[$this->status['code']]
                ?? '';
    }
}
