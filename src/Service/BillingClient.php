<?php

namespace App\Service;

use App\DTO\UserAuthDto;
use App\DTO\UserCurrentDto;
use App\Exception\BillingUnavailableException;
use App\Security\User;
use JsonException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\SerializerInterface;

class BillingClient
{
    private $urlBilling;
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->urlBilling = $_ENV['BILLING_ADDRES'];
        $this->serializer = $serializer;
    }

    /**
     * @throws BillingUnavailableException
     * @throws JsonException
     */
    public function login($data)
    {
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/auth');
        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ]
        ];
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);


        if ($res === false) {
            throw new BillingUnavailableException('Ошибка со стороны сервера');
        }
        curl_close($ch);
        $resJSON = json_decode($res, true);
        if (isset($resJSON['code'])) {
            if ($resJSON['code'] === 401) {
                throw new UserNotFoundException('Неправильная пара логин/пароль');
            }
            if ($resJSON['code'] === 400) {
                throw new UserNotFoundException('Неправильная пара логин/пароль');
            }
        }


        $userAuthDTO = $this->serializer->deserialize($res, UserAuthDto::class, "json");
        $user = new User();
        $user->setApiToken($userAuthDTO->token);
        $decodedJWT = $this->getJWT($userAuthDTO->token);
        $user->setEmail($decodedJWT['email']);
        $user->setRoles($decodedJWT['roles']);
        return $user;
    }

    public function getCurrentUser($user) {
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/users/current');
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $user->getApiToken() 
            ]
        ];
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);


        if ($res === false) {
            throw new BillingUnavailableException('Ошибка со стороны сервера');
        }
        if (isset($resJSON['code'])) {
            throw new BillingUnavailableException('Ошибка со стороны сервера');
        }
        $userCurrent = $this->serializer->deserialize($res, UserCurrentDto::class, 'json');

        return $userCurrent;
    }

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
