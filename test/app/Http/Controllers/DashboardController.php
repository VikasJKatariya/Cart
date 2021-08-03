<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Booking;
use App\Zone;
use App\AddressBook;
use App\Weight;
use App\Country;
use Validator;
use Twilio\Rest\Client;
use Twilio\Jwt\ClientToken;


class DashboardController extends Controller
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

    public function twilio()
    {
        $accountSid = 'AC7b04fc78126e81179b80ec866646d92b';
        $authToken  = 'c8e5e9bc998638af654d86845a373350';
        $client = new Client($accountSid, $authToken);
        try
        {
            // Use the client to do fun stuff like send text messages!
            $client->messages->create(
            // the number you'd like to send the message to
                '+917567434957',
            array(
                 // A Twilio phone number you purchased at twilio.com/console
                 'from' => '+17206198223',
                 // the body of the text message you'd like to send
                 'body' => 'Hey Ketav! It’s good to see you after long time!'
            )
         );
        }
        catch (Exception $e)
        {
            echo "Error: " . $e->getMessage();
        }
    }
    public function profilestore(Request $request){

        $input = $request->all();
        $rules = [
            'country_id'=>'required|exists:countries,id',
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email,' . Auth::user()->id,
            'company' => 'required',
            'imageuser' => 'nullable|mimes:jpeg,jpg,png,gif',
            'phone' => 'required',
            'address1' => 'required',
            'postal_code' => 'required',
            'city' => 'required',

        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
        } else {

            try {

                $user = User::find(Auth::user()->id);
                $user->name = $request->name;
                $user->lastname = $request->lastname;
                if ($request->hasFile('imageuser')) {
                    $destinationPath = public_path().'/images/users/';
                    $file = $request->imageuser;
                    $fileName = time() . '.'.$file->clientExtension();
                    $file->move($destinationPath, $fileName);
                    $user->image = $fileName;

                }



                if ($request->hasFile('logo_image_user')) {
                    $destinationPath = public_path().'/images/users_logos/';
                    $logofile = $request->logo_image_user;
                    $logofileName = time() . '.'.$logofile->clientExtension();
                    $logofile->move($destinationPath, $logofileName);
                    $user->logo_image = $logofileName;

                }
                $user->email = $request->email;
                unset($input['_token']);
                Auth::user()->userdetail()->update(['company'=>$input['company'],
                    'phone'=>$input['phone'],
                    'address1'=>$input['address1'],
                    'address2'=>$input['address2'],
                    'address3'=>$input['address3'],
                    'postal_code'=>$input['postal_code'],
                    'state'=>$input['state'],
                    'city'=>$input['city'],
                    'country_id'=>$input['country_id'],

                ]);
                $user->save();

                 $msg = 'successfully updated.';
                return redirect()->route('profile')->with('success', $msg);
            } catch ( \Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if(isset($ex->errorInfo[2])) {
                  $msg = $ex->errorInfo[2];
                }

                return redirect()->back()->withInput($request->all())->with('error', $msg);
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if(isset($ex->errorInfo[2])) {
                  $msg = $ex->errorInfo[2];
                }

                return redirect()->back()->withInput($request->all())->with('error', $msg);
            }
        }

    }
    /**
     * Display Dashboard page for UPX
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $agents = User::whereRole('agent')->count();
        $bookings = Booking::orderby('id','desc');
        $zones = Zone::count();
        $weights = Weight::count();
        if(Auth::user()->role == 'agent'){
           $bookings =  $bookings->where('booked_by',Auth::user()->id);
        }
        $bookings = $bookings->count();

        $addresses = AddressBook::orderby('id','desc')->where('created_by',Auth::user()->id)->count();


        $booking = Booking::withCount('dimentions')->orderby('id','desc');
        if(Auth::user()->role == 'agent'){
            $booking = $booking->whereBookedBy(Auth::user()->id);
        }
        $booking = $booking->take(5)->get();

        return view('upx.dashboard.index',compact('agents','bookings','zones','weights','addresses','booking'));
    }

    public function profile(){
        $countries = Country::get();
        return view('upx.profile.update',compact('countries'));
    }
    public function checkUniqueCodeNumber(Request $request){
        $input = $request->all();
        $user = User::where([['code_number',$input['code_number']],['role','agent'],['id','<>',$input['id']]])->first();
        if($user){
            $arr = array("status" => 200, "msg" => 'code number exist',);
        }else{
            $arr = array("status" => 400, "msg" => 'code number does not exist');
        }
        return \Response::json($arr);
    }


}
