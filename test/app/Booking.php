<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $fillable = ['booked_by','service_id','service_type','payment_status','handling_price','current_status','package_type','tracking_number','booking_instruction','upx_price','agent_price','actual_weight','volumetric_weight','final_insure_amt','final_consignment_amt','final_upx_price','final_agent_price','mail_notify','price_per_kg_upx','price_per_kg_agent','discount_amt','packing_charge_amt','tax_amt','final_total_upx','final_total_agent','agent_invoice','handling_price_agent'];

    /*************************** Get all dimention of booking **************************/
    public function dimentions(){
    	return $this->hasMany('App\BookingDimension','booking_id','id');
    }

    /************************** Get all address of booking *****************************/

    public function addresses(){
    	return $this->hasMany('App\BookingAddress','booking_id','id');
    }

    /************************ Get all Logs of Booking *********************************/

    public function logs(){
    	return $this->hasMany('App\BookingStatusLog','booking_id','id');
    }

    /*********************** get data of  Booking done by *****************************/

    public function createdby(){
    	return $this->belongsTo('App\User','booked_by','id')->withTrashed();
    }

    /*************************** Sender address **************************/
    public function addressessender(){
        return $this->hasOne('App\BookingAddress','booking_id','id')->where('type','sender');
    }

    /*************************** Receiver Address **************************/
    public function addressesreceiver(){
        return $this->hasOne('App\BookingAddress','booking_id','id')->where('type','receiver');
    }

    /*************************** Receiver Address **************************/
    public function addressesreturn(){
        return $this->hasOne('App\BookingAddress','booking_id','id')->where('type','return');
    }

    /************************ Get Log Status *********************************/

    public function logstatus(){
        return $this->belongsTo('App\LogStatus','current_status','id');
    }

    /***************************************** Payment *************************************************/
    public function paymentbooking(){
        return $this->belongsToMany('App\Payment','payment_bookings','booking_id','payment_id')->withTimestamps();
    }
    /*********************** get data of  Booking done by *****************************/

    public function service(){
        return $this->belongsTo('App\Service','service_id','id');
    }
}
