<?php

namespace Awuxtron\Websocket;

use Awuxtron\Websocket\Exceptions\ConnectionException;
use Awuxtron\Websocket\Utils\SocketStream;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Ratchet\RFC6455\Handshake\ResponseVerifier;

class Client
{
    /**
     * The current package version.
     */
    public const VERSION = '0.0.1';

    /**
     * The supported schemes.
     */
    protected const SCHEMES = [
        'ws' => ['tcp', 80],
        'wss' => ['ssl', 443],
    ];

    /**
     * The websocket server uri.
     *
     * @var UriInterface
     */
    protected UriInterface $uri;

    /**
     * The websocket client options.
     *
     * @var Options
     */
    protected Options $options;

    /**
     * The socket stream instance.
     *
     * @var SocketStream
     */
    protected SocketStream $stream;

    /**
     * Create a new websocket client instance.
     *
     * @param string|UriInterface  $uri     the websocket server uri, only uri contains <b>ws</b> or <b>wss</b> are accepted, if no scheme is provided, <b>ws</b> will use by default
     * @param array<mixed>|Options $options the client options, see {@see \Awuxtron\WebSocket\Options} for list of available options
     */
    public function __construct(UriInterface|string $uri, Options|array $options = [])
    {
        $this->uri = Utils::uriFor($uri);
        $this->options = Options::of($options);
    }

    /**
     * Connect to the websocket uri.
     *
     * @return Websocket
     */
    public function connect(): Websocket
    {
        // Open new connection.
        $this->stream = new SocketStream(
            $this->getUriForStream(),
            $this->options->connect_timeout,
            $this->getStreamContext()
        );

        // Upgrade the connection.
        $bytes = $this->stream->write(
            Message::toString($request = $this->getUpgradeRequest($key = $this->generateWebsocketKey()))
        );

        if ($bytes == 0) {
            throw new ConnectionException('Unable to upgrade the connection.');
        }

        $this->validateResponse($request, $this->stream->read(), $key);

        return new Websocket($this);
    }

    /**
     * Close the connection.
     */
    public function disconnect(): void
    {
        $this->stream->close();
    }

    /**
     * Get the client options.
     *
     * @return Options
     */
    public function getOptions(): Options
    {
        return $this->options;
    }

    /**
     * Get the socket stream instance.
     *
     * @return SocketStream
     */
    public function getStream(): SocketStream
    {
        return $this->stream;
    }

    /**
     * Generate a request will send to the websocket server to upgrade the connection.
     *
     * @param string $key
     *
     * @return RequestInterface
     */
    protected function getUpgradeRequest(string $key): RequestInterface
    {
        $headers = [
            'Connection' => 'Upgrade',
            'Upgrade' => 'websocket',
            'User-Agent' => 'Awuxtron-Websocket/' . static::VERSION,
            'Sec-WebSocket-Key' => $key,
            'Sec-WebSocket-Version' => 13,
        ];

        $auth = $this->options->auth;

        if (!empty($user = $this->uri->getUserInfo())) {
            $auth = explode(':', $user, 2);
        }

        if (!empty($auth)) {
            if (is_array($auth)) {
                $auth = 'Basic ' . base64_encode("{$auth[0]}:{$auth[1]}");
            }

            $headers['Authorization'] = $auth;
        }

        /** @var array<string, string|string[]> $headers */
        $headers = array_replace($headers, $this->options->headers);

        return new Request('GET', $this->uri, $headers);
    }

    /**
     * Get the valid address for php stream resource from the uri.
     *
     * @return string
     */
    protected function getUriForStream(): string
    {
        $scheme = $this->uri->getScheme() ?: 'ws';

        if (!array_key_exists($scheme, static::SCHEMES)) {
            throw new MalformedUriException("The scheme '{$scheme}' is not supported.");
        }

        $port = $this->uri->getPort() ?: static::SCHEMES[$scheme][1];

        return static::SCHEMES[$scheme][0] . '://' . $this->uri->getHost() . ':' . $port;
    }

    /**
     * Get the stream context using when connection.
     *
     * @return resource
     */
    protected function getStreamContext(): mixed
    {
        return $this->options->context ?: stream_context_create();
    }

    /**
     * Generate a random websocket key.
     *
     * @return string
     */
    protected function generateWebsocketKey(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwzyz1234567890+/=';
        $charRange = strlen($chars) - 1;
        $key = '';

        for ($i = 0; $i < 16; ++$i) {
            $key .= $chars[random_int(0, $charRange)];
        }

        return base64_encode($key);
    }

    /**
     * Ensure the response message is valid.
     *
     * @param RequestInterface $request
     * @param string           $message
     * @param string           $key
     */
    protected function validateResponse(RequestInterface $request, string $message, string $key): void
    {
        $response = Message::parseResponse($message);

        if (!(new ResponseVerifier)->verifyAll($request, $response)) {
            throw new ConnectionException(
                sprintf(
                    'Server returned invalid response: (%s) %s.',
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                )
            );
        }

        // Validate websocket key.
        $expected = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        if ($expected != $response->getHeader('Sec-WebSocket-Accept')[0]) {
            throw new ConnectionException('Websocket key from server and client does not match.');
        }
    }
}
