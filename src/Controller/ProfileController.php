<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public BillingClient $client;
    public function __construct(
                                BillingClient $client,
                                )
    {
        $this->client = $client;
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $userInfo = $this->client->getCurrentUser($this->getUser());
        
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'userName' => $userInfo->username,
            'userBalance' => $userInfo->balance,
        ]);
    }

    #[Route('/profile/history', name: 'app_profile_history')]
    public function indexHistory(BillingClient $client, CourseRepository $courseRepository): Response
    {
        $res = $this->client->getTransactions([], $this->getUser()->getApiToken());

        $historyData = [];
        foreach ($res as $data) {
            $historyData[] = [
                'type' => $data['type'],
                'value' => $data['value'],
                'created_at' => $data['created_at'],
                'course' => $data['type'] !==
                'free' && isset($data['code']) ? $courseRepository->findOneBy(['code' => $data['code']]) : null,
                'expires_at' => $data['expires_at'] ?? null,
            ];
        }

        return $this->render('profile/history.html.twig', [
            'transactions' => $historyData
        ]);
    }
}
