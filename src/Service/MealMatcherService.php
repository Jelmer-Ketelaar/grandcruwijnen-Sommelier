<?php

namespace App\Service;


use GuzzleHttp\Client;

class MealMatcherService {
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://mealmatcher.grandcruwijnen.nl']);
    }

    /**
     */
    public function getIndexPage(): string
    {
        return 'landing page/index.html.twig';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMealsCategories()
    {
        $response = $this->client->request('GET', '/api/meal_categories');

        $categories = json_decode($response->getBody()->getContents());
        usort($categories, function ($categoryA, $categoryB) {
            return strcmp($categoryA->name, $categoryB->name);
        });

        return $categories;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getParentMealCategories(): array
    {
        $categories = $this->getMealsCategories();
        $parentCategories = [];

        foreach ($categories as $category)
        {
            if ($category->parent === null)
            {
                $parentCategories[] = $category;
            }
        }

        return $parentCategories;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCategoriesForParent(int $parentId): array
    {
        $categories = $this->getMealsCategories();
        $childCategories = [];

        foreach ($categories as $category)
        {
            if ($category->parent !== null && $category->parent->categoryId === $parentId)
            {
                $childCategories[] = $category;
            }
        }

        return $childCategories;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMealsForCategory(int $categoryId)
    {
        $response = $this->client->request('GET', '/meals/category/' . $categoryId);

        return json_decode($response->getBody()->getContents());
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWinesForMeal($mealId)
    {
        $response = $this->client->request('GET', '/api/meal_matches?mealId=' . $mealId . '&limit=50');

        return json_decode($response->getBody()->getContents());
    }
}