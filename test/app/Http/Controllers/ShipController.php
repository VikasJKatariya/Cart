<?php

namespace App\Http\Controllers;

use App\Service;
use Illuminate\Http\Request;
use App\Country;
use App\AddressBook;
use Auth;
use PDF;
use Validator;
use App\ZoneCountry;
use App\Weight;
use App\Booking;
use App\BookingAddress;
use App\BookingStatusLog;
use App\BookingDimension;
// use Log;
use Illuminate\Support\Facades\Log;
;

use Illuminate\Support\Facades\Crypt;
use App\Jobs\SendMailChangeStatus;
use App\Jobs\StoreSenderReceiverAddress;

class ShipController extends Controller
{
    /**
     * Call AUth middleware for check login
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {


        $countries = Country::get();
        $receivecountries = Country::whereIn('id', ZoneCountry::pluck('country_id')->toArray())->get();
        $services = Service::where('status', 'active')->get();

        return view('upx.ship.index', compact('countries', 'receivecountries', 'services'));
    }


    public function addressbooksearch(Request $request)
    {
        // echo "<pre>";
        // print_r($request->all());
        // exit();
        try {
            $search = $request->search;
            $name = $request->name;
           
            // $addressbook = AddressBook::where('created_by', Auth::user()->id)->where('name', 'like', '%' . $search . '%')->where('type', 'Sender')->get();
            $addressbook = AddressBook::where('created_by', Auth::user()->id)->where('name', 'like', '%' . $search . '%')->get();
            //  dd($addressbook);
            $skillData = array();
            if (!empty($addressbook)) {
                foreach ($addressbook as $add) {
                    $data['id'] = $add->id;
                    $data['value'] = $add->name . ' ' . $add->city . ' ' . $add->address1;
                    $data['name'] = $add->name;
                    $data['email'] = $add->email;
                    $data['phone_number'] = $add->phone_number;
                    $data['company'] = $add->company;
                    $data['country_id'] = $add->country_id;
                    $data['state'] = $add->state;
                    $data['city'] = $add->city;
                    $data['address1'] = $add->address1;
                    $data['address2'] = $add->address2;
                    $data['address3'] = $add->address3;
                    $data['postalcode'] = $add->postalcode;

                    array_push($skillData, $data);
                }

            }

            return \Response::json($skillData);


        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        }
        return \Response::json($arr);
    }

    public function timeline(Request $request) 
    {        
        $input = $request->all();  
        $timeline_data = '';
        $timeline = ZoneCountry::with('zone_data')->where([['country_id',$request->country_id],['service_id',$request->service_id]])->first();
        if(!empty($timeline)){
            $timeline_data = $timeline->zone_data->transit_time;
        }
        $data = array( 'status' => 200, 'timeline' =>  $timeline_data );
        return $data;
    }
    public function quotationcalculate(Request $request)
    {
        $input = $request->all();
        $time_line = [];
        $timeline = ZoneCountry::with('zone_data')->where([['country_id',$request->country],['service_id',$request->service_id]])->first();

        if(!empty($timeline)){
            $time_line =  $timeline->zone_data->transit_time;
        }
        if ($input['quotation_status'] == 1) {
           
            if ($input['service_id'] == 1 || $input['service_id'] == 2 ) {
                // print_r($input);
                $digits = 4;
                $randomkey =  rand(pow(10, $digits-1), pow(10, $digits)-1);
                $booking_detail = array();
                $i = 1;

                foreach ($input['length'] as $key => $value) {
                    $key_is_insure = $randomkey;
                    if($i == 1) {$keyr = '1';}else { $keyr = $randomkey;}
                    // if(!empty($input['is_insure'][$key] == 0)) {$key_is_insure = '1';}else { $key_is_insure = $randomkey;}
                    if($i == 1) {$key_insureamt = '1';}else { $key_insureamt = $randomkey;}
                   
                    if($i == 1) {
                        $addbutton = '<a class="add_button" title="Add shipment package"><i class="fa fa-plus-circle addiconclass" aria-hidden="true"></i></a> </div></div>';
                     // $addbutton = '<a class="add_button" title="Add shipment package"><i class="fa fa-plus-circle addiconclass" aria-hidden="true"></i></a></div></div>';
                    }else { 
                        $addbutton = '<a class="remove_button" title="Add shipment package"><i class="fa fa-minus-circle removeiconclass" aria-hidden="true"></i></a></div></div>';
                    }
    
                    $booking_detail55 = '<div class="col-md-12 booking_div"><div class="form-group mainbookingdiv"><div class="fieldbooking">
                    <input type="text" class="weightunits error" name="length[]" value="'.$input['length'][$key].'" required> <label class="mainlable">Length</label> <label class="extralable">Cm</label></div>
                    <div class="fieldbooking"> <input type="text" class="weightunits error" value="'.$input['width'][$key].'" name="width[]" required> <label class="mainlable">Width</label><label class="extralable">Cm</label></div>
                    <div class="fieldbooking"><input type="text" class="weightunits error" value="'.$input['height'][$key].'" name="height[]" required><label class="mainlable">Height</label><label class="extralable">Cm</label></div>
                    <div class="fieldbooking"> <input type="text" class="weightunits error" value="'.$input['kg'][$key].'" name="kg[]" required> <label class="mainlable">Weight</label><label class="extralable kilolb">Kg</label></div>
                    <div class="fieldbooking"><input type="text" class="weightunits consignment consignment_'.$keyr.'" value="'.$input['consignment'][$key].'" data-id="'.$keyr.'" name="consignment[]"> <label class="mainlable">Consignment</label><label class="currency_amt">GBP &#163;</label></div>
                    <div class="fieldbooking"> <input type="checkbox" class="is_insure is_insure_'.$key_is_insure.'" name="is_insure[]" data-id="'.$key_is_insure.'" >
                    <input type="text" class="weightunits insureamt insureamt_'.$key_insureamt.'" value="'.$input['insureamt'][$key].'" name="insureamt[]"> <label class="mainlable">Insure AMT</label><label class="currency_amt">GBP &#163;</label> </div>
                    <div class="fieldbooking"><textarea type="text" class="weightunitsdec"name="description[]"> '.$input['description'][$key].'</textarea><label class="mainlable">Description</label></div>'.$addbutton;
                    array_push($booking_detail, $booking_detail55);
                    $i++;
    
                }
                $data = array( 'status' => 200, 'country'=>$input['country'], 'service_id'=> $input['service_id'], 'service_type'=> $input['service_type'], 'package_type'=>$input['package_type'], 'quotation_status' => $input['quotation_status'], 'result' => $booking_detail ,'timeline' =>  $timeline );
                return $data;

            }else {

                $booking_detail = array();
                $i = 1;
                foreach ($input['document_package_type'] as $key => $value) {
                    $selected1 = '';
                    $selected2 = '';
                    if ($input['document_package_type'][$key] == 0.5) {
                        $selected1 = 'selected';
                    }else{
                        $selected2 = 'selected';
                    }
                    if($i == 1) {
                        $addremovebtn = '<a class="add_document cursor-pointer" title="Add shipment package"><i  class="fa fa-plus-circle addiconclass" aria-hidden="true"></i></a></div>';
                    }else { 
                        $addremovebtn = '<a class="remove_document_button" title="Remove shipment package"><i class="fa fa-minus-circle removeiconclass" aria-hidden="true"></i></a></div>';
                    }

                    $maindocumentdiv = ' <div class="row">
                        <div class="col-md-3 form-group">
                            <select name="document_package_type[]" class="form-control">
                                <option value="0.5" '.$selected1.'>0.5Kg</option>
                                <option value="1" '.$selected2.'>1Kg</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <textarea type="text" class="form-control" name="document_description[]"> '.$input['document_description'][$key].' </textarea>
                        </div>'.$addremovebtn;

                    array_push($booking_detail, $maindocumentdiv);
                    $i++;
                }
                $databtn = array( 'status' => 200, 'country'=>$input['country'], 'service_id'=> $input['service_id'], 'package_type'=>$input['package_type'], 'quotation_status' => $input['quotation_status'], 'result' => $booking_detail ,'timeline' =>  $timeline);
                return $databtn;
            }
          
        }else {

            $msg = 'success';
            $actual = array();
            $error = true;
            $valumetric = array();
            $service_id = $input['service_id'];
            $servicetype = isset($input['service_type']) ? $input['service_type'] : null;
    
            /******************* if service is door to door ****************************/
            if ($service_id == 1) { 
                    foreach ($input['length'] as $key => $value) {
    
                        $valumetric[] = ($input['length'][$key] * $input['width'][$key] * $input['height'][$key]) / 6000;
                        $actual[] = $input['kg'][$key];
                        $insureamt[] = $input['insureamt'][$key];
                        $consignment[] = $input['consignment'][$key];
    
                    }
                    $totalquantity = count($input['length']);
    
                    $volumnmetricweight = GetRoundByFraction(round(array_sum($valumetric), 2));
                    $actualweight = GetRoundByFraction(array_sum($actual));
                    $insurefinalamt = array_sum($insureamt);
                    $consignmentfinalamt = array_sum($consignment);
                    $zone = ZoneCountry::where('country_id', $input['country'],['service_id',$service_id])->first();
                    $upxprice = $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;
    
                    
                    if (!empty($zone)) {
                        /*if(isset($input['is_insure'])){
    
                        }*/
                        $zoneid = $zone->zone_id;
                        $maxvalue = max($volumnmetricweight, $actualweight);
                       
                        $systemvalue = 1.00;
                        $selectedweight = Weight::where('weight', '>=', $systemvalue)->orderby('weight', 'asc')->first();
    
                        
                        
                        //$handling_price = handling_price();
                        $error = true;
                        $msg = 'Weight is out of range.';
    
                        if (!empty($selectedweight)) {
                            $weightid = $selectedweight->id;
                            // dd($zoneid);
                            $upxprice = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'price');
                            $handling_price = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'handling');
                            if (Auth::user()->role == 'admin') {
                                $agentprice = $upxprice;
                                $handling_price_agent = $handling_price;
                            }
                            if (Auth::user()->role == 'agent') {
                                $agentprice = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype,  'price');
                                $handling_price_agent = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype, 'handling');
                                
                            }
    
                            
                            /*check the agent price added or not*/
                            if ($agentprice <= 0 && Auth::user()->role == 'agent') {
                                $error = true;
                                $arr = array("status" => 400, "quotation_status" => $input['quotation_status'], "msg" => "Please contact to admin for add the agent price in price slab.");
                                rollback();
                                return \Response::json($arr);
                            }
    
                            
                            $price_per_kg_upx = $upxprice;
                            $price_per_kg_agent = $agentprice;
    
                            if ($maxvalue <= 5) {
                                $upxprice = $upxprice * 5;
                                $agentprice = $agentprice * 5;
                            } else {
                                $upxprice = $upxprice * $maxvalue;
                                $agentprice = $agentprice * $maxvalue;
                            }
    
    
                            $finalprice = $upxprice + $insurefinalamt + $handling_price;
                            $finalagentprice = $agentprice + $insurefinalamt + $handling_price_agent;
                            /*echo '<pre>';
                            print_r('agentprice'.$agentprice);
                            print_r('insurefinalamt'.$insurefinalamt);
                            print_r('handling_price_agent'.$handling_price_agent);
                            exit;*/
                            if (Auth::user()->role == 'admin') {
                                $finalagentprice = 0.00;
                            }
                            $error = false;
                            $msg = 'Success';
    
                        }
    
                    } else {
                        $error = true;
                        $msg = 'Country is not in valid zone.';
                    }
                    $book = 1;
                    if (Auth::user()->role == 'agent') {
                        $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                        $limitamout = auth()->user()->booking_limit;
                        $checkfinalamount = $duaamount + $finalprice;
                        if ($limitamout < $checkfinalamount) {
                            $book = 0;
                        }
                    }
                  
                        // $view = view("upx.ship.loadprice", compact('error', 'msg', 'totalquantity', 'volumnmetricweight', 'actualweight', 'weightid', 'upxprice', 'insurefinalamt', 'consignmentfinalamt', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                        $view = array(
                            'error' => $error,
                            'msg' => $msg,
                            'totalquantity' => $totalquantity,
                            'volumnmetricweight' => $volumnmetricweight,
                            'actualweight' => $actualweight,
                            'weightid' => $weightid,
                            'upxprice' => $upxprice,
                            'insurefinalamt' => $insurefinalamt,
                            'consignmentfinalamt' => $consignmentfinalamt,
                            'finalprice' => $finalprice,
                            'finalagentprice' => $finalagentprice,
                            'handling_price' => $handling_price,
                            'book' => $book,
                             'timeline' =>  $timeline,
                        );
                        $arr = array("status" => 200, "quotation_status" => $input['quotation_status'], "result" => $view);
            } else if($service_id == 2){
    
                foreach ($input['length'] as $key => $value) {
    
                    $valumetric[] = ($input['length'][$key] * $input['width'][$key] * $input['height'][$key]) / 6000;
                    $actual[] = $input['kg'][$key];
                    $insureamt[] = $input['insureamt'][$key];
                    $consignment[] = $input['consignment'][$key];
    
                }
                $totalquantity = count($input['length']);
    
                $volumnmetricweight = GetRoundByFraction(round(array_sum($valumetric), 2));
                $actualweight = GetRoundByFraction(array_sum($actual));
                $insurefinalamt = array_sum($insureamt);
                $consignmentfinalamt = array_sum($consignment);
                $zone = ZoneCountry::where('country_id', $input['country'],['service_id',$service_id])->first();
    
               
                $upxprice = $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;
    
                
                if (!empty($zone)) {
                    /*if(isset($input['is_insure'])){
    
                    }*/
                    $zoneid = $zone->zone_id;
                    $maxvalue = max($volumnmetricweight, $actualweight);
                   
                    $systemvalue = 1.00;
                    $selectedweight = Weight::where('weight', '>=', $systemvalue)->orderby('weight', 'asc')->first();
    
                    
                    
                    //$handling_price = handling_price();
                    $error = true;
                    $msg = 'Weight is out of range.';
    
                    if (!empty($selectedweight)) {
                        $weightid = $selectedweight->id;
                        // dd($zoneid);
                        
                        $upxprice = getmypricedhl($zoneid, 'upx_price', 0, 'price');
                        $handling_price = getmypricedhl($zoneid, 'upx_price', 0,'handling');
                        if (Auth::user()->role == 'admin') {
                            $agentprice = $upxprice;
                            $handling_price_agent = $handling_price;
                        }
                        if (Auth::user()->role == 'agent') {
                            $agentprice = getmypricedhl( $zoneid, 'agent_price', Auth::user()->id, 'price');
                            $handling_price_agent = getmypricedhl( $zoneid, 'agent_price', Auth::user()->id, 'handling');
                            
                        }
    
                        
                        /*check the agent price added or not*/
                        if ($agentprice <= 0 && Auth::user()->role == 'agent') {
                            $error = true;
                            $arr = array("status" => 400, "quotation_status" => $input['quotation_status'], "msg" => "Please contact to admin for add the agent price in price slab.");
                            rollback();
                            return \Response::json($arr);
                        }
    
                        
                        $price_per_kg_upx = $upxprice;
                        $price_per_kg_agent = $agentprice;
    
                        if ($maxvalue <= 5) {
                            $upxprice = $upxprice * 5;
                            $agentprice = $agentprice * 5;
                        } else {
                            $upxprice = $upxprice * $maxvalue;
                            $agentprice = $agentprice * $maxvalue;
                        }
    
    
                        $finalprice = $upxprice + $insurefinalamt + $handling_price;
                        $finalagentprice = $agentprice + $insurefinalamt + $handling_price_agent;
                        /*echo '<pre>';
                        print_r('agentprice'.$agentprice);
                        print_r('insurefinalamt'.$insurefinalamt);
                        print_r('handling_price_agent'.$handling_price_agent);
                        exit;*/
                        if (Auth::user()->role == 'admin') {
                            $finalagentprice = 0.00;
                        }
                        $error = false;
                        $msg = 'Success';
    
                    }
    
                } else {
                    $error = true;
                    $msg = 'Receiver Country is not in valid zone.';
                }
                $book = 1;
                if (Auth::user()->role == 'agent') {
                    $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                    $limitamout = auth()->user()->booking_limit;
                    $checkfinalamount = $duaamount + $finalprice;
                    if ($limitamout < $checkfinalamount) {
                        $book = 0;
                    }
                }
                // $view = view("upx.ship.loadprice", compact('error', 'msg', 'totalquantity', 'volumnmetricweight', 'actualweight', 'weightid', 'upxprice', 'insurefinalamt', 'consignmentfinalamt', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                // $arr = array("status" => 200, "result" => $view);
                $view = array(
                    'error' => $error,
                    'msg' => $msg,
                    'totalquantity' => $totalquantity,
                    'volumnmetricweight' => $volumnmetricweight,
                    'actualweight' => $actualweight,
                    'weightid' => $weightid,
                    'upxprice' => $upxprice,
                    'insurefinalamt' => $insurefinalamt,
                    'consignmentfinalamt' => $consignmentfinalamt,
                    'finalprice' => $finalprice,
                    'finalagentprice' => $finalagentprice,
                    'handling_price' => $handling_price,
                    'book' => $book,
                     'timeline' =>  $timeline,
                );
                $arr = array("status" => 200, "quotation_status" => $input['quotation_status'], "result" => $view);
    
            } else {
    
                /******************* if service is document at booking time ****************************/
    
                $zone = ZoneCountry::where('country_id', $input['country'])->where('service_id', $service_id)->first();
                $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;
    
                if (!empty($zone)) {
                    $zoneid = $zone->zone_id;
                
                    foreach ($input['document_package_type'] as $key => $value) {
                        $actual[] = $input['document_package_type'][$key];
                        $getupxprice = getmydocumentpricedocument($zoneid, $value,'upx_price', 0,'price');
                        $upxprice[] = $getupxprice;
                        
                        $gethandling_price = getmydocumentpricedocument($zoneid, $value,'upx_price', 0,'handling');
                        $handling_price[] = $gethandling_price;
                        if (Auth::user()->role == 'admin') {
                            $agentprice = $upxprice;
                            $handling_price_agent[] = $gethandling_price;
                        }
                        if (Auth::user()->role == 'agent') {
    
                            $agent_price = getmydocumentpricedocument($zoneid, $value,'agent_price', Auth::user()->id,'price');
                            $agentprice[] = $agent_price;
                            $handling_price_agent[] = getmydocumentpricedocument( $zoneid, $value, 'agent_price', Auth::user()->id, 'handling');
    
                            /*check the agent price added or not*/
                            if ($agent_price <= 0 && Auth::user()->role == 'agent') {
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Please contact to admin for add the agent price in price slab.");
                                rollback();
                                return \Response::json($arr);
                            }
                        
                        }
                        
    
                    }
                     
                    $totalquantity = count($input['document_package_type']);
                    $actualweight = array_sum($actual);
                    $maxvalue = $actualweight;
                    //$handling_price = handling_price();
                    $upxprice = array_sum($upxprice);
                    $agentprice = array_sum($agentprice);
                    $handling_price_agent = array_sum($handling_price_agent);
    
                    $handling_price = array_sum($handling_price);
                    $finalprice = $upxprice + $handling_price;
                    $finalagentprice = $agentprice + $handling_price_agent;
                    if (Auth::user()->role == 'admin') {
                        $finalagentprice = 0.00;
                    }
                    //dd($agentprice);
    
                    $price_per_kg_upx = $upxprice;
                    $price_per_kg_agent = $agentprice;
                    $error = false;
                    $msg = 'Success';
    
                } else {
                    $error = true;
                    $msg = 'Receiver Country is not in valid zone.';
                    $arr = array("status" => 400, "msg" => $msg);
                    return \Response::json($arr);
                }
                $book = 1;
                if (Auth::user()->role == 'agent') {
                    $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                    $limitamout = auth()->user()->booking_limit;
                    $checkfinalamount = $duaamount + $finalprice;
                    // dd($checkfinalamount);
                    if ($limitamout < $checkfinalamount) {
                        $book = 0;
                    }
                }
                // $view = view("upx.ship.documentprice", compact('error', 'msg', 'totalquantity', 'actualweight', 'upxprice', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                $view = array(
                    'volumnmetricweight' => '',
                    'insurefinalamt' =>  '',
                    'consignmentfinalamt' =>  '',
                    'error' => $error,
                    'msg' => $msg,
                    'totalquantity' => $totalquantity,
                    'actualweight' => $actualweight,
                    'weightid' => $weightid,
                    'upxprice' => $upxprice,
                    'finalprice' => $finalprice,
                    'finalagentprice' => $finalagentprice,
                    'handling_price' => $handling_price,
                    'book' => $book,
                     'timeline' =>  $timeline,
                );
                $arr = array("status" => 200, "quotation_status" => $input['quotation_status'], "result" => $view);
                
            }
            return $arr;
        }
    }

    public function bookingprostore(Request $request)
    {
       
        $input = $request->all();
        // echo "<pre>";
        // print_r($input);
        // exit();
        $rules = array(
            'coutry_s' => "required|exists:countries,id",
            'first_name_s' => 'required',
            'last_name_s' => 'required',
            'email_s' => 'required|email',
            //   'company_s' => 'required',
            'phone_s' => 'required',
            'address1_s' => 'required',
            'postal_code_s' => 'required',
            'city_s' => 'required',
            'country_r' => "required|exists:countries,id",
            'full_name_r' => 'required',
            'last_name_s' => 'required',
            'address1_r' => 'required',
            'postal_code_r' => 'required',
            'city_r' => 'required',
            'length' => 'array',
            'length.*' => 'required_if:service_id,in:1,2', // if service is door to door
            'width' => 'array',
            'width.*' => 'required_if:service_id,in:1,2',
            'height' => 'array',
            'height.*' => 'required_if:service_id,in:1,2',
            'kg' => 'array',
            'kg.*' => 'required_if:service_id,in:1,2',

        );

        if (isset($input['return_address']) && $input['return_address'] == 1) {
            $rules['country_d'] = 'required|exists:countries,id';
            $rules['first_name_d'] = 'required';
            $rules['last_name_d'] = 'required';
            $rules['email_d'] = 'required|email';
            $rules['company_d'] = 'required';
            $rules['phone_d'] = 'required';
            $rules['address1_d'] = 'required';
            $rules['postal_code_d'] = 'required|numeric';
            $rules['city_d'] = 'required';


        }
        $message = [
            'coutry_s.required' => 'The Sender Country field is required.',
            'first_name_s.required' => 'The Sender First Name field is required.',
            'last_name_s.required' => 'The Sender Last Name field is required.',
            'email_s.required' => 'The Sender Email field is required.',
            'email_s.email' => 'The Sender Email must be a valid email address.',
            'phone_s.required' => 'The Sender Phone field is required.',
            'address1_s.required' => 'The Sender Address 1 field is required.',
            'postal_code_s.required' => 'The Sender Postal code field is required.',
            'city_s.required' => 'The Sender City field is required.',
            'country_r.required' => 'The Receiver Country field is required.',
            'full_name_r.required' => 'The Receiver Full Name field is required.',
            'email_r.required' => 'The Receiver Email field is required.',
            'email_r.email' => 'The Receiver Email must be a valid email address.',
            'address1_r.required' => 'The Receiver Address 1 field is required.',
            'postal_code_r.required' => 'The Receiver Postal code field is required.',
            'city_r.required' => 'The Receiver City field is required.',
            'country_d.required' => 'The Return Country field is required.',
            'first_name_d.required' => 'The Return First Name field is required.',
            'last_name_d.required' => 'The Return Last Name field is required.',
            'email_d.required' => 'The Return Email field is required.',
            'email_d.email' => 'The Return Email must be a valid email address.',
            'company_d.required' => 'The Return Company field is required.',
            'phone_d.required' => 'The Return Phone field is required.',
            'address1_d.required' => 'The Return Address 1 field is required.',
            'postal_code_d.required' => 'The Return Postal code field is required.',
            'postal_code_d.numeric' => 'The Return Postal code must be valid.',
            'city_d.required' => 'The Return City field is required.',
            "length.*.required" => "All Length fields are required.",
            "width.*.required" => "All Width fields are required.",
            "height.*.required" => "All Height fields are required.",
            "kg.*.required" => "All Weight fields are required.",

        ];
        $validator = Validator::make($input, $rules, $message);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {
            Log::info('Validator check done step 1!');
            begin();
            try {
                $msg = 'success';
                $actual = array();
                $error = true;
                $valumetric = array();
                $service_id = $input['service_id'];
                $servicetype = isset($input['service_type']) ? $input['service_type'] : null;
                
                /******************* if service is door to door ****************************/

                if ($service_id == 1) {
                    foreach ($input['length'] as $key => $value) {

                        $valumetric[] = ($input['length'][$key] * $input['width'][$key] * $input['height'][$key]) / 6000;
                        $actual[] = $input['kg'][$key];
                        $insureamt[] = $input['insureamt'][$key];
                        $consignment[] = $input['consignment'][$key];

                    }
                    $totalquantity = count($input['length']);

                    
                    $volumnmetricweight = GetRoundByFraction(round(array_sum($valumetric), 2));
                    $actualweight = GetRoundByFraction(array_sum($actual));
                    $insurefinalamt = array_sum($insureamt);
                    $consignmentfinalamt = array_sum($consignment);
                    $zone = ZoneCountry::where('country_id', $input['country_r'],['service_id',$service_id])->first();
                    $upxprice = $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;

                    

                    if (!empty($zone)) {
                        /*if(isset($input['is_insure'])){

                        }*/
                        $zoneid = $zone->zone_id;
                        $maxvalue = max($volumnmetricweight, $actualweight);
                       
                        $systemvalue = 1.00;
                        $selectedweight = Weight::where('weight', '>=', $systemvalue)->orderby('weight', 'asc')->first();

                        
                        
                        //$handling_price = handling_price();
                        $error = true;
                        $msg = 'Weight is out of range.';

                        if (!empty($selectedweight)) {
                            $weightid = $selectedweight->id;
                            // dd($zoneid);
                            $upxprice = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'price');
                            $handling_price = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'handling');
                            if (Auth::user()->role == 'admin') {
                                $agentprice = $upxprice;
                                $handling_price_agent = $handling_price;
                            }
                            if (Auth::user()->role == 'agent') {
                                $agentprice = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype,  'price');
                                $handling_price_agent = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype, 'handling');
                                
                            }

                            
                            /*check the agent price added or not*/
                            if ($agentprice <= 0 && Auth::user()->role == 'agent') {
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Please contact to admin for add the agent price in price slab.");
                                rollback();
                                return \Response::json($arr);
                            }

                            
                            $price_per_kg_upx = $upxprice;
                            $price_per_kg_agent = $agentprice;

                            if ($maxvalue <= 5) {
                                $upxprice = $upxprice * 5;
                                $agentprice = $agentprice * 5;
                            } else {
                                $upxprice = $upxprice * $maxvalue;
                                $agentprice = $agentprice * $maxvalue;
                            }


                            $finalprice = $upxprice + $insurefinalamt + $handling_price;
                            $finalagentprice = $agentprice + $insurefinalamt + $handling_price_agent;
                            /*echo '<pre>';
                            print_r('agentprice'.$agentprice);
                            print_r('insurefinalamt'.$insurefinalamt);
                            print_r('handling_price_agent'.$handling_price_agent);
                            exit;*/
                            if (Auth::user()->role == 'admin') {
                                $finalagentprice = 0.00;
                            }
                            $error = false;
                            $msg = 'Success';

                        }

                    } else {
                        $error = true;
                        $msg = 'Receiver Country is not in valid zone.';
                    }
                    $book = 1;
                    if (Auth::user()->role == 'agent') {
                        $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                        $limitamout = auth()->user()->booking_limit;
                        $checkfinalamount = $duaamount + $finalprice;
                        if ($limitamout < $checkfinalamount) {
                            $book = 0;
                        }
                    }

                    if ($input['booking_status'] == 0) {
                        Log::info('Calculate price step 2!');
                        $view = view("upx.ship.loadprice", compact('error', 'msg', 'totalquantity', 'volumnmetricweight', 'actualweight', 'weightid', 'upxprice', 'insurefinalamt', 'consignmentfinalamt', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                        $arr = array("status" => 200, "result" => $view);
                    } else {

                        if ($book == 0) {
                            $error = true;
                            $msg = 'You have exceeded the booking limit  &#163;' . auth()->user()->booking_limit;
                        }
                        if ($error) {
                            $arr = array("status" => 400, "msg" => $msg);
                        } else {

                            $bookpaymentstatus = 'paid';
                            if (Auth::user()->role == 'agent') {
                                $bookpaymentstatus = 'unpaid';
                            }

                            $mail_notify = '0';
                            if (isset($input['mail_notify'])) {
                                $mail_notify = '1';
                            }
                            Log::info('check mail 0 or 1 ='.$mail_notify.' 1 = sendmail  step 3!');


                            /***************************************** Booking store  ****************************************/
                            $final_total_upx = $finalprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            $final_total_agent = $finalagentprice;

                            if ($finalagentprice >= $input['discount_amt']) {
                                $final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            } else {
                                //$final_total_agent = $finalagentprice + $input['packing_charge_amt'] + $input['tax_amt'];
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Discount amount must be less than or equal to &#163;" . $finalagentprice);
                                rollback();
                                return \Response::json($arr);
                            }

                            $tracking_number = generateTrackNumber();
                            $bookingid = Booking::create([
                                'booked_by' => Auth::id(),
                                'service_id' => $service_id,
                                'service_type' => $servicetype,
                                'current_status' => 1,
                                'payment_status' => $bookpaymentstatus,
                                'handling_price' => $handling_price,
                                'handling_price_agent' => $handling_price_agent,
                                'package_type' => $input['package_type'],
                                //'tracking_number' => getTrackNumber(),
                                'tracking_number' => $tracking_number,
                                'booking_instruction' => $input['booking_instruction'],
                                'actual_weight' => $actualweight,
                                'volumetric_weight' => $volumnmetricweight,
                                'upx_price' => $upxprice,
                                'agent_price' => $agentprice,
                                'final_insure_amt' => $insurefinalamt,
                                'final_consignment_amt' => $consignmentfinalamt,
                                'mail_notify' => $mail_notify,
                                'final_upx_price' => $finalprice,
                                'final_agent_price' => $finalagentprice,
                                'price_per_kg_upx' => $price_per_kg_upx,
                                'price_per_kg_agent' => $price_per_kg_agent,
                                'discount_amt' => $input['discount_amt'],
                                'packing_charge_amt' => $input['packing_charge_amt'],
                                'tax_amt' => $input['tax_amt'],
                                'final_total_upx' => $final_total_upx,
                                'final_total_agent' => $final_total_agent,
                            ])->id;
                            Log::info('bookingid bookling data store  step 4!');

                            /***************************************** Booking store  ****************************************/

                            $inputdata = $request->except('image_s');
                            dispatch(new StoreSenderReceiverAddress(Auth::id(), $inputdata));
                            /***************************************** Booking sender address  ****************************************/
                            $id_doc_image = '';
                            if ($request->hasFile('image_s')) {
                                $destinationPath = public_path() . '/images/id_document/';
                                $file = $request->image_s;
                                $id_doc_image = time() . '.' . $file->clientExtension();
                                $file->move($destinationPath, $id_doc_image);
                            }

                            /***************************************** Booking sender address  ****************************************/
                            $identity_card = $input['id_type_s'];
                            if ($identity_card == 'Other') {
                                $id_type =  $input['id_type_other_s'];
                            }else {
                                $id_type =  $input['id_type_s'];
                            }
                            BookingAddress::create([
                                'booking_id' => $bookingid,
                                'type' => 'sender',
                                'name' => $input['first_name_s'],
                                'lastname' => $input['last_name_s'],
                                'email' => $input['email_s'],
                                'address1' => $input['address1_s'],
                                'address2' => $input['address2_s'],
                                'address3' => $input['address3_s'],
                                'country_id' => $input['coutry_s'],
                                'state' => $input['state_s'],
                                'city' => $input['city_s'],
                                'postalcode' => $input['postal_code_s'],
                                'phonenumber' => $input['phone_s'],
                                'company' => $input['company_s'],
                                'id_type' => $id_type,
                                'id_number' => $input['id_number_s'],
                                'id_doc_image' => $id_doc_image,
                            ]);
                            Log::info('BookingAddress sender data store  step 5!');
                            /***************************************** Booking sender address  ****************************************/

                            /***************************************** Booking receiver address  ****************************************/
                            BookingAddress::create([
                                'booking_id' => $bookingid,
                                'type' => 'receiver',
                                'name' => $input['full_name_r'],
                                'email' => $input['email_r'],
                                'address1' => $input['address1_r'],
                                'address2' => $input['address2_r'],
                                'address3' => $input['address3_r'],
                                'country_id' => $input['country_r'],
                                'city' => $input['city_r'],
                                'state' => $input['state_r'],
                                'postalcode' => $input['postal_code_r'],
                                'phonenumber' => $input['phone_r'],
                                'company' => $input['company_r'],
                            ]);
                            Log::info('BookingAddress receiver data store  step 6!');
                            /***************************************** Booking receiver address  ****************************************/


                            /***************************************** Booking return address  ****************************************/
                            if (isset($input['return_address']) && $input['return_address'] == 1) {
                                BookingAddress::create([
                                    'booking_id' => $bookingid,
                                    'type' => 'return',
                                    'name' => $input['first_name_d'],
                                    'email' => $input['email_d'],
                                    'address1' => $input['address1_d'],
                                    'address2' => $input['address2_d'],
                                    'address3' => $input['address3_d'],
                                    'country_id' => $input['country_d'],
                                    'city' => $input['city_d'],
                                    'postalcode' => $input['postal_code_d'],
                                    'phonenumber' => $input['phone_d'],
                                    'company' => $input['company_d'],
                                ]);
                                Log::info('BookingAddress return data store  step 6!');
                            }
                            /***************************************** Booking return address  ****************************************/

                            /***************************************** Booking Dimension  ****************************************/
                            $box_count = count($input['length']);
                            foreach ($input['length'] as $key => $value) {
                                BookingDimension::create([
                                    'booking_id' => $bookingid,
                                    'lenth' => $input['length'][$key],
                                    'width' => $input['width'][$key],
                                    'height' => $input['height'][$key],
                                    'weight' => $input['kg'][$key],
                                    'insure_amt' => $input['insureamt'][$key],
                                    'consignment_amt' => $input['consignment'][$key],
                                    'description' => $input['description'][$key],
                                    'box_number' => getBoxNumber(),
                                    'box_page' => ($key + 1) . '/' . $box_count,
                                    'total_on_dimension' => 0.00,
                                    'total_on_weight' => 0.00
                                ]);
                            }
                            Log::info('BookingDimension booking detail ex:lenth,width,height,weight data store  step 7!');
                            /***************************************** Booking Dimension  ****************************************/

                            /***************************************** Booking StatusLog  ****************************************/
                            BookingStatusLog::create([
                                'booking_id' => $bookingid,
                                'status' => 'shipped',
                            ]);
                            /***************************************** Booking StatusLog  ****************************************/

                            if ($mail_notify == 1) {
                            Log::info('mail mail_notify = true (1)  step 8!');

                                dispatch(new SendMailChangeStatus(array($bookingid)));

                            Log::info('mail SendMailChangeStatus function return  step 9!');

                            }

                            /*************  save admin to agent invoice pdf at booking time (door to door service)  *******************/
                            $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($bookingid)->first();
                            $logoimage = 'logo.png';
                            if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
                                //  $booking->createdby->logo_image = 'defaultinvoice.png';
                            } else {
                                $booking->createdby->logo_image = $booking->createdby->logo_image;
                            }
                            if ($booking->createdby->role == 'admin') {
                                $booking->createdby->logo_image = 'logo.png';
                            }
                            $data = ['booking' => $booking, 'logoimage' => $logoimage];
                            $pdf = PDF::loadView('upx.bookinghistory.agentinvoice', $data);
                            $filename = $booking->tracking_number . '_invoice_' . $booking->id . '.pdf';
                            $pdfpath = public_path() . '/agentinvoice/' . $filename;
                            $pdf->save($pdfpath);

                            //update agent invoice column at booking time
                            Booking::where('id', $bookingid)->update(['agent_invoice' => $filename]);

                            // $booking = Booking::withCount('dimentions')->whereId($bookingid)->first();
                            $boxids = implode("|", $booking->dimentions->pluck('box_number')->toArray());
                            send_sms($booking->tracking_number,$input['first_name_s'], $final_total_upx,$input['phone_r']);
                            commit();
                            $arr = array("status" => 200, "msg" => $msg, 'bookingid' => $bookingid, 'boxids' => $boxids);
                        }
                    }
                } else if($service_id == 2){ 
                    foreach ($input['length'] as $key => $value) {

                        $valumetric[] = ($input['length'][$key] * $input['width'][$key] * $input['height'][$key]) / 6000;
                        $actual[] = $input['kg'][$key];
                        $insureamt[] = $input['insureamt'][$key];
                        $consignment[] = $input['consignment'][$key];

                    }
                    $totalquantity = count($input['length']);

                    
                    $volumnmetricweight = GetRoundByFraction(round(array_sum($valumetric), 2));
                    $actualweight = GetRoundByFraction(array_sum($actual));
                    $insurefinalamt = array_sum($insureamt);
                    $consignmentfinalamt = array_sum($consignment);
                    $zone = ZoneCountry::where('country_id', $input['country_r'],['service_id',$service_id])->first();

                   
                    $upxprice = $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;

                    

                    if (!empty($zone)) {
                        /*if(isset($input['is_insure'])){

                        }*/
                        $zoneid = $zone->zone_id;
                        $maxvalue = max($volumnmetricweight, $actualweight);
                       
                        $systemvalue = 1.00;
                        $selectedweight = Weight::where('weight', '>=', $systemvalue)->orderby('weight', 'asc')->first();

                        
                        
                        //$handling_price = handling_price();
                        $error = true;
                        $msg = 'Weight is out of range.';

                        if (!empty($selectedweight)) {
                            $weightid = $selectedweight->id;
                            // dd($zoneid);
                            
                            $upxprice = getmypricedhl($zoneid, 'upx_price', 0, 'price');
                            $handling_price = getmypricedhl($zoneid, 'upx_price', 0,'handling');
                            if (Auth::user()->role == 'admin') {
                                $agentprice = $upxprice;
                                $handling_price_agent = $handling_price;
                            }
                            if (Auth::user()->role == 'agent') {
                                $agentprice = getmypricedhl( $zoneid, 'agent_price', Auth::user()->id, 'price');
                                $handling_price_agent = getmypricedhl( $zoneid, 'agent_price', Auth::user()->id, 'handling');
                                
                            }

                            
                            /*check the agent price added or not*/
                            if ($agentprice <= 0 && Auth::user()->role == 'agent') {
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Please contact to admin for add the agent price in price slab.");
                                rollback();
                                return \Response::json($arr);
                            }

                            
                            $price_per_kg_upx = $upxprice;
                            $price_per_kg_agent = $agentprice;

                            if ($maxvalue <= 5) {
                                $upxprice = $upxprice * 5;
                                $agentprice = $agentprice * 5;
                            } else {
                                $upxprice = $upxprice * $maxvalue;
                                $agentprice = $agentprice * $maxvalue;
                            }


                            $finalprice = $upxprice + $insurefinalamt + $handling_price;
                            $finalagentprice = $agentprice + $insurefinalamt + $handling_price_agent;
                            /*echo '<pre>';
                            print_r('agentprice'.$agentprice);
                            print_r('insurefinalamt'.$insurefinalamt);
                            print_r('handling_price_agent'.$handling_price_agent);
                            exit;*/
                            if (Auth::user()->role == 'admin') {
                                $finalagentprice = 0.00;
                            }
                            $error = false;
                            $msg = 'Success';

                        }

                    } else {
                        $error = true;
                        $msg = 'Receiver Country is not in valid zone.';
                    }
                    $book = 1;
                    if (Auth::user()->role == 'agent') {
                        $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                        $limitamout = auth()->user()->booking_limit;
                        $checkfinalamount = $duaamount + $finalprice;
                        if ($limitamout < $checkfinalamount) {
                            $book = 0;
                        }
                    }

                    if ($input['booking_status'] == 0) {
                        $view = view("upx.ship.loadprice", compact('error', 'msg', 'totalquantity', 'volumnmetricweight', 'actualweight', 'weightid', 'upxprice', 'insurefinalamt', 'consignmentfinalamt', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                        $arr = array("status" => 200, "result" => $view);
                    } else {

                        if ($book == 0) {
                            $error = true;
                            $msg = 'You have exceeded the booking limit  &#163;' . auth()->user()->booking_limit;
                        }
                        if ($error) {
                            $arr = array("status" => 400, "msg" => $msg);
                        } else {

                            $bookpaymentstatus = 'paid';
                            if (Auth::user()->role == 'agent') {
                                $bookpaymentstatus = 'unpaid';
                            }

                            $mail_notify = '0';
                            if (isset($input['mail_notify'])) {
                                $mail_notify = '1';
                            }


                            /***************************************** Booking store  ****************************************/
                            $final_total_upx = $finalprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            $final_total_agent = $finalagentprice;

                            if ($finalagentprice >= $input['discount_amt']) {
                                $final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            } else {
                                //$final_total_agent = $finalagentprice + $input['packing_charge_amt'] + $input['tax_amt'];
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Discount amount must be less than or equal to &#163;" . $finalagentprice);
                                rollback();
                                return \Response::json($arr);
                            }

                            $tracking_number = generateTrackNumber();
                            $bookingid = Booking::create([
                                'booked_by' => Auth::id(),
                                'service_id' => $service_id,
                                'service_type' => $servicetype,
                                'current_status' => 1,
                                'payment_status' => $bookpaymentstatus,
                                'handling_price' => $handling_price,
                                'handling_price_agent' => $handling_price_agent,
                                'package_type' => $input['package_type'],
                                //'tracking_number' => getTrackNumber(),
                                'tracking_number' => $tracking_number,
                                'booking_instruction' => $input['booking_instruction'],
                                'actual_weight' => $actualweight,
                                'volumetric_weight' => $volumnmetricweight,
                                'upx_price' => $upxprice,
                                'agent_price' => $agentprice,
                                'final_insure_amt' => $insurefinalamt,
                                'final_consignment_amt' => $consignmentfinalamt,
                                'mail_notify' => $mail_notify,
                                'final_upx_price' => $finalprice,
                                'final_agent_price' => $finalagentprice,
                                'price_per_kg_upx' => $price_per_kg_upx,
                                'price_per_kg_agent' => $price_per_kg_agent,
                                'discount_amt' => $input['discount_amt'],
                                'packing_charge_amt' => $input['packing_charge_amt'],
                                'tax_amt' => $input['tax_amt'],
                                'final_total_upx' => $final_total_upx,
                                'final_total_agent' => $final_total_agent,
                            ])->id;

                            /***************************************** Booking store  ****************************************/

                            $inputdata = $request->except('image_s');
                            dispatch(new StoreSenderReceiverAddress(Auth::id(), $inputdata));
                            /***************************************** Booking sender address  ****************************************/
                            $id_doc_image = '';
                            if ($request->hasFile('image_s')) {
                                $destinationPath = public_path() . '/images/id_document/';
                                $file = $request->image_s;
                                $id_doc_image = time() . '.' . $file->clientExtension();
                                $file->move($destinationPath, $id_doc_image);
                            }

                            /***************************************** Booking sender address  ****************************************/
                            $identity_card = $input['id_type_s'];
                            if ($identity_card == 'Other') {
                                $id_type =  $input['id_type_other_s'];
                            }else {
                                $id_type =  $input['id_type_s'];
                            }
                            BookingAddress::create([
                                'booking_id' => $bookingid,
                                'type' => 'sender',
                                'name' => $input['first_name_s'],
                                'lastname' => $input['last_name_s'],
                                'email' => $input['email_s'],
                                'address1' => $input['address1_s'],
                                'address2' => $input['address2_s'],
                                'address3' => $input['address3_s'],
                                'country_id' => $input['coutry_s'],
                                'state' => $input['state_s'],
                                'city' => $input['city_s'],
                                'postalcode' => $input['postal_code_s'],
                                'phonenumber' => $input['phone_s'],
                                'company' => $input['company_s'],
                                'id_type' => $id_type,
                                'id_number' => $input['id_number_s'],
                                'id_doc_image' => $id_doc_image,
                            ]);
                            /***************************************** Booking sender address  ****************************************/

                            /***************************************** Booking receiver address  ****************************************/
                            BookingAddress::create([
                                'booking_id' => $bookingid,
                                'type' => 'receiver',
                                'name' => $input['full_name_r'],
                                'email' => $input['email_r'],
                                'address1' => $input['address1_r'],
                                'address2' => $input['address2_r'],
                                'address3' => $input['address3_r'],
                                'country_id' => $input['country_r'],
                                'city' => $input['city_r'],
                                'state' => $input['state_r'],
                                'postalcode' => $input['postal_code_r'],
                                'phonenumber' => $input['phone_r'],
                                'company' => $input['company_r'],
                            ]);
                            /***************************************** Booking receiver address  ****************************************/


                            /***************************************** Booking return address  ****************************************/
                            if (isset($input['return_address']) && $input['return_address'] == 1) {
                                BookingAddress::create([
                                    'booking_id' => $bookingid,
                                    'type' => 'return',
                                    'name' => $input['first_name_d'],
                                    'email' => $input['email_d'],
                                    'address1' => $input['address1_d'],
                                    'address2' => $input['address2_d'],
                                    'address3' => $input['address3_d'],
                                    'country_id' => $input['country_d'],
                                    'city' => $input['city_d'],
                                    'postalcode' => $input['postal_code_d'],
                                    'phonenumber' => $input['phone_d'],
                                    'company' => $input['company_d'],
                                ]);
                            }
                            /***************************************** Booking return address  ****************************************/

                            /***************************************** Booking Dimension  ****************************************/
                            $box_count = count($input['length']);
                            foreach ($input['length'] as $key => $value) {
                                BookingDimension::create([
                                    'booking_id' => $bookingid,
                                    'lenth' => $input['length'][$key],
                                    'width' => $input['width'][$key],
                                    'height' => $input['height'][$key],
                                    'weight' => $input['kg'][$key],
                                    'insure_amt' => $input['insureamt'][$key],
                                    'consignment_amt' => $input['consignment'][$key],
                                    'description' => $input['description'][$key],
                                    'box_number' => getBoxNumber(),
                                    'box_page' => ($key + 1) . '/' . $box_count,
                                    'total_on_dimension' => 0.00,
                                    'total_on_weight' => 0.00
                                ]);
                            }
                            /***************************************** Booking Dimension  ****************************************/

                            /***************************************** Booking StatusLog  ****************************************/
                            BookingStatusLog::create([
                                'booking_id' => $bookingid,
                                'status' => 'shipped',
                            ]);
                            /***************************************** Booking StatusLog  ****************************************/

                            if ($mail_notify == 1) {

                                dispatch(new SendMailChangeStatus(array($bookingid)));

                            }

                            /*************  save admin to agent invoice pdf at booking time (door to door service)  *******************/
                            $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($bookingid)->first();
                            $logoimage = 'logo.png';
                            if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
                                //  $booking->createdby->logo_image = 'defaultinvoice.png';
                            } else {
                                $booking->createdby->logo_image = $booking->createdby->logo_image;
                            }
                            if ($booking->createdby->role == 'admin') {
                                $booking->createdby->logo_image = 'logo.png';
                            }
                            $data = ['booking' => $booking, 'logoimage' => $logoimage];
                            $pdf = PDF::loadView('upx.bookinghistory.agentinvoice', $data);
                            $filename = $booking->tracking_number . '_invoice_' . $booking->id . '.pdf';
                            $pdfpath = public_path() . '/agentinvoice/' . $filename;
                            $pdf->save($pdfpath);

                            //update agent invoice column at booking time
                            Booking::where('id', $bookingid)->update(['agent_invoice' => $filename]);

                            // $booking = Booking::withCount('dimentions')->whereId($bookingid)->first();
                            $boxids = implode("|", $booking->dimentions->pluck('box_number')->toArray());
                            send_sms($booking->tracking_number,$input['first_name_s'], $final_total_upx,$input['phone_r']);
                            commit();
                            $arr = array("status" => 200, "msg" => $msg, 'bookingid' => $bookingid, 'boxids' => $boxids);
                        }
                    }
                } else {

                    /******************* if service is document at booking time ****************************/

                    $zone = ZoneCountry::where('country_id', $input['country_r'])->where('service_id', $service_id)->first();
                    $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;

                    if (!empty($zone)) {
                        $zoneid = $zone->zone_id;
                       
                        foreach ($input['document_package_type'] as $key => $value) {
                            $actual[] = $input['document_package_type'][$key];
                            $getupxprice = getmydocumentpricedocument($zoneid, $value,'upx_price', 0,'price');
                            $upxprice[] = $getupxprice;
                            
                            $gethandling_price = getmydocumentpricedocument($zoneid, $value,'upx_price', 0,'handling');
                            $handling_price[] = $gethandling_price;
                            if (Auth::user()->role == 'admin') {
                                $agentprice = $upxprice;
                                $handling_price_agent[] = $gethandling_price;
                            }
                            if (Auth::user()->role == 'agent') {

                                $agent_price = getmydocumentpricedocument($zoneid, $value,'agent_price', Auth::user()->id,'price');
                                $agentprice[] = $agent_price;
                                $handling_price_agent[] = getmydocumentpricedocument( $zoneid, $value, 'agent_price', Auth::user()->id, 'handling');

                                /*check the agent price added or not*/
                                if ($agent_price <= 0 && Auth::user()->role == 'agent') {
                                    $error = true;
                                    $arr = array("status" => 400, "msg" => "Please contact to admin for add the agent price in price slab.");
                                    rollback();
                                    return \Response::json($arr);
                                }
                            
                            }
                            

                        }
                        
                        $totalquantity = count($input['document_package_type']);
                        $actualweight = array_sum($actual);
                        $maxvalue = $actualweight;
                        //$handling_price = handling_price();
                        $upxprice = array_sum($upxprice);
                        $agentprice = array_sum($agentprice);
                        $handling_price_agent = array_sum($handling_price_agent);

                        $handling_price = array_sum($handling_price);
                        $finalprice = $upxprice + $handling_price;
                        $finalagentprice = $agentprice + $handling_price_agent;
                        if (Auth::user()->role == 'admin') {
                            $finalagentprice = 0.00;
                        }
                        //dd($agentprice);

                        $price_per_kg_upx = $upxprice;
                        $price_per_kg_agent = $agentprice;
                        $error = false;
                        $msg = 'Success';

                    } else {
                        $error = true;
                        $msg = 'Receiver Country is not in valid zone.';
                        $arr = array("status" => 400, "msg" => $msg);
                        return \Response::json($arr);
                    }
                    $book = 1;
                    if (Auth::user()->role == 'agent') {
                        $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                        $limitamout = auth()->user()->booking_limit;
                        $checkfinalamount = $duaamount + $finalprice;
                        // dd($checkfinalamount);
                        if ($limitamout < $checkfinalamount) {
                            $book = 0;
                        }
                    }
                    if ($input['booking_status'] == 0) {
                        $view = view("upx.ship.documentprice", compact('error', 'msg', 'totalquantity', 'actualweight', 'upxprice', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                        $arr = array("status" => 200, "result" => $view);
                    } else {

                        if ($book == 0) {
                            $error = true;
                            $msg = 'You have exceeded the booking limit  &#163;' . auth()->user()->booking_limit;
                        }
                        if ($error) {
                            $arr = array("status" => 400, "msg" => $msg);
                        } else {

                            $bookpaymentstatus = 'paid';
                            if (Auth::user()->role == 'agent') {
                                $bookpaymentstatus = 'unpaid';
                            }

                            $mail_notify = '0';
                            if (isset($input['mail_notify'])) {
                                $mail_notify = '1';
                            }


                            /***************************************** Booking store  ****************************************/
                            $final_total_upx = $finalprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            $final_total_agent = $finalagentprice;

                            if ($finalagentprice > $input['discount_amt']) {
                                $final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            } else {
                                $final_total_agent = $finalagentprice + $input['packing_charge_amt'] + $input['tax_amt'];
                            }
                            //dd($final_total_agent);
                            $tracking_number = generateTrackNumber();
                            $bookingid = Booking::create([
                                'booked_by' => Auth::id(),
                                'service_id' => $service_id,
                                'service_type' => $servicetype,
                                'current_status' => 1,
                                'payment_status' => $bookpaymentstatus,
                                'handling_price' => $handling_price,
                                'handling_price_agent'=> $handling_price_agent,
                                'package_type' => $input['package_type'],
                                //'tracking_number' => getTrackNumber(),
                                'tracking_number' => $tracking_number,
                                'booking_instruction' => $input['booking_instruction'],
                                'actual_weight' => $actualweight,
                                //  'volumetric_weight' => $volumnmetricweight,
                                'upx_price' => $upxprice,
                                'agent_price' => $agentprice,
                                // 'final_insure_amt' => $insurefinalamt,
                                // 'final_consignment_amt' => $consignmentfinalamt,
                                'mail_notify' => $mail_notify,
                                'final_upx_price' => $finalprice,
                                'final_agent_price' => $finalagentprice,
                                'price_per_kg_upx' => $price_per_kg_upx,
                                'price_per_kg_agent' => $price_per_kg_agent,
                                'discount_amt' => $input['discount_amt'],
                                'packing_charge_amt' => $input['packing_charge_amt'],
                                'tax_amt' => $input['tax_amt'],
                                'final_total_upx' => $final_total_upx,
                                'final_total_agent' => $final_total_agent,
                            ])->id;
                            /***************************************** Booking store  ****************************************/

                            $inputdata = $request->except('image_s');
                            dispatch(new StoreSenderReceiverAddress(Auth::id(), $inputdata));
                            /***************************************** Booking sender address  ****************************************/
                            $id_doc_image = '';
                            if ($request->hasFile('image_s')) {
                                $destinationPath = public_path() . '/images/id_document/';
                                $file = $request->image_s;
                                $id_doc_image = time() . '.' . $file->clientExtension();
                                $file->move($destinationPath, $id_doc_image);
                            }
                            $identity_card = $input['id_type_s'];
                            if ($identity_card == 'Other') {
                                $id_type =  $input['id_type_other_s'];
                            }else {
                                $id_type =  $input['id_type_s'];
                            }
                            BookingAddress::create([
                                'booking_id' => $bookingid,
                                'type' => 'sender',
                                'name' => $input['first_name_s'],
                                'lastname' => $input['last_name_s'],
                                'email' => $input['email_s'],
                                'address1' => $input['address1_s'],
                                'address2' => $input['address2_s'],
                                'address3' => $input['address3_s'],
                                'country_id' => $input['coutry_s'],
                                'state' => $input['state_s'],
                                'city' => $input['city_s'],
                                'postalcode' => $input['postal_code_s'],
                                'phonenumber' => $input['phone_s'],
                                'company' => $input['company_s'],
                                'id_type' => $id_type,
                                'id_number' => $input['id_number_s'],
                                'id_doc_image' => $id_doc_image,
                            ]);
                            /***************************************** Booking sender address  ****************************************/

                            /***************************************** Booking receiver address  ****************************************/
                            BookingAddress::create([
                                'booking_id' => $bookingid,
                                'type' => 'receiver',
                                'name' => $input['full_name_r'],
                                'email' => $input['email_r'],
                                'address1' => $input['address1_r'],
                                'address2' => $input['address2_r'],
                                'address3' => $input['address3_r'],
                                'country_id' => $input['country_r'],
                                'city' => $input['city_r'],
                                'state' => $input['state_r'],
                                'postalcode' => $input['postal_code_r'],
                                'phonenumber' => $input['phone_r'],
                                'company' => $input['company_r'],
                            ]);
                            /***************************************** Booking receiver address  ****************************************/


                            /***************************************** Booking return address  ****************************************/
                            if (isset($input['return_address']) && $input['return_address'] == 1) {
                                BookingAddress::create([
                                    'booking_id' => $bookingid,
                                    'type' => 'return',
                                    'name' => $input['first_name_d'],
                                    'email' => $input['email_d'],
                                    'address1' => $input['address1_d'],
                                    'address2' => $input['address2_d'],
                                    'address3' => $input['address3_d'],
                                    'country_id' => $input['country_d'],
                                    'city' => $input['city_d'],
                                    'postalcode' => $input['postal_code_d'],
                                    'phonenumber' => $input['phone_d'],
                                    'company' => $input['company_d'],
                                ]);
                            }

                            /***************************************** Booking Dimension  ****************************************/
                            $box_count = count($input['document_package_type']);

                            foreach ($input['document_package_type'] as $key => $value) {
                                if ($service_id == 3) {
                                    $description = $input['document_description'][$key];
                                } else {
                                    $description = $input['description'][$key];
                                }

                                BookingDimension::create([
                                    'booking_id' => $bookingid,
                                    //   'lenth' => $input['length'][$key],
                                    //  'width' => $input['width'][$key],
                                    //  'height' => $input['height'][$key],
                                    'weight' => $input['document_package_type'][$key],
                                    //  'insure_amt' => $input['insureamt'][$key],
                                    //  'consignment_amt' => $input['consignment'][$key],
                                    'description' => $description,
                                    'box_number' => getBoxNumber(),
                                    'box_page' => ($key + 1) . '/' . $box_count,
                                    'total_on_dimension' => 0.00,
                                    'total_on_weight' => 0.00
                                ]);
                            }
                            /***************************************** Booking Dimension  ****************************************/

                            /***************************************** Booking StatusLog  ****************************************/

                            BookingStatusLog::create([
                                'booking_id' => $bookingid,
                                'status' => 'shipped',
                            ]);
                            if ($mail_notify == 1) {
                                dispatch(new SendMailChangeStatus(array($bookingid)));
                            }

                            /************ save admin to agent invoice pdf at booking time (document service) ***************/
                            $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($bookingid)->first();
                            $logoimage = 'logo.png';
                            if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
                                //  $booking->createdby->logo_image = 'defaultinvoice.png';
                            } else {
                                $booking->createdby->logo_image = $booking->createdby->logo_image;
                            }
                            if ($booking->createdby->role == 'admin') {
                                $booking->createdby->logo_image = 'logo.png';
                            }

                            $filename = $booking->tracking_number . '_invoice_' . $booking->id . '.pdf';
                            $pdfpath = public_path() . '/agentinvoice/' . $filename;

                            $data = ['booking' => $booking, 'logoimage' => $logoimage];
                            $pdf = PDF::loadView('upx.bookinghistory.agentdocumentinvoice', $data);
                            $pdf->save($pdfpath);

                            //update agent invoice column at booking time
                            Booking::where('id', $bookingid)->update(['agent_invoice' => $filename]);

                            //$booking = Booking::withCount('dimentions')->whereId($bookingid)->first();
                            $boxids = implode("|", $booking->dimentions->pluck('box_number')->toArray());
                            send_sms($booking->tracking_number,$input['first_name_s'], $final_total_upx, $input['phone_r']);
                            commit();
                            $arr = array("status" => 200, "msg" => $msg, 'bookingid' => $bookingid, 'boxids' => $boxids);
                        }
                    }

                }

            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }
                rollback();
                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }
                rollback();
                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            }
        }

        return \Response::json($arr);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * edit booking
     */
    public function edit($id)
    {
        $countries = Country::get();
        $receivecountries = Country::whereIn('id', ZoneCountry::pluck('country_id')->toArray())->get();
        $services = Service::where('status', 'active')->get();
        $booking = Booking::withCount('dimentions')->where('id', $id)->first();

        return view('upx.ship.edit', compact('countries', 'receivecountries', 'booking', 'services'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \Throwable
     * update booking
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $rules = array(
            'coutry_s' => "required|exists:countries,id",
            'first_name_s' => 'required',
            'last_name_s' => 'required',
            'email_s' => 'required|email',
            //   'company_s' => 'required',
            'phone_s' => 'required',
            'address1_s' => 'required',
            'postal_code_s' => 'required',
            'city_s' => 'required',
            'country_r' => "required|exists:countries,id",
            'full_name_r' => 'required',
            'last_name_s' => 'required',
            'address1_r' => 'required',
            'postal_code_r' => 'required',
            'city_r' => 'required',
            'length' => 'array',
            'length.*' => 'required_if:service_id,in:1', // if service is door to door
            'width' => 'array',
            'width.*' => 'required_if:service_id,in:1',
            'height' => 'array',
            'height.*' => 'required_if:service_id,in:1',
            'kg' => 'array',
            'kg.*' => 'required_if:service_id,in:1',

        );

        if (isset($input['return_address']) && $input['return_address'] == 1) {
            $rules['country_d'] = 'required|exists:countries,id';
            $rules['first_name_d'] = 'required';
            $rules['last_name_d'] = 'required';
            $rules['email_d'] = 'required|email';
            $rules['company_d'] = 'required';
            $rules['phone_d'] = 'required';
            $rules['address1_d'] = 'required';
            $rules['postal_code_d'] = 'required|numeric';
            $rules['city_d'] = 'required';


        }
        $message = [
            'coutry_s.required' => 'The Sender Country field is required.',
            'first_name_s.required' => 'The Sender First Name field is required.',
            'last_name_s.required' => 'The Sender Last Name field is required.',
            'email_s.required' => 'The Sender Email field is required.',
            'email_s.email' => 'The Sender Email must be a valid email address.',
            'phone_s.required' => 'The Sender Phone field is required.',
            'address1_s.required' => 'The Sender Address 1 field is required.',
            'postal_code_s.required' => 'The Sender Postal code field is required.',
            'city_s.required' => 'The Sender City field is required.',
            'country_r.required' => 'The Receiver Country field is required.',
            'full_name_r.required' => 'The Receiver Full Name field is required.',
            'email_r.required' => 'The Receiver Email field is required.',
            'email_r.email' => 'The Receiver Email must be a valid email address.',
            'address1_r.required' => 'The Receiver Address 1 field is required.',
            'postal_code_r.required' => 'The Receiver Postal code field is required.',
            'city_r.required' => 'The Receiver City field is required.',
            'country_d.required' => 'The Return Country field is required.',
            'first_name_d.required' => 'The Return First Name field is required.',
            'last_name_d.required' => 'The Return Last Name field is required.',
            'email_d.required' => 'The Return Email field is required.',
            'email_d.email' => 'The Return Email must be a valid email address.',
            'company_d.required' => 'The Return Company field is required.',
            'phone_d.required' => 'The Return Phone field is required.',
            'address1_d.required' => 'The Return Address 1 field is required.',
            'postal_code_d.required' => 'The Return Postal code field is required.',
            'postal_code_d.numeric' => 'The Return Postal code must be valid.',
            'city_d.required' => 'The Return City field is required.',
            "length.*.required" => "All Length fields are required.",
            "width.*.required" => "All Width fields are required.",
            "height.*.required" => "All Height fields are required.",
            "kg.*.required" => "All Weight fields are required.",

        ];
        $validator = Validator::make($input, $rules, $message);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {
            begin();
            try {
                $msg = 'success';
                $actual = array();
                $error = true;
                $valumetric = array();
                $service_id = $input['service_id'];
                $servicetype = isset($input['service_type']) ? $input['service_type'] : null;
                // if service is door to door
                if ($service_id == 1) {

                    foreach ($input['length'] as $key => $value) {

                        $valumetric[] = ($input['length'][$key] * $input['width'][$key] * $input['height'][$key]) / 6000;
                        $actual[] = $input['kg'][$key];
                        $insureamt[] = $input['insureamt'][$key];
                        $consignment[] = $input['consignment'][$key];

                    }
                    
                    $totalquantity = count($input['length']);
                    $volumnmetricweight = GetRoundByFraction(round(array_sum($valumetric), 2));
                    $actualweight = GetRoundByFraction(array_sum($actual));
                    $insurefinalamt = array_sum($insureamt);
                    $consignmentfinalamt = array_sum($consignment);
                    $zone = ZoneCountry::where([['country_id', $input['country_r']],['service_id',$service_id]])->first();
                    $upxprice = $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;

                    if (!empty($zone)) {
                        /*if(isset($input['is_insure'])){

                        }*/
                        $zoneid = $zone->zone_id;
                        $maxvalue = max($volumnmetricweight, $actualweight);

                        $systemvalue = 1.00;
                        $selectedweight = Weight::where('weight', '>=', $systemvalue)->orderby('weight', 'asc')->first();
                        //$handling_price = handling_price();
                        $error = true;
                        $msg = 'Weight is out of range.';

                        if (!empty($selectedweight)) {
                            $weightid = $selectedweight->id;
                            // dd($zoneid);
                            $upxprice = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'price');
                            $handling_price = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'handling');
                            if (Auth::user()->role == 'admin') {
                                $agentprice = $upxprice;
                                $handling_price_agent = $handling_price;
                            }
                            if (Auth::user()->role == 'agent') {
                                $agentprice = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype,  'price');
                                $handling_price_agent = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype, 'handling');
                            }
                            /*check the agent price added or not*/
                            if ($agentprice <= 0 && Auth::user()->role == 'agent') {
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Please contact to admin for add the agent price in price slab.");
                                rollback();
                                return \Response::json($arr);
                            }

                            $price_per_kg_upx = $upxprice;
                            $price_per_kg_agent = $agentprice;
                            if ($maxvalue <= 5) {
                                $upxprice = $upxprice * 5;
                                $agentprice = $agentprice * 5;
                            } else {
                                $upxprice = $upxprice * $maxvalue;
                                $agentprice = $agentprice * $maxvalue;
                            }

                            $finalprice = $upxprice + $insurefinalamt + $handling_price;
                            $finalagentprice = $agentprice + $insurefinalamt + $handling_price_agent;
                            if (Auth::user()->role == 'admin') {
                                $finalagentprice = 0.00;
                            }
                            $error = false;
                            $msg = 'Success';

                        }

                    } else {
                        $error = true;
                        $msg = 'Receiver Country is not in valid zone.';
                    }
                    $book = 1;
                    if (Auth::user()->role == 'agent') {
                        $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                        $limitamout = auth()->user()->booking_limit;
                        $checkfinalamount = $duaamount + $finalprice;
                        if ($limitamout < $checkfinalamount) {
                            $book = 0;
                        }
                    }
                    if ($input['booking_status'] == 0) {
                        $view = view("upx.ship.loadprice", compact('error', 'msg', 'totalquantity', 'volumnmetricweight', 'actualweight', 'weightid', 'upxprice', 'insurefinalamt', 'consignmentfinalamt', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                        $arr = array("status" => 200, "result" => $view);
                    } else {

                        if ($book == 0) {
                            $error = true;
                            $msg = 'You have exceeded the booking limit  &#163;' . auth()->user()->booking_limit;
                        }
                        if ($error) {
                            $arr = array("status" => 400, "msg" => $msg);
                        } else {

                            $bookpaymentstatus = 'paid';
                            $status = 'confirmed';
                            if (Auth::user()->role == 'agent') {
                                $bookpaymentstatus = 'unpaid';
                                $status = 'pending';
                            }

                            $mail_notify = '0';
                            if (isset($input['mail_notify'])) {
                                $mail_notify = '1';
                            }


                            /***************************************** Booking update  ****************************************/
                            $final_total_upx = $finalprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            //$final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            if ($finalagentprice >= $input['discount_amt']) {
                                $final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            } else {
                                //$final_total_agent = $finalagentprice + $input['packing_charge_amt'] + $input['tax_amt'];
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Discount amount must be less than or equal to &#163;" . $finalagentprice);
                                rollback();
                                return \Response::json($arr);
                            }
                            $tracking_number = generateTrackNumber();
                            $bookingid = Booking::where('id', $id)->update([
                                'booked_by' => Auth::id(),
                                'service_id' => $service_id,
                                'service_type' => $servicetype,
                                'current_status' => 1,
                                'payment_status' => $bookpaymentstatus,
                                'status' => $status,
                                'handling_price' => $handling_price,
                                'handling_price_agent' => $handling_price_agent,
                                'package_type' => $input['package_type'],
                                //'tracking_number' => getTrackNumber(),
                                //'tracking_number' => $tracking_number,
                                'booking_instruction' => $input['booking_instruction'],
                                'actual_weight' => $actualweight,
                                'volumetric_weight' => $volumnmetricweight,
                                'upx_price' => $upxprice,
                                'agent_price' => $agentprice,
                                'final_insure_amt' => $insurefinalamt,
                                'final_consignment_amt' => $consignmentfinalamt,
                                'mail_notify' => $mail_notify,
                                'final_upx_price' => $finalprice,
                                'final_agent_price' => $finalagentprice,
                                'price_per_kg_upx' => $price_per_kg_upx,
                                'price_per_kg_agent' => $price_per_kg_agent,
                                'discount_amt' => $input['discount_amt'],
                                'packing_charge_amt' => $input['packing_charge_amt'],
                                'tax_amt' => $input['tax_amt'],
                                'final_total_upx' => $final_total_upx,
                                'final_total_agent' => $final_total_agent,
                            ]);
                            $bookingid = $id;
                            /***************************************** Booking update  ****************************************/

                            $inputdata = $request->except('image_s');
                            dispatch(new StoreSenderReceiverAddress(Auth::id(), $inputdata));
                            /***************************************** Booking sender address update  ****************************************/
                            $id_doc_image = '';
                            if ($request->hasFile('image_s')) {
                                $destinationPath = public_path() . '/images/id_document/';
                                $file = $request->image_s;
                                $id_doc_image = time() . '.' . $file->clientExtension();
                                $file->move($destinationPath, $id_doc_image);
                            }
                            /*sender update*/
                            if (!empty($input['sender_id'])) {
                                BookingAddress::where('id', $input['sender_id'])->update([
                                    'booking_id' => $bookingid,
                                    'type' => 'sender',
                                    'name' => $input['first_name_s'],
                                    'lastname' => $input['last_name_s'],
                                    'email' => $input['email_s'],
                                    'address1' => $input['address1_s'],
                                    'address2' => $input['address2_s'],
                                    'address3' => $input['address3_s'],
                                    'country_id' => $input['coutry_s'],
                                    'state' => $input['state_s'],
                                    'city' => $input['city_s'],
                                    'postalcode' => $input['postal_code_s'],
                                    'phonenumber' => $input['phone_s'],
                                    'company' => $input['company_s'],
                                    'id_type' => $input['id_type_s'],
                                    'id_number' => $input['id_number_s'],
                                    'id_doc_image' => $id_doc_image,
                                ]);
                            }

                            /***************************************** Booking sender address update ****************************************/

                            /***************************************** Booking receiver address  update ****************************************/
                            if (!empty($input['receiver_id'])) {
                                BookingAddress::where('id', $input['receiver_id'])->update([
                                    'booking_id' => $bookingid,
                                    'type' => 'receiver',
                                    'name' => $input['full_name_r'],
                                    'email' => $input['email_r'],
                                    'address1' => $input['address1_r'],
                                    'address2' => $input['address2_r'],
                                    'address3' => $input['address3_r'],
                                    'country_id' => $input['country_r'],
                                    'city' => $input['city_r'],
                                    'state' => $input['state_r'],
                                    'postalcode' => $input['postal_code_r'],
                                    'phonenumber' => $input['phone_r'],
                                    'company' => $input['company_r'],
                                ]);
                            }

                            /***************************************** Booking receiver address update  ****************************************/


                            /***************************************** Booking return address  update ****************************************/
                            if (isset($input['return_address']) && $input['return_address'] == 1) {
                                if (!empty($input['return_id'])) {
                                    BookingAddress::where('id', $input['return_id'])->update([
                                        'booking_id' => $bookingid,
                                        'type' => 'return',
                                        'name' => $input['first_name_d'],
                                        'email' => $input['email_d'],
                                        'address1' => $input['address1_d'],
                                        'address2' => $input['address2_d'],
                                        'address3' => $input['address3_d'],
                                        'country_id' => $input['country_d'],
                                        'city' => $input['city_d'],
                                        'postalcode' => $input['postal_code_d'],
                                        'phonenumber' => $input['phone_d'],
                                        'company' => $input['company_d'],
                                    ]);
                                }

                            }
                            /***************************************** Booking Dimension  update****************************************/
                            BookingDimension::where('booking_id', $id)->delete();
                            $box_count = count($input['length']);
                            foreach ($input['length'] as $key => $value) {
                                BookingDimension::create([
                                    'booking_id' => $bookingid,
                                    'lenth' => $input['length'][$key],
                                    'width' => $input['width'][$key],
                                    'height' => $input['height'][$key],
                                    'weight' => $input['kg'][$key],
                                    'insure_amt' => $input['insureamt'][$key],
                                    'consignment_amt' => $input['consignment'][$key],
                                    'description' => $input['description'][$key],
                                    'box_number' => getBoxNumber(),
                                    'box_page' => ($key + 1) . '/' . $box_count,
                                    'total_on_dimension' => 0.00,
                                    'total_on_weight' => 0.00
                                ]);
                            }
                            /***************************************** Booking Dimension update  ****************************************/

                            /***************************************** Booking StatusLog  ****************************************/

                            if ($mail_notify == 1) {
                                dispatch(new SendMailChangeStatus(array($bookingid)));
                            }

                            /********************* save admin to agent invoice pdf at booking update time*******************/
                            $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($bookingid)->first();
                            $logoimage = 'logo.png';
                            if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
                                //  $booking->createdby->logo_image = 'defaultinvoice.png';
                            } else {
                                $booking->createdby->logo_image = $booking->createdby->logo_image;
                            }
                            if ($booking->createdby->role == 'admin') {
                                $booking->createdby->logo_image = 'logo.png';
                            }
                            $data = ['booking' => $booking, 'logoimage' => $logoimage];
                            $pdf = PDF::loadView('upx.bookinghistory.agentinvoice', $data);

                            $filename = $booking->tracking_number . '_invoice_' . $booking->id . '.pdf';
                            $pdfpath = public_path() . '/agentinvoice/' . $filename;
                            $pdf->save($pdfpath);

                            //update agent invoice column at booking update time (document service)
                            Booking::where('id', $bookingid)->update(['agent_invoice' => $filename]);

                            $booking = Booking::withCount('dimentions')->whereId($bookingid)->first();
                            $boxids = implode("|", $booking->dimentions->pluck('box_number')->toArray());
                            commit();
                            $arr = array("status" => 200, "msg" => "Booking update successfully.", 'bookingid' => $bookingid, 'boxids' => $boxids);
                        }
                    }
                } elseif($service_id == 2){

                    foreach ($input['length'] as $key => $value) {

                        $valumetric[] = ($input['length'][$key] * $input['width'][$key] * $input['height'][$key]) / 6000;
                        $actual[] = $input['kg'][$key];
                        $insureamt[] = $input['insureamt'][$key];
                        $consignment[] = $input['consignment'][$key];

                    }
                    
                    $totalquantity = count($input['length']);
                    $volumnmetricweight = GetRoundByFraction(round(array_sum($valumetric), 2));
                    $actualweight = GetRoundByFraction(array_sum($actual));
                    $insurefinalamt = array_sum($insureamt);
                    $consignmentfinalamt = array_sum($consignment);
                    $zone = ZoneCountry::where([['country_id', $input['country_r']],['service_id',$service_id]])->first();
                    $upxprice = $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;

                    if (!empty($zone)) {
                        /*if(isset($input['is_insure'])){

                        }*/
                        $zoneid = $zone->zone_id;
                        $maxvalue = max($volumnmetricweight, $actualweight);

                        $systemvalue = 1.00;
                        $selectedweight = Weight::where('weight', '>=', $systemvalue)->orderby('weight', 'asc')->first();
                        //$handling_price = handling_price();
                        $error = true;
                        $msg = 'Weight is out of range.';

                        if (!empty($selectedweight)) {
                            $weightid = $selectedweight->id;
                            // dd($zoneid);
                            $upxprice = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'price');
                            $handling_price = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $servicetype, 'handling');
                            if (Auth::user()->role == 'admin') {
                                $agentprice = $upxprice;
                                $handling_price_agent = $handling_price;
                            }
                            if (Auth::user()->role == 'agent') {
                                $agentprice = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype,  'price');
                                $handling_price_agent = getmypricedoortodoor($weightid, $zoneid, 'agent_price', Auth::user()->id, $servicetype, 'handling');
                            }
                            /*check the agent price added or not*/
                            if ($agentprice <= 0 && Auth::user()->role == 'agent') {
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Please contact to admin for add the agent price in price slab.");
                                rollback();
                                return \Response::json($arr);
                            }

                            $price_per_kg_upx = $upxprice;
                            $price_per_kg_agent = $agentprice;
                            if ($maxvalue <= 5) {
                                $upxprice = $upxprice * 5;
                                $agentprice = $agentprice * 5;
                            } else {
                                $upxprice = $upxprice * $maxvalue;
                                $agentprice = $agentprice * $maxvalue;
                            }

                            $finalprice = $upxprice + $insurefinalamt + $handling_price;
                            $finalagentprice = $agentprice + $insurefinalamt + $handling_price_agent;
                            if (Auth::user()->role == 'admin') {
                                $finalagentprice = 0.00;
                            }
                            $error = false;
                            $msg = 'Success';

                        }

                    } else {
                        $error = true;
                        $msg = 'Receiver Country is not in valid zone.';
                    }
                    $book = 1;
                    if (Auth::user()->role == 'agent') {
                        $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                        $limitamout = auth()->user()->booking_limit;
                        $checkfinalamount = $duaamount + $finalprice;
                        if ($limitamout < $checkfinalamount) {
                            $book = 0;
                        }
                    }
                    if ($input['booking_status'] == 0) {
                        $view = view("upx.ship.loadprice", compact('error', 'msg', 'totalquantity', 'volumnmetricweight', 'actualweight', 'weightid', 'upxprice', 'insurefinalamt', 'consignmentfinalamt', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                        $arr = array("status" => 200, "result" => $view);
                    } else {

                        if ($book == 0) {
                            $error = true;
                            $msg = 'You have exceeded the booking limit  &#163;' . auth()->user()->booking_limit;
                        }
                        if ($error) {
                            $arr = array("status" => 400, "msg" => $msg);
                        } else {

                            $bookpaymentstatus = 'paid';
                            $status = 'confirmed';
                            if (Auth::user()->role == 'agent') {
                                $bookpaymentstatus = 'unpaid';
                                $status = 'pending';
                            }

                            $mail_notify = '0';
                            if (isset($input['mail_notify'])) {
                                $mail_notify = '1';
                            }


                            /***************************************** Booking update  ****************************************/
                            $final_total_upx = $finalprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            //$final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            if ($finalagentprice >= $input['discount_amt']) {
                                $final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            } else {
                                //$final_total_agent = $finalagentprice + $input['packing_charge_amt'] + $input['tax_amt'];
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Discount amount must be less than or equal to &#163;" . $finalagentprice);
                                rollback();
                                return \Response::json($arr);
                            }
                            $tracking_number = generateTrackNumber();
                            $bookingid = Booking::where('id', $id)->update([
                                'booked_by' => Auth::id(),
                                'service_id' => $service_id,
                                'service_type' => $servicetype,
                                'current_status' => 1,
                                'payment_status' => $bookpaymentstatus,
                                'status' => $status,
                                'handling_price' => $handling_price,
                                'handling_price_agent' => $handling_price_agent,
                                'package_type' => $input['package_type'],
                                //'tracking_number' => getTrackNumber(),
                                //'tracking_number' => $tracking_number,
                                'booking_instruction' => $input['booking_instruction'],
                                'actual_weight' => $actualweight,
                                'volumetric_weight' => $volumnmetricweight,
                                'upx_price' => $upxprice,
                                'agent_price' => $agentprice,
                                'final_insure_amt' => $insurefinalamt,
                                'final_consignment_amt' => $consignmentfinalamt,
                                'mail_notify' => $mail_notify,
                                'final_upx_price' => $finalprice,
                                'final_agent_price' => $finalagentprice,
                                'price_per_kg_upx' => $price_per_kg_upx,
                                'price_per_kg_agent' => $price_per_kg_agent,
                                'discount_amt' => $input['discount_amt'],
                                'packing_charge_amt' => $input['packing_charge_amt'],
                                'tax_amt' => $input['tax_amt'],
                                'final_total_upx' => $final_total_upx,
                                'final_total_agent' => $final_total_agent,
                            ]);
                            $bookingid = $id;
                            /***************************************** Booking update  ****************************************/

                            $inputdata = $request->except('image_s');
                            dispatch(new StoreSenderReceiverAddress(Auth::id(), $inputdata));
                            /***************************************** Booking sender address update  ****************************************/
                            $id_doc_image = '';
                            if ($request->hasFile('image_s')) {
                                $destinationPath = public_path() . '/images/id_document/';
                                $file = $request->image_s;
                                $id_doc_image = time() . '.' . $file->clientExtension();
                                $file->move($destinationPath, $id_doc_image);
                            }
                            /*sender update*/
                            if (!empty($input['sender_id'])) {
                                BookingAddress::where('id', $input['sender_id'])->update([
                                    'booking_id' => $bookingid,
                                    'type' => 'sender',
                                    'name' => $input['first_name_s'],
                                    'lastname' => $input['last_name_s'],
                                    'email' => $input['email_s'],
                                    'address1' => $input['address1_s'],
                                    'address2' => $input['address2_s'],
                                    'address3' => $input['address3_s'],
                                    'country_id' => $input['coutry_s'],
                                    'state' => $input['state_s'],
                                    'city' => $input['city_s'],
                                    'postalcode' => $input['postal_code_s'],
                                    'phonenumber' => $input['phone_s'],
                                    'company' => $input['company_s'],
                                    'id_type' => $input['id_type_s'],
                                    'id_number' => $input['id_number_s'],
                                    'id_doc_image' => $id_doc_image,
                                ]);
                            }

                            /***************************************** Booking sender address update ****************************************/

                            /***************************************** Booking receiver address  update ****************************************/
                            if (!empty($input['receiver_id'])) {
                                BookingAddress::where('id', $input['receiver_id'])->update([
                                    'booking_id' => $bookingid,
                                    'type' => 'receiver',
                                    'name' => $input['full_name_r'],
                                    'email' => $input['email_r'],
                                    'address1' => $input['address1_r'],
                                    'address2' => $input['address2_r'],
                                    'address3' => $input['address3_r'],
                                    'country_id' => $input['country_r'],
                                    'city' => $input['city_r'],
                                    'state' => $input['state_r'],
                                    'postalcode' => $input['postal_code_r'],
                                    'phonenumber' => $input['phone_r'],
                                    'company' => $input['company_r'],
                                ]);
                            }

                            /***************************************** Booking receiver address update  ****************************************/


                            /***************************************** Booking return address  update ****************************************/
                            if (isset($input['return_address']) && $input['return_address'] == 1) {
                                if (!empty($input['return_id'])) {
                                    BookingAddress::where('id', $input['return_id'])->update([
                                        'booking_id' => $bookingid,
                                        'type' => 'return',
                                        'name' => $input['first_name_d'],
                                        'email' => $input['email_d'],
                                        'address1' => $input['address1_d'],
                                        'address2' => $input['address2_d'],
                                        'address3' => $input['address3_d'],
                                        'country_id' => $input['country_d'],
                                        'city' => $input['city_d'],
                                        'postalcode' => $input['postal_code_d'],
                                        'phonenumber' => $input['phone_d'],
                                        'company' => $input['company_d'],
                                    ]);
                                }

                            }
                            /***************************************** Booking Dimension  update****************************************/
                            BookingDimension::where('booking_id', $id)->delete();
                            $box_count = count($input['length']);
                            foreach ($input['length'] as $key => $value) {
                                BookingDimension::create([
                                    'booking_id' => $bookingid,
                                    'lenth' => $input['length'][$key],
                                    'width' => $input['width'][$key],
                                    'height' => $input['height'][$key],
                                    'weight' => $input['kg'][$key],
                                    'insure_amt' => $input['insureamt'][$key],
                                    'consignment_amt' => $input['consignment'][$key],
                                    'description' => $input['description'][$key],
                                    'box_number' => getBoxNumber(),
                                    'box_page' => ($key + 1) . '/' . $box_count,
                                    'total_on_dimension' => 0.00,
                                    'total_on_weight' => 0.00
                                ]);
                            }
                            /***************************************** Booking Dimension update  ****************************************/

                            /***************************************** Booking StatusLog  ****************************************/

                            if ($mail_notify == 1) {
                                dispatch(new SendMailChangeStatus(array($bookingid)));
                            }

                            /********************* save admin to agent invoice pdf at booking update time*******************/
                            $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($bookingid)->first();
                            $logoimage = 'logo.png';
                            if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
                                //  $booking->createdby->logo_image = 'defaultinvoice.png';
                            } else {
                                $booking->createdby->logo_image = $booking->createdby->logo_image;
                            }
                            if ($booking->createdby->role == 'admin') {
                                $booking->createdby->logo_image = 'logo.png';
                            }
                            $data = ['booking' => $booking, 'logoimage' => $logoimage];
                            $pdf = PDF::loadView('upx.bookinghistory.agentinvoice', $data);

                            $filename = $booking->tracking_number . '_invoice_' . $booking->id . '.pdf';
                            $pdfpath = public_path() . '/agentinvoice/' . $filename;
                            $pdf->save($pdfpath);

                            //update agent invoice column at booking update time (document service)
                            Booking::where('id', $bookingid)->update(['agent_invoice' => $filename]);

                            $booking = Booking::withCount('dimentions')->whereId($bookingid)->first();
                            $boxids = implode("|", $booking->dimentions->pluck('box_number')->toArray());
                            commit();
                            $arr = array("status" => 200, "msg" => "Booking update successfully.", 'bookingid' => $bookingid, 'boxids' => $boxids);
                        }
                    }
                }else {
                    /*if service is document at booking update time*/
                    $zone = ZoneCountry::where('country_id', $input['country_r'])->where('service_id', $service_id)->first();
                    $weightid = $finalprice = $price_per_kg_upx = $price_per_kg_agent = 0;

                    if (!empty($zone)) {
                        $zoneid = $zone->zone_id;
                        foreach ($input['document_package_type'] as $key => $value) {
                            $actual[] = $input['document_package_type'][$key];
                            $upxprice[] = getmydocumentprice($zoneid, $value, $service_id, 'upx_price', Auth::user()->id);
                            if (Auth::user()->role == 'admin') {
                                $agentprice = $upxprice;
                            }
                            if (Auth::user()->role == 'agent') {
                                $agent_price = getmydocumentprice($zoneid, $value, $service_id, 'agent_price', Auth::user()->id);
                                $agentprice[] = $agent_price;
                            }
                            
                            /*check the agent price added or not*/
                            if ($agent_price <= 0 && Auth::user()->role == 'agent') {
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Please contact to admin for add the agent price in price slab.");
                                rollback();
                                return \Response::json($arr);
                            }
                        }
                       

                        $totalquantity = count($input['document_package_type']);
                        $actualweight = array_sum($actual);
                        $maxvalue = $actualweight;
                        $handling_price = handling_price();
                        $upxprice = array_sum($upxprice);
                        $agentprice = array_sum($agentprice);
                        $finalprice = $upxprice + $handling_price;
                        $finalagentprice = $agentprice;

                        if (Auth::user()->role == 'admin') {
                            $finalagentprice = 0.00;
                        }
                        $price_per_kg_upx = $upxprice;
                        $price_per_kg_agent = $agentprice;
                        $error = false;
                        $msg = 'Success';

                    } else {
                        $error = true;
                        $msg = 'Receiver Country is not in valid zone.';
                    }
                    $book = 1;
                    if (Auth::user()->role == 'agent') {
                        $duaamount = auth()->user()->unpaidbokings->sum('final_agent_price');
                        $limitamout = auth()->user()->booking_limit;
                        $checkfinalamount = $duaamount + $finalprice;
                        if ($limitamout < $checkfinalamount) {
                            $book = 0;
                        }
                    }
                    if ($input['booking_status'] == 0) {
                        $view = view("upx.ship.documentprice", compact('error', 'msg', 'totalquantity', 'actualweight', 'upxprice', 'finalprice', 'finalagentprice', 'handling_price', 'book'))->render();
                        $arr = array("status" => 200, "result" => $view);
                    } else {

                        if ($book == 0) {
                            $error = true;
                            $msg = 'You have exceeded the booking limit  &#163;' . auth()->user()->booking_limit;
                        }
                        if ($error) {
                            $arr = array("status" => 400, "msg" => $msg);
                        } else {

                            $bookpaymentstatus = 'paid';
                            if (Auth::user()->role == 'agent') {
                                $bookpaymentstatus = 'unpaid';
                            }

                            $mail_notify = '0';
                            if (isset($input['mail_notify'])) {
                                $mail_notify = '1';
                            }


                            /***************************************** Booking store update (document service)  ****************************************/
                            $final_total_upx = $finalprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            // $final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            if ($finalagentprice >= $input['discount_amt']) {
                                $final_total_agent = $finalagentprice - $input['discount_amt'] + $input['packing_charge_amt'] + $input['tax_amt'];
                            } else {
                                //$final_total_agent = $finalagentprice + $input['packing_charge_amt'] + $input['tax_amt'];
                                $error = true;
                                $arr = array("status" => 400, "msg" => "Discount amount must be less than or equal to &#163;" . $finalagentprice);
                                rollback();
                                return \Response::json($arr);
                            }
                            $tracking_number = generateTrackNumber();
                            $bookingid = Booking::where('id', $id)->update([
                                'booked_by' => Auth::id(),
                                'service_id' => $service_id,
                                'service_type' => $servicetype,
                                'current_status' => 1,
                                'payment_status' => $bookpaymentstatus,
                                'handling_price' => $handling_price,
                                'package_type' => $input['package_type'],
                                //'tracking_number' => getTrackNumber(),
                                //  'tracking_number' => $tracking_number,
                                'booking_instruction' => $input['booking_instruction'],
                                'actual_weight' => $actualweight,
                                //  'volumetric_weight' => $volumnmetricweight,
                                'upx_price' => $upxprice,
                                'agent_price' => $agentprice,
                                // 'final_insure_amt' => $insurefinalamt,
                                // 'final_consignment_amt' => $consignmentfinalamt,
                                'mail_notify' => $mail_notify,
                                'final_upx_price' => $finalprice,
                                'final_agent_price' => $finalagentprice,
                                'price_per_kg_upx' => $price_per_kg_upx,
                                'price_per_kg_agent' => $price_per_kg_agent,
                                'discount_amt' => $input['discount_amt'],
                                'packing_charge_amt' => $input['packing_charge_amt'],
                                'tax_amt' => $input['tax_amt'],
                                'final_total_upx' => $final_total_upx,
                                'final_total_agent' => $final_total_agent,
                            ]);
                            $bookingid = $id;
                            /***************************************** Booking store update (document service)  ****************************************/

                            $inputdata = $request->except('image_s');
                            dispatch(new StoreSenderReceiverAddress(Auth::id(), $inputdata));
                            /***************************************** Booking sender address update (document service)  ****************************************/
                            $id_doc_image = '';
                            if ($request->hasFile('image_s')) {
                                $destinationPath = public_path() . '/images/id_document/';
                                $file = $request->image_s;
                                $id_doc_image = time() . '.' . $file->clientExtension();
                                $file->move($destinationPath, $id_doc_image);
                            }
                            if (!empty($input['sender_id'])) {
                                BookingAddress::where('id', $input['sender_id'])->update([
                                    'booking_id' => $bookingid,
                                    'type' => 'sender',
                                    'name' => $input['first_name_s'],
                                    'lastname' => $input['last_name_s'],
                                    'email' => $input['email_s'],
                                    'address1' => $input['address1_s'],
                                    'address2' => $input['address2_s'],
                                    'address3' => $input['address3_s'],
                                    'country_id' => $input['coutry_s'],
                                    'state' => $input['state_s'],
                                    'city' => $input['city_s'],
                                    'postalcode' => $input['postal_code_s'],
                                    'phonenumber' => $input['phone_s'],
                                    'company' => $input['company_s'],
                                    'id_type' => $input['id_type_s'],
                                    'id_number' => $input['id_number_s'],
                                    'id_doc_image' => $id_doc_image,
                                ]);
                            }

                            /***************************************** Booking sender address update (document service)  ****************************************/

                            /***************************************** Booking receiver address update (document service)  ****************************************/
                            if (!empty($input['receiver_id'])) {
                                BookingAddress::where('id', $input['receiver_id'])->update([
                                    'booking_id' => $bookingid,
                                    'type' => 'receiver',
                                    'name' => $input['full_name_r'],
                                    'email' => $input['email_r'],
                                    'address1' => $input['address1_r'],
                                    'address2' => $input['address2_r'],
                                    'address3' => $input['address3_r'],
                                    'country_id' => $input['country_r'],
                                    'city' => $input['city_r'],
                                    'state' => $input['state_r'],
                                    'postalcode' => $input['postal_code_r'],
                                    'phonenumber' => $input['phone_r'],
                                    'company' => $input['company_r'],
                                ]);
                            }
                            /***************************************** Booking receiver address update (document service)  ****************************************/


                            /***************************************** Booking return address  update (document service) ****************************************/
                            if (isset($input['return_address']) && $input['return_address'] == 1) {
                                if (!empty($input['return_id'])) {
                                    BookingAddress::where('id', $input['return_id'])->update([
                                        'booking_id' => $bookingid,
                                        'type' => 'return',
                                        'name' => $input['first_name_d'],
                                        'email' => $input['email_d'],
                                        'address1' => $input['address1_d'],
                                        'address2' => $input['address2_d'],
                                        'address3' => $input['address3_d'],
                                        'country_id' => $input['country_d'],
                                        'city' => $input['city_d'],
                                        'postalcode' => $input['postal_code_d'],
                                        'phonenumber' => $input['phone_d'],
                                        'company' => $input['company_d'],
                                    ]);
                                }
                            }
                            /***************************************** Booking Dimension  update (document service) ****************************************/
                            BookingDimension::where('booking_id', $id)->delete();
                            $box_count = count($input['document_package_type']);

                            foreach ($input['document_package_type'] as $key => $value) {

                                if ($service_id == 3) {
                                    $description = $input['document_description'][$key];
                                } else {
                                    $description = $input['description'][$key];
                                }
                                BookingDimension::create([
                                    'booking_id' => $bookingid,
                                    //   'lenth' => $input['length'][$key],
                                    //  'width' => $input['width'][$key],
                                    //  'height' => $input['height'][$key],
                                    'weight' => $input['document_package_type'][$key],
                                    //  'insure_amt' => $input['insureamt'][$key],
                                    //  'consignment_amt' => $input['consignment'][$key],
                                    'description' => $description,
                                    'box_number' => getBoxNumber(),
                                    'box_page' => ($key + 1) . '/' . $box_count,
                                    'total_on_dimension' => 0.00,
                                    'total_on_weight' => 0.00
                                ]);
                            }
                            /***************************************** Booking Dimension  update (document service) ****************************************/

                            if ($mail_notify == 1) {
                                dispatch(new SendMailChangeStatus(array($bookingid)));
                            }
                            /********************* save admin to agent invoice pdf at booking update time*******************/
                            $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($bookingid)->first();
                            $logoimage = 'logo.png';
                            if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
                                //  $booking->createdby->logo_image = 'defaultinvoice.png';
                            } else {
                                $booking->createdby->logo_image = $booking->createdby->logo_image;
                            }
                            if ($booking->createdby->role == 'admin') {
                                $booking->createdby->logo_image = 'logo.png';
                            }
                            $data = ['booking' => $booking, 'logoimage' => $logoimage];
                            $pdf = PDF::loadView('upx.bookinghistory.agentdocumentinvoice', $data);

                            $filename = $booking->tracking_number . '_invoice_' . $bookingid . '.pdf';
                            $pdfpath = public_path() . '/agentinvoice/' . $filename;
                            $pdf->save($pdfpath);

                            //update agent invoice column at booking update time (document service)
                            Booking::where('id', $bookingid)->update(['agent_invoice' => $filename]);

                            // $booking = Booking::withCount('dimentions')->whereId($bookingid)->first();
                            $boxids = implode("|", $booking->dimentions->pluck('box_number')->toArray());
                            commit();
                            $arr = array("status" => 200, "msg" => 'Booking update successfully.', 'bookingid' => $bookingid, 'boxids' => $boxids);
                        }
                    }

                }

            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }
                rollback();
                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }
                rollback();
                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            }
        }

        return \Response::json($arr);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     *  Copied to a new booking
     */

    public function bookingcopy(Request $request)
    {

        $input = $request->all();
        $rules = array(
            'id' => "required|exists:bookings,id",
        );
        $message = [
            'id.required' => 'The booking id is required.',
        ];
        $validator = Validator::make($input, $rules, $message);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {
            begin();
            try {
                $input = $request->all();
                $msg = 'success';
                $booking = Booking::with(['dimentions', 'addresses'])->find($input['id']);
                if ($booking) {
                    $booking->load('addresses', 'dimentions');
                    $newbooking = $booking->replicate();
                    $bookpaymentstatus = 'paid';
                    $status = 'confirmed';
                    if (Auth::user()->role == 'agent') {
                        $bookpaymentstatus = 'unpaid';
                        $status = 'pending';
                    }
                    $tracking_number = generateTrackNumber();
                    $newbooking->payment_status = $bookpaymentstatus;
                    $newbooking->status = $status;
                    $newbooking->tracking_number = $tracking_number;
                    $newbooking->booked_by = Auth::user()->id;
                    $newbooking->save();

                    /*  $inputdata = $request->except('image_s');
                    dispatch(new StoreSenderReceiverAddress(Auth::id(), $inputdata));
                    */

                    $newbooking->push(); //Push before to get id of $clone
                    /*copy the sender, receiver and return user data*/
                    foreach ($booking->addresses as $address) {
                        $new_address = $address->replicate();
                        $new_address->booking_id = $newbooking->id;
                        $new_address->save();
                        //$booking->addresses()->attach($booking->addresses);
                    }

                    foreach ($booking->dimentions as $dimention) {
                        $box_number = getBoxNumber();
                        $new_dimention = $dimention->replicate();
                        $new_dimention->box_number = $box_number;
                        $new_dimention->booking_id = $newbooking->id;
                        $new_dimention->save();
                        // $booking->dimentions()->attach($booking->dimentions);
                    }
                    /***************************************** Booking StatusLog  ****************************************/

                    BookingStatusLog::create([
                        'booking_id' => $input['id'],
                        'status' => 'shipped',
                    ]);
                    //dd('sd');
                    if ($booking->mail_notify == 1) {
                        dispatch(new SendMailChangeStatus(array($booking->id)));
                    }
                    commit();
                    $arr = array("status" => 200, "msg" => "Booking copied successfully.", 'data' => []);
                } else {
                    $arr = array("status" => 400, "msg" => "Booking not found.", "data" => []);
                }

            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }
                rollback();
                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }
                rollback();
                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            }
        }

        return \Response::json($arr);
    }

     /**
     * Display a mosal of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function quotation_modal(Request $request)
    {
        $services = Service::where('status', 'active')->get();
        return view('upx.ship.getquotationmodal', compact('services'));
    }

}
