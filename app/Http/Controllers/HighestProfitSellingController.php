<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Product;
use App\Order;
use DataTables;
use Carbon\Carbon;
class HighestProfitSellingController extends Controller
{

    //*******************************************************
    //Function Name : profit_index
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************
    
    public function profit_index()
    {
        $products = Product::all();
        return view('top_profit.index');
    }

    //*******************************************************
    //Function Name : selling_index
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************

    public function selling_index()
    {
        $products = Product::all();
        return view('top_selling.index');
    }

    //*******************************************************
    //Function Name : top_profit
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************

    public function top_profit(Request $request) {
        if(isset($request->product_search) && $request->product_search != ''){
            if($request->product_search == 'all_product'){
                $profit  = Order::with('product')->selectRaw('sku_id,id,order_id,order_date, sum(orders.item_profit) as sum')->groupBy('sku_id')->orderBy('sum','DESC')->whereMonth(
                'created_at', '=', Carbon::now()->subMonth()->month)->get();
            }else if($request->product_search == 'parent_product'){
                    $profit  =  Order::with(array('product' => function($query) {
                        $query->whereNull('parent_id');
                    }))->selectRaw('sku_id,id,order_id,order_date, sum(orders.item_profit) as sum')->groupBy('sku_id')->orderBy('sum','DESC')->whereMonth(
                'created_at', '=', Carbon::now()->subMonth()->month)->get();
            }
        }else{
             $profit  = Order::with('product')->selectRaw('sku_id,id,order_id,order_date, sum(orders.item_profit) as sum')->groupBy('sku_id')->orderBy('sum','DESC')->whereMonth(
                'created_at', '=', Carbon::now()->subMonth()->month)->limit(5)->get();
        }
        

        return DataTables::of($profit)
        ->addColumn('sku', function ($q) {
            return $q->sku_id;
        })
        ->addColumn('title', function ($q) {
            return $q->product->title;
        })
        ->addColumn('order_id', function ($q) {
            return $q->order_id ;
        })
        ->addColumn('order_date', function ($q) {
            return $q->order_date ;
        })
        ->addColumn('sum', function ($q) {
            return $q->sum;
        })
        ->addIndexColumn()
        ->rawColumns([''])->make(true);
    }

    //*******************************************************
    //Function Name : top_selling
    //Author : Vikas Katariya
    //date : 12-07-2021
    //*******************************************************

    public function top_selling(Request $request) {

        $profit  = Order::with('product')->selectRaw('sku_id,id,order_id,order_date, sum(orders.item_quantity) as item_quantity')->groupBy('sku_id')->orderBy('item_quantity','DESC')->limit(5)->get();

        return DataTables::of($profit)
        ->addColumn('sku', function ($q) {
            return $q->sku_id;
        })
        ->addColumn('title', function ($q) {
            return $q->product->title;
        })
        ->addColumn('order_id', function ($q) {
            return $q->order_id ;
        })
        ->addColumn('order_date', function ($q) {
            return $q->order_date ;
        })
        ->addColumn('item_quantity', function ($q) {
            return $q->item_quantity;
        })
        ->addIndexColumn()
        ->rawColumns([''])->make(true);
    }
}
