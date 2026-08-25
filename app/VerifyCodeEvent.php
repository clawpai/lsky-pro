<?php

namespace App;

enum VerifyCodeEvent: string
{
    /** 注册 */
    case Register = 'register';

    /** 绑定手机号、邮箱 */
    case Bind = 'bind';

    /** 找回密码 */
    case ForgetPassword = 'forget_password';
    
    /** 验证手机号、邮箱 */
    case Verify = 'verify';
}
