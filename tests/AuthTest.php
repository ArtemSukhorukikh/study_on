<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Service\BillingClient;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;

class AuthTest extends AbstractTest
{
    public function testValidLogin(): void
    {
        $client = AbstractTest::getClient();
        $client->disableReboot();
        $client->getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Вход');
        $form = $buttonCrawlerNode->form();
        $form['email'] = 'test@mail.com';
        $form['password'] = 'test';
        $client->submit($form);
        $client->followRedirect();
        $this->assertResponseOk();
    }
    
    public function testInvalidLogin(): void
    {
        $client = AbstractTest::getClient();
        $client->disableReboot();
        $client->getContainer()->set(
            'App\Service\BillingClient', 
            new BillingClientMock()
        );
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Вход');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            ['email' => 'testqwe@mail.com', 'password' => 'test']
        );
    
        $this->assertResponseCode(302);
    }

    public function testRegistration(): void
    {
        $client = AbstractTest::getClient();
        $client->disableReboot();
        $client->getContainer()->set(
            'App\Service\BillingClient', 
            new BillingClientMock()
        );
        $crawler = $client->request('GET', '/register');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Зарегистрироваться');
        $form = $buttonCrawlerNode->form();
        $client->submit(
            $form,
            ['register[username]' => 'testqwe@mail.com',
             'register[password][password]' => 'test',
             'register[password][password_repeat]' => 'test']
        );

        $this->assertResponseOk();
    }

    public function testInvalidRegistration(): void
    {
        $client = AbstractTest::getClient();
        $client->disableReboot();
        $client->getContainer()->set(
            'App\Service\BillingClient', 
            new BillingClientMock()
        );
        $crawler = $client->request('GET', '/register');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Зарегистрироваться');
        $form = $buttonCrawlerNode->form();
        $crawler = $client->submit(
            $form,
            ['register[username]' => 'testqwe@mail.com',
             'register[password][password]' => 'test2',
             'register[password][password_repeat]' => 'test']
        );
        
        $this->assertNotEmpty($crawler->filter('li')->first());
        $this->assertEquals('The values do not match.', $crawler->filter('li')->first()->text());

        $crawler = $client->submit(
            $form,
            ['register[username]' => 'testqwe@mail.com',
             'register[password][password]' => 'test',
             'register[password][password_repeat]' => 'test']
        );
        
        $this->assertNotEmpty($crawler->filter('li')->first());
        $this->assertEquals('This value is too short. It should have 6 characters or more.', $crawler->filter('li')->first()->text());

        $crawler = $client->submit(
            $form,
            ['register[username]' => 'testqwe',
             'register[password][password]' => 'testtest',
             'register[password][password_repeat]' => 'testtest']
        );
        
        $this->assertNotEmpty($crawler->filter('li')->first());
        $this->assertEquals('This value is not a valid email address.', $crawler->filter('li')->first()->text());
        
    }
}