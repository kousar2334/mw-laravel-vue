<?php

namespace App\Models;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'variant_id', 'id');
    }
}
