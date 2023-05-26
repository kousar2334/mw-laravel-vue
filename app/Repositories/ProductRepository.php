<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{

    /**
     * Will return $product list
     * 
     * @param Object $request
     * @return Collections
     */
    public function productList($request)
    {
        $query = Product::with(['variantPrices' => function ($q) {
            $q->with(['productVariantOne' => function ($one) {
                $one->select('id', 'product_id', 'variant');
            }, 'productVariantTwo' => function ($two) {
                $two->select('id', 'product_id', 'variant');
            }, 'productVariantThree' => function ($three) {
                $three->select('id', 'product_id', 'variant');
            }])
                ->select('id', 'product_id', 'price', 'stock', 'product_variant_one', 'product_variant_two', 'product_variant_three');
        }]);

        return $query->paginate(10)->withQueryString();
    }
}
