<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//******************************************************* START PRODUCT **************************************************************//
Route::get('product',['as' => 'product', 'uses' => 'ProductController@index']);
Route::resource('product','ProductController');
Route::group(['prefix' => 'product','as'=>'product.'], function () {
    Route::match(['get', 'post'],'getallproduct',['as'=>'getallproduct', 'uses'=> 'ProductController@getallproduct']);
    Route::post('getproductmodel',['as' => 'getproductmodel', 'uses' => 'ProductController@getproductmodel']);
});
//******************************************************* END PRODUCT **************************************************************//

//******************************************************* START ORDER **************************************************************//
Route::get('order',['as' => 'order', 'uses' => 'OrderController@index']);
Route::resource('order','OrderController');
Route::group(['prefix' => 'order','as'=>'order.'], function () {
    Route::match(['get', 'post'],'getallorder',['as'=>'getallorder', 'uses'=> 'OrderController@getallorder']);
    Route::post('getordermodel',['as' => 'getordermodel', 'uses' => 'OrderController@getordermodel']);
});
//******************************************************* END ORDER **************************************************************//

//******************************************************* START MERGE PRODUCT **************************************************************//
Route::resource('merge_product','MergeProductController');
Route::group(['prefix' => 'merge_product','as'=>'merge_product.'], function () {
    Route::match(['get', 'post'],'getallmergeproduct',['as'=>'getallmergeproduct', 'uses'=> 'MergeProductController@getallmergeproduct']);
    Route::post('getmergeproductmodel',['as' => 'getmergeproductmodel', 'uses' => 'MergeProductController@getmergeproductmodel']);
});
//******************************************************* END MERGE PRODUCT **************************************************************//

//******************************************************* START API **************************************************************//
Route::get('calculate_profit_of_order_item',['as' => 'calculate_profit_of_order_item', 'uses' => 'APIController@calculate_profit_of_order_item']);
Route::get('calculate_profit_of_order_item_data',['as' => 'calculate_profit_of_order_item_data', 'uses' => 'APIController@calculate_profit_of_order_item_data']);
Route::get('profit_order_item',['as' => 'profit_order_item', 'uses' => 'APIController@profit_order_item']);
//******************************************************* END API **************************************************************//

//******************************************************* PROFIT SELL HIGHEST **************************************************************//
Route::get('profit_index',['as' => 'profit_index', 'uses' => 'HighestProfitSellingController@profit_index']);
Route::get('selling_index',['as' => 'selling_index', 'uses' => 'HighestProfitSellingController@selling_index']);
Route::post('top_profit',['as' => 'top_profit', 'uses' => 'HighestProfitSellingController@top_profit']);
Route::post('top_selling',['as' => 'top_selling', 'uses' => 'HighestProfitSellingController@top_selling']);
//******************************************************* END PROFIT SELL HIGHEST **************************************************************//


//******************************************************* START TABLE **************************************************************//
Route::resource('table','TableController');
Route::group(['prefix' => 'table','as'=>'table.'], function () {
    Route::match(['get', 'post'],'gettable',['as'=>'gettable', 'uses'=> 'TableController@gettable']);
});
//******************************************************* END TABLE **************************************************************/