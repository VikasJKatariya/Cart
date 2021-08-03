<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use DataTables;
use App\Order;
use Validator;
class ProductController extends Controller
{
    //*******************************************************
    //Function Name : index
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function index()
    {
        return view('product.index');
    }

    //*******************************************************
    //Function Name : getallproduct
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function getallproduct(Request $request) {

        $products = Product::all();
        return DataTables::of($products)
        ->addColumn('action', function ($q) {
            return '<a class="openform" data-toggle="modal" title="Edit Product" data-target=".modal_edit_list" data-id="' . $q->id . '"><i class="fa fa-edit"></i></a>  <a class="delete_product" title="Delete product" data-id="' . $q->id . '"><i class="fa fa-trash"></i></a>';
        })
        ->addColumn('sku', function ($q) {
            return $q->sku ;
        })
        ->addColumn('title', function ($q) {
            return $q->title ;
        })
        ->addColumn('quantity', function ($q) {
            return $q->quantity ;
        })
        ->addColumn('selling_price', function ($q) {
            return $q->selling_price ;
        })
        ->addColumn('buying_price', function ($q) {
            return $q->buying_price ;
        })
        ->addColumn('created_at', function ($q) {
            return date('d-m-Y', strtotime($q->created_at));
        })
        ->addIndexColumn()
        ->rawColumns(['action'])->make(true);
    }

    //*******************************************************
    //Function Name : getproductmodel
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************

    public function getproductmodel(Request $request)
    {        
        $product = array();
        if(isset($request->id) && $request->id != '') {
            $id = $request->id;
            $product = Product::whereId($id)->first();
        }
        return view('product.getmodel',compact('product'));
    }
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

        if (isset($request->product_id)) {
            $product_id = decrypt($request->product_id);
            $rules['title'] = 'required|unique:products,title,' . $product_id;
            $rules['selling_price'] = 'required|gt:buying_price,' . $product_id;
            $rules['buying_price'] = 'required|lt:selling_price,' . $product_id;
            // $rules[] = array();
        }else{
            $rules['title'] = 'required|unique:products,title';
            $rules['buying_price'] = 'required|numeric|lt:selling_price';
            $rules['selling_price'] = 'required|numeric|gt:buying_price';
            $rules['quantity'] = 'required|numeric';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
           $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {
            try {
                $input = $request->all();
                if (isset($request->product_id)) {
                    $product_id = decrypt($request->product_id);
                    $product = Product::find($product_id);
                    $product->update($input);
                    $msg = 'Product updated successfully.';
                } else {
                    $product = new Product;
                    $input['sku'] = random_code(6);
                    $product::create($input);
                    $msg = 'Product added successfully.';
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
            Product::find($id)->delete();
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
