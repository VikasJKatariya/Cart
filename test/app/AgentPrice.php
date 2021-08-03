<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgentPrice extends Model
{
    protected $table = 'agent_prices';
    protected $fillable = ['zones_id','weight_id','agent_id','agent_price','handling_price'];
}
