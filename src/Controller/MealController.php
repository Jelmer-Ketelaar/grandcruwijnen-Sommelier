<?php

namespace App\Controller;


use App\Service\MealMatcherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class MealController extends AbstractController {
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/', name: 'landing_page')]
    public function getIndex(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('landing page/index.html.twig', ['choices' => $mealMatcherService->getIndexPage()]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/', name: 'meal_categories')]
    public function getMealCategories(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getParentMealCategories()]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/{parentId}', name: 'meal_categories_for_parent')]
    public function getCategoriesForParent(int $parentId, MealMatcherService $mealMatcherService)
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getCategoriesForParent($parentId)]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/meals/{categoryId}', name: 'meals_for_category')]
    public function getMealsForCategory(int $categoryId, MealMatcherService $mealMatcherService)
    {
        return $this->render('meals/index.html.twig', ['meals' => $mealMatcherService->getMealsForCategory($categoryId)]);
    }

    #[Route('/wines/{wineId}', name: 'wines_for_meal')]
    public function getWinesForMeals($wineId, MealMatcherService $mealMatcherService)
    {
        return $this->render('wines/index.html.twig', ['wines' => $mealMatcherService->getWinesForMeal($wineId)]);
    }
}