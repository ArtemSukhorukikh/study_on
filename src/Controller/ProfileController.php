<?php

namespace App\Controller;

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
}
