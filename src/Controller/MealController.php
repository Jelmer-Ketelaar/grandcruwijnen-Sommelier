<?php

namespace App\Controller;


use App\Service\MealMatcherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class MealController extends AbstractController {
    
    #[Route('/', name: 'landing_page')]
    public function getIndex(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('landing page/index.html.twig', ['choices' => $mealMatcherService->getIndexPage()]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/categories', name: 'meal_categories')]
    public function getMealCategories(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getParentMealCategories()]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/{parentId}', name: 'meal_categories_for_parent')]
    public function getCategoriesForParent(int $parentId, MealMatcherService $mealMatcherService): Response
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getCategoriesForParent($parentId)]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/meals/{categoryId}', name: 'meals_for_category')]
    public function getMealsForCategory(int $categoryId, MealMatcherService $mealMatcherService): Response
    {
        return $this->render('meals/index.html.twig', ['meals' => $mealMatcherService->getMealsForCategory($categoryId)]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    #[Route('/matches/{mealId}', name: 'wines_for_meals')]
    public function getWinesForMeals($mealId, MealMatcherService $mealMatcherService): Response
    {
        return $this->render('wines/index.html.twig', ['matches' => $mealMatcherService->getWinesForMeal($mealId)]);
    }
}