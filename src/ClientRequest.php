<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

use Exception;
use rguezque\Forge\Exceptions\CurlException;

/**
 * Represents an HTTP client-side request.
 * 
 * @method ClientRequest withRequestMethod(string $method) Specifies/overwrite the http request method for request
 * @method ClientRequest withHeader(string $key, string $value) Add a header to the request
 * @method ClientRequest withHeaders(array $headers) Add multiple headers to the request
 * @method ClientRequest withBasicAuth(string $username, string $password) Add an Authorization header for basic authorization
 * @method ClientRequest withTokenAuth(string $token) Add an Authorization header for JWT authorization
 * @method ClientRequest withPostFields($data, bool $encode = true) Add posts fields to send to request
 * @method array send() Send the client request and return the result into an array with keys "status" and "response".
 */
class ClientRequest {

    /**
     * Predefined const GET
     * 
     * @var string
     */
    public const GET = 'GET';

    /**
     * Predefined const POST
     * 
     * @var string
     */
    public const POST = 'POST';

    /**
     * Predefined const PUT
     * 
     * @var string
     */
    public const PUT = 'PUT';

    /**
     * Predefined const PATCH
     * 
     * @var string
     */
    public const PATCH = 'PATCH';

    /**
     * Predefined const DELETE
     * 
     * @var string
     */
    public const DELETE = 'DELETE';

    /**
     * URL to request
     * 
     * @var string
     */
    private $url;

    /**
     * Default request method
     * 
     * @var string
     */
    private $method = 'GET';

    /**
     * Headers to send
     * 
     * @var string[]
     */
    private $headers = [];

    /**
     * Data to send
     * 
     * @var string|array
     */
    private $body = null;

    /**
     * Prepare the request
     * 
     * @var string $uri URI to send the request
     * @var array $options Request options
     */
    public function __construct(string $url, array $options = []) {
        $this->url = $url;
        $this->method = isset($options['method']) ? $this->withRequestMethod($options['method']) : ClientRequest::GET;
        $this->headers = isset($options['headers']) ? $this->withHeaders($options['headers']) : [];
        $this->body = isset($options['body']) ? $this->withPostFields($options['data']) : null;
    }

    /**
     * Specifies/overwrite the http request method for request
     * 
     * @var string $method Default HTTP request method
     * @return ClientRequest
     */
    public function withRequestMethod(string $method): ClientRequest {
        $this->method = strtoupper(trim($method));
        return $this;
    }

    /**
     * Add a header to the request
     * 
     * @var string $key Header name
     * @var string $value Header content
     * @return ClientRequest
     */
    public function withHeader(string $key, string $value): ClientRequest {
        $this->headers[trim($key)] = $value;
        return $this;
    }

    /**
     * Add multiple headers to the request
     * 
     * @var string $headers Associative array with headers as key and his content
     * @return ClientRequest
     */
    public function withHeaders(array $headers): ClientRequest {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Add an Authorization header for basic authorization. The user-id or username 
     * and password are concatenated with a colon (:) and encoded using Base64.
     * 
     * @var string $username Identity
     * @var string $password Credential
     * @return ClientRequest
     */
    public function withBasicAuth(string $username, string $password): ClientRequest {
        $this->withHeader('Authorization', sprintf('Basic %s', base64_encode("$username:$password")));
        return $this;
    }

    /**
     * Add an Authorization header for JWT authorization
     * 
     * @var string $token JSON Web Token
     * @return ClientRequest
     */
    public function withTokenAuth(string $token): ClientRequest {
        $this->withHeader('Authorization', sprintf('Bearer %s', $token));
        return $this;
    }

    /**
     * Add post fields to send to request
     * 
     * @var array|string $data Data to send
     * @var bool $encode Specifies if data must be encoded to JSON
     * @return ClientRequest
     */
    public function withPostFields($data): ClientRequest {
        $this->body = $data;
        return $this;
    }


    /**
     * Send the client request and return the result into an array with keys "status" and "response".
     * 
     * @return array
     * @throws CurlException
     */
    public function send(): array {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

        if (!empty($this->headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        if ($this->body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        }

        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new CurlException("cURL error: $error");
        }

        curl_close($ch);

        return [
            'status' => $status_code,
            'response' => $response,
        ];
    }

}
