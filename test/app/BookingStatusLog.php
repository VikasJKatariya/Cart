<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingStatusLog extends Model
{
    protected $table = 'booking_status_logs';
    protected $fillable = ['booking_id','status'];	

    /**********************************************  Booking Data *****************************************/

    public function booking(){
    	return $this->belongsTo('App\Booking','booking_id','id');
    }
}
