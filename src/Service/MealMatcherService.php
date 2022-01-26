<?php

namespace App\Service;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MealMatcherService
{
    private Client $client;

    public function __construct()
    {
        //Set the base URL of the api website
        $this->client = new Client(['base_uri' => $_SERVER['BASE_URL']]);
    }

    public function getIndexPage(): string
    {
        return 'landing/index.html.twig';
    }

    /**
     * @throws GuzzleException
     */
    public function getParentMealCategories(): array
    {
        $categories = $this->getMealsCategories();
        $parentCategories = [];

        foreach ($categories as $category) {
            //if the parent of the category is equal to null
            //put $category in $parentCategories
            if ($category->parent === null) {
                $parentCategories[] = $category;
            }
        }

        return $parentCategories;
    }

    /**
     * @throws GuzzleException
     */
    public function getMealsCategories()
    {
        $response = $this->client->request('GET', '/api/meal_categories');

        //The json string being decoded
        $categories = json_decode($response->getBody()->getContents());
        //Sort the names
        usort($categories, static function ($categoryA, $categoryB) {
            /*
             * This function is used to find whether two strings are same or not.
             * It returns 0 if $categoryA and $categoryB are same
             * less than zero if $categoryA is smaller than $categoryB
             * greater than zero if $categoryA is greater than $categoryB
             * */
            return strcmp($categoryA->name, $categoryB->name);
        });

        return $categories;
    }

    /**
     * @throws GuzzleException
     */
    public function getCategoriesForParent(int $parentId): array
    {
        $categories = $this->getMealsCategories();
        $childCategories = [];

        foreach ($categories as $category) {
            //If the parent of the category is not null and the categoryId is equal to $parentId
            if ($category->parent !== null && $category->parent->categoryId === $parentId) {
                //put $category in $childCategories[]
                $childCategories[] = $category;
            }
        }

        return $childCategories;
    }

    /**
     * @throws GuzzleException
     */
    public function getMealsForCategory(int $categoryId)
    {
        $response = $this->client->request('GET', '/meals/category/' . $categoryId);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function getWinesForMeal($mealId)
    {
        $response = $this->client->request('GET', '/api/meal_matches?meal=' . $mealId);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function getIngredients()
    {
        $response = $this->client->request('GET', '/api/ingredients');

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws GuzzleException
     */
    public function getWinesForIngredients($ingredients)
    {
        $ingredientMap = [];
        foreach ($ingredients as $ingredient) {
            $ingredientMap[] = ['id' => $ingredient, 'amount' => 1];
        }
        $response = $this->client->request('POST', '/winestein/meals/create', ['json' => $ingredientMap]);
        dd($response->getBody()->getContents());

        return json_decode($response->getBody()->getContents());
    }
}