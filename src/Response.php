<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use OutOfBoundsException;

/**
 * Represents an HTTP response.
 * 
 * @method Response create(int $code = Response::HTTP_OK, string $phrase = '') Create a Response with a HTTP status code and custom phrase
 * @method Response withContent(string $content) Add content to body of response
 * @method Response withStatus(int $code) Set the http status code
 * @method Response withStatusPhrase(string $phrase) Set the status phrase for the actual response statuc code
 * @method Response withProtocolVersion(string $version) Set the http protocol version
 * @method Response withHeader(string $key, string $value) Add a header to response
 * @method Response withheaders(array $headers) Add headers array to response
 * @method Response clear() Clear headers and content of response and reset to default values
 * @method int getStatus() Retrieves the http status code
 * @method string getStatusPhrase() Retrieves the http status text
 * @method string getProtocolVersion() Retrieves the http protocol version
 * @method string getContent() Retrieves the body of response
 */
class Response {

    /**
     * HTTP status code
     * 
     * @var int
     */
    private $status_code;

    /**
     * HTTP status text
     * 
     * @var string
     */
    private $status_phrase = '';

    /**
     * HTTP protocol version
     * 
     * @var string
     */
    private $version;

    /**
     * Headers
     * 
     * @var array
     */
    protected $headers = [];

    /**
     * Content of response
     * 
     * @var string
     */
    private $content;

    // Informative responses
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_EARLY_HINTS = 103;           // RFC8297
    // Successfull responses
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    // Redirects
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    // Client Errors (Bad Requests)
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_TOO_EARLY = 425;                                                   // RFC-ietf-httpbis-replay-04
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    // Server Errors
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * HTTP Status Codes
     * 
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     */
    private $http_status = [
        // Informative responses
        100 => 'Continue',	                        //[RFC7231, Section 6.2.1]
        101	=> 'Switching Protocols',	            //[RFC7231, Section 6.2.2]
        102	=> 'Processing',	                    //[RFC2518]
        103	=> 'Early Hints',	                    //[RFC8297]
        // Successfull responses
        200 => 'OK',	                            //[RFC7231, Section 6.3.1]
        201 => 'Created',	                        //[RFC7231, Section 6.3.2]
        202 => 'Accepted',	                        //[RFC7231, Section 6.3.3]
        203 => 'Non-Authoritative Information',	    //[RFC7231, Section 6.3.4]
        204 => 'No Content',	                    //[RFC7231, Section 6.3.5]
        205 => 'Reset Content',	                    //[RFC7231, Section 6.3.6]
        206 => 'Partial Content',	                //[RFC7233, Section 4.1]
        207 => 'Multi-Status',	                    //[RFC4918]
        208 => 'Already Reported',	                //[RFC5842]
        226	=> 'IM Used',	                        //[RFC3229]
        // Redirects
        300 => 'Multiple Choices',	                //[RFC7231, Section 6.4.1]
        301 => 'Moved Permanently',	                //[RFC7231, Section 6.4.2]
        302 => 'Found',	                            //[RFC7231, Section 6.4.3]
        303 => 'See Other',	                        //[RFC7231, Section 6.4.4]
        304 => 'Not Modified',	                    //[RFC7232, Section 4.1]
        305 => 'Use Proxy',	                        //[RFC7231, Section 6.4.5]
        306 => '(Unused)',	                        //[RFC7231, Section 6.4.6]
        307 => 'Temporary Redirect',	            //[RFC7231, Section 6.4.7]
        308 => 'Permanent Redirect',	            //[RFC7538]
        // Client Errors (Bad Requests)
        400 => 'Bad Request',	                    //[RFC7231, Section 6.5.1]
        401 => 'Unauthorized',	                    //[RFC7235, Section 3.1]
        402 => 'Payment Required',	                //[RFC7231, Section 6.5.2]
        403 => 'Forbidden',	                        //[RFC7231, Section 6.5.3]
        404 => 'Not Found',	                        //[RFC7231, Section 6.5.4]
        405 => 'Method Not Allowed',	            //[RFC7231, Section 6.5.5]
        406 => 'Not Acceptable',	                //[RFC7231, Section 6.5.6]
        407 => 'Proxy Authentication Required',	    //[RFC7235, Section 3.2]
        408 => 'Request Timeout',	                //[RFC7231, Section 6.5.7]
        409 => 'Conflict',	                        //[RFC7231, Section 6.5.8]
        410 => 'Gone',	                            //[RFC7231, Section 6.5.9]
        411 => 'Length Required',	                //[RFC7231, Section 6.5.10]
        412 => 'Precondition Failed',	            //[RFC7232, Section 4.2][RFC8144, Section 3.2]
        413 => 'Payload Too Large',	                //[RFC7231, Section 6.5.11]
        414 => 'URI Too Long',	                    //[RFC7231, Section 6.5.12]
        415 => 'Unsupported Media Type',	        //[RFC7231, Section 6.5.13][RFC7694, Section 3]
        416 => 'Range Not Satisfiable',	            //[RFC7233, Section 4.4]
        417 => 'Expectation Failed',	            //[RFC7231, Section 6.5.14]
        421 => 'Misdirected Request',	            //[RFC7540, Section 9.1.2]
        422 => 'Unprocessable Entity',	            //[RFC4918]
        423 => 'Locked',	                        //[RFC4918]
        424 => 'Failed Dependency',	                //[RFC4918]
        425 => 'Too Early',	                        //[RFC8470]
        426 => 'Upgrade Required',	                //[RFC7231, Section 6.5.15]
        428 => 'Precondition Required',	            //[RFC6585]
        429 => 'Too Many Requests',	                //[RFC6585]
        431 => 'Request Header Fields Too Large',	//[RFC6585]
        451 => 'Unavailable For Legal Reasons',	    //[RFC7725]
        // Server Errors
        500 => 'Internal Server Error',	            //[RFC7231, Section 6.6.1]
        501 => 'Not Implemented',	                //[RFC7231, Section 6.6.2]
        502 => 'Bad Gateway',	                    //[RFC7231, Section 6.6.3]
        503 => 'Service Unavailable',	            //[RFC7231, Section 6.6.4]
        504 => 'Gateway Timeout',	                //[RFC7231, Section 6.6.5]
        505 => 'HTTP Version Not Supported',	    //[RFC7231, Section 6.6.6]
        506 => 'Variant Also Negotiates',	        //[RFC2295]
        507 => 'Insufficient Storage',	            //[RFC4918]
        508 => 'Loop Detected',	                    //[RFC5842]
        510 => 'Not Extended',	                    //[RFC2774]
        511 => 'Network Authentication Required'	//[RFC6585]
    ];

