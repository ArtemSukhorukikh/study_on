<?php
namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;


class CourseNewDto
{
    #[Serializer\Type("string")]
    public string $type;

    #[Serializer\Type("string")]
    public string $title;

    #[Serializer\Type("string")]
    public string $code;

    #[Serializer\Type("float")]
    public float $price;

}