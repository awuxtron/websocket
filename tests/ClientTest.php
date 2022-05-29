<?php

use Awuxtron\Websocket\Client;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use Ratchet\RFC6455\Handshake\RequestVerifier;

beforeEach(function () {
    $this->uri = 'ws://localhost:8080/';
    $this->client = new Client($this->uri);
    $this->reflection = new ReflectionClass($this->client);

    $this->getStreamContext = $this->reflection->getMethod('getStreamContext');
    $this->getStreamContext->setAccessible(true);
});

it('can create new stream context', function () {
    expect($resource = $this->getStreamContext->invoke($this->client))
        ->toBeResource()
        ->and(get_resource_type($resource))
        ->toEqual('stream-context')
    ;
});

it('can use existing stream context defined in options', function () {
    $context = stream_context_create(['ssl' => ['verify_peer' => false]]);

    $this->client->setOptions(['context' => $context]);

    expect($this->getStreamContext->invoke($this->client))->toEqual($context);
});

it('can generate valid uri for socket stream', function ($uri, $expect) {
    $this->client->setUri($uri);

    $method = $this->reflection->getMethod('getUriForStream');
    $method->setAccessible(true);

    expect($method->invoke($this->client))->toEqual($expect);
})->with([
    ['ws://localhost', 'tcp://localhost:80'],
    ['wss://localhost', 'ssl://localhost:443'],
    ['ws://localhost:8080', 'tcp://localhost:8080'],
    ['wss://localhost:8080', 'ssl://localhost:8080'],
    ['ws://user:pass@domain.com/path?query=value#hash', 'tcp://domain.com:80'],
]);

it('can generate upgrade request', function () {
    $method = $this->reflection->getMethod('getUpgradeRequest');
    $method->setAccessible(true);

    $key = $this->reflection->getMethod('generateWebsocketKey');
    $key->setAccessible(true);

    $request = $method->invoke($this->client, $key->invoke($this->client));

    expect((new RequestVerifier)->verifyAll($request))->toBeTrue();
});

test('connect with unknown scheme always fails', function () {
    expect(fn () => $this->client->setUri('unknown://localhost')->connect())->toThrow(
        MalformedUriException::class,
        "The scheme 'unknown' is not supported."
    );
});
