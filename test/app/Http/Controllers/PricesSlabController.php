<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Zone;
use App\Weight;
use Validator;
use App\PriceSlab;
use Auth;
use App\AddressBook;
use App\UserDetail;
use App\User;
use App\AgentPrice;
use App\Service;
use App\DocumentPriceSlab;
use App\DocumentAgentPrice;
use App\DhlPriceSlabs;
use App\DhlAgentPrices;

class PricesSlabController extends Controller
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
        $zones = Zone::orderby('name','asc')->get();
        $weights = Weight::orderby('weight','asc')->get();
        $agents = User::whereRole('agent')->get();
        $services = Service::get();
        return view('upx.price_slab.index',compact('zones','weights','agents','services'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function change(Request $request)
    {
        $input = $request->all();
        $rules = array(
          'zoneid'=>"required|exists:zones,id",
          'weightid'=>'required|exists:weights,id',
          'type' => 'required|in:upx_price,agent_price',
          'price'=>'numeric|required',

        );

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
          $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {

          try {

            if(isset($request->service_type_main) && $request->service_type_main == 'dhl'){
              if($request->type == 'upx_price'){
                  $priceslab = DhlPriceSlabs::where('zone_id',$request->zoneid)->first();
                  if(!empty($priceslab)){
                          if($request->pricetype == "price"){
                            $priceslab->update(['upx_price'=>$request->price]);  
                          } 
                          if($request->pricetype == "handling"){
                            $priceslab->update(['handling_price'=>$request->price]);  
                          }
                          
                  }else{
                      $newprice = new DhlPriceSlabs;
                      $newprice->zone_id = $request->zoneid;
                      $newprice->service_id =  2;
                      $newprice->weight = 1;
                      if($request->pricetype == "price"){
                        $newprice->upx_price = $request->price;
                      }
                      if($request->pricetype == "handling"){
                        $newprice->handling_price = $request->price;
                      }
                      $newprice->save();
                  }
              }

               if($request->type == 'agent_price'){
                  $apriceslab = DhlAgentPrices::where([['zones_id',$request->zoneid],['agent_id',$request->agentid]])->first();
                  if(!empty($apriceslab)){
                      if($request->pricetype == "price"){
                          $apriceslab->update(['agent_price'=>$request->price]);
                      }
                      if($request->pricetype == "handling"){
                        $apriceslab->update(['handling_price'=>$request->price]);
                      }

                  }else{
                      $anewprice = new DhlAgentPrices;
                      $anewprice->zones_id = $request->zoneid;
                      $anewprice->weight = 1;
                      $anewprice->service_id =  2;
                      $anewprice->agent_id = $request->agentid;
                      if($request->pricetype == "price"){
                        $anewprice->agent_price = $request->price;
                      }
                      if($request->pricetype == "handling"){
                        $anewprice->handling_price = $request->price;
                      }
                      $anewprice->save();
                  }
              }

            }else{

              if($request->type == 'upx_price'){
                  $priceslab = PriceSlab::where([['zones_id',$request->zoneid],['weight_id',$request->weightid],['type',$request->service_type]])->first();
                  
                  if(!empty($priceslab)){
                      if($request->pricetype == "price"){
                        $priceslab->update(['upx_price'=>$request->price]);
                      }
                      if($request->pricetype == "handling"){
                        $priceslab->update(['handling_price'=>$request->price]);
                      }
                          
                  }else{
                      $newprice = new PriceSlab;
                      $newprice->zones_id = $request->zoneid;
                      $newprice->type =  $request->service_type;
                      $newprice->service_id =  1;
                      $newprice->weight_id = $request->weightid;
                      if($request->pricetype == "price"){
                        $newprice->upx_price = $request->price;
                      }
                      if($request->pricetype == "handling"){
                        $newprice->handling_price = $request->price;
                        
                      }

                      $newprice->save();
                  }
              }

              if($request->type == 'agent_price'){
                  $apriceslab = AgentPrice::where([['zones_id',$request->zoneid],['weight_id',$request->weightid],['agent_id',$request->agentid],['type',$request->service_type]])->first();
                 
                  if(!empty($apriceslab)){
                      if($request->pricetype == "price"){
                        $apriceslab->update(['agent_price'=>$request->price]);
                      }
                      if($request->pricetype == "handling"){
                        $apriceslab->update(['handling_price'=>$request->price]);
                      }
                  }else{
                      $anewprice = new AgentPrice;
                      $anewprice->zones_id = $request->zoneid;
                      $anewprice->weight_id = $request->weightid;
                      $anewprice->type =  $request->service_type;
                      $anewprice->service_id =  1;
                      $anewprice->agent_id = $request->agentid;
                      if($request->pricetype == "price"){
                        $anewprice->agent_price = $request->price;
                      }
                      if($request->pricetype == "handling"){
                        $anewprice->handling_price = $request->price;
                        
                      }
                      $anewprice->save();
                  }
              }
            }
            


            $arr = array("status"=>200);
          } catch ( \Illuminate\Database\QueryException $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          } catch (Exception $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          }
        }

        return \Response::json($arr);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tableload(Request $request)
    {
        $pricetype = $request->user_type;

        $serviceid = $request->serviceselect;
        $agents = User::whereRole('agent');
        if(isset($request->agentids) && !empty($request->agentids)) {
            $agents = $agents->whereIn('id',$request->agentids);
        }
        $agentcout = $agents->count();
        $agents = $agents->get();

        if(empty($request->agentids)) {
          $agentcout = 0;
          $agents = array();
        }

        if($pricetype == '') {
          $rowspan = ($agentcout * 4) + 4;
        }
        if($pricetype == 'upx') {
          $rowspan = 4;
        }

        if($pricetype == 'agent') {
          $rowspan = ($agentcout * 4) + 4;
        }
        $zones = Zone::orderby('name','asc');
        if(isset($request->zoneids) && !empty($request->zoneids)) {
            $zones = $zones->whereIn('id',$request->zoneids);
        }

        if(isset($serviceid) && !empty($serviceid)) {
            $zones = $zones->where('service_id',$serviceid);
        }
        $zones = $zones->get();



        $weights = Weight::orderby('weight','asc');
        if(isset($request->weightids) && !empty($request->weightids)) {
            $weights = $weights->whereIn('id',$request->weightids);
        }
        $weights = $weights->get();
        /*if()*/
        if($serviceid == 1){
          return view('upx.price_slab.tableload',compact('zones','weights','pricetype','agents','rowspan','serviceid'));
        }
        if($serviceid == 2){
         return view('upx.price_slab.dhlload',compact('zones','weights','pricetype','agents','rowspan','serviceid')); 
        }
        if($serviceid == 3){
          return view('upx.price_slab.documentload',compact('zones','pricetype','agents','rowspan','serviceid'));
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function receiver(Request $request)
    {
        $input = $request->all();
        $rules = array(
          'name'=>"required|max:30",
          'company'=>'max:30',
          'address1'=>'required',
          'phone_number'=>'numeric|nullable',
          'postalcode'=>'required',
          'city'=>'required|string',
          'country_id'=>'required|exists:countries,id',



        );

        $message = array(
          'name.required'=>"The Full Name field is required.",


        );

        $validator = Validator::make($input, $rules,$message);
        if($validator->fails()) {
          $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {

          try {
            $return = array();
            $input['created_by'] = Auth::user()->id;
            $addressbook = new AddressBook;
            $addressbook->create($input);

            $arr = array("status"=>200);
          } catch ( \Illuminate\Database\QueryException $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          } catch (Exception $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          }
        }

        return \Response::json($arr);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sender(Request $request)
    {
        $input = $request->all();
        $rules = array(
          'name'=>"required|max:30",
          'lastname'=>'required|max:30',
          'company'=>'required|max:30',
          'email'=>'required|email|unique:users,email,' . Auth::user()->id,
          'phone'=>'required|numeric|nullable',
          'address1'=>'required',
          'postal_code'=>'required',
          'city'=>'required|string',
          'country_id'=>'required|exists:countries,id',



        );

        $message = array(
          'name.required'=>"The First Name field is required.",
          'lastname.required'=>"The Last Name field is required.",



        );

        $validator = Validator::make($input, $rules,$message);
        if($validator->fails()) {
          $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {

          try {
            $userdetail = UserDetail::whereUserId(Auth::user()->id)->first();
            if(!empty($userdetail)){
                Auth::user()->update($input);
                $userdetail->update($input);
            }else{
                $input['user_id'] = Auth::user()->id;
                $newdetail = new UserDetail;
                $newdetail->create($input);
            }
            $arr = array("status"=>200);
          } catch ( \Illuminate\Database\QueryException $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          } catch (Exception $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }

            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          }
        }

        return \Response::json($arr);
    }

    /**
     * @param Request $request
     * @return mixed
     * Change the price of document service based on selecetd agent
     */
    public function documentchange(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'zoneid'=>"required|exists:zones,id",
            //'weightid'=>'required|exists:weights,id',
            'service_type'=>'required|in:halfgram,onekg',
            'type' => 'required|in:upx_price,agent_price',
            'price'=>'numeric|required',

        );

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {

            try {
                
                if($request->type == 'upx_price'){
                    $priceslab = DocumentPriceSlab::where([['zone_id',$request->zoneid],['weight',$request->weightid]])->first();
                    if(!empty($priceslab)){
                          if($request->pricetype == "price"){
                            $priceslab->update(['upx_price'=>$request->price]);  
                          } 
                          if($request->pricetype == "handling"){
                            $priceslab->update(['handling_price'=>$request->price]);  
                          }
                        
                    }else{
                        $newprice = new DocumentPriceSlab;
                        $newprice->zone_id = $request->zoneid;
                        $newprice->service_id =  3;
                        $newprice->weight = $request->weightid;
                        if($request->pricetype == "price"){
                          $newprice->upx_price = $request->price;
                        }
                        if($request->pricetype == "handling"){
                          $newprice->handling_price = $request->price;
                        }
                        $newprice->save();
                    }
                }

                if($request->type == 'agent_price'){
                    $apriceslab = DocumentAgentPrice::where([['zones_id',$request->zoneid],['weight',$request->weightid],['agent_id',$request->agentid]])->first();
                    if(!empty($apriceslab)){
                          if($request->pricetype == "price"){
                            $apriceslab->update(['agent_price'=>$request->price]);  
                          } 
                          if($request->pricetype == "handling"){
                            $apriceslab->update(['handling_price'=>$request->price]);  
                          }
                        
                    }else{
                        $anewprice = new DocumentAgentPrice;
                        $anewprice->zones_id = $request->zoneid;
                        $anewprice->agent_id = $request->agentid;
                        $anewprice->weight =  $request->weightid;
                        $anewprice->service_id =  3;
                        if($request->pricetype == "price"){
                          $anewprice->agent_price = $request->price;
                        } 
                        if($request->pricetype == "handling"){
                          $anewprice->handling_price = $request->price;
                        }
                        
                        $anewprice->save();
                    }
                }


                $arr = array("status"=>200);
            } catch ( \Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if(isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }

                $arr = array("status" => 400, "msg" =>$msg, "result" => array());
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if(isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }

                $arr = array("status" => 400, "msg" =>$msg, "result" => array());
            }
        }

        return \Response::json($arr);
    }
}
