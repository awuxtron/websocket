<?php

namespace Awuxtron\Websocket\Enums;

enum Opcode: int
{
    case CONTINUE = 0;

    case TEXT = 1;

    case BINARY = 2;

    case CLOSE = 8;

    case PING = 9;

    case PONG = 10;
}
