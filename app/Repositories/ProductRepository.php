<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
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
     * Will return product details
     *
     *@param Int $product_id
     */
    public function productDetails($product_id)
    {
        return Product::where('id', $product_id)->first();
    }

    /**
     * Will return product variants
     *
     *@param Int $product_id
     *@return Collections
     */
    public function productVariants($product_id)
    {
        return ProductVariant::where('product_id', $product_id)
            ->select('variant as tags', 'variant_id as option')
            ->get();
    }

    /**
     * Will return product variant prices
     *
     *@param Int $product_id
     *@return Collections
     */
    public function productVariantsPrices($product_id)
    {

        $prices = ProductVariantPrice::with(['productVariantOne', 'productVariantTwo', 'productVariantThree'])->where('product_id', $product_id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'price' => $item->price,
                    'stock' => $item->stock,
                    'title' => $item->getTitle()
                ];
            });

        return $prices;
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

            //Update product variant
            $product_variants = json_decode($request['product_variant'], true);
            $product->variants()->delete();
            if (!empty($product_variants)) {
                foreach ($product_variants as $variant) {
                    $new_variant = new ProductVariant();
                    $new_variant->variant = $variant['tags'];
                    $new_variant->variant_id = $variant['option'];
                    $new_variant->product_id = $product->id;
                    $new_variant->save();
                }
            }

            //Update variant price
            $product_variant_prices = json_decode($request['product_variant_prices'], true);
            if (!empty($product_variant_prices)) {
                foreach ($product_variant_prices as $variant_price) {
                    $variant_array = explode('/', trim($variant_price['title'], '/'));
                    if (sizeof($variant_array) > 0) {
                        $new_price = new ProductVariantPrice();
                        $new_price->price = $variant_price['price'];
                        $new_price->stock = $variant_price['stock'];
                        $new_price->product_variant_one = ProductVariant::where('product_id', $product->id)->where('variant', $variant_array[0])->first()->id;
                        $new_price->product_variant_two = sizeof($variant_array) > 1 ? ProductVariant::where('product_id', $product->id)->where('variant', $variant_array[1])->first()->id : null;
                        $new_price->product_variant_three = sizeof($variant_array) > 2 ? ProductVariant::where('product_id', $product->id)->where('variant', $variant_array[2])->first()->id : null;
                        $new_price->product_id = $product->id;
                        $new_price->save();
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return false;
        }
    }
}
