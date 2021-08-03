<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DhlAgentPrices extends Model
{
    protected $table = 'dhl_agent_prices';
    protected $fillable = ['zones_id','service_id','agent_id','agent_price','handling_price','weight'];
    public $timestamps = false;
}
