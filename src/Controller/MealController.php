<?php

namespace App\Controller;


use App\Dto\ProductMatch;
use App\Entity\Product;
use App\Service\MealMatcherService;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MealController extends AbstractController
{

    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/", name="landing_page")
     */
    public function getIndex(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('landing/index.html.twig', ['choices' => $mealMatcherService->getIndexPage()]);
    }

    /**
     * @throws GuzzleException
     * @Route("/categories", name="meal_categories")
     */
    public function getMealCategories(MealMatcherService $mealMatcherService): Response
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getParentMealCategories()]);
    }

    /**
     * @throws GuzzleException
     * @Route("/category/{parentId}", name="meal_categories_for_parent")
     */
    public function getCategoriesForParent(int $parentId, MealMatcherService $mealMatcherService): Response
    {
        return $this->render('categories/index.html.twig', ['meals' => $mealMatcherService->getCategoriesForParent($parentId)]);
    }

    /**
     * @throws GuzzleException
     * @Route("/meals/{categoryId}", name="meals_for_category")
     */
    public function getMealsForCategory(int $categoryId, MealMatcherService $mealMatcherService): Response
    {
        return $this->render('meals/index.html.twig', ['meals' => $mealMatcherService->getMealsForCategory($categoryId)]);
    }

    /**
     * @throws GuzzleException
     * @Route("/matches/{mealId}", name="wines_for_meals")
     */
    public function getWinesForMeals(Request $request, $mealId, MealMatcherService $mealMatcherService): Response
    {
        $matches = [];
//        dd($mealMatcherService->getWinesForMeal($mealId));
        $session = $this->requestStack->getSession();
        $session->set('mealId', array('mealId' => $mealId));

        $formMinPrice = $request->query->get('price-min');
        if ($formMinPrice === null) {
            $formMinPrice = 0;
        }
        $formMaxPrice = $request->query->get('price-max');
        if ($formMaxPrice === null) {
            $formMaxPrice = 5000;
        }


        $page = (int)$request->query->get('page');
        $productsPerPage = 18;
        if ($page === 0) {
            $page = 1;
        }
        $skuScores = [];
        $skus = [];
        foreach ($mealMatcherService->getWinesForMeal($mealId) as $wine) {
            $skus[] = $wine->wine->sku;
            $skuScores[$wine->wine->sku] = $wine->score;
        }

        /** @var Product[] $products */
        $products = $this->getDoctrine()->getRepository(Product::class)->findWinesBySkus($skus, $formMinPrice, $formMaxPrice, $page, $productsPerPage);
        foreach ($products as $product) {
            $productMatch = new ProductMatch($product, $skuScores[$product->getSku()]);
            $matches[] = $productMatch;
        }

        usort($matches, static function ($matchA, $matchB) {
            return $matchA->getScore() === $matchB->getScore() ? 0 : ($matchA->getScore() < $matchB->getScore() ? 1 : -1);
        });

        $matchesForPage = array_slice($matches, ($page - 1) * $productsPerPage, $productsPerPage);
        $totalProductCount = count($matches);

        $totalPages = ceil($totalProductCount / $productsPerPage);
        $mealArr = [urldecode($mealId)];

        //TODO: give price filter to pagination

        return $this->render('wines/index.html.twig', [
            'matches' => $matchesForPage,
            'min_price' => $formMinPrice,
            'max_price' => $formMaxPrice,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'meal_id' => $mealArr
        ]);


    }
}