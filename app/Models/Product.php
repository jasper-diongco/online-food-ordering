<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $guarded = [];

    public function category() {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }
}
