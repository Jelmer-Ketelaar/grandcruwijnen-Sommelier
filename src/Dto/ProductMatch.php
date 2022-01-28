<?php

namespace App\Dto;

class ProductMatch {
    public $product;
    public $score;
    public $specialPrice;
    public function __construct ($product, $score, $specialPrice) {
        $this->product = $product;
        $this->score = $score;
        $this->specialPrice = $specialPrice;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return mixed
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param mixed
     */
    public function getSpecialPrice()
    {
        return $this->specialPrice;
    }
}