<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $fillable = ['agent_id','wallet_amout','stripe_amount','final_amount','final_amount','transation_id','read_payment','admin_read_payment'];

    /***************************************** Get agent data *************************************************/
    public function agent(){
    	return $this->belongsTo('App\User','agent_id','id')->withTrashed();
    }

    /***************************************** Get all invoice *************************************************/
    public function paymentbooking(){
    	return $this->belongsToMany('App\Booking','payment_bookings','payment_id','booking_id')->withTimestamps();
    }
}
