<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';

    protected $fillable = [
        'name','service_id'
    ];
    public function countries(){
    	return $this->belongsToMany('App\Country','zone_countries','zone_id','country_id')->withTimestamps();
    }
    public function service(){
        return $this->belongsTo('App\Service','service_id','id');
    }
}
