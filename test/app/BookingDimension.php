<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingDimension extends Model
{
    protected $table = 'booking_dimensions';
    protected $fillable = ['booking_id','lenth','width','height','weight','insure_amt','consignment_amt','total_on_dimension','total_on_weight','description','box_number','box_page'];

    /**********************************************  Booking Data *****************************************/

    public function booking(){
    	return $this->belongsTo('App\Booking','booking_id','id');
    }
}
