<?php

namespace App;

/**
 * 三方授权事件
 */
enum OAuthEvent: string
{
    case Login = 'login';
    case Register = 'register';
    case Bind = 'bind';
}
