<?php
namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class UserRegisterDto
{
    #[Serializer\Type("string")]
    public string $username;

    #[Serializer\Type("string")]
    public string $password;
}