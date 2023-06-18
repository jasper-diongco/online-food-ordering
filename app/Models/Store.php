<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    public $guarded = [];

    public function subscribers() {
        return $this->belongsToMany(User::class, 'subscriptions', 'store_id', 'user_id');
    }
}
