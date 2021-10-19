<?php

namespace App\Controller;

use App\Entity\MealMatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MealController extends AbstractController
{
    #[Route('/', name: 'meal')]
    public function index(Request $request): Response {
        //get everything from mealMatcher
        $mealMatcher = $this->getDoctrine()->getRepository(MealMatcher::class)->findAll();
        return $this->json($mealMatcher);
    }

    public function create(Request $request): Response {
        return $this->json('');
    }
}
