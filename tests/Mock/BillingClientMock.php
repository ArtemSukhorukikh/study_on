<?php

namespace App\Tests\Mock;

use App\DTO\CourseNewDto;
use App\DTO\UserCurrentDto;
use App\Exception\BillingUnavailableException;
use App\Security\User;
use App\Service\BillingClient;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class BillingClientMock extends BillingClient
{
    private $urlBilling;
    protected $serializer;
    public $courses;
    public $transactions;

    public function __construct()
    {
        $this->urlBilling = $_ENV['BILLING_ADDRES'];
        $this->courses = [
            [
                'code' => 'uid1',
                'title' => 'Python Basic',
                'type' => 'rent',
                'price' => 150,
            ],
            [
                'code' => 'uid2',
                'title' => 'Java-разработчик',
                'type' => 'free',
                'price' => 0,
            ],
            [
                'code' => 'uid3',
                'title' => '1С-разработчик',
                'type' => 'buy',
                'price' => 150,
            ],
        ];
        $this->transactions = [
//            [
//                "id" => 1,
//                'code' => "uid3",
//                'type' => "payment",
//                'value' => 1500,
//                'created_at' => (new \DateTime())->format('Y-m-d T H:i:s')
//            ],
            [
                'id' => 2,
                'code' => "uid1",
                'type' => "payment",
                'value' => 150,
                'created_at' => (new \DateTime())->format('Y-m-d T H:i:s'),
                'expires_at' => (new \DateTime())->modify('next month')->format('Y-m-d T H:i:s')
            ],
        ];
    }
    private function generateToken($roles, string $username): string
    {
        $data = [
            'email' => $username,
            'roles' => $roles,
            'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
        ];
        $query = base64_encode(json_encode($data));
        return 'header.' . $query . '.signature';
    }
    /**
     * @throws BillingUnavailableException
     * @throws JsonException
     */
    public function login($data)
    {
        $dataFromJSON = json_decode($data, true);
        if ($dataFromJSON['username'] === 'test@mail.com' && $dataFromJSON['password'] === 'test') {
            $user = new User();
            $user->setEmail('test@mail.com');
            $user->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
            $user->setApiToken($this->generateToken(['ROLE_USER', 'ROLE_SUPER_ADMIN'], 'test@mail.com'));
            $user->setRefreshToken('asfqwr12312rqw');
            return $user;
        }
        throw new UserNotFoundException('Неправильная пара логин/пароль');
    }

    public function getCurrentUser($user)
    {
        if ($user->getUserIdentifier() === 'test@mail.com') {
            $userCurrent = new UserCurrentDto();
            $userCurrent->username = 'test@mail.com';
            $userCurrent->roles = ['ROLE_USER', 'ROLE_SUPER_ADMIN'];
            $userCurrent->balance = 15000;
            return $userCurrent;
        }
        throw new UserNotFoundException('Неправильная пара логин/пароль');
    }

    public function register($data)
    {
        $user = new User();
        $user->setEmail('test@mail.com');
        $user->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
        $user->setApiToken($this->generateToken(['ROLE_USER', 'ROLE_SUPER_ADMIN'], 'test@mail.com'));
        return $user;
    }

    public function getAllCourses()
    {
        return $this->courses;
    }

    public function getCourseByCode(string $code)
    {
        foreach ($this->courses as $course) {
            if ($course['code'] === $code) {
                return $course;
            }
        }
        throw new BillingUnavailableException('Данный курс не найден');
    }

    public function getTransactions($filter, $token)
    {
        $transactionsTest = [];
        if (isset($filter['type']) && $filter['type'] === 'payment') {
            foreach ($this->transactions as $transaction) {
                if (isset($filter['code'], $filter['skip_expired'])) {
                    if ($transaction['code'] === $filter['code'] && $transaction['expires_at'] > new \DateTime()) {
                        $transactionsTest[] = $transaction;
                    }
                } elseif (isset($filter['code'])) {
                    if ($transaction['code'] === $filter['code']) {
                        $transactionsTest[] = $transaction;
                    }
                }
            }
        } else {
            return $this->transactions;
        }

        return $transactionsTest;
    }

    public function pay($course, $token)
    {

        $flag = false;
        foreach ($this->courses as $courseBilling) {
            if ($courseBilling['code'] === $course->getCode()) {
                $courseDto = $courseBilling;
                $flag = true;
            }
        }
        if (!$flag) {
            throw new BillingUnavailableException('Данный курс в системе не найден', 404);
        }
        $this->transactions[] = [
            'id' => 5,
            'code' => $course->getCode(),
            'type' => "payment",
            'value' => $courseDto['price'],
            'created_at' => (new \DateTime())->format('Y-m-d T H:i:s'),
        ];
        return [
            'success' => true,
            'course_type' => $courseDto['type'],
        ];
    }

    public function newCourse(?User $user, CourseNewDto $courseNewDto)
    {
        $typeTest = 0;
        if ($courseNewDto->type === 'buy') {
            $typeTest = 2;
        } if ($courseNewDto ->type === 'rent') {
            $typeTest = 1;
        }
        $this->courses[] = [
            'code' => $courseNewDto->code,
            'title' => $courseNewDto->title,
            'type' => $typeTest,
            'price' => $courseNewDto->price,
        ];
        return ['success' => true];
    }

    public function editCourse(?User $user, CourseNewDto $courseNewDto, $courseCode)
    {
        $typeTest = 0;
        if ($courseNewDto->type === 'buy') {
            $typeTest = 2;
        } if ($courseNewDto ->type === 'rent') {
            $typeTest = 1;
        }
        $this->courses[] = [
            'code' => $courseNewDto->code,
            'title' => $courseNewDto->title,
            'type' => $typeTest,
            'price' => $courseNewDto->price,
        ];
        foreach ($this->courses as $course) {
            if ($course['code'] === $courseCode) {
                $course['code'] = $courseNewDto->code;
                $course['type'] = $typeTest;
                $course['price'] = $courseNewDto->price;
                $course['title'] = $courseNewDto->title;
            }
        }
        return ['success' => true];
    }

}