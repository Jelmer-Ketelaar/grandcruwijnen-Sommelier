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
    public function getWinesForMeals(Request $request, $mealId, MealMatcherService $mealMatcherService)
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

        if ($formMinPrice === null)
        {
            $formMinPrice = 1;
        }
        if ($formMaxPrice === null)
        {
            $formMaxPrice = 100000;
        }

        //wijnsoort, meenemen
        $wineSorts = $request->query->get('wineSorts');

        /** @var Product[] $products */
        $products = $this->getDoctrine()->getRepository(Product::class)->findWinesBySkus($skus);

//        $calculatePercentage = 0;
        foreach ($products as $product)
        {
//            $priceSpecial = null;
            $productMatch = new ProductMatch($product, $skuScores[$product->getSku()]);
            $winePrice = $productMatch->product->getPrice();

            if ($minWinePrice > $winePrice)
            {
                $minWinePrice = $winePrice;
            }

            if ($maxWinePrice < $winePrice)
            {
                $maxWinePrice = $winePrice;
            }

            if ($winePrice >= $formMinPrice && $winePrice <= $formMaxPrice)
            {
                $matches[] = $productMatch;
            }
        }

        usort($matches, static function ($matchA, $matchB) {
            return $matchA->getScore() === $matchB->getScore() ? 0 : ($matchA->getScore() < $matchB->getScore() ? 1 : -1);
        });

        $matchesForPage = array_slice($matches, $productsPerPage * ($page - 1), $productsPerPage);

        $totalProductCount = count($matches);
        $totalPages = (int)ceil($totalProductCount / $productsPerPage);

//        $ingredientId =;
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

        $countWines = ['countWit' => $countWit, 'countRood' => $countRood, 'countRosé' => $countRosé, 'countPort' => $countPort, 'countSherry' => $countSherry, 'countMadeira' => $countMadeira, 'countVermout' => $countVermout];

        $countAards = 0;
        $countComplex = 0;
        $countDonkerFruit = 0;
        $countDroog = 0;
        $countHoutgerijpt = 0;
        $countKrachtig = 0;
        $countKruidig = 0;
        $countMineraal = 0;
        $countRoodFruit = 0;
        $countTannines = 0;
        $countVol = 0;
        $allSkus = [];
        foreach ($matchesForPage as $idForWine) {
            array_push($allSkus, $idForWine->product->getSku());
        }

            $wineProfile = $request->query->get('wineProfile');
            if(isset($wineProfile)) {
                foreach ($mealMatcherService->getWineProfileForWines($allSkus) as $amountProfile)
                {
                    $profilis = json_decode($amountProfile, true)['profiles'];

                    if (in_array('Aards', $profilis))
                    {
                        $countAards ++;
                    }
                    if (in_array('Complex', $profilis))
                    {
                        $countComplex ++;
                    }
                    if (in_array('DonkerFruit', $profilis))
                    {
                        $countDonkerFruit ++;
                    }
                    if (in_array('Droog', $profilis))
                    {
                        $countDroog ++;
                    }
                    if (in_array('Houtgerijpt', $profilis))
                    {
                        $countHoutgerijpt ++;
                    }
                    if (in_array('Krachtig', $profilis))
                    {
                        $countKrachtig ++;
                    }
                    if (in_array('Kruidig', $profilis))
                    {
                        $countKruidig ++;
                    }
                    if (in_array('Mineraal', $profilis))
                    {
                        $countMineraal ++;
                    }
                    if (in_array('RoodFruit', $profilis))
                    {
                        $countRoodFruit ++;
                    }
                    if (in_array('Tannines', $profilis))
                    {
                        $countTannines ++;
                    }
                    if (in_array('Vol', $profilis))
                    {
                        $countVol ++;
                    }
                }
            }
        $countProfile = ['countAards' => $countAards, 'countComplex' => $countComplex, 'countDonkerFruit' => $countDonkerFruit, 'countDroog' => $countDroog, 'countHoutgerijpt' => $countHoutgerijpt, 'countKrachtig' => $countKrachtig, 'countKruidig' => $countKruidig, 'countMineraal' => $countMineraal, 'countRoodFruit' => $countMineraal, 'countTannines' => $countTannines, 'countVol' => $countVol];

