<?php

namespace App\Service;

class DecodeJWTToken
{
    public function getJWT($token)
    {
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);
        return [
            'email' => $payload['email'],
            'roles' => $payload['roles'],
            'exp' => $payload['exp']
        ];
    }
}