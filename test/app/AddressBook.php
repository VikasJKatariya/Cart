<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AddressBook extends Model
{
    protected $table = 'address_books';
    
     protected $fillable = [
        'name','email', 'phone_number', 'company','country_id','state','city','address1','address2','address3','postalcode','created_by'
    ];

    public function country(){
    	return $this->belongsTo('App\Country','country_id','id');
    }
    public function addedby(){
    	return $this->belongsTo('App\User','created_by','id')->withTrashed();
    }
}
