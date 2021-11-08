<?php

namespace App\Dto;

class ProductMatch {
    public $product;
    public $score;
    public function __construct ($product, $score) {
        $this->product = $product;
        $this->score = $score;
    }
}

?>