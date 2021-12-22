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
        //matches is an array
        $matches = [];

        //If there is any letter in front it will convert to a 0.
        $page = (int)$request->query->get('page');

        //set 18 products per page
        $productsPerPage = 18;
        //start at page 1 if no page is clicked yet
        if ($page === 0) {
            $page = 1;
        }
        //skuScores is an arry
        $skuScores = [];
        //skus is an arry
        $skus = [];
        foreach ($mealMatcherService->getWinesForMeal($mealId) as $wine) {
            $skus[] = $wine->wine->sku;
            $skuScores[$wine->wine->sku] = $wine->score;
        }

        $minWinePrice = 10000;
        $maxWinePrice = 0;

        $formMinPrice = $request->query->get('price-min');
        $formMaxPrice = $request->query->get('price-max');

        if ($formMinPrice === null) {
            $formMinPrice = 1;
        }
        if ($formMaxPrice === null) {
            $formMaxPrice = 100000;
        }

        //wijnsoort, meenemen
        $wineSorts = $request->query->get('wineSorts');
        $specialPriceSorts = $request->query->get('specialPriceSorts');
        // dd($wineSorts);
        /** @var Product[] $products */
        $products = $this->getDoctrine()->getRepository(Product::class)->findWinesBySkus($skus, $formMinPrice, $formMaxPrice);


        foreach ($products as $product) {
            $productMatch = new ProductMatch($product, $skuScores[$product->getSku()]);
            $winePrice = $productMatch->product->getprice();


            if ($minWinePrice > $winePrice) {
                $minWinePrice = $winePrice;
            }

            if ($maxWinePrice < $winePrice) {
                $maxWinePrice = $winePrice;
            }

            if ($winePrice >= $formMinPrice && $winePrice <= $formMaxPrice) {
                $matches[] = $productMatch;
            }
        }

        usort($matches, static function ($matchA, $matchB) {
            return $matchA->getScore() === $matchB->getScore() ? 0 : ($matchA->getScore() < $matchB->getScore() ? 1 : -1);
        });

        $matchesForPage = array_slice($matches, $productsPerPage * ($page - 1), $productsPerPage);

        $totalProductCount = count($matches);
        $totalPages = (int)ceil($totalProductCount / $productsPerPage);

        $mealArr = [urldecode($mealId)];

        $countWit = 0;
        $countRood = 0;
        $countRosé = 0;
        $countPort = 0;
        $countSherry = 0;
        $countMadeira = 0;
        $countVermout = 0;


        foreach ($matchesForPage as $amountWine) {
            if ($amountWine->product->getWineSort() === 'Wit') {
                $countWit++;
            }
            if ($amountWine->product->getWineSort() === 'Rood') {
                $countRood++;
            }
            if ($amountWine->product->getWineSort() === 'Rosé') {
                $countRosé++;
            }
            if ($amountWine->product->getWineSort() === 'Port') {
                $countPort++;
            }
            if ($amountWine->product->getWineSort() === 'Sherry') {
                $countSherry++;
            }
            if ($amountWine->product->getWineSort() === 'Madeira') {
                $countMadeira++;
            }
            if ($amountWine->product->getWineSort() === 'Vermout') {
                $countVermout++;
            }
        }

        $countSpecialPrice = 0;
        foreach ($matchesForPage as $amountSale) {
            if ($amountSale->product->getSpecialPrice() === 'specialPrice') {
                $countSpecialPrice++;
            }
        }

        $countWines = ['countWit' => $countWit, 'countRood' => $countRood, 'countRosé' => $countRosé, 'countPort' => $countPort, 'countSherry' => $countSherry, 'countMadeira' => $countMadeira, 'countVermout' => $countVermout];
        $countSpecialPrice = ['countSpecialPrice' => $countSpecialPrice];
        $image = 'https://mealmatcher.grandcruwijnen.dev/meals/{{ meal.id }}';

//        dd($wineSorts);

        return $this->render('wines/index.html.twig', [
            'matches' => $matchesForPage,
            'min_price' => $formMinPrice,
            'max_price' => $formMaxPrice,
            'maxWinePrice' => $maxWinePrice,
            'minWinePrice' => $minWinePrice,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'meal_id' => $mealArr,
            'wineSorts' => $wineSorts,
            'countWines' => $countWines,
            'specialPriceSorts' => $specialPriceSorts,
            'countSpecialPrice' => $countSpecialPrice
        ]);
    }
}