<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use DataTables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use Validator;
use Mail;
use App\Country;
use App\UserDetail;
class AgentsController extends Controller
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
        return view('upx.agent.index');
    }

    /**
     * Get all agents using datatable ajax request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request) {

        $agents = User::with('unpaidbokings')->whereRole('agent')->orderby('id','desc')->get();

        return DataTables::of($agents)
        ->addColumn('action', function ($q) {
            return '<a  data-agent_id="'.$q->id.'" title="Edit Agent" data-toggle="modal" data-target=".modal_edit_list" class="openform"><i class="fa fa-pencil"></i></a> | <a class="delete_agents" title="Delete Agent" data-id="' . $q->id . '"><i class="fa fa-trash"></i></a>
                | <a class="change_password" data-toggle="modal" title="Change Password" data-target="#change_pass" data-id="' . $q->id . '"><i class="fa fa-key"></i></a>';
        })
        ->addColumn('image', function ($q) {

             $image = url('public/images/users/default.png');
              if(!empty($q->image) && $q->image !== null) :
                    $image = url('public/images/users/'.$q->image);
              endif;


            return '<img src="'.$image.'" style="width:50px; height:50px; border-radius:50%;">';
        })
        ->addColumn('name', function ($q) {
            return $q->name;
        })
        ->addColumn('lastname', function ($q) {
            return $q->lastname;
        })
        ->addColumn('email', function ($q) {
            return $q->email;
        })
        ->addColumn('password', function ($q) {
            return $q->original_password;
        })

        ->addColumn('role', function ($q) {
            return $q->role;
        })
        ->addColumn('unpaid_amount', function ($q) {
            $paybleamout = $q->unpaidbokings->sum('final_agent_price');
            if($paybleamout <= $q->booking_limit){
                return '<span style="font-weight:600; color:green;">&#163; '.$paybleamout.'</span>';
            }else{
                return '<span style="font-weight:600; color:red;">&#163; '.$paybleamout.'</span>';
            }

        })
        ->addColumn('booking_limit', function ($q) {

            return '&#163; '.$q->booking_limit;
        })
        ->addColumn('status', function ($q) {
            if ($q->status == 'Active') {
                return '<button type="button" class="btn btn-xs btn-circle btn-success changestatus" data-status="Inactive" data-id="' . $q->id . '">' . $q->status . '</button>';
            }
            if ($q->status == 'Inactive') {
                return '<button type="button" class="btn btn-xs btn-circle btn-danger changestatus"  data-status="Active"  data-id="' . $q->id . '">' . $q->status . '</button>';
            }
        })
        ->addIndexColumn()
        ->rawColumns(['status', 'action','image','unpaid_amount','booking_limit'])->make(true);
    }

     /**
     * Change status of agent active or inactive
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response send response in json
     */
    public function changestatus(Request $request) {

        try {
            $user = User::find($request->id)->update(['status'=>$request->status]);
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
     * Get model for add edit addressbook
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getmodel(Request $request)
    {

        $agent = array();
        $countries = Country::get();
        if(isset($request->agent_id) && $request->agent_id != '') {
            $agent = User::whereId($request->agent_id)->first();
        }
        return view('upx.agent.modelopen',compact('agent','countries'));
    }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('upx.agent.create', compact('agent'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {


        $rules = [
            'name' => 'required|max:30',
            'lastname' => 'required|max:30',
            'status'=>'in:Active,Inactive',
            'booking_limit'=>'required|max:8',
            'company_no'=>'required',
            'vat_number'=>'required',
            'code_number'=>'required|numeric|min:2'
        ];

        $message = [
            'name.required' => 'The First Name field is required.',
            'lastname.required' => 'The Last Name field is required.',

        ];


        if (isset($request->agent_id)) {
            $rules['email'] = 'required|email|unique:users,email,' . $request->agent_id;
            $rules['code_number'] = 'required|unique:users,code_number,' . $request->agent_id;

        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['code_number'] = 'required|unique:users,code_number';
        }
        $validator = Validator::make($request->all(), $rules,$message);
        if ($validator->fails()) {
           $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {
            begin();
            try {
                $input = $request->all();

                $input['role'] = 'agent';
               if ($request->hasFile('imageuser')) {
                        $destinationPath = public_path().'/images/users/';
                        $file = $request->imageuser;
                        $fileName = time() . '.'.$file->clientExtension();
                        $file->move($destinationPath, $fileName);
                        $input['image'] = $fileName;
                    }

                if ($request->hasFile('logo_image_user')) {
                    $destinationPath = public_path().'/images/users_logos/';
                    $logofile = $request->logo_image_user;
                    $logofileName = time() . '.'.$logofile->clientExtension();
                    $logofile->move($destinationPath, $logofileName);
                    $input['logo_image'] = $logofileName;
                }
                if (isset($request->agent_id)) {



                    $user = User::find($request->agent_id);
                    $user->update($input);
                    $user->userdetail()->update(['company'=>$input['company'],
                    'phone'=>$input['phone'],
                    'address1'=>$input['address1'],
                    'address2'=>$input['address2'],
                    'address3'=>$input['address3'],
                    'postal_code'=>$input['postal_code'],
                    'state'=>$input['state'],
                    'city'=>$input['city'],
                    'country_id'=>$input['country_id'],

                ]);

                    $msg = 'Agent updated successfully.';
                } else {

                    $pw_token = rand();
                    $encrypted = Crypt::encryptString($pw_token);
                    $input['token'] = $pw_token;
                    $input['password'] = '';
                    $input['original_password'] = '';

                    $user = new User;
                    $userid = $user->create($input)->id;

                    $udetail = new UserDetail;
                    $udetail->user_id = $userid;
                    $udetail->company = $input['company'];
                    $udetail->phone = $input['phone'];
                    $udetail->address1 = $input['address1'];
                    $udetail->address2 = $input['address2'];
                    $udetail->address3 = $input['address3'];
                    $udetail->postal_code = $input['postal_code'];
                    $udetail->state = $input['state'];




                    $udetail->city = $input['city'];
                    $udetail->country_id = $input['country_id'];
                    $udetail->save();


                    $email = $request->email;
                    $url = url('upx/agent/password').'/'.$encrypted;
                    Mail::send('upx.mailtemplate.welcome_agent', array(
                        'name'    => $request->name.' '.$request->lastname,
                        'email'         => $email,
                        'url'=>$url
                    ), function ($message) use ($email) {
                        $message->to($email)->subject('Agent Register');
                    });

                    $msg = 'Agent added successfully.';
                }

                $newarray = array();

                commit();
                 $arr = array("status"=>200,"msg"=>$msg);
            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;
                rollback();
                $arr = array("status" => 400, "msg" =>$msg, "result" => array());
            } catch (Exception $ex) {
                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;
                rollback();
                $arr = array("status" => 400, "msg" =>$msg, "result" => array());
            }
        }

        return \Response::json($arr);
    }

    /**
     * show the specified content
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        try {
            $id = $request->id;
            if($id != Null){

                $agent = User::where('id',$id)->first(['id','original_password']);
                return view('upx.agent.changepassword',compact('agent'));

            }
            $arr = array("status" => 400, "msg" => 'User not found!', "result" => array());
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = 'oops!Something went wrong';
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        }
        return \Response::json($arr);
    }

    /**
     * show the specified content
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $rules = [
            'password' => 'required|min:6',
            'password_confirmation' => 'required_with:password|same:password|min:6'
        ];
        $message = [
            'password_confirmation.required_with' => 'The password confirmation field is required',
            'password_confirmation.same' => 'The password and confirm password field must be same'
        ];
        $validator = Validator::make($request->all(), $rules,$message);
        if ($validator->fails()) {
            $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {

            try {
                $input = $request->all();
                $user = User::find($input['agentid']);
                $user->original_password = $input['password'];
                $user->password = Hash::make($input['password']);
                $user->update();
                $arr = array("status" => 200, "msg" => 'Password has been successfully updated.', "result" => array());
            } catch (\Illuminate\Database\QueryException $ex) {
                $msg = 'oops!Something went wrong';
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                }
                $arr = array("status" => 400, "msg" => $msg, "result" => array());
            }
        }
        return \Response::json($arr);
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            User::find($id)->delete();
            $arr = array("status" => 200, "msg" => 'success');
        } catch (\Illuminate\Database\QueryException $ex) {
            $msg = 'You can not delete this as related data are there in system.';
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        } catch (Exception $ex) {
            $msg = 'You can not delete this as related data are there in system.';
            if (isset($ex->errorInfo[2])) {
                $msg = $ex->errorInfo[2];
            }
            $arr = array("status" => 400, "msg" => $msg, "result" => array());
        }
        return \Response::json($arr);
    }
}
