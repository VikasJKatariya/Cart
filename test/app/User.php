<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','lastname', 'email', 'password','status','role','token','image','booking_limit','wallet_amount','logo_image','company_no','vat_number','code_number','original_password'

    ];

    protected $dates = ['deleted_at'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function userdetail(){
        return $this->hasOne('App\UserDetail','user_id','id')->withDefault();
    }

    public function unpaidbokings(){
        return $this->hasMany('App\Booking','booked_by','id')->where('payment_status','unpaid');
    }

    public function unreadpayment(){
        return $this->hasMany('App\Payment','agent_id','id')->where('read_payment','0');
    }
    public function unreadadminpayment(){
        return $this->hasMany('App\Payment','agent_id','id')->where('admin_read_payment',0);
    }

}
