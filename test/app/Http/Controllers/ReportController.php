<?php

namespace App\Http\Controllers;

use App\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

use DataTables;
use Illuminate\Support\Facades\Redirect;

class ReportController extends Controller
{
    public function index()
    {
        return view('upx.report.index');
    }
    public function reportprint(Request $request)
    {
        $booking = Booking::withCount('dimentions')->with(['addressessender'=> function($query){
            $query->with('country');
        }])->orderby('id', 'desc');
        $getstartdate = $request->startdate;
        $startdate = explode("GMT", $getstartdate);
        $chkstartdate = date('y-m-d', strtotime($startdate[0]));

        $getenddate = $request->enddate;
        $enddate = explode("GMT", $getenddate);
        $chkenddate = date('y-m-d', strtotime($enddate[0]));

        if (isset($request->startdate) && !empty($request->startdate) && isset($request->enddate) && !empty($request->enddate)) {
            $booking->whereDate('created_at', '>=', Carbon::parse($chkstartdate)->toDateString())
                ->whereDate('created_at', '<=', Carbon::parse($chkenddate)->toDateString());
            }
            $printreports = $booking->get();
            // return Redirect::view('upx.report.printreport', compact('printreports'));
            return view('upx.report.printreport', compact('printreports'));
    
            // echo "<pre>";
            // print_r($booking->toArray());
            // exit();
    }

    public function getall(Request $request)
    {
        $booking = Booking::withCount('dimentions')->with(['addressessender'=> function($query){
            $query->with('country');
        }])->orderby('id', 'desc');

        $getstartdate = $request->startdate;
        $startdate = explode("GMT", $getstartdate);
        $chkstartdate = date('y-m-d', strtotime($startdate[0]));

        $getenddate = $request->enddate;
        $enddate = explode("GMT", $getenddate);
        $chkenddate = date('y-m-d', strtotime($enddate[0]));

        if (isset($request->startdate) && !empty($request->startdate) && isset($request->enddate) && !empty($request->enddate)) {
            $booking->whereDate('created_at', '>=', Carbon::parse($chkstartdate)->toDateString())
                ->whereDate('created_at', '<=', Carbon::parse($chkenddate)->toDateString());
        }

        $booking = $booking->get();

        // echo "<pre>";
        // print_r($booking->toArray());
        // exit();
        
        return DataTables::of($booking)
        ->addColumn('address_1', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->address1;
            }else  return '-';
                
        })
        ->addColumn('address_2', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->address2 ?? '-';
            }else  return '-';
            
        })
        ->addColumn('address_3', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->address3 ?? '-';
            }else  return '-';
           
        })
        ->addColumn('city', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->city ?? '-';
            }else  return '-';
            
        })
        ->addColumn('state', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->state ?? '-';
            }else  return '-';
           
        })
        ->addColumn('post_code', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->postalcode ?? '-';
            }else  return '-';
            
        })
        ->addColumn('country', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->country->name ?? '-';
            }else  return '-';
           
        })
        ->addColumn('telephone', function ($q) {
            if (!empty($q->addressessender->type) && $q->addressessender->type !='') {
                return $q->addressessender->phonenumber ?? '-';
            }else  return '-';
            
        })
        ->addColumn('pieces', function ($q) {
            if (!empty($q->dimentions_count) && $q->dimentions_count !='') {
                return $q->dimentions_count ?? '-';
            }else  return '-';
        })
        ->addColumn('weight', function ($q) {
            if (!empty($q->actual_weight) && $q->actual_weight !='' && !empty($q->volumetric_weight) && $q->volumetric_weight !='') {
                return max($q->actual_weight, $q->volumetric_weight) ?? '-';
            }else  return '-';
        })
        ->addColumn('invoice_Value', function ($q) {
            if (!empty($q->final_total_upx) && $q->final_total_upx !='') {
                return max($q->final_total_upx, $q->final_total_upx) ?? '-';
            }else  return '-';
        })
        ->addColumn('bag_no', function ($q) {
            return '-' ?? '-';
        })
        ->addColumn('description', function ($q) {
            if (!empty($q->booking_instruction) && $q->booking_instruction !='') {
                return max($q->booking_instruction, $q->booking_instruction) ?? '-';
            }else  return '-';
        })
        ->addColumn('kyc', function ($q) {
            if (!empty($q->addressessender->id_type) && $q->addressessender->id_type !='') {
                return $q->addressessender->id_type ?? '-';
            }else  return '-';
        })
        ->addColumn('kyc_no', function ($q) {
            if (!empty($q->addressessender->id_number) && $q->addressessender->id_number !='') {
                return $q->addressessender->id_number ?? '-';
            }else  return '-';
        })

        ->addIndexColumn()
        ->rawColumns(['address_1', 'address_2', 'address_3', 'city', 'state', 'post_code', 'country', 'telephone', 'pieces', 'id_type', 'kyc_no'])->make(true);
        
    }
}
