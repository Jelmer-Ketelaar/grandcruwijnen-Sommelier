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

    public function getParentMealCategories()
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

    public function getCategoriesForParent(int $parentId)
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

    public function getMealsForCategory(int $categoryId)
    {
        $response = $this->client->request('GET', '/meals/category/'.$categoryId);

        $meals = json_decode($response->getBody()->getContents());
        return $meals;
    }
}