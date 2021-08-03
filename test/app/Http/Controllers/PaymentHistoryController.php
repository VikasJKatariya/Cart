<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Payment;
use Auth;
use DataTables;

class PaymentHistoryController extends Controller
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
        return view('upx.paymenthistory.index');
    }


    public function readmultiple(Request $request)
    {

        try {
            if(!empty($request->paymentids)){
                if(Auth::user()->role == 'admin'){
                    Payment::whereIn('id',$request->paymentids)->update(['admin_read_payment'=>'1']);

                }else{
                    Payment::whereIn('id',$request->paymentids)->update(['read_payment'=>'1']);

                }

            }
              if(auth()->user()->role == 'agent'){
                $count =  auth()->user()->unreadpayment()->count();
              }else{
                $count =  CountPaymentUnreadAdmin();
              }

            $arr = array("status" => 200, "msg" => 'Successfully deleted.','count'=>$count);
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

    /**
     * Get all Payment history
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getpaymentdetail(Request $request) {

    	$paymentid = $request->paymentid;
        if(Auth::user()->role == 'admin'){
            Payment::whereId($paymentid)->update(['admin_read_payment'=>'1']);
        }else{
            Payment::whereId($paymentid)->update(['read_payment'=>'1']);
        }
    	$payment = Payment::whereId($paymentid)->first();
        if(auth()->user()->role == 'agent'){
            $count =  auth()->user()->unreadpayment()->count();
          }else{
            $count =  CountPaymentUnreadAdmin();
          }
          $viewpayment = view('upx.paymenthistory.modelopen',compact('payment'))->render();
          $return = array('count'=>$count,'view'=>$viewpayment);
    	   return \Response::json($return);
    }
    /**
     * Get all Payment history
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request) {

        $agents = Payment::with('paymentbooking')->orderby('id','desc');
        if(Auth::user()->role == 'agent')
        {
        	$agents =  $agents->whereAgentId(Auth::user()->id);
        }
        $agents =  $agents->get();



        return DataTables::of($agents)
        ->addColumn('action', function ($q) {
            return '<a href="" data-id="'.$q->id.'" class="checkhistory" data-toggle="modal" data-target=".transationhistory" title="View Payment History" ><span class="fa fa-eye"></span></a>';
        })
        ->addColumn('payment_by', function ($q) {
            return $q->agent->name.' '.$q->agent->lastname;
        })
        ->addColumn('amount', function ($q) {
            return '&#163 '.$q->final_amount;
        })
        ->addColumn('tracknumbers', function ($q) {
        	$trackings = $q->paymentbooking->pluck('tracking_number')->toArray();
            return implode('<br>', $trackings);
        })
        ->addColumn('date', function ($q) {
            return $q->created_at;
        })
        ->addIndexColumn()
        ->setRowClass(function ($q) {
            if(Auth::user()->role == 'admin'){
                return $q->admin_read_payment == 0 ? 'unread' : '';
            }else{
               return $q->read_payment == 0 ? 'unread' : '';
            }

        })
        ->rawColumns(['amount', 'action','tracknumbers'])->make(true);
    }

}
