<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Setting;

class SettingController extends Controller
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

        return view('upx.setting.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $input = $request->all();
        
            try {

                if(!empty($request->setting)){
                    foreach ($request->setting as $key => $value) {
                        Setting::where('key',$key)->update(['value'=>$value]);
                    }
                }

                 $msg = 'successfully updated.';
                return redirect()->route('setting.index')->with('success', $msg);
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
