<?php

use App\Booking;
use App\PriceSlab;
use App\AgentPrice;
use App\Setting;
use App\Payment;
use Mail as MailUser;
use App\Country;
use App\ZoneCountry;
use App\BookingDimension;
use App\User;
use App\DocumentAgentPrice;
use App\DocumentPriceSlab;
use App\DhlAgentPrices;
use App\DhlPriceSlabs;
use App\Service;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Jwt\ClientToken;



/************************************** For Active sidebar *****************************/

function activeMenu($uri = '') {
    $active = '';
    if (Request::is(Request::segment(1) . '/' . $uri . '/*') || Request::is(Request::segment(1) . '/' . $uri) || Request::is($uri)) {
        $active = 'active';
    }
    return $active;
}


function checkPermission($permissions){
	if(auth()->check())
	{
		$userAccess = auth()->user()->role;
		foreach ($permissions as $key => $value) {
			if($value == $userAccess){
				return true;
			}
		}
		return false;
	}else{
		return false;
	}
}
function GetMyLogo(){
	$image = "logo.png";
	if(auth()->check()){
		if(auth()->user()->role == 'agent' && !empty(auth()->user()->logo_image) && auth()->user()->logo_image != null){
			$image = auth()->user()->logo_image;
		}elseif(auth()->user()->role == 'agent' && empty(auth()->user()->logo_image) && auth()->user()->logo_image == null){
			$image = "defaultinvoice.png";
		}
	}
	return $image;
}
function GetRoundByFraction($number = 0.00){
	$whole = floor($number);
	$fraction = $number - $whole;
	if($fraction >= 0.25){
	   $whole += 1;
	}
	if($whole == 0){
		$whole = 1;
	}
	return $whole;
}

function time_elapsed_string($ptime)
{
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}

function sendnotification($booking){
	$senderemail = $receiveremail = '';
            $data = [
                'data' => $booking,
            ];

            if($booking->mail_notify == 1){
                Log::info('mail sending...');
                $data['usertype'] = 'sender';
                $senderemail = $booking->addressessender->email;
                $maildata = MailUser::send('upx.mailtemplate.bookingstatus', array(
                'email' => $senderemail,
                'data'=> $data
                ), function ($message) use ($senderemail) {
                    $message->to($senderemail)
                        ->subject('Booking notification');
                });
                Log::info('mail sending sender site done.');
                /*******************************/

                if (MailUser::failures()) {
                    // return response showing failed emails
                    dd($maildata);
                    return $maildata;
                }

                /*******************************/
                if(!empty($booking->addressesreceiver->email) && $booking->addressesreceiver->email != ''){
                    $data['usertype'] = 'receiver';
                    $receiveremail = $booking->addressesreceiver->email;
                    MailUser::send('upx.mailtemplate.bookingstatus', array(
                    'email' => $receiveremail,
                    'data'=> $data
                    ), function ($message) use ($receiveremail) {
                        $message->to($receiveremail)->subject('Booking notification');
                    });
                }
                Log::info('mail sending receiver site done.');
            }
            return true;
}
function CountPaymentUnreadAdmin(){
	return Payment::whereAdminReadPayment('0')->count();
}
function settingvalue($key){
	$return = Setting::where('key',$key)->first();
	if(!empty($return)){
		return $return->value;
	}else{
		return '';
	}
}
function handling_price(){
	$return = Setting::where('key','hangling_price')->first();
	if(!empty($return)){
		return $return->value;
	}else{
		return 0.00;
	}
}
function getTrackNumber() {
	$n = 8;
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return 'UP'.$randomString;
}

function GetCountries($service_id="") {
    $zone_country_ids = ZoneCountry::pluck('country_id')->toArray();
    if($service_id){
        $zone_country_ids = ZoneCountry::where('service_id',$service_id)->pluck('country_id')->toArray();
    }
	$receivecountries = Country::whereIn('id',$zone_country_ids)->get();
    return $receivecountries;
}
function getBoxNumber() {
	$n = 7;
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return 'BOX'.$randomString;
}

//=============================================Get Door to Door price ========================================//
function getmypricedoortodoor($weightid,$zoneid,$type,$agentid=0,$servicetype="economy",$pricestype){
    if($type == 'agent_price'){



        $price = AgentPrice::where([['zones_id',$zoneid],['agent_id',$agentid],['type',$servicetype]])->first();

        if(!empty($price)){
            if($pricestype == 'price')
            {
                return $price->agent_price;
            }else if($pricestype == 'handling')
            {
                return $price->handling_price;
            }
        }else{
            return 0.00;
        }
    }
    if($type = 'upx_price'){

        $price = PriceSlab::where([['zones_id',$zoneid],['type',$servicetype]])->first();
        if(!empty($price)){
            if($pricestype == 'price')
            {
                return $price->upx_price;
            }else if($pricestype == 'handling')
            {
                return $price->handling_price;
            }

        }else{
            return 0.00;
        }
    }
}
//=============================================Get Door to Door price ========================================//


function getmyprice($weightid,$zoneid,$type,$agentid=0,$servicetype="economy",$service_id = 1){
	if($type == 'agent_price'){
		$price = AgentPrice::where([['weight_id',$weightid],['zones_id',$zoneid],['agent_id',$agentid],['service_id',$service_id],['type',$servicetype]])->first();
		if(!empty($price)){
			return $price->agent_price;
		}else{
			return 0.00;
		}
	}
	if($type = 'upx_price'){
		$price = PriceSlab::where([['weight_id',$weightid],['zones_id',$zoneid],['service_id',$service_id],['type',$servicetype]])->first();
		if(!empty($price)){
			return $price->upx_price;
		}else{
			return 0.00;
		}
	}
}

