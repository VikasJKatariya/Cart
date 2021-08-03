<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Auth;
class PublicAccessController extends Controller
{
    public function password($token){
        if(Auth::check()){
            Auth::logout();
        }
    	$decrypted = Crypt::decryptString($token);
    	$user = User::whereToken($decrypted)->first();
    	if(!empty($user)){

    		return view('auth.generatepassword',compact('token'));
    	}else{

    		$msg = "You've already changed your password.";
    		return redirect()->route('login')->with('error', $msg);
    	}
    }

     /**
     * Generate new password for new agents
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response If success redirect to login and if failed back
     */
    public function generatepw(Request $request) {


        $rules = [
            'password' => 'required|confirmed|min:6|max:30',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {

            return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
        } else {


            try {
                $input = $request->all();
                $decrypted = Crypt::decryptString($input['token']);
                $user = User::where('token',$decrypted)->first();

                if(!empty($user)){

                    $user->password = \Hash::make($input['password']);
                    $user->original_password = $input['password'];
                    $user->token = null;

                    $user->save();

                }

                $msg = 'Password Generated Successfully.';

                return redirect()->route('login')->with('success', $msg);

            } catch (\Illuminate\Database\QueryException $ex) {

                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;

                return redirect()->back()->withInput($request->all())->with('error', $msg);

            } catch (Exception $ex) {

                $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;

                return redirect()->back()->withInput($request->all())->with('error', $msg);

            } catch (DecryptException $ex) {
	            $msg = $ex->getMessage();
                if (isset($ex->errorInfo[2])) :
                    $msg = $ex->errorInfo[2];
                endif;

                return redirect()->back()->withInput($request->all())->with('error', $msg);
	        }
        }
    }
}