//        dd($countWines);

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
        ]);
    }

    /**
     * @throws GuzzleException
     * @Route("/create/own/meal", name="create/own/meal")
     */
    public function getIngredients(Request $request, MealMatcherService $mealMatcherService)
    {

        $ingredientSelected = $request->query->all();
        $ingredientNameId = [];

        if ($ingredientSelected !== [])
        {
            foreach ($mealMatcherService->getIngredients() as $ingredient)
            {
                foreach ($ingredientSelected['ingredientId'] as $ing)
                {
                    $ingredientId = substr($ingredient->ingredientId, 1, - 1);
                    if ($ing === $ingredientId)
                    {
                        $ingredientNameId[] = ['id' => $ingredientId, 'name' => $ingredient->name];
                    }
                }
            }
        }

        return $this->render('ingrediënts/index.html.twig', [
            'ingredients' => $mealMatcherService->getIngredients(),
            'ingredientSelected' => $ingredientNameId,
        ]);
    }

    /**
     * @throws GuzzleException
     * @Route("/create/meal", name="profile_for_wines")
     */
    public function getWineProfileForWines(Request $request, MealMatcherService $mealMatcherService)
    {
        $wineProfile = $request->query->get('wineProfile');

        $countAards = 0;
        $countComplex = 0;
        $countDonkerFruit = 0;
        $countDroog = 0;
        $countHoutgerijpt = 0;
        $countKrachtig = 0;
        $countKruidig = 0;
        $countMineraal = 0;
        $countRoodFruit = 0;
        $countTannines = 0;
        $countVol = 0;
        dd($wineProfile);
        foreach ($wineProfile as $amountProfile)
        {
            if ($amountProfile->product->getWineProfile() === 'Aards')
            {
                $countAards ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Complex')
            {
                $countComplex ++;
            }
            if ($amountProfile->product->getWineProfile() === 'DonkerFruit')
            {
                $countDonkerFruit ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Droog')
            {
                $countDroog ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Houtgerijpt')
            {
                $countHoutgerijpt ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Krachtig')
            {
                $countKrachtig ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Kruidig')
            {
                $countKruidig ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Mineraal')
            {
                $countMineraal ++;
            }
            if ($amountProfile->product->getWineProfile() === 'RoodFruit')
            {
                $countRoodFruit ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Tannines')
            {
                $countTannines ++;
            }
            if ($amountProfile->product->getWineProfile() === 'Vol')
            {
                $countVol ++;
            }
        }
        $countProfile = ['countAards' => $countAards, 'countComplex' => $countComplex, 'countDonkerFruit' => $countDonkerFruit, 'countDroog' => $countDroog, 'countHoutgerijpt' => $countHoutgerijpt, 'countKrachtig' => $countKrachtig, 'countKruidig' => $countKruidig, 'countMineraal' => $countMineraal, 'countRoodFruit' => $countMineraal, 'countTannines' => $countTannines, 'countVol' => $countVol];

        return $this->render('wines_for_ingredients/index.html.twig', [
            'countProfile' => $countProfile]);
    }

    /**
     * @throws GuzzleException
     * @Route("/create/meal", name="wines_for_ingredients")
     */
    public function getWinesForIngredients(Request $request, MealMatcherService $mealMatcherService)
    {
        $ingredientSelected = $request->query->get("ingredients");
//        dd($ingredientSelected);

        if ($ingredientSelected == null)
        {
            $ingredientSelected = ['name' => ''];
        }
        //matches is an array
        $matches = [];

        //If there is any letter in front it will convert to a 0.
        $page = (int) $request->query->get('page');

        //set 18 products per page
        $productsPerPage = 18;
        //start at page 1 if no page is clicked yet
        if ($page === 0)
        {
            $page = 1;
        }
        //skuScores is an arry
        $skuScores = [];
        //skus is an arry
        $skus = [];
        foreach ($mealMatcherService->getWinesForIngredients($ingredientSelected) as $wine)
        {
            $skus[] = $wine->wine->sku;
            $skuScores[$wine->wine->sku] = $wine->score;
        }

        $minWinePrice = 10000;
        $maxWinePrice = 0;

        $formMinPrice = $request->query->get('price-min');
        $formMaxPrice = $request->query->get('price-max');

        if ($formMinPrice === null)
        {
            $formMinPrice = 1;
        }
        if ($formMaxPrice === null)
        {
            $formMaxPrice = 100000;
        }

        //wijnsoort, meenemen
        $wineSorts = $request->query->get('wineSorts');


//        dd($wineSorts);
        // dd($wineSorts);
        /** @var Product[] $products */
        $products = $this->getDoctrine()->getRepository(Product::class)->findWinesBySkus($skus);


        foreach ($products as $product)
        {
            $productMatch = new ProductMatch($product, $skuScores[$product->getSku()]);
            $winePrice = $productMatch->product->getPrice();

            if ($minWinePrice > $winePrice)
            {
                $minWinePrice = $winePrice;
            }

            if ($maxWinePrice < $winePrice)
            {
                $maxWinePrice = $winePrice;
            }

            if ($winePrice >= $formMinPrice && $winePrice <= $formMaxPrice)
            {
                $matches[] = $productMatch;
            }
        }

        usort($matches, static function ($matchA, $matchB) {
            return $matchA->getScore() === $matchB->getScore() ? 0 : ($matchA->getScore() < $matchB->getScore() ? 1 : - 1);
        });

        $matchesForPage = array_slice($matches, $productsPerPage * ($page - 1), $productsPerPage);

        $totalProductCount = count($matches);
        $totalPages = (int) ceil($totalProductCount / $productsPerPage);

        $mealArr = [urlencode($ingredientSelected)];

        $countWit = 0;
        $countRood = 0;
        $countRosé = 0;
        $countPort = 0;
        $countSherry = 0;
        $countMadeira = 0;
        $countVermout = 0;


        foreach ($matchesForPage as $amountWine)
        {
            if ($amountWine->product->getWineSort() === 'Wit')
            {
                $countWit ++;
            }
            if ($amountWine->product->getWineSort() === 'Rood')
            {
                $countRood ++;
            }
            if ($amountWine->product->getWineSort() === 'Rosé')
            {
                $countRosé ++;
            }
            if ($amountWine->product->getWineSort() === 'Port')
            {
                $countPort ++;
            }
            if ($amountWine->product->getWineSort() === 'Sherry')
            {
                $countSherry ++;
            }
            if ($amountWine->product->getWineSort() === 'Madeira')
            {
                $countMadeira ++;
            }
            if ($amountWine->product->getWineSort() === 'Vermout')
            {
                $countVermout ++;
            }
        }

        $countWines = ['countWit' => $countWit, 'countRood' => $countRood, 'countRosé' => $countRosé, 'countPort' => $countPort, 'countSherry' => $countSherry, 'countMadeira' => $countMadeira, 'countVermout' => $countVermout];

        return $this->render('wines_for_ingredients/index.html.twig', [
            'ingredients' => $mealMatcherService->getIngredients(),
            'ingredientSelected' => $ingredientSelected['ingredientId'],
            'matches' => $matchesForPage,
            'min_price' => $formMinPrice,
            'max_price' => $formMaxPrice,
            'maxWinePrice' => $maxWinePrice,
            'minWinePrice' => $minWinePrice,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'ingredient_id' => $mealArr,
            'wineSorts' => $wineSorts,
            'countWines' => $countWines
        ]);
    }
}
