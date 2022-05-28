<?php

namespace Awuxtron\Websocket;

final class Options
{
    /**
     * Pass an authentication string or an array of HTTP authentication parameters to use with the request. The array must contain the username in index [0], the password in index [1]. Pass null to disable authentication for a request.
     *
     * @var string|array{string, string}|null
     */
    public string|array|null $auth = null;

    /**
     * Float describing the number of seconds to wait while trying to connect to websocket server. Use 0 to wait indefinitely (the default behavior).
     *
     * @var float
     */
    public float $connect_timeout = -1;

    /**
     * Associative array of headers to add to the request. Each key is the name of a header, and each value is a string or array of strings representing the header field values.
     *
     * @var array<string, string|string[]>
     */
    public array $headers = [];

    /**
     * Pass a custom stream context to use when connect to the websocket server.
     *
     * @var null|resource
     */
    public mixed $context = null;

    /**
     * Set the max fragment size for payload. If payload is over fragment size, it will be separate to multiple message.
     *
     * @var int<1, max>
     */
    public int $max_fragment_size = 4096;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value; // @phpstan-ignore-line
            }
        }
    }

    /**
     * Create a new options instance.
     *
     * @param array<string, mixed>|Options $options
     *
     * @return self
     */
    public static function of(self|array $options): self
    {
        if ($options instanceof self) {
            return $options;
        }

        return new self($options);
    }
}
