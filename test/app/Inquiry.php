<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = ['firstname','lastname','email','contactno','shipper_address','service','service_type','length','width','height','weight','country','total','chargeable_weight','status'];

    public function getcountry(){
        return $this->belongsTo('App\Country','country','id');
    }

    public function getservice(){
        return $this->belongsTo('App\Service','service','id');
    }
}
