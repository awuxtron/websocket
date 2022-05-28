<?php

namespace Awuxtron\Websocket\Utils;

use Awuxtron\Websocket\Exceptions\StreamConnectionException;
use GuzzleHttp\Psr7\Stream;

class SocketStream extends Stream
{
    /**
     * The socket stream resource.
     *
     * @var resource
     */
    protected mixed $stream;

    /**
     * Open a new connection to the address.
     *
     * @param string        $address
     * @param null|float    $timeout
     * @param null|resource $context
     * @param int           $flags
     *
     * @throws StreamConnectionException
     *
     * @see stream_socket_client()
     */
    public function __construct(string $address, ?float $timeout = -1, mixed $context = null, int $flags = STREAM_CLIENT_CONNECT)
    {
        $stream = @stream_socket_client(
            $address,
            $errno,
            $error,
            $timeout,
            $flags | STREAM_CLIENT_PERSISTENT,
            $context
        );

        if ($stream === false) {
            throw new StreamConnectionException(sprintf('Connection to %s failed: (%s) %s.', $address, $errno, $error));
        }

        stream_set_timeout($stream, (int) $timeout);

        parent::__construct($this->stream = $stream);
    }

    /**
     * Read data from the stream.
     *
     * @param null|int $length
     *
     * @return string
     */
    public function read($length = null): string
    {
        if ($length === null) {
            $buffer = '';

            while (!$this->eof()) {
                if ('' === ($byte = $this->read(1024))) {
                    return $buffer;
                }

                $buffer .= $byte;

                if ($this->getMetadata('unread_bytes') <= 0) {
                    break;
                }
            }

            return $buffer;
        }

        return parent::read($length);
    }

    /**
     * Get the stream resource.
     *
     * @return resource
     */
    public function getResource(): mixed
    {
        return $this->stream;
    }
}
