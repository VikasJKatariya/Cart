<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'user_details';
    protected $fillable = ['user_id','company','phone','address1','address2','address3','postal_code','state','city','country_id'];

    public function country(){
    	return $this->belongsTo('App\Country','country_id','id');
    }
}
