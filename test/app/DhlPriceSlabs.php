<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DhlPriceSlabs extends Model
{
    protected $table = 'dhl_price_slabs';
    protected $fillable = ['zones_id','service_id','upx_price','weight','handling_price'];
    public $timestamps = false;
}
