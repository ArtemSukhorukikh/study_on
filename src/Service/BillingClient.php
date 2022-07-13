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
    public DecodeJWTToken $decodeJWTToken;

    public function __construct(SerializerInterface $serializer, DecodeJWTToken $decodeJWTToken)
    {
        $this->urlBilling = $_ENV['BILLING_ADDRES'];
        $this->serializer = $serializer;
        $this->decodeJWTToken = $decodeJWTToken;
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
        $user->setRefreshToken($userAuthDTO->refresh_token);
        $decodedJWT = $this->decodeJWTToken->getJWT($userAuthDTO->token);
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

    public function register($data) 
    {
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/register');
        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
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
        if (isset($resJSON['errors'])) {
            throw new BillingUnavailableException("Проверьте правильность введных вами данных");
        }


        $userAuthDTO = $this->serializer->deserialize($res, UserAuthDto::class, "json");
        $user = new User();
        $user->setApiToken($userAuthDTO->token);
        $decodedJWT = $this->decodeJWTToken->getJWT($userAuthDTO->token);
        $user->setEmail($decodedJWT['email']);
        $user->setRoles($decodedJWT['roles']);
        $user->setRefreshToken($userAuthDTO->refresh_token);
        return $user;
    }

    public function refreshToken(string $refreshToken)
    {
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/token/refresh');
        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['refresh_token' => $refreshToken]),
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
        $result = json_decode($res, true);
        if (isset($result['errors'])) {
            throw new BillingUnavailableException(json_encode($result['errors']));
        }
        return json_decode($res, true);
    }

    public function getAllCourses()
    {
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/courses/');
        $options = [
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
        return json_decode($res, true);
    }

    public function getCourseByCode(string $code)
    {
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/courses/' . $code);
        $options = [
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
        return json_decode($res, true);
    }

    public function getTransactions($filter, $token)
    {
        $urlFilters = '?';
        if (isset($filter['type'])) {
            $urlFilters .= 'filter[type]=' . $filter['type'] . '&';
        }
        if (isset($filter['course_code'])) {
            $urlFilters .= 'filter[course_code]=' . $filter['course_code'] . '&';
        }
        if (isset($filter['skip_expired'])){
            $urlFilters .= 'filter[skip_expired]=' . $filter['skip_expired'];
        }
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/transactions' . $urlFilters);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
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

        return json_decode($res, true);
    }

    public function pay($course, $token)
    {
        $ch = curl_init($_ENV['BILLING_ADDRES'] . 'api/v1/courses/' . $course->getCode() . '/pay');
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
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

        return json_decode($res, true);
    }
}
