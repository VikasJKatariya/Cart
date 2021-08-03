<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'sku_id', 'order_id','item_quantity','item_price','order_date','item_profit','created_at','updated_at'
    ];

    public function product(){
        return $this->belongsTo('App\Product', 'sku_id','sku');
    }
}
