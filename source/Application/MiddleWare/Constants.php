<?php
namespace Application\MiddleWare;

class Constants
{

    const HEADER_HOST   = 'Host';     // host header
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_CONTENT_LENGTH = 'Content-Length';

    const METHOD_GET    = 'get';
    const METHOD_POST   = 'post';
    const METHOD_PUT    = 'put';
    const METHOD_DELETE = 'delete';

    const HTTP_METHODS  = ['get','put','post','delete'];

    const STANDARD_PORTS = [
        'ftp' => 21,
        'ssh' => 22,
        'http' => 80,
        'https' => 443
    ];

    const CONTENT_TYPE_FORM_ENCODED = 'application/x-www-form-urlencoded';
    const CONTENT_TYPE_MULTI_FORM   = 'multipart/form-data';
    const CONTENT_TYPE_JSON         = 'application/json';
    const CONTENT_TYPE_HAL_JSON     = 'application/hal+json';

    const DEFAULT_STATUS_CODE    = 200;
    const DEFAULT_BODY_STREAM    = 'php://input';
    const DEFAULT_REQUEST_TARGET = '/';

    const ERROR_BAD = 'ERROR: ';
    const ERROR_UNKNOWN = 'ERROR: unknown';
    const ERROR_HTTP_METHOD = 'ERROR: invalid HTTP method';
    const ERROR_BODY_UNREADABLE = 'ERROR: body is unreadable';
    const ERROR_INVALID_URI = 'ERROR: invalid URI';
    const ERROR_INVALID_STATUS = 'ERROR: invalid status code';
    const ERROR_INVALID_UPLOADED  = 'ERROR: must supply an array of UploadedFileInterface instances';
    const ERROR_NO_SEEK = 'ERROR: cannot seek on this stream';
    const ERROR_BAD_FILE = 'ERROR: no uploaded file';
    const ERROR_BAD_DIR = 'ERROR: directory does not exist';
    const ERROR_FILE_NOT = 'ERROR: file is not an uploaded file';
    const ERROR_MOVE_UNABLE = 'ERROR: unable to move file';
    const ERROR_MOVE_DONE = 'ERROR: file already moved';
    const ERROR_NO_UPLOADED_FILES = 'ERROR: no uploaded files';
    const ERROR_MUST_BE_STRING = 'ERROR: input must be string';

    const MODE_READ = 'r';
    const MODE_WRITE = 'w';


    // see: https://tools.ietf.org/html/rfc7231#section-6.1
    const STATUS_CODES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I_m A Teapot',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];
}
