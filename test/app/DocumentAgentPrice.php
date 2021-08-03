<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentAgentPrice extends Model
{
    protected $table = 'document_agent_prices';
    protected $fillable = ['zones_id','service_id','agent_id','agent_price','weight','handling_price'];
    public $timestamps = false;
}
