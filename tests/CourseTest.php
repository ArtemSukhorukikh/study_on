<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use App\Service\BillingClient;

class CourseTest extends AbstractTest
{
    public function testSomething(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/');
        $link = $crawler->selectLink('Пройти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
    }


    public function testCreatingCourse(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/new');
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');

        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'course[code]' => 'uuid06',
                'course[name]' => 'HTML-курс',
                'course[description]' => 'Курс по HTML',
                'course[type]' => 'rent',
                'course[price]' => '25'
            ]
        );
        $crawler = $client->followRedirect();
        $courseName = $crawler->filter('h1')->text();
        self::assertEquals('HTML-курс', $courseName);
        $courseDesq = $crawler->filter('.course-description')->text();
        self::assertEquals('Курс по HTML', $courseDesq);
    }

    public function testCountCourses(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/');
        $countCourses = 3;
        self::assertCount($countCourses, $crawler->filter('.card-body'));
    }

    public function testCoursesPagesSuccessful(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coursesAll = $courseRepository->findAll();
        foreach ($coursesAll as $course) {
            $client->request('GET', '/courses/' . $course->getId());
            $this->assertResponseOk();
            $client->request('GET', '/courses/' . $course->getId() . '/edit');
            $this->assertResponseOk();
            $client->request('GET', '/lessons/new/' . $course->getId());
            $this->assertResponseOk();
        }
    }

    public function testLessonsCount(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coursesAll = $courseRepository->findAll();
        self::assertNotEmpty($coursesAll);
        foreach ($coursesAll as $course) {
            $crawler = $client->request('GET', '/courses/' . $course->getId());
            $this->assertResponseOk();
            self::assertCount(count($course->getLessons()), $crawler->filter('.mt-2'));
        }
    }

    public function testValidationCodeCourse(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/new');
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coutseCount = $this->count($courseRepository->findAll());
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'course[code]' => 'uid01',
                'course[name]' => 'Test',
                'course[description]' => 'Test',
                'course[type]' => 'rent',
                'course[price]' => '25'
            ]
        );
        $this->assertResponseRedirect();
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
    }

    public function testValidationCourse(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/new');
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coutseCount = $this->count($courseRepository->findAll());
        $client->submit(
            $form,
            [
                'course[code]' => 'QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQW',
                'course[name]' => 'QWEQ',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
                'course[type]' => 'rent',
                'course[price]' => '25'
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
        $client->submit(
            $form,
            [
                'course[code]' => 'uid22',
                'course[name]' => 'QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWE
                                   QWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQWEQW',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
                'course[type]' => 'rent',
                'course[price]' => '25'
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
    }

    public function testWithBlankFieldsCourse(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/new');
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $coutseCount = $this->count($courseRepository->findAll());
        $buttonCrawlerNode = $crawler->selectButton('Сохранить');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            [
                'course[code]' => '',
                'course[name]' => 'EQW',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
                'course[type]' => 'rent',
                'course[price]' => '25'
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
        $client->submit(
            $form,
            [
                'course[code]' => 'uid22',
                'course[name]' => '',
                'course[description]' => 'QWEQWEQWEQWEQWEQWEQW',
                'course[type]' => 'rent',
                'course[price]' => '25'
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
        $client->submit(
            $form,
            [
                'course[code]' => 'uid22',
                'course[name]' => 'QWEqwee',
                'course[description]' => '',
                'course[type]' => 'rent',
                'course[price]' => '25'
            ]
        );
        $this->assertResponseCode(422);
        $coutseCountNew = $this->count($courseRepository->findAll());
        self::assertEquals($coutseCount, $coutseCountNew);
    }

    public function testDeleteCourse(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $client->submitForm('course-delete');
        self::assertTrue($client->getResponse()->isRedirect('/courses/'));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $courses = $courseRepository->findAll();
        self::assertNotEmpty($courses);
        $actualCoursesCount = count($courses);

        self::assertCount($actualCoursesCount, $crawler->filter('.card-body'));
    }

    public function testEditCourse(): void
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $link = $crawler->filter('.course-edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Изменить');
        $form = $submitButton->form();
        $course = self::getEntityManager()
            ->getRepository(Course::class)
            ->findOneBy(['code' => $form['course[code]']->getValue()]);
        $form['course[name]'] = 'Измененный курс';
        $form['course[description]'] = 'Измененный курс';
        $form['course[type]'] = 'rent';
        $form['course[price]'] = '25';
        $client->submit($form);
        $crawler = $client->followRedirect();


//        self::assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getId()));
        $courseName = $crawler->filter('h1')->text();
        self::assertEquals('Измененный курс', $courseName);

        $courseDescription = $crawler->filter('.course-description')->text();
        self::assertEquals('Измененный курс', $courseDescription);
    }

    public function testPay()
    {
        $auth = new Auth();
        $crawler = $auth->login();
        $client = self::getClient();
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
        $courseRepository = self::getEntityManager()->getRepository(Course::class);
        $crawler = $client->request('GET', '/courses/' . $courseRepository->findOneBy(['code' => 'uid3'])->getId());
        $link = $crawler->selectLink('Купить')->link();
        $client->click($link);
        $link = $crawler->selectLink('Да')->link();
        $client->click($link);
        $crawler = $client->followRedirect();
        $text = $crawler->filter('.alert-success')->text();
        self::assertEquals($text, "Оплата прошла успешно");
    }

    public function getFixtures(): array
    {
        return [
            CourseFixtures::class,
        ];
    }
}