    /**
     * Create a response
     * 
     * @param string $content Content of response
     * @param int $code HTTP status code
     * @param array $headers Headers of response
     */
    public function __construct(
        string $content = '', 
        int $code = Response::HTTP_OK, 
        array $headers = []
    ) {
        $this->withContent($content);
        $this->withStatus($code);
        $this->withHeaders($headers);
        $this->withProtocolVersion('1.0');
    }

    /**
     * Create a Response with a HTTP status code and custom phrase
     * 
     * @param int $code HTTP Status code
     * @param string $phrase Status phrase
     * @return Response
     */
    public static function create(int $code = Response::HTTP_OK, string $phrase = ''): Response {
        $response = new Response;
        $response->withStatus($code);
        
        if('' !== $phrase) {
            $response->withStatusPhrase($phrase);
        }
        
        return $response;
    }

    /**
     * Add content to body of response
     * 
     * @param mixed $content Content of response
     * @return Response
     */
    public function withContent($content): Response {
        $this->content .= $content;
        
        return $this;
    }

    /**
     * Set the http status code
     * 
     * @param int $code HTTP status code
     * @return Response
     */
    public function withStatus(int $code): Response {
        if(!array_key_exists($code, $this->http_status)) {
            throw new OutOfBoundsException('Invalid HTTP status code. See http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml');
        }

        $this->status_code = $code;
        return $this;
    }

    /**
     * Set the status phrase for the actual response statuc code
     * 
     * @param string $phrase Phrase for actual status code
     * @return Response
     */
    public function withStatusPhrase(string $phrase): Response {
        $this->status_phrase = $phrase;

        return $this;
    }

    /**
     * Set the http protocol version
     * 
     * @param string $version Protocol version
     * @return Response
     */
    public function withProtocolVersion(string $version): Response {
        $this->version = $version;

        return $this;
    }

    /**
     * Add a header to response
     * 
     * @param string $key Attribute of header
     * @param string $value Value of header
     * @return Response
     */
    public function withHeader(string $key, string $value): Response {
        $this->headers[$key] = $value;
        
        return $this;
    }

    /**
     * Add headers array to response
     * 
     * @param array $headers Headers definition
     * @return Response
     */
    public function withHeaders(array $headers): Response {
        foreach($headers as $key => $value) {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    /**
     * Retrieves the http status code
     * 
     * @return int
     */
    public function getStatus(): int {
        return $this->status_code;
    }

    /**
     * Retrieves the http status text
     * 
     * @return string
     */
    public function getStatusPhrase(): string {
        return ('' !== $this->status_phrase) 
        ? $this->status_phrase 
        : $this->http_status[$this->status_code];
    }

    /**
     * Retrieves the http protocol version
     * 
     * @return string
     */
    public function getProtocolVersion(): string {
        return $this->version;
    }

    /**
     * Retrieves the body of response
     * 
     * @return string
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * Send the headers of response
     * 
     * @return void
     */
    protected function sendHeaders() {
        if ('HTTP/1.0' != $_SERVER['SERVER_PROTOCOL']) {
            $this->withProtocolVersion('1.1');
        }

        // Status
        header(sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatus(), $this->getStatusPhrase()), true, $this->getStatus());

        // Headers
        foreach ($this->headers as $key => $value) {
            if(is_array($value)) {
                foreach ($value as $k => $v) {
                    header(sprintf('%s: %s', $k, $v), false);
                }
            } else {
                header(sprintf('%s: %s', $key, $value), false);
            }
        }

        return $this;
    }

    /**
     * Send the content of response
     * 
     * @return void
     */
    protected function sendContent(): void {
        echo $this->content;
    }

    /**
     * Clear headers and content of response and reset to default values
     * 
     * @return Response
     */
    public function clear(): Response {
        $this->headers = [];
        $this->content = '';
        $this->status_code = 200;
        $this->status_phrase = '';

        return $this;
    }
}

?>