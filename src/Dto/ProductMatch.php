<?php

namespace App\Dto;

class ProductMatch {
    public $product;
    public $score;
    public function __construct ($product, $score) {
        $this->product = $product;
        $this->score = $score;
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
}