function getmydocumentprice($zoneid,$weight,$serviceid,$type,$agentid = 0){
    if($type == 'agent_price'){
        $agentprice = DocumentAgentPrice::where([['zones_id',$zoneid],['agent_id',$agentid],['service_id',$serviceid],['weight',$weight]])->first();
        if(!empty($agentprice)){
            return $agentprice->agent_price;
        }else{
            return 0;
        }

    }
    if($type = 'upx_price'){
        $upx_price = DocumentPriceSlab::where([['zone_id',$zoneid],['service_id',$serviceid],['weight',$weight]])->first();
        if(!empty($upx_price)){
            return $upx_price->upx_price;
        }else{
            return 0;
        }
    }
}


function getmydocumentpricedocument($zoneid,$weight,$type,$agentid = 0,$pricetype){
    if($type == 'agent_price'){
        $agentprice = DocumentAgentPrice::where([['zones_id',$zoneid],['agent_id',$agentid],['weight',$weight]])->first();
        if(!empty($agentprice)){
            if($pricetype == 'price')
            {
                return $agentprice->agent_price;
            }
            if($pricetype == 'handling')
            {
                return $agentprice->handling_price;
            }

        }else{
            return 0;
        }

    }
    if($type = 'upx_price'){
        $upx_price = DocumentPriceSlab::where([['zone_id',$zoneid],['weight',$weight]])->first();
        if(!empty($upx_price)){
             if($pricetype == 'price')
            {
                return $upx_price->upx_price;
            }
            if($pricetype == 'handling')
            {
                return $upx_price->handling_price;
            }
        }else{
            return 0;
        }
    }
}


function getmypricedhl($zoneid,$type,$agentid,$pricetype){
    if($type == 'agent_price'){
        $price = DhlAgentPrices::where([['zones_id',$zoneid],['agent_id',$agentid]])->first();
        if(!empty($price)){
            if($pricetype == 'price')
            {
                return $price->agent_price;
            }
            if($pricetype == 'handling')
            {
                return $price->handling_price;
            }
        }else{
            return 0.00;
        }
    }
    if($type = 'upx_price'){
        $price = DhlPriceSlabs::where('zone_id',$zoneid)->first();
        if(!empty($price)){
            if($pricetype == 'price')
            {
                return $price->upx_price;
            }
            if($pricetype == 'handling')
            {
                return $price->handling_price;
            }

        }else{
            return 0.00;
        }
    }
}



function begin() {
    \DB::beginTransaction();
}
function commit() {
    \DB::commit();
}

function rollback() {
    \DB::rollBack();
}
function generateTrackNumber(){
    $userid = auth()->user()->id;
    $user = User::where('id',$userid)->first();
    $boxnumber = rand(10,1000);
    if($user){
        $code_number = ($user->code_number)?$user->code_number:rand(10,1000);
        //$bookingcount = BookingDimension::where('booking_id',$booking_id)->count()+1;
        $bookingcount = Booking::where('booked_by',$userid)->count()+1;
        $boxnumber= $code_number.str_pad($bookingcount, 6, '0', STR_PAD_LEFT);
    }
   /* $n = 7;
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }*/
    return $boxnumber;
}
function Getservices() {
    $services = Service::where('status', 'active')->get();
    return $services;
}

function send_sms($tracking_number,$name,$final_total_upx) {


    $accountSid = 'AC7b04fc78126e81179b80ec866646d92b';
    $authToken  = 'c8e5e9bc998638af654d86845a373350';
    $client = new Client($accountSid, $authToken);
    try
        {
            // Use the client to do fun stuff like send text messages!
            $client->messages->create(
            // the number you'd like to send the message to
                '+917567434957',
            array(
                 // A Twilio phone number you purchased at twilio.com/console
                 'from' => '+17206198223',
                 // the body of the text message you'd like to send
                 'body' => 'Hi '.$name.', we received payment of £'.$final_total_upx.' for your consignment booking with UPX. .
                            Your tracking number is '.$tracking_number.'. You will get updates regarding the shipping and delivery by SMSs from us.
                            Thanks!'
            )
         );
        }
        catch (Exception $e)
        {
            echo "Error: " . $e->getMessage();
        }
}

function send_sms_status($tracking_num,$logid,$name,$phonenumber) {

    $accountSid = 'AC7b04fc78126e81179b80ec866646d92b';
    $authToken  = 'c8e5e9bc998638af654d86845a373350';
    $client = new Client($accountSid, $authToken);

    if ($logid == 1) {
        $msg = 'Hi '.$name.', your consignment is being processed at the Shipped department. Its on its way to the destination.';
    } elseif ($logid == 2 ) {
        $msg = 'Hi '.$name.', your consignment is being processed at the Collected department. Its on its way to the destination.';
    } elseif ($logid == 3 ) {
        $msg = 'Hi '.$name.', your consignment [is out for deliver and will be delivered soon today. Its on its way to the destination.';
    } elseif ($logid == 4 ) {
        $msg = 'Hi '.$name.', your consignment is being processed at the customs department. Its on its way to the destination.';
    } elseif ($logid == 5 ) {
        $msg = 'Hi '.$name.', your consignment is being processed at the Delivering department. Its on its way to the destination.';
    } elseif ($logid == 6 ) {
        $msg = 'Hi '.$name.', your consignment is Delivered.';
    }
    try
        {
            //Use the client to do fun stuff like send text messages!
            $client->messages->create(
           // the number you'd like to send the message to
                '+917567434957',
            array(
                 // A Twilio phone number you purchased at twilio.com/console
                 'from' => '+17206198223',
                 // the body of the text message you'd like to send
                 'body' => $msg
            )
         );
        }
        catch (Exception $e)
        {
            echo "Error: " . $e->getMessage();
        }
}
?>
