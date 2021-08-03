<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use DataTables;
use Validator;
class MergeProductController extends Controller
{
    //*******************************************************
    //Function Name : index
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************

    public function index()
    {
        $products = Product::whereNull('parent_id')->get();
        return view('merge_product.index',compact('products'));
    }

    //*******************************************************
    //Function Name : getallmergeproduct
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************

    public function getallmergeproduct(Request $request) {

        $products = Product::where('parent_id','!=', NULL)->get();

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
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    //*******************************************************
    //Function Name : getmergeproductmodel
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    public function getmergeproductmodel(Request $request)
    {        


        $product = array();
        $products = Product::whereNull('parent_id')->get();
        if(isset($request->id) && $request->id != '') {
            $id = $request->id;
            $product = Product::whereId($id)->first();
        }
        return view('merge_product.getmodel',compact('product','products'));
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
        $input = $request->all();

        if (isset($request->product_id)) {
            $product_id = $request->product_id;
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
                    $input['parent_id'] =  $request->product_sku;
                    $product->buying_price = $request->buying_price;
                    
                    $product->update($input);
                    $msg = 'Product updated successfully.';
                } else {


                    $product = new Product;
                    $product->title = $request->title;  
                    $product->parent_id = $request->product_sku;
                    $product->sku = random_code(6);
                    $product->quantity = $request->quantity;
                    $product->selling_price = $request->selling_price;
                    $product->buying_price = $request->buying_price;
                    $product->buying_price = $request->buying_price;
                    $product->save();
                    
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
