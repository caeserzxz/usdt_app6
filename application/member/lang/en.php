<?php
return [
    //登陆相关
    'login' =>[
        'password_error'=>'Incorrect password, wrong format.',
        'not_exist'=>'User does not exist.',
        'is_ban'=>'User has been disabled.',
        'login_is_lock'=>'Too many incorrect passwords. Account closure, unblocking time:$1',
        'password_error'=>'User password is incorrect.',
        'success'=>'login successful.'
    ],
    //注册相关
    'register'=>[
        'register_close'=>'Registration is temporarily unavailable.',
        'success'=>'registration success.',
    ],
    //找回密码相关
    'forgetpwd'=>[
        'success'=>'Password has been reset, please log in with new password.'
    ],
    //密码验证
    'checkpwd'=>[
        'empty_pwd'=>'Please enter the password.',
        'pwd_length_error'=>'Password must be greater than eight characters.',
        'pwd_not_number'=>'Password cannot be all numbers.',
        'pwd_not_letter'=>'Password cannot be all letters.'
    ]

];