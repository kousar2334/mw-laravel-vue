<?php

namespace App\Models;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{

    public function productVariantOne()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one', 'id');
    }

    public function productVariantTwo()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two', 'id');
    }

    public function productVariantThree()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three', 'id');
    }

    public function getTitle()
    {
        $array = [];
        if ($this->productVariantOne != null) {
            array_push($array, $this->productVariantOne->variant);
        }
        if ($this->productVariantTwo != null) {
            array_push($array, $this->productVariantTwo->variant);
        }
        if ($this->productVariantThree != null) {
            array_push($array, $this->productVariantThree->variant);
        }
        return implode('/', $array);
    }
}
