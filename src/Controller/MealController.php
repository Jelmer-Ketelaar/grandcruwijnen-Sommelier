<?php

namespace App\Controller;


use App\Service\MealMatcherService;
use Grandcruwijnen\SDK\API;
use Grandcruwijnen\SDK\Products;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MealController extends AbstractController
{

    #[Route('/', name: 'landing_page')]
    public function getIndex(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('landing page/index.html.twig', ['choices' => $mealMatcherService->getIndexPage()]);
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
    #[Route('/{parentId}', name: 'meal_categories_for_parent')]
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
    #[Route('/matches/{mealId}', name: 'wines_for_meals')]
    public function getWinesForMeals($mealId, MealMatcherService $mealMatcherService)
    {

        $api = new API("jelmer@grandcruwijnen.nl", "7Wn2okY7!A@mX-DZMmw7tanFaQ*sTGef87o!Gn4_mE6ctiqmLk2hH6LX_deN_K8P7U6LRs7H2BT.cGWvh", "https://beta.grandcruwijnen.dev");
        $products = new Products($api);
        $sku = '01001';
        $products = $products->getProduct($sku);

        return $this->render('wines/index.html.twig', [
            'products' => $products, 'matches' => $mealMatcherService->getWinesForMeal($mealId)
        ]);
    }
}
