<?php

use Awuxtron\Websocket\Client;
use Awuxtron\Websocket\Options;
use Awuxtron\Websocket\Websocket;
use Psr\Http\Message\UriInterface;

if (!function_exists('ws')) {
    /**
     * Helper function to quickly connect to the websocket server.
     *
     * @param string|UriInterface  $uri     the websocket server uri, only uri contains <b>ws</b> or <b>wss</b> are accepted, if no scheme is provided, <b>ws</b> will use by default
     * @param array<mixed>|Options $options the client options, see {@see \Awuxtron\WebSocket\Options} for list of available options
     *
     * @return Websocket
     */
    function ws(UriInterface|string $uri, Options|array $options = []): Websocket
    {
        return (new Client($uri, $options))->connect();
    }
}
