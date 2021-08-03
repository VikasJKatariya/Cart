<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PriceSlab extends Model
{
    protected $table = 'price_slabs';
    protected $fillable = ['zones_id','weight_id','upx_price','agent_price','handling_price'];
    
}
