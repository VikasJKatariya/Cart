<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentPriceSlab extends Model
{
    protected $table = 'document_price_slabs';
    protected $fillable = ['zone_id','service_id','upx_price','weight','handling_price'];
    public $timestamps = false;
}
