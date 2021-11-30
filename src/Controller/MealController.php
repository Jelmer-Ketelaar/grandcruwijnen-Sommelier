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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;


class MealController extends AbstractController {

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
     * This returns the categories page as meals with the getParentMealCategories function from the mealMatcherService
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
        
        $mealIdlimited = $session->get('mealId');

        //dd($mealId);

        
        try {
            if($mealIdlimited['mealId'] != $mealId){
                $session->set('mealIdAgo', $mealIdlimited);
                $firstTime = true;
            } else {
                $firstTime = false;
            }
        } catch (\Throwable $th) {
            if($mealIdlimited != $mealId){
                $session->set('mealIdAgo', $mealIdlimited);
                $firstTime = true;
            } else {
                $firstTime = false;
            }
        }


        $session->set('mealId', array('mealId' => $mealId));

        $page = (int) $request->query->get('page');
        $productsPerPage = 18;
        if ($page === 0)
        {
            $page = 1;
        }
        $skuScores = [];
        $skus = [];
        foreach ($mealMatcherService->getWinesForMeal($mealId) as $wine)
        {
            $skus[] = $wine->wine->sku;
            $skuScores[$wine->wine->sku] = $wine->score;
        }

        $minWinePrice = 10000;
        $maxWinePrice = 0;

        if($firstTime == true){
            $session->set('price-max', null);
            $session->set('price-min', null);
        }
        
        $formMinPrice = $request->query->get('price-min');
        $formMaxPrice = $request->query->get('price-max');

        // als hij gezet is
        if($formMinPrice !== null){
            $session->set('price-min', $formMinPrice);
            $formMinPrice = $session->get('price-min');
        }
        if($formMaxPrice !== null){
            $session->set('price-max', $formMaxPrice);
            $formMaxPrice = $session->get('price-max');
        }

        if($session->get('price-min') !== null){
            $formMinPrice = $session->get('price-min');
        }
        if($session->get('price-max') !== null){
            $formMaxPrice = $session->get('price-max');
        }








        if($formMinPrice === null){
            $formMinPrice = 1;
        }
        if($formMaxPrice === null){
            $formMaxPrice = 100000;
        }
        
        
        /** @var Product[] $products */
        $products = $this->getDoctrine()->getRepository(Product::class)->findWinesBySkus($skus, $formMinPrice, $formMaxPrice);

        foreach ($products as $product)
        {
            $productMatch = new ProductMatch($product, $skuScores[$product->getSku()]);
            $winePrice = $productMatch->product->getprice();

            if ($minWinePrice > $winePrice)
            {
                $minWinePrice = $winePrice;
            }

            if($maxWinePrice < $winePrice) 
            {
                $maxWinePrice = $winePrice;
            }

            $matches[] = $productMatch;
        }

        
        $getMealId = $session->get('mealId');

        // If first time this page loaded: Set min and max price in session for filter
        if($firstTime == true){
            $mealIdAgo = $getMealId['mealId'];
            $session->set('min_price', $minWinePrice);
            $session->set('max_price', $maxWinePrice);
            $session->set('mealId', $mealId);
        }

        $maxWinePrice = $session->get('max_price');
        $minWinePrice = $session->get('min_price');


        usort($matches, static function ($matchA, $matchB) {
            return $matchA->getScore() === $matchB->getScore() ? 0 : ($matchA->getScore() < $matchB->getScore() ? 1 : - 1);
        });

        $matchesForPage = array_slice($matches, ($page) * $productsPerPage, $productsPerPage);
        $totalProductCount = count($matches);
        $totalPages = ceil($totalProductCount / $productsPerPage);
        $mealArr = [urldecode($mealId)];

        return $this->render('wines/index.html.twig', [
            'matches' => $matchesForPage,
            'min_price' => $formMinPrice,
            'max_price' => $formMaxPrice,
            'maxWinePrice' => $maxWinePrice,
            'minWinePrice' => $minWinePrice,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'meal_id' => $mealArr
        ]);
    }
}