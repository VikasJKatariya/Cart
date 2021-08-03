<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Booking;
use Auth;
use DataTables;
use App\LogStatus;
use App\BookingStatusLog;
use App\User;
use Carbon\Carbon;
use PDF;
use DNS1D;
use Validator;
use Session;
use Stripe;
use App\Payment;
use App\Wallet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BookingAgentHistoryController extends Controller
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
        $logstatus = LogStatus::get();

        return view('upx.bookingagenthistory.index', compact('logstatus'));
    }

    public function payment()
    {
        return view('upx.payment');
    }

    public function excel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Package Type');
        $sheet->setCellValue('C1', 'Quantity');
        $sheet->setCellValue('D1', 'Weight');
        $sheet->setCellValue('E1', 'Quoted');
        $sheet->setCellValue('F1', 'Tracking Number');
        $sheet->setCellValue('G1', 'Booked By');
        $sheet->setCellValue('H1', 'Current Status');
        $sheet->setCellValue('I1', 'Payment Status');


        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save("php://output");

    }

    public function payamount(Request $request)
    {
        begin();
        try {
            $ids = json_decode($request->bookingid);
            $total = 0.00;
            if (!empty($ids)) {
                $bookings = Booking::whereIn('id', $ids);
                //$total = $bookings->sum('final_agent_price');
                $total = $bookings->sum('final_total_agent');
            }
            $walletamount = 0.00;
            if ($request->checkbox == 1) {
                $walletamount = Auth::user()->wallet_amount;
                if ($walletamount < $total) {
                    $newamount = $total - $walletamount;
                    $total = round($newamount, 2);
                    Wallet::create(['agent_id' => Auth::user()->id, 'type' => 'reduce', 'amount' => $walletamount, 'current_amount' => 0.00, 'transation_token' => '-']);
                    User::find(Auth::user()->id)->update(['wallet_amount' => 0.00]);
                }
            }

            Stripe\Stripe::setApiKey(config('constants.STRIPE_SECRET'));
            Stripe\Charge::create([
                "amount" => $total * 100,
                "currency" => "GBP",
                "source" => $request->token,
                "description" => "payment from " . auth()->user()->name . ' ' . auth()->user()->lastname
            ]);

            Booking::whereIn('id', $ids)->update(['payment_status' => 'paid', 'status' => 'confirmed']);
            $paymentid = Payment::create(['agent_id' => Auth::user()->id, 'wallet_amout' => $walletamount, 'stripe_amount' => $total, 'final_amount' => $walletamount + $total, 'transation_id' => $request->token])->id;
            Payment::find($paymentid)->paymentbooking()->sync($ids);
            commit();
            $arr = array("status" => 200, "msg" => 'Your Payment is successfully done. Please check your Payment status.');

        } catch (\Stripe\Error\RateLimit $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        } catch (\Stripe\Error\InvalidRequest $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Invalid parameters were supplied to Stripe's API
        } catch (\Stripe\Error\Authentication $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Network communication with Stripe failed
        } catch (\Stripe\Error\Base $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Display a very generic error to the user, and maybe send
            // yourself an email
        } catch (\Illuminate\Database\QueryException $ex) {

            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        } catch (Exception $ex) {

            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        }
        return \Response::json($arr);
    }

    public function walletpayment(Request $request)
    {
        begin();
        try {
            if (is_array($request->bookingid)) {
                $ids = $request->bookingid;
            } else {
                $ids = json_decode($request->bookingid);
            }

            $total = 0.00;
            if (!empty($ids)) {
                $bookings = Booking::whereIn('id', $ids);
                $total = $bookings->sum('final_agent_price');
            }
            $currentamount = Auth::user()->wallet_amount - $total;
            Booking::whereIn('id', $ids)->update(['payment_status' => 'paid']);
            User::find(Auth::user()->id)->update(['wallet_amount' => $currentamount]);
            Wallet::create(['agent_id' => Auth::user()->id, 'type' => 'reduce', 'amount' => $total, 'current_amount' => $currentamount, 'transation_token' => '-']);
            $paymentid = Payment::create(['agent_id' => Auth::user()->id, 'wallet_amout' => $total, 'stripe_amount' => 0.00, 'final_amount' => $total, 'transation_id' => '-'])->id;
            Payment::find($paymentid)->paymentbooking()->sync($ids);
            commit();
            $arr = array("status" => 200, "msg" => 'Your Payment is successfully done. Please check your Payment status.');

        } catch (\Stripe\Error\RateLimit $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        } catch (\Stripe\Error\InvalidRequest $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Invalid parameters were supplied to Stripe's API
        } catch (\Stripe\Error\Authentication $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Network communication with Stripe failed
        } catch (\Stripe\Error\Base $ex) {
            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
            // Display a very generic error to the user, and maybe send
            // yourself an email
        } catch (\Illuminate\Database\QueryException $ex) {

            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        } catch (Exception $ex) {

            $msg = $ex->getMessage();
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            rollback();
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        }
        return \Response::json($arr);
    }

    public function balancefromwallet(Request $request)
    {


        try {

            $ids = $request->bookingid;
            $total = 0.00;
            if (!empty($ids)) {
                $total = Booking::whereIn('id', $ids)->sum('final_total_agent');
            }
            $walletamount = Auth::user()->wallet_amount;
            if ($request->checkbox == 1) {
                if ($walletamount < $total) {
                    $stripe = 1;
                    $newamount = $total - $walletamount;
                    $payable_amount = round($newamount, 2);
                } else {
                    $stripe = 0;
                    $payable_amount = $total;
                }
            }
            if ($request->checkbox == 0) {
                $stripe = 1;
                $payable_amount = $total;

            }


            $arr = array("status" => 200, 'stripe' => $stripe, 'payable_amount' => $payable_amount);
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

    public function changemultiplestatus(Request $request)
    {
        $bookings = $bookingids = array();
        $total = 0.00;

        if (!empty($request->bookingid)) {
            $bookingids = $request->bookingid;
            $bookings = Booking::whereIn('id', $request->bookingid);
            //  $total = $bookings->sum('final_agent_price');
            $total = $bookings->sum('final_total_agent');
            $bookings = $bookings->get();

        }
        $ids = json_encode($bookingids);

        return view('upx.bookingagenthistory.modelopen', compact('bookings', 'total', 'ids'));
    }

    /**
     * Get all History using datatable ajax request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request)
    {

        $logs = LogStatus::get();
        $booking = Booking::withCount('dimentions')->with('createdby', 'logstatus')->orderby('id', 'desc');

        $getstartdate = $request->startdate;
        $startdate = explode("GMT", $getstartdate);
        $chkstartdate = date('y-m-d', strtotime($startdate[0]));

        $getenddate = $request->enddate;
        $enddate = explode("GMT", $getenddate);
        $chkenddate = date('y-m-d', strtotime($enddate[0]));

        if (isset($request->statusid) && !empty($request->statusid)) {
            $booking->whereIn('current_status', $request->statusid);
        }

        if (isset($request->payment_status) && !empty($request->payment_status)) {
            $booking->where('payment_status', $request->payment_status);
        }

        if (isset($request->usertype) && !empty($request->usertype)) {
            $booking->whereIn('booked_by', $request->usertype);
        }

        if (isset($request->startdate) && !empty($request->startdate) && isset($request->enddate) && !empty($request->enddate)) {
            $booking->whereDate('created_at', '>=', Carbon::parse($chkstartdate)->toDateString())
                ->whereDate('created_at', '<=', Carbon::parse($chkenddate)->toDateString());
        }


        if (Auth::user()->role == 'agent') {
            $booking = $booking->whereBookedBy(Auth::user()->id);
        }
        $booking = $booking->get();

        return DataTables::of($booking)
            ->addColumn('current_status', function ($q) {
                return ($q->logstatus) ? $q->logstatus->status : '';
            })
            ->addColumn('payable_amount', function ($q) {
                if ($q->createdby->role == 'admin') {
                    return '-';
                } else {
                    return '&#163 ' . $q->final_total_agent . '<br><a href="" data-id="' . $q->id . '" class="checkhistory" data-toggle="modal" data-target=".transationhistory" ><span class="fa fa-eye"></span></a>';
                }

            })
            ->addColumn('payment_status', function ($q) {
                if ($q->payment_status == 'paid') {
                    return '<b style="color:green">' . $q->payment_status . '</b>';
                } else {
                    return '<b style="color:red">' . $q->payment_status . '</b>';
                }

            })
            ->addColumn('quantity_weight_quote', function ($q) {
                return $q->dimentions_count . '<br>' . max($q->actual_weight, $q->volumetric_weight) . ' Kg <br>&#163 ' . $q->final_total_upx;
            })
            ->addColumn('tracking_number', function ($q) {
                if($q->service_id == 1){
                    if($q->service_type == 'economy'){
                        $type = "Door to door(Economy)";
                    }else{
                        $type = "Door to door(Express)";
                    }
                }elseif ($q->service_id == 2) {
                    $type = "DHL";
                }else{
                    $type = "Document";
                }
                return $type.' '.$q->tracking_number;
            })
            ->addColumn('booking_date', function ($q) {
                return date('d M Y', strtotime($q->created_at));
            })
            ->addColumn('pstatus', function ($q) {
                return $q->status;
            })
            ->addColumn('paymentstatus', function ($q) {
                return $q->payment_status;
            })
            ->addColumn('status', function ($q) {
                $class = 'label-warning';
                if ($q->status == 'canceled') {
                    $class = 'label-danger';
                }
                if ($q->status == 'reopen') {
                    $class = 'label-primary';
                }
                if ($q->status == 'confirmed') {
                    $class = 'label-success';
                }
                return '<small class="label ' . $class . '">' . ucwords($q->status) . '</small>';
            })
            ->addColumn('invoice_download', function ($q) {
                $disabled = '';
                if ($q->status == 'canceled') {
                    $disabled = 'disabled';
                }
                return '<a href="' . route('invoice.view', ['id' => $q->id]) . '" target="_blank" class="btn btn-primary pull-right ' . $disabled . '" style="margin-right: 5px;">
                <i class="fa fa-download"></i> Invoice</a><br><br>
                <a href="#" data-awbid="' . $q->id . '"  data-box_id="' . implode("|", $q->dimentions->pluck('box_number')->toArray()) . '"  class="btn btn-primary pull-right awbdownloadclass ' . $disabled . '" style="margin-right: 5px;">
                <i class="fa fa-download"></i> AWB <span class="bookingspin"></span></a>';
            })
            ->addColumn('action', function ($q) {
                $btn = '';
                if ($q->payment_status == 'unpaid' && $q->booked_by == Auth::user()->id && $q->status != 'canceled') {
                    $btn .= ' | <a href="' . route('booking.edit', [$q->id]) . '" title="Edit Booking"><span class="fa fa-pencil"></span></a>';
                }
                if ($q->status == 'pending' || $q->status == 'reopen') {
                    $status = 'canceled';
                    $status_text = '<i class="fa fa-remove"></i>';
                    $title = 'Reopen Booking';
                    $title = 'Cancel Booking';
                } else if ($q->status == 'canceled') {
                    $status = 'reopen';
                    $status_text = '<i class="fa fa-refresh" aria-hidden="true"></i>';
                    $title = 'Reopen Booking';
                }
                if ($q->payment_status != 'paid' && in_array($q->status, ['pending', 'canceled', 'reopen'])) {
                    $btn .= ' | <a class="cancel_booking" data-id="' . $q->id . '" data-status="' . $status . '" title="' . $title . '">' . $status_text . '</a>';
                }
                if ($q->booked_by == Auth::user()->id && $q->status != 'canceled') {
                    $btn .= ' | <a class="copy_booking" data-id="' . $q->id . '"  title="Copy Booking"><i class="fa fa-copy"></i></a>';
                }
                return '<a href="' . route('bookinghistory.show', [$q->id]) . '" title="View Booking"><span class="fa fa-eye"></span></a> ' . $btn;
            })
            ->addIndexColumn()
            ->rawColumns(['status', 'action', 'quantity_weight_quote', 'booking_status', 'invoice_download', 'awb_download', 'payable_amount', 'notification', 'payment_status'])->make(true);
    }


}
