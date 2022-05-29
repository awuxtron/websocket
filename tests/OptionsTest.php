<?php

use Awuxtron\Websocket\Options;

it('can set options by array', function () {
    $options = ['connect_timeout' => 30];

    expect(Options::of($options)->connect_timeout)
        ->toEqual(30)
        ->and((new Options($options))->connect_timeout)
        ->toEqual(30)
    ;
});
