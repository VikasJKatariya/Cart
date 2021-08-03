<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'sku', 'parent_id','title','quantity','selling_price','buying_price','created_at','updated_at'
    ];

    public function order(){
        return $this->belongsTo('App\Order', 'sku_id','sku');
    }
}
