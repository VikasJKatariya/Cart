<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\Product;
use DataTables;
use Validator;
use Carbon\Carbon;
class OrderController extends Controller
{
    //*******************************************************
    //Function Name : index
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function index()
    { 
        $products = Product::all();
        return view('order.index',compact('products'));
    }
    //*******************************************************
    //Function Name : getallorder
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function getallorder(Request $request) {

        $order = Order::with('product')->get();
        return DataTables::of($order)
        ->addColumn('action', function ($q) {
            return '<a class="openform" data-toggle="modal" title="Edit Order" data-target=".modal_edit_list" data-id="' . $q->id . '"><i class="fa fa-edit"></i></a>  <a class="delete_order" title="Delete Order" data-id="' . $q->id . '"><i class="fa fa-trash"></i></a>';
        })
        ->addColumn('sku', function ($q) {
            return $q->sku_id ;
        })
        ->addColumn('product_name', function ($q) {
            return $q->product->title ;
        })
        ->addColumn('order_id', function ($q) {
            return $q->order_id ;
        })
        ->addColumn('item_quantity', function ($q) {
            return $q->item_quantity ;
        })
        ->addColumn('item_price', function ($q) {
            return $q->item_price ;
        })
        ->addColumn('order_date', function ($q) {
            return $q->order_date ;
        })
        ->addColumn('created_at', function ($q) {
            return date('d-m-Y', strtotime($q->created_at));
        })
        ->addIndexColumn()
        ->rawColumns(['action'])->make(true);
    }

    //*******************************************************
    //Function Name : getordermodel
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function getordermodel(Request $request)
    {        
        $order = array();
        $products = Product::all();
        if(isset($request->id) && $request->id != '') {
            $id = $request->id;
            $order = Order::whereId($id)->first();
        }
        return view('order.getmodel',compact('order','products'));
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

    //*******************************************************
    //Function Name : store
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function store(Request $request)
    {
        $input = $request->all();
        $first_product = Product::where('sku',$request->product_id)->first();
        if($input['item_quantity'] > $first_product->quantity){
            return array("status" => 400, "msg" => "Product is out of stoke.", "result" => array());
        }

        if (isset($request->orderid)) {
            $orderid = decrypt($request->orderid);
           // $rules['product_id'] = 'required' . $order_id;
          //  $rules['item_quantity'] = 'required|numeric,' . $order_id;
             $rules[] = array();
        }else{
            $rules['product_id'] = 'required';
            $rules['item_quantity'] = 'required|numeric';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
           $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {
            try {
                $input = $request->all();
                if (isset($request->orderid)) {
                    $order_id = decrypt($request->orderid);
                    $order = Order::find($order_id);
                    
                    $order->update($input);
                    $msg = 'Order updated successfully.';
                } else {
                    $first_product = Product::where('sku',$request->product_id)->first();

                    $order = new Order;
                    $input['order_id'] = get_order_number((rand(10,100)));
                    $input['order_date'] = Carbon::now();

                    $input['item_profit'] = ( $first_product->selling_price -  $first_product->buying_price ) * $input['item_quantity'] ;
                    $input['item_price'] = $first_product->selling_price ;
                    $input['sku_id'] = $request->product_id ;

                    $order::create($input);
                    $msg = 'Order added successfully.';
                }
                $arr = array("status"=>200,"msg"=>$msg);
            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;

                $arr = array("status" => 400, "msg" =>$msg, "result" => array());
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;
                $arr = array("status" => 400, "msg" =>$msg, "result" => array());
            }
        }

        return \Response::json($arr);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    //*******************************************************
    //Function Name : destroy
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function destroy($id)
    {
        try {
            Order::find($id)->delete();
            $arr = array("status" => 200, "msg" => 'success');
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = 'You can not delete this as related data are there in system.';
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        } catch (Exception $ex) {
            $msg = 'You can not delete this as related data are there in system.';
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        }

        return \Response::json($arr);
    }
}
