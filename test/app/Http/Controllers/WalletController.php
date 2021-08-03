<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Wallet;
use DataTables;
use Validator;
use Stripe;
use Auth;
use App\User;

class WalletController extends Controller
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

    public function index(){
    	return view('upx.wallet.index');
    }
    /**
     * Get all wallet datatable ajax request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request) {

        $wallets = Wallet::where('agent_id',Auth::id())->orderby('id','desc')->get();
       
        return DataTables::of($wallets)
       
        ->addColumn('date', function ($q) {
            return date('d M Y',strtotime($q->created_at));
        })
        ->addColumn('amount', function ($q) {
            if($q->type == 'add'){
            	return '<span style="color:green; font-weight:600; ">+ '.$q->amount.'</span>';
            }else{
            	return '<span style="color:red; font-weight:600; ">- '.$q->amount.'</span>';
            }
        })
        ->addColumn('wallet_balance', function ($q) {
            return '&#163; '.$q->current_amount;
        })
        ->addIndexColumn()
        ->rawColumns(['amount'])->make(true);
    }

    public function add(Request $request) {
    	
    	$rules = [
            'amountInCents' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
        } else {
            begin();
            try {
                $input = $request->all();
                
                Stripe\Stripe::setApiKey(config('constants.STRIPE_SECRET'));
		        Stripe\Charge::create ([
		                "amount" => $input['amountInCents'],
		                "currency" => "GBP",
		                "source" => $input['stripeToken'],
		                "description" => "payment from ".auth()->user()->name.' '.auth()->user()->lastname
		        ]);
		        $total_current  = Auth::user()->wallet_amount + $input['amountInCents']/100; 
		        
		       
               	User::whereId(Auth::user()->id)->update(['wallet_amount'=>$total_current]);

                Wallet::create(['agent_id'=>Auth::user()->id,'type'=>'add','amount'=>$input['amountInCents']/100,'current_amount'=>$total_current,'transation_token' => $input['stripeToken']]);
               	$msg ='Your amount &#163; '.($request->amountInCents/100).' is successfully added in your wallet.';
                commit();
                return redirect()->route('wallet.index')->with('success', $msg);
            }  catch (\Stripe\Error\RateLimit $ex) {
			   $msg = $ex->getMessage();
	            if (isset($ex->errorInfo[2])) {
	                $msg = $ex->errorInfo[2];
	            }
                rollback();
            	return redirect()->back()->withInput($request->all())->with('error', $msg);
			} catch (\Stripe\Error\InvalidRequest $ex) {
				$msg = $ex->getMessage();
	            if (isset($ex->errorInfo[2])) {
	                $msg = $ex->errorInfo[2];
	            }
                rollback();
            	return redirect()->back()->withInput($request->all())->with('error', $msg);
			  // Invalid parameters were supplied to Stripe's API
			} catch (\Stripe\Error\Authentication $ex) {
				$msg = $ex->getMessage();
	            if (isset($ex->errorInfo[2])) {
	                $msg = $ex->errorInfo[2];
	            }
                rollback();
            	return redirect()->back()->withInput($request->all())->with('error', $msg);
			  // Authentication with Stripe's API failed
			  // (maybe you changed API keys recently)
			} catch (\Stripe\Error\ApiConnection $ex) {
				$msg = $ex->getMessage();
	            if (isset($ex->errorInfo[2])) {
	                $msg = $ex->errorInfo[2];
	            }
                rollback();
            	return redirect()->back()->withInput($request->all())->with('error', $msg);
			  // Network communication with Stripe failed
			} catch (\Stripe\Error\Base $ex) {
				$msg = $ex->getMessage();
	            if (isset($ex->errorInfo[2])) {
	                $msg = $ex->errorInfo[2];
	            }
                rollback();
            	return redirect()->back()->withInput($request->all())->with('error', $msg);
			  // Display a very generic error to the user, and maybe send
			  // yourself an email
			
            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;
                rollback();
                return redirect()->back()->withInput($request->all())->with('error', $msg);
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;
                rollback();
                return redirect()->back()->withInput($request->all())->with('error', $msg);
            }
        }
    }
    
}
