<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;

class LessonTest extends AbstractTest
{
    public function testNewLesson(): void
    {
        $client = AbstractTest::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $crawler = $client->request('GET', '/lessons/new/' . $courseRepository->findAll()[0]->getId());
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => 'ТестовоеНазвание',
                'lesson[content]' => 'Test',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseRedirect();
    }

    public function testEditLesson(): void
    {
        $client = AbstractTest::getClient();
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $crawler = $client->request('GET', '/lessons/' . $lessonRepository->findAll()[0]->getId() . '/edit');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Изменить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => 'ТестовоеНазвание',
                'lesson[content]' => 'Test',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseRedirect();
    }

    public function testDeleteLesson(): void
    {
        $client = AbstractTest::getClient();
        $lessonRepository = self::getEntityManager()->getRepository(Lesson::class);
        $crawler = $client->request('GET', '/lessons/' . $lessonRepository->findAll()[0]->getId());
        $this->assertResponseOk();
        $client->submitForm('lesson-delete');
        $this->assertResponseRedirect();
    }

    public function testEmptyLesson(): void
    {
        $client = AbstractTest::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $crawler = $client->request('GET', '/lessons/new/' . $courseRepository->findAll()[0]->getId());
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => '',
                'lesson[content]' => 'Test',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseCode(422);
        $client->submit(
            $form,
            [
                'lesson[name]' => 'test',
                'lesson[content]' => '',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseCode(422);
        $client->submit(
            $form,
            [
                'lesson[name]' => 'Test',
                'lesson[content]' => 'Test',
                'lesson[number]' => null,
            ]
        );
        $this->assertResponseCode(422);
    }

    public function testValidateLesson(): void
    {
        $client = AbstractTest::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $crawler = $client->request('GET', '/lessons/new/' . $courseRepository->findAll()[0]->getId());
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'lesson[name]' => 'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest
                                   testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest',
                'lesson[content]' => 'test',
                'lesson[number]' => 5,
            ]
        );
        $this->assertResponseCode(422);
        $client->submit(
            $form,
            [
                'lesson[name]' => 'Test',
                'lesson[content]' => 'Test',
                'lesson[number]' => 999999999999999999999999999999999999999999,
            ]
        );
        $this->assertResponseCode(422);
    }

    public function getFixtures(): array
    {
        return [
            CourseFixtures::class,
        ];
    }
}
