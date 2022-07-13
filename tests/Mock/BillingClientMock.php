<?php
namespace App\Tests\Mock;

use App\DTO\UserCurrentDto;
use App\Security\User;
use App\Service\BillingClient;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class BillingClientMock extends BillingClient
{
    private $urlBilling;
    protected $serializer;

    public function __construct()
    {
        $this->urlBilling = $_ENV['BILLING_ADDRES'];
   
    }
    /**
     * @throws BillingUnavailableException
     * @throws JsonException
     */
    public function login($data)
    {
        $dataFromJSON = json_decode($data, true);
        if ($dataFromJSON['username'] == 'test@mail.com' && $dataFromJSON['password'] == 'test') {
            $user = new User();
            $user->setEmail('test@mail.com');
            $user->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
            return $user;
        }
        throw new UserNotFoundException('Неправильная пара логин/пароль');
    }

    public function getCurrentUser($user) {
        if ($user->getUserIdentifier() == 'test@mail.com') {
            $userCurrent = new UserCurrentDto();
            $userCurrent->username = 'test@mail.com';
            $userCurrent->roles = ['ROLE_USER', 'ROLE_SUPER_ADMIN'];
            $userCurrent->balance = 150;
            return $userCurrent;
        }
        throw new UserNotFoundException('Неправильная пара логин/пароль');
    }

    public function register($data) 
    {
        $user = new User();
        $user->setEmail('test@mail.com');
        $user->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
        return $user;
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