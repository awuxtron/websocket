<?php

namespace Awuxtron\Websocket\Enums;

enum CloseStatus: int
{
    case NORMAL = 1000;
    case GOING_AWAY = 1001;
    case PROTOCOL = 1002;
    case BAD_DATA = 1003;
    case NO_STATUS = 1005;
    case ABNORMAL = 1006;
    case BAD_PAYLOAD = 1007;
    case POLICY = 1008;
    case TOO_BIG = 1009;
    case MAND_EXT = 1010;
    case SRV_ERR = 1011;
    case TLS = 1015;
}
