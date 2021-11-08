<?php

namespace App\Controller;

use App\Dto\ProductMatch;
use App\Repository\ProductRepository;
use App\Service\MealMatcherService;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MealController extends AbstractController
{

    /**
     * @throws GuzzleException
     */
    #[Route('/', name: 'landing_page')]
    public function getIndex(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('landing/index.html.twig', ['choices' => $mealMatcherService->getIndexPage()]);
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/categories', name: 'meal_categories')]
    public function getMealCategories(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getParentMealCategories()]);
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/category/{parentId}', name: 'meal_categories_for_parent')]
    public function getCategoriesForParent(int $parentId, MealMatcherService $mealMatcherService): Response
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getCategoriesForParent($parentId)]);
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/meals/{categoryId}', name: 'meals_for_category')]
    public function getMealsForCategory(int $categoryId, MealMatcherService $mealMatcherService): Response
    {
        return $this->render('meals/index.html.twig', ['meals' => $mealMatcherService->getMealsForCategory($categoryId)]);
    }

    /**
     * @throws GuzzleException
     */
    #[Route('matches/{mealId}', name: 'wines_for_meals')]
    public function getWinesForMeals(ProductRepository $products, $mealId, MealMatcherService $mealMatcherService): Response
    {
        $matches = [];
//        dd($mealMatcherService->getWinesForMeal($mealId));
        foreach ($mealMatcherService->getWinesForMeal($mealId) as $wine) {
            $product = $products->findOneBy(['sku' => $wine->wine->sku]);
            // $product = $products->findOneBy(['sku' => $wine->wine->sku]);
            if ($product !== null) {
                $score = $wine->score;
                $productMatch = new ProductMatch($product, $score);
                $matches[] = $productMatch;
//                $score'' = $product;

                //TODO: Scores toevoegen aan de wijn
            }
        }

        return $this->render('wines/index.html.twig', [
            'matches' => $matches
        ]);
    }
}
