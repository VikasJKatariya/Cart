<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ZoneCountry;
use App\Weight;
use App\Booking;
use Validator;
use App\ContactUs;
use App\Inquiry;
use App\User;
use Mail;

class FrontPageManagementController extends Controller
{
    public function index()
    {
        return view('front.home.index');
    }

    public function quote()
    {
        return view('front.quote.index');
    }

    public function track($id = '')
    {
        $booking = array();
        if ($id != '') {
            $booking = Booking::withCount('dimentions')->whereTrackingNumber($id)->first();

        }

        return view('front.track.index', compact('id', 'booking'));
    }

    public function contact()
    {
        return view('front.contact.index');
    }

    public function aboutus()
    {
        return view('front.aboutus.index');
    }

     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service sea_freight page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function sea_freight()
    {
        return view('front.services.sea_freight');
    }

     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service air_freight page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function air_freight()
    {
        return view('front.services.air_freight');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service road_freight page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function road_freight()
    {
        return view('front.services.road_freight');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service storage_and_warehousing page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function storage_and_warehousing()
    {
        return view('front.services.storage_and_warehousing');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service customs_clearance page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function customs_clearance()
    {
        return view('front.services.customs_clearance');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service door_to_door page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function door_to_door()
    {
        return view('front.services.door_to_door');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service d2d_India page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function d2d_India()
    {
        return view('front.services.d2d_India');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service d2d_pakistan page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function d2d_pakistan()
    {
        return view('front.services.d2d_pakistan');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service d2d_tanzania page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function d2d_tanzania()
    {
        return view('front.services.d2d_tanzania');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service d2d_kenya page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function d2d_kenya()
    {
        return view('front.services.d2d_kenya');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service d2d_malawi page
     * Developed by Vishal Mandora on 08-07-2020
     */
    public function d2d_malawi()
    {
        return view('front.services.d2d_malawi');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service parcels_courier page
     * Developed by Vishal Mandora on 06-10-2020
     */
    public function parcels_courier()
    {
        return view('front.services.parcels_courier');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service parcels_courier page
     * Developed by Vishal Mandora on 06-10-2020
     */
    public function d2d_srilanka()
    {
        return view('front.services.d2d_srilanka');
    }
     /**
     * @param \Illuminate\Http\Request void
     * @return \Illuminate\View\View
     * Return Service parcels_courier page
     * Developed by Vishal Mandora on 06-10-2020
     */
    public function d2d_bangladesh()
    {
        return view('front.services.d2d_bangladesh');
    }

    public function trackwithnumber(Request $request)
    {

        $tnumber = $request->tract_number;
        return redirect('track/' . $tnumber);
    }


    public function submit(Request $request)
    {


        $rules = [
            'name' => 'required',
            'email' => 'required',
            'message' => 'required',

        ];


        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {

            try {
                $input = $request->all();
                ContactUs::create($input);
                $msg = ' We will be in touch with you shortly.';
                $email = $request->email;
                $name = $request->name;
                $sub = $request->message;


                Mail::send('upx.mailtemplate.contact_us', array(
                    'name' => $name,
                    'sub' => $sub,

                ), function ($message) use ($email) {
                    $message->to($email)->subject('UPX Website Contact Form');
                });
                $arr = array("status" => 200, "msg" => $msg);
            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;

                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;

                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            }
        }

        return \Response::json($arr);
    }

    public function getprice(Request $request)
    {

        try {

            $input = $request->all();
            $timeline_data = '';
            $timeline = ZoneCountry::with('zone_data')->where([['country_id',$request->country],['service_id',$request->service]])->first();
            if(!empty($timeline)){
                $timeline_data = $timeline->zone_data->transit_time;
            }
            $valumetric = $volumnmetricweight = $actualweight = 0;
            if (!empty($input['length']) && !empty($input['width']) && !empty($input['height'])) {
                $valumetric = ($input['length'] * $input['width'] * $input['height']) / 6000;
                $volumnmetricweight = GetRoundByFraction(round($valumetric));

            }
            $actual = $input['weight'];
            if($actual > 0){
                $actualweight = GetRoundByFraction($actual);
            }

            $service = $input['service'];

            $service_type = $input['service_type'];

            if ($service == 3) {
                $volumnmetricweight = $actualweight = $input['document_service_type'];
                $service_type = $input['document_service_type'];
            }
            $zone = ZoneCountry::where(['country_id' => $input['country'], 'service_id' => $service])->first();
            $zoneid = 0;
            if ($zone) {
                $zoneid = $zone->zone_id;
            }


            $systemvalue = 1.00;
            $selectedweight = Weight::where('weight', '>=', $systemvalue)->orderby('weight', 'asc')->first();
            $weightid = $selectedweight->id;

            $maxvalue = max($volumnmetricweight, $actualweight);

            if ($service == 1) {
                //$upxprice = getmyprice($weightid, $zoneid, 'upx_price','0',$service_type,$service);
                $upxprice = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $service_type, 'price');
                $handling_price = getmypricedoortodoor($weightid, $zoneid, 'upx_price', 0, $service_type, 'handling');
            }
            if ($service == 2) {
                $upxprice = getmypricedhl($zoneid, 'upx_price', 0, 'price');
                $handling_price = getmypricedhl($zoneid, 'upx_price', 0, 'handling');
            }
            if ($service == 3) {
                //$upxprice = getmydocumentprice($zoneid, $input['document_service_type'], $service,'upx_price');
                $upxprice = getmydocumentpricedocument($zoneid, $input['document_service_type'], 'upx_price', 0, 'price');
                $handling_price = getmydocumentpricedocument($zoneid, $input['document_service_type'], 'upx_price', 0, 'handling');
            }
            if ($maxvalue <= 5) {
                $upxprice = $upxprice * 5;

            } else {
                $upxprice = $upxprice * $maxvalue;
            }
            $finalprice = 0;
            if($maxvalue > 0){
                $finalprice = $upxprice + $handling_price;
            }
            if($service == 3){
                $finalprice = $upxprice + $handling_price;
            }

            $countries = GetCountries($service);
          //  $arr = array('status' => 200, 'weight' => $maxvalue, 'price' => round($upxprice, 2));
            $arr = array('status' => 200, 'weight' => $maxvalue, 'price' => round($finalprice, 2), 'countries'=>$countries , 'timeline' => $timeline_data);
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

    public function inquiry(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'contactno' => 'required',
            'email' => 'required',
            'shipper_address' => 'required',
            'length'=> 'required_if:service,in:1,2',
            'width' => 'required_if:service,in:1,2',
            'height' => 'required_if:service,in:1,2',
            'weight' => 'required_if:service,in:1,2',
            'country' => 'required',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {
            begin();
            try {

                $inquiry = new Inquiry();
                $service_type = $request->service_type;
                if($request->service == 3){
                    $service_type = $request->document_service_type;
                }
                $input['service_type'] = $service_type;
                $data= $inquiry->fill($input)->save();
                //dd($data);
                if ($data) {
                    $email = $request->email;
                    $user = User::where('role', 'admin')->first();
                    $adminname = $user->name . ' ' . $user->lastname;
                    $adminemail = config('app.app_email');
                    $data = Inquiry::where('email',$input['email'])->orderby('id','desc')->first();

                    Mail::send('upx.mailtemplate.inquiry', array(
                        'name' => $adminname,
                        'data' => $data
                    ), function ($message) use ($adminemail, $email) {
                        $message->from($email);
                        $message->to($adminemail)->subject('New inquiry');
                    });
                    commit();
                    if (Mail::failures()) {
                        rollback();
                        $arr = array('status' => 400, 'msg' => "Inquiry send failed, please try again!");
                    }else{
                        $arr = array('status' => 200, 'msg' => "Thank you for your booking. One of our colleagues will call you within 24 hours to finalise your booking.");
                    }

                } else {
                    $arr = array('status' => 400, 'msg' => "Inquiry send failed, please try again!");
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
        }
        return \Response::json($arr);
    }

}
