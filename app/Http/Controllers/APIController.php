<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Order;
class APIController extends Controller
{

    //*******************************************************
    //Function Name : calculate_profit_of_order_item_data
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function calculate_profit_of_order_item_data()
    {
        $data = Order::selectRaw('sku_id, sum(orders.item_profit) as sum')->groupBy('sku_id')->get();
        return view('api_response.index',compact('data'));
    }

    public function profit_order_item()
    {
        try {
            $data = Order::selectRaw('sku_id, sum(orders.item_profit) as sum')->groupBy('sku_id')->get();
            $arr = array("status" => 200, "message" => "Success", "Total Profit" => $data);
        } catch (\Exception $ex) {
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            } else {
                $msg = $ex->getMessage();
            }
            $arr = array("status" => 400, "message" => $msg, "data" => array());
        }
        return \Response::json($arr);
    }

    //*******************************************************
    //Function Name : calculate_profit_of_order_item
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function calculate_profit_of_order_item(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'sku_id' => "required",
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first(), "data" => array());
        } else {
            try {
                $data = Order::where('sku_id' , $request->sku_id )->sum('item_profit');
                $arr = array("status" => 200, "message" => "Success", "data" => $data);
            } catch (\Exception $ex) {
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                } else {
                    $msg = $ex->getMessage();
                }
                $arr = array("status" => 400, "message" => $msg, "data" => array());
            }
        }
        return \Response::json($arr);
    }


}
