<?php

namespace App\Http\Controllers;

use App\BookingAddress;
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
use Mail;
use App\Jobs\SendMailChangeStatus;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\toWords;
use App\Country;
use App\Service;
use App\ZoneCountry;
use Illuminate\Support\Facades\Crypt;

class BookingHistoryController extends Controller
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
        $users = User::get();
        $receivecountries = Country::whereIn('id', ZoneCountry::pluck('country_id')->toArray())->get();

        return view('upx.bookinghistory.index', compact('logstatus', 'users', 'receivecountries'));
    }


    /**
     * Change notification status as On off
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response json error or success
     */
    public function notifystatus(Request $request)
    {
        try {
            $user = Booking::find($request->bookingid)->update(['mail_notify' => $request->notify]);
            $arr = array("status" => 200, "msg" => 'success');
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

    public function download(Request $request)
    {


        $booking = Booking::withCount('dimentions')->with('createdby')->orderby('id', 'desc');

        $getstartdate = $request->startdate;
        $startdate = explode("GMT", $getstartdate);
        $chkstartdate = date('y-m-d', strtotime($startdate[0]));

        $getenddate = $request->enddate;
        $enddate = explode("GMT", $getenddate);
        $chkenddate = date('y-m-d', strtotime($enddate[0]));

        if (isset($request->statusid) && !empty($request->statusid)) {
            $booking->whereIn('current_status', $request->statusid);
        }

        if (isset($request->countries) && !empty($request->countries)) {
            $countries = $request->countries;
            $booking->wherehas('addressesreceiver', function ($q) use ($countries) {
                $q->whereIn('country_id', $countries);
            });
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Date');
        $sheet->getColumnDimension('A')->setAutoSize(true);

        $sheet->setCellValue('B1', 'Tracking Number');
        $sheet->getColumnDimension('B')->setAutoSize(true);

        $sheet->setCellValue('C1', 'Quantity');
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('D1', 'Weight');
        $sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->setCellValue('E1', 'Quoted');
        $sheet->getColumnDimension('E')->setAutoSize(true);

        $sheet->setCellValue('F1', 'Sender Name');
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $sheet->setCellValue('G1', 'Receiver Name');
        $sheet->getColumnDimension('G')->setAutoSize(true);

        $sheet->setCellValue('H1', 'Receiver Country');
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $sheet->setCellValue('I1', 'Booked By');
        $sheet->getColumnDimension('I')->setAutoSize(true);

        $sheet->setCellValue('J1', 'Booking By Name');
        $sheet->getColumnDimension('J')->setAutoSize(true);

        $sheet->setCellValue('K1', 'Package Type');
        $sheet->getColumnDimension('K')->setAutoSize(true);

        $sheet->setCellValue('L1', 'Current Status');
        $sheet->getColumnDimension('L')->setAutoSize(true);

        $sheet->setCellValue('M1', 'Payment Status');
        $sheet->getColumnDimension('M')->setAutoSize(true);


        $sheet->freezePaneByColumnAndRow(3, 2);
        if (!empty($booking)) {
            $i = 2;
            foreach ($booking as $book) {
                $sheet->setCellValue('A' . $i, date('d M Y', strtotime($book->created_at)));
                $sheet->setCellValue('B' . $i, $book->tracking_number);
                $sheet->setCellValue('C' . $i, $book->dimentions_count);
                $sheet->setCellValue('D' . $i, max($book->actual_weight, $book->volumetric_weight) . ' Kg');
                $sheet->setCellValue('E' . $i, $book->final_upx_price . ' £');
                $sheet->setCellValue('F' . $i, $book->addressessender->name . ' ' . $book->addressessender->lastname);
                $sheet->setCellValue('G' . $i, $book->addressesreceiver->name . ' ' . $book->addressesreceiver->lastname);
                $sheet->setCellValue('H' . $i, $book->addressesreceiver->country->name);
                $sheet->setCellValue('I' . $i, $book->createdby->role);
                $sheet->setCellValue('J' . $i, $book->createdby->name . ' ' . $book->createdby->lastname);
                $sheet->setCellValue('K' . $i, $book->package_type);
                $sheet->setCellValue('L' . $i, $book->logstatus->status);
                $sheet->setCellValue('M' . $i, $book->payment_status);

                $i++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Bookinghistory.xlsx"');
        $writer->save("php://output");


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
        $booking = Booking::with('addressesreceiver')->withCount('dimentions')->with('createdby')->orderby('id', 'desc');


        $getstartdate = $request->startdate;
        $startdate = explode("GMT", $getstartdate);
        $chkstartdate = date('y-m-d', strtotime($startdate[0]));

        $getenddate = $request->enddate;
        $enddate = explode("GMT", $getenddate);
        $chkenddate = date('y-m-d', strtotime($enddate[0]));

        if (isset($request->statusid) && !empty($request->statusid)) {
            $booking->whereIn('current_status', $request->statusid);
        }

        if (isset($request->countries) && !empty($request->countries)) {
            $countries = $request->countries;
            $booking->wherehas('addressesreceiver', function ($q) use ($countries) {
                $q->whereIn('country_id', $countries);
            });
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
            ->addColumn('notification', function ($q) {
                $check = '';
                if ($q->mail_notify == 1) {
                    $check = 'checked';
                }
                return '<label class="switch"><input data-bookingid="' . $q->id . '" class="changenotification" type="checkbox" ' . $check . '><span class="slider round"></span></label>';
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
            ->addColumn('receiver_country', function ($q) {
                return $q->addressesreceiver->country->name;
            })
            ->addColumn('booked_by', function ($q) {
                return $q->createdby->name . ' ' . $q->createdby->lastname;
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
                return '<a  href="' . route('invoice.view', ['id' => $q->id]) . '" target="_blank" class="btn btn-primary pull-right ' . $disabled . '" style="margin-right: 5px;">
            <i class="fa fa-download"></i> Invoice </a><br><br>
            <a  href="#" data-awbid="' . $q->id . '"  data-box_id="' . implode("|", $q->dimentions->pluck('box_number')->toArray()) . '"  class="btn btn-primary pull-right awbdownloadclass ' . $disabled . '" style="margin-right: 5px;">
            <i class="fa fa-download"></i> AWB
            <span class="bookingspin"></span></a>
            <a  href="'.url("public/agentinvoice").'/'.$q->agent_invoice.'" target="_blank" class="btn btn-primary pull-right ' . $disabled . '" style="margin-right: 5px; margin-top: 5px;">
            <i class="fa fa-download"></i> Agent Invoice </a>
            <a  href="#" data-bookingid="' . $q->id . '"  data-box_id="' . implode("|", $q->dimentions->pluck('box_number')->toArray()) . '"  class="btn btn-primary pull-right proformadownload ' . $disabled . '" style="margin-right: 5px; margin-top: 5px;">
            <i class="fa fa-download"></i> PRO FORMA INVOICE
            <span class="bookingspin"></span></a>';
                //' . route('invoice.view', ['id' => $q->id]) . '
                /*
                <a  href="' . route('agentinvoice.view', ['id' => $q->id]) . '" target="_blank" class="btn btn-primary pull-right ' . $disabled . '" style="margin-right: 5px; margin-top: 5px;">
            <i class="fa fa-download"></i> Agent Invoice </a>*/
            })
            ->addColumn('booking_status', function ($q) use ($logs) {
                $return = '<select class="form-control changestatus" data-id="' . $q->id . '">';
                $selected = '';
                if (!empty($logs)):
                    foreach ($logs as $log) {
                        if ($log->id == $q->current_status) {
                            $selected = 'selected';
                        }
                        $return .= '<option value="' . $log->id . '" ' . $selected . '>' . $log->status . '</option>';
                        $selected = '';
                    }
                endif;

                $return .= '</select>';

                return $return;
            })
            ->addColumn('action', function ($q) {
                $btn = '';
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
                    $btn = ' | <a class="cancel_booking" data-id="' . $q->id . '" data-status="' . $status . '" title="' . $title . '">' . $status_text . '</a>';
                }
                if ($q->booked_by == Auth::user()->id && $q->status != 'canceled') {
                    $btn .= ' | <a href="' . route('booking.edit', [$q->id]) . '" title="Edit Booking"><span class="fa fa-pencil"></span></a>';
                }
                if ($q->status != 'canceled') {
                    $btn .= ' | <a class="copy_booking" data-id="' . $q->id . '"  title="Copy Booking"><i class="fa fa-copy"></i></a>';
                }
                return '<a href="' . route('bookinghistory.show', ['id'=>$q->id,'service_id'=>$q->service_id,'country_id' => $q->addressesreceiver->country_id]) . '" title="View Booking"><span class="fa fa-eye"></span></a> ' . $btn;
            })
            ->addIndexColumn()
            ->rawColumns(['status', 'action', 'quantity_weight_quote', 'booking_status', 'invoice_download', 'awb_download', 'payable_amount', 'notification', 'payment_status'])->make(true);
    }

    public function updatepaymentstatus(Request $request)
    {

        $rules = [
            'payment_status' => 'in:paid,unpaid',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {

            try {
                $ids = json_decode($request->bookingid);
                if (!empty($ids) && is_array($ids)) {
                    Booking::whereIn('id', $ids)->update(['payment_status' => $request->payment_status, 'status' => 'confirmed']);
                }
                $arr = array("status" => 200, "msg" => 'Your Status is successfully updated.');
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
        }
        return \Response::json($arr);
    }

    public function updatetrackstatus(Request $request)
    {

        $rules = [
            'current_status' => 'exists:log_statuses,id',
        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {

            try {
                $ids = json_decode($request->bookingid);
                if (!empty($ids) && is_array($ids)) {
                    Booking::whereIn('id', $ids)->update(['current_status' => $request->current_status]);


                    dispatch(new SendMailChangeStatus($ids));

                    $logstatus = LogStatus::whereId($request->current_status)->first();
                    if (!empty($logstatus)) {
                        foreach ($ids as $id) {
                            BookingStatusLog::create(['booking_id' => $id, 'status' => $logstatus->status]);
                        }
                    }

                }


                $arr = array("status" => 200, "msg" => 'Your Status is successfully updated.');
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
        }
        return \Response::json($arr);
    }

    public function getstickyprice(Request $request)
    {
        $bookings = $bookingids = array();
        $total = 0.00;

        if (!empty($request->bookingid)) {
            $bookingids = $request->bookingid;
            $bookings = Booking::whereIn('id', $request->bookingid);
            //$total = $bookings->sum('final_agent_price');
            $total = $bookings->sum('final_total_agent');


        }
        $arr = array("status" => 200, "result" => $total);
        return \Response::json($arr);


    }

    public function checkhistory(Request $request)
    {
        $bookingid = $request->bookingid;
        $booking = Booking::whereId($bookingid)->first();
        return view('upx.bookinghistory.transactionhistory', compact('booking'));
    }

    public function changemultiplestatus(Request $request)
    {
        $bookings = $bookingids = array();
        $total = 0.00;

        if (!empty($request->bookingid)) {
            $bookingids = $request->bookingid;
            $bookings = Booking::whereIn('id', $request->bookingid);
            /* $total = $bookings->sum('final_agent_price');*/
            $total = $bookings->sum('final_total_agent');
            $bookings = $bookings->get();

        }
        $ids = json_encode($bookingids);

        return view('upx.bookinghistory.modelopen', compact('bookings', 'total', 'ids'));
    }

    public function changemultipletrack(Request $request)
    {
        $bookings = $bookingids = array();
        if (!empty($request->bookingid)) {
            $bookingids = $request->bookingid;
            $bookings = Booking::whereIn('id', $request->bookingid)->get();
        }
        $logstatus = LogStatus::get();
        $ids = json_encode($bookingids);
        return view('upx.bookinghistory.modelopentrack', compact('bookings', 'ids', 'logstatus'));
    }

    public function awb($id, $boxnumber = '')
    {

        $booking = Booking::withCount('dimentions')->with(['dimentions' => function ($q) use ($boxnumber) {
            $q->where('box_number', $boxnumber);
        }])->whereId($id)->first();

        $obj = new toWords($booking->final_upx_price, 'pounds', 'p');
        $qrcode = DNS1D::getBarcodePNG($booking->tracking_number, "C128");
        //$qrcode =  DNS1D::getBarcodeHTML($booking->tracking_number, "C128");
        //return view('upx.bookinghistory.awbdownload',compact('qrcode'));
        $data = ['booking' => $booking];
        $data['qrcode'] = $qrcode;
        $data['in_word'] = ucwords($obj->words);

        $pdf = PDF::loadView('upx.bookinghistory.awbdownload', $data);

        //return view('upx.bookinghistory.awbdownload');
        return $pdf->download($booking->dimentions[0]->box_number . '.pdf');


    }

    public function awbview($id, $boxnumber = '')
    {

        $booking = Booking::withCount('dimentions')->with(['dimentions' => function ($q) use ($boxnumber) {
            $q->where('box_number', $boxnumber);
        }])->whereId($id)->first();

        if(count($booking->dimentions) > 0){
            $obj = new toWords($booking->final_upx_price, 'pounds', 'p');
            $qrcode = DNS1D::getBarcodePNG($booking->tracking_number, "C128");
            //$qrcode =  DNS1D::getBarcodeHTML($booking->tracking_number, "C128");
            //return view('upx.bookinghistory.awbdownload',compact('qrcode'));
            $data = ['booking' => $booking];
            $data['qrcode'] = $qrcode;
            $data['in_word'] = ucwords($obj->words);


            $pdf = PDF::loadView('upx.bookinghistory.awbdownload', $data);

            // $pdf = PDF::loadView('upx.bookinghistory.awbdownload', $data)->setPaper('A5','landscape');
            //return view('upx.bookinghistory.awbdownload',$data);

            return $pdf->stream($booking->dimentions[0]->box_number . '.pdf')
                ->header('Content-Type', 'application/pdf');
        }else{
            return  view('upx.errors.404');
        }



    }

    public function invoiceview($id)
    {
        $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($id)->first();

        $logoimage = 'logo.png';
        if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
            //  $booking->createdby->logo_image = 'defaultinvoice.png';
        } else {
            $booking->createdby->logo_image = $booking->createdby->logo_image;
        }
        if ($booking->createdby->role == 'admin') {
            $booking->createdby->logo_image = 'logo.png';
        }

        $data = ['booking' => $booking, 'logoimage' => $logoimage];
        //return view('upx.bookinghistory.agentinvoice', $data);

        $pdf = PDF::loadView('upx.bookinghistory.invoicedownload', $data);
        if ($booking->service_id == 3) {
            $pdf = PDF::loadView('upx.bookinghistory.documentinvoice', $data);
        }
       // return view('upx.bookinghistory.invoicedownload', $data);
        if(auth()->user()->role == 'agent' &&  $booking->booked_by != auth()->user()->id ){
            return view('upx.errors.unauthorized');
        }
        return $pdf->stream($booking->tracking_number . 'invoice.pdf')
            ->header('Content-Type', 'application/pdf');
        // return $pdf->download($booking->tracking_number.'invoice.pdf');
    }


    public function invoice($id)
    {
        $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($id)->first();

        $logoimage = 'logo.png';
        if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
            $booking->createdby->logo_image = 'defaultinvoice.png';
        } else {
            $booking->createdby->logo_image = $booking->createdby->logo_image;
        }
        if ($booking->createdby->role == 'admin') {
            $booking->createdby->logo_image = 'logo.png';
        }
        /* echo '<pre>';
         print_r($booking->toArray());
         exit;*/

        $data = ['booking' => $booking, 'logoimage' => $logoimage];

        $pdf = PDF::loadView('upx.bookinghistory.invoicedownload', $data);

        //return view('upx.bookinghistory.invoicedownload');

        return $pdf->download($booking->tracking_number . 'invoice.pdf');
    }

    public function open($id)
    {
        $id = Crypt::decrypt($id);
        $booking = Booking::withCount('dimentions')->whereId($id)->first();
        /*echo '<pre>';
        print_r($booking->toArray());
        exit;*/
        $data = ['booking' => $booking];

        $pdf = PDF::loadView('upx.bookinghistory.invoicedownload', $data);

        //return view('upx.bookinghistory.invoicedownload');
        return $pdf->stream($booking->tracking_number . 'invoice.pdf');
        //return $pdf->download($booking->tracking_number.'invoice.pdf');
    }

    /**
     * Change Booking status as per status
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response send response in json
     */
    public function changestatus(Request $request)
    {

        try {


            $log = LogStatus::whereId($request->logid)->first();

            $booking = Booking::find($request->id);

            $booking->current_status = $request->logid;
            $booking->tracking_number = $request->tracking_num;
            $booking->booking_instruction = $request->booking_instruction;
            $booking->service_id = $request->service;



            $booking->save();
            $booking->load('addressessender', 'addressesreceiver', 'logstatus');

            sendnotification($booking);


            $newlog = new BookingStatusLog();
            $newlog->status = $log->status;

            $booking_receiver = Booking::with('addressesreceiver')->whereId($request->id)->first();
     
            send_sms_status($request->tracking_num,$request->logid,$booking_receiver->addressesreceiver->name.' '.$booking_receiver->addressesreceiver->lastname,$booking_receiver->addressesreceiver->phonenumber);

            $booking->logs()->save($newlog);
            $arr = array("status" => 200, "msg" => 'success');
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
     * Change Booking status modal
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response send response in json
     */
    public function changestatusmodal(Request $request)
    {
        $booking = Booking::find($request->id)->first();
        $logs = LogStatus::get();
        $logid = $request->logid;
        $services = Service::where('status', 'active')->get();

        return view('upx.bookinghistory.modalstatus', compact('booking','logs','logid','services'));
    }

    public function show($id,Request $request)
    {

        $timeline_data = '';
        $timeline = ZoneCountry::with('zone_data')->where([['country_id',$request->country_id],['service_id',$request->service_id]])->first();
        if(!empty($timeline)){
            $timeline_data = $timeline->zone_data->transit_time;
        }
        $booking = Booking::withCount('dimentions')->where('id', $id)->first();

        $view = 'upx.bookinghistory.invoiceview';
        if ($booking->service_id == 3) {
            $view = 'upx.bookinghistory.documentinvoiceview';
        }
        if(auth()->user()->role == 'agent' &&  $booking->booked_by != auth()->user()->id ){
            return view('upx.errors.unauthorized');
        }
        return view($view, compact('booking','timeline_data'));

    }

    public function uploaddocimage(Request $request)
    {
        try {

            $bookingid = $request->bookingid;
            $bookingaddress = BookingAddress::find($bookingid);
            if ($bookingaddress) {
                $id_doc_image = '';
                if ($request->hasFile('id_doc_image')) {
                    $destinationPath = public_path() . '/images/id_document/';
                    $file = $request->id_doc_image;
                    $id_doc_image = time() . '.' . $file->clientExtension();
                    $file->move($destinationPath, $id_doc_image);
                }
                $bookingaddress->id_doc_image = $id_doc_image;
                $bookingaddress->save();
            }
            $arr = array("status" => 200, "msg" => 'Document upload successfully.');
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

    public function cancelBooking(Request $request)
    {
        $input = $request->all();
        try {
            $booking = Booking::find($input['id']);
            $status = $input['status'];
            if ($booking) {
                Booking::where('id', $input['id'])->update(['status' => $status]);
            }
            $arr = array("status" => 200, "msg" => 'Booking ' . $status . ' Successfully.');
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
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sendinvoicemodel(Request $request)
    {
        $bookings = $bookingids = array();
        $total = 0.00;

        if (!empty($request->bookingid)) {
            $bookingids = $request->bookingid;
            $bookings = Booking::with(['createdby' => function ($query) {
                $query->where('role', 'agent');
            }])->whereIn('id', $request->bookingid);
            /* $total = $bookings->sum('final_agent_price');*/
            $total = $bookings->sum('final_total_agent');
            $bookings = $bookings->get();

        }
        // dd($bookings);
        $ids = json_encode($bookingids);
        return view('upx.bookinghistory.sendinvoicemodel', compact('bookings', 'total', 'ids'));
    }

    public function sendinvoicemail(Request $request)
    {

        $bookingids = json_decode($request->bookingid);
        $subject = $request->subject;
        $bodyMessage = 'Please find the attachment.';
        if (!empty($request->body)) {
            $bodyMessage = $request->body;
        }
        begin();
        try {

            $bookings = Booking::whereIn('id', $bookingids)->get();
            if (count($bookings) > 0) {
                foreach ($bookings as $booking) {
                    $agent = $booking->createdby;

                    if ($agent->role == 'agent') {
                        $agentemail = $agent->email;
                        $filename = public_path() . '/agentinvoice/' . $booking->agent_invoice;

                        $maildata = Mail::send('upx.mailtemplate.sendinvoice', array(
                            'email' => $agentemail,
                            'name' => $booking->createdby->name,
                            'bodyMessage' => $bodyMessage,
                            'data' => $booking
                        ), function ($message) use ($agentemail, $subject, $filename) {
                            //$message->to('sonal.ramdatti@gmail.com')
                            $message->to($agentemail)
                                ->subject($subject)
                                ->attach($filename, [
                                    'as' => 'File name',
                                    'mime' => 'application/pdf',
                                ]);;
                        });
                        if (Mail::failures()) {
                            // return response showing failed emails
                            $arr = array("status" => 200, "msg" => "Invoice send failed.", "result" => $maildata);
                        }
                    }
                }
                $arr = array("status" => 200, "msg" => "Invoice send successfully.", "result" => array());
            }
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

    /**
     * @param $id
     * @param string $boxnumber
     * @return mixed
     */
    public function proformaview($id, $boxnumber = '')
    {

        $booking = Booking::withCount('dimentions')->with(['dimentions' => function ($q) use ($boxnumber) {
            $q->where('box_number', $boxnumber);
        }])->whereId($id)->first();

        $obj = new toWords($booking->final_upx_price, 'pounds', 'p');
        $qrcode = DNS1D::getBarcodePNG($booking->tracking_number, "C128");
        //$qrcode =  DNS1D::getBarcodeHTML($booking->tracking_number, "C128");
      //  return view('upx.bookinghistory.proformainvoice',compact('qrcode'));
        $data = ['booking' => $booking];
        $data['qrcode'] = $qrcode;
        $data['in_word'] = ucwords($obj->words);
//dd($booking);
        $pdf = PDF::loadView('upx.bookinghistory.proformainvoice', $data);

        // $pdf = PDF::loadView('upx.bookinghistory.proformainvoice', $data)->setPaper('A5','landscape');
       // return view('upx.bookinghistory.proformainvoice',$data);

        return $pdf->stream($booking->dimentions[0]->box_number . '.pdf')
            ->header('Content-Type', 'application/pdf');


    }

    /**
     * @param $id
     * @return mixed
     * used for testing (view of upx to agent invoice )
     */
    public function agentinvoiceview($id)
    {
        $booking = Booking::withCount('dimentions')->with('createdby.userdetail')->whereId($id)->first();

        $logoimage = 'logo.png';
        if ($booking->createdby->logo_image == '' && $booking->createdby->role == 'agent') {
            //  $booking->createdby->logo_image = 'defaultinvoice.png';
        } else {
            $booking->createdby->logo_image = $booking->createdby->logo_image;
        }
        if ($booking->createdby->role == 'admin') {
            $booking->createdby->logo_image = 'logo.png';
        }

        $data = ['booking' => $booking, 'logoimage' => $logoimage];
        //return view('upx.bookinghistory.agentinvoice', $data);

        $pdf = PDF::loadView('upx.bookinghistory.agentinvoice', $data);
        if ($booking->service_id == 3) {
            $pdf = PDF::loadView('upx.bookinghistory.agentdocumentinvoice', $data);
        }
        // return view('upx.bookinghistory.invoicedownload', $data);
        return $pdf->stream($booking->tracking_number . 'invoice.pdf')
            ->header('Content-Type', 'application/pdf');
        // return $pdf->download($booking->tracking_number.'invoice.pdf');
    }

}
