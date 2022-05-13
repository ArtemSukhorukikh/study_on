<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use Symfony\Component\Panther\PantherTestCase;

class LessonTest extends AbstractTest
{
    public function testSomething(): void
    {
        $client = AbstractTest::getClient();
        $crawler = $client->request('GET', '/');

        //$this->assertSelectorTextContains('h1', 'Hello World');
    }

    public function getFixtures(): array
    {
        return [
            CourseFixtures::class,
        ];
    }
}
