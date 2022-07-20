<?php

namespace App\Tests;

use App\Service\BillingClient;
use App\Tests\Mock\BillingClientMock;

class Auth extends AbstractTest
{
    public function login()
    {
        $client = AbstractTest::getClient();
        $client->disableReboot();
        $client->getContainer()->set(
            BillingClient::class,
            new BillingClientMock()
        );
        $crawler = $client->request('GET', '/login');
        $this->assertResponseOk();
        $buttonCrawlerNode = $crawler->selectButton('Вход');
        $form = $buttonCrawlerNode->form();
        $form['email'] = 'test@mail.com';
        $form['password'] = 'test';
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        $crawler = $client->request('GET', '/courses');
        self::assertEquals('/courses', $client->getRequest()->getPathInfo());
        return $crawler;
    }
}