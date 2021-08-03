<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    
    public function zones(){
    	return $this->belongsToMany('App\Zone','zone_countries','country_id','zone_id')->withTimestamps();;
    }

    
}
