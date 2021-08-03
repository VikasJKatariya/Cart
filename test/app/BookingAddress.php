<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingAddress extends Model
{
    protected $table = 'booking_addresses';
    protected $fillable = ['booking_id','type','name','lastname','email','address1','address2','address3','country_id','state','city','postalcode','phonenumber','company','id_type','id_number','id_doc_image','vat_number'];

    /**********************************************  Booking Data *****************************************/

    public function booking(){
    	return $this->belongsTo('App\Booking','booking_id','id');
    }

    /**********************************************  Booking country *****************************************/
    public function country(){
    	return $this->belongsTo('App\Country','country_id','id');
    }
}
