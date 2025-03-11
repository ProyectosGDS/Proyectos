<?php

return [

    'secret' => env('JWT_SECRET',null),
    'expired_token' => env('JWT_EXPIRED_TOKEN',60),
    'receivers' => [
        env('PRINCIPAL_HOST_NAME_RECEIVER'),
        'http://localhost:5173',
        'http://192.168.20.116',
    ]

];