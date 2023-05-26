<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;

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

        //Filter by product tile
        if ($request->has('title') && $request->title != null) {
            $query = $query->where('title', 'LIKE', '%' . $request->title . '%');
        }

        //Filter By product Prices
        if ($request->has('price_from') && $request->price_from != null && $request->has('price_to') && $request->price_to) {
            $query = $query->with(['variantPrices' => function ($q) use ($request) {
                $q->whereBetween('price', [$request->price_from, $request->price_to]);
            }])->whereHas('variantPrices', function ($price) use ($request) {
                $price->whereBetween('price', [$request->price_from, $request->price_to]);
            });
        }

        //Filter by product date
        if ($request->has('date') && $request->date != null) {
            $query = $query->whereDate('created_at', $request->date);
        }

        //Filter by product variant
        if ($request->has('variant') && $request->variant != null) {
            $query = $query->with(['variantPrices' => function ($q) use ($request) {
                $q->where('product_variant_one', $request->variant)
                    ->orWhere('product_variant_two', $request->variant)
                    ->orWhere('product_variant_three', $request->variant);;
            }])->whereHas('variantPrices', function ($q) use ($request) {
                $q->where('product_variant_one', $request->variant)
                    ->orWhere('product_variant_two', $request->variant)
                    ->orWhere('product_variant_three', $request->variant);
            });
        }

        return $query->paginate(5)->withQueryString();
    }

    /**
     * Wil return variant list
     * 
     * @return Collections
     */
    public function variants()
    {
        $query = Variant::with(['productVariants' => function ($q) {
            $q->select('id', 'variant', 'variant_id');
        }])
            ->select('id', 'title');


        return $query->get();
    }

    /**
     * Update product 
     * 
     * @param Object $request
     * @return bool
     */
    public function updateProduct($request)
    {
        try {
            DB::beginTransaction();
            $product = Product::find($request['id']);
            if ($product == null) {
                return false;
            }
            $product->title = $request['title'];
            $product->sku = $request['sku'];
            $product->description = $request['description'];
            $product->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
