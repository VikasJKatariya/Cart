<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZoneCountry extends Model
{
   	protected $table = 'zone_countries';
    protected $fillable = ['zone_id','service_id','country_id'];

     public function zone_data(){
        return $this->belongsTo('App\Zone','zone_id','id');
    }
}
