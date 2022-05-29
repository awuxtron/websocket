<?php

namespace Awuxtron\Websocket\Enums;

enum Opcode: int
{
    public const CONTINUE = 0;

    public const TEXT = 1;

    public const BINARY = 2;

    public const CLOSE = 8;

    public const PING = 9;

    public const PONG = 10;
}
