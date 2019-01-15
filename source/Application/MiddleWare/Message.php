<?php
namespace Application\MiddleWare;

use Psr\Http\Message\ { MessageInterface, StreamInterface, UriInterface };

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 *
 * Messages are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 *
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
class Message implements MessageInterface
{
    protected $body;
    protected $version;     // HTTP version
    protected $httpHeaders = array();

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        if (!$this->body) {
            $this->body = new Stream(self::DEFAULT_BODY_STREAM);
        }
        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return self
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        if (!$body->isReadable()) {
            throw new InvalidArgumentException(self::ERROR_BODY_UNREADABLE);
        }
        $this->body = $body;
        return $this;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        foreach ($this->getHttpHeaders() as $key => $value) {
            header($key . ': ' . $value);
        }
    }

    protected function getHttpHeaders()
    {
        if (!$this->httpHeaders) {
            if (function_exists('apache_request_headers')) {
                $this->httpHeaders = apache_request_headers();
            } else {
                $this->httpHeaders = $this->altApacheRequestHeaders();
            }
        }
        return $this->httpHeaders;
    }

    protected function altApacheRequestHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (stripos($key, 'HTTP_') !== FALSE) {
                $headerKey = str_ireplace('HTTP_', '', $key);
                $headers[$this->explodeHeader($headerKey)] = $value;
            } elseif (stripos($key, 'CONTENT_') !== FALSE) {
                $headers[$this->explodeHeader($key)] = $value;
            }
        }
        return $headers;
    }

    protected function explodeHeader($header)
    {
        $headerParts = explode('_', $header);
        $headerKey = ucwords(implode(' ', strtolower($headerParts)));
        return str_replace(' ', '-', $headerKey);
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return boolval($this->findHeader($name));
    }

    protected function findHeader($name)
    {
        $found = FALSE;
        foreach (array_keys($this->getHttpHeaders()) as $header) {
            if (stripos($header, $name) !== FALSE) {
                $found = $header;
                break;
            }
        }
        return $found;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        $line = $this->getHeaderLine($name);
        if ($line) {
            return explode(',', $line);
        } else {
            return array();
        }
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        $found = $this->findHeader($name);
        if ($found) {
            return $this->httpHeaders[$found];
        } else {
            return '';
        }
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        $found = $this->findHeader($name);
        if ($found) {
            $this->httpHeaders[$found] = $value;
        } else {
            $this->httpHeaders[$name] = $value;
        }
        return $this;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        $found = $this->findHeader($name);
        if ($found) {
            $this->httpHeaders[$found] .= $value;
        } else {
            $this->httpHeaders[$name] = $value;
        }
        return $this;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return self
     */
    public function withoutHeader($name)
    {
        $found = $this->findHeader($name);
        if ($found) {
            unset($this->httpHeaders[$found]);
        }
        return $this;
    }

    public function getHeadersAsString()
    {
        $output = '';
        $headers = $this->getHeaders();
        if ($headers && is_array($headers)) {
            foreach ($headers as $key => $value) {
                if ($output) {
                    $output .= "\r\n" . $key . ': ' . $value;
                } else {
                    $output .= $key . ': ' . $value;
                }
            }
        }
        return $output;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        if (!$this->version) {
            $this->version = $this->onlyVersion($_SERVER['SERVER_PROTOCOL']);
        }
        return $this->version;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return self
     */
    public function withProtocolVersion($version)
    {
        $this->version = $this->onlyVersion($version);
        return $this;
    }

    protected function onlyVersion($version)
    {
        if (!empty($version)) {
            return preg_replace('/[^0-9\.]/', '', $version);
        } else {
            return NULL;
        }
    }

}
