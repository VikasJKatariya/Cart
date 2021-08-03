<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Weight;
use DataTables;
use Validator;

class WeightController extends Controller
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
        return view('upx.weight.index');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('upx.weight.create');
    }


    /**
     * Get all weight using datatable ajax request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request) {

        $agents = Weight::orderby('weight','asc')->get();

        return DataTables::of($agents)
        ->addColumn('action', function ($q) {
            return '<a  data-wight_id="'.$q->id.'" data-toggle="modal" data-target=".modal_edit_list" class="openform"><i class="fa fa-pencil"></i></a> | <a class="delete_weight" data-id="' . $q->id . '"><i class="fa fa-trash"></i></a>';
        }) 
        ->addIndexColumn()
        ->rawColumns(['status', 'action'])->make(true);
    }


    /**
     * Get model for add edit addressbook
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getmodel(Request $request)
    {
        

        $weight = Weight::whereId($request->wight_id)->first();
        return view('upx.weight.modelopen',compact('weight'));
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
        $input = $request->all();
        $rules = [
            "weight"  => 'numeric|required|unique:weights,weight,'.$id,
        ];
         $validator = Validator::make($input, $rules);
        if($validator->fails()) {
          $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {
            try {
            $msg = 'successfullt updated.';
            Weight::find($id)->update($input); 
            $arr = array("status"=>200,"msg"=>$msg);
          } catch ( \Illuminate\Database\QueryException $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }
           
            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          } catch (Exception $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }
            
            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          }
        }
        return \Response::json($arr);
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

        $input['weight'] = array_unique(array_filter($input['weight']));

        $rules = [
            "weight"  => 'required|array|min:1',
            "weight.*" => 'numeric|unique:weights,weight'
        ];

       $message = [
            "weight.*.numeric" => "All Weight must be a decimal number."
        ];

        $validator = Validator::make($input, $rules,$message);
       
        if($validator->fails()) {
          $arr = array("status"=>400,"msg"=>$validator->errors()->first(),"result"=>array());
        } else {

         
          try {
            $msg = 'success';
            if(!empty($input['weight'])){
                foreach ($input['weight'] as $weight) {
                    Weight::create(['weight'=>$weight]);
                }
            }
            
            $arr = array("status"=>200,"msg"=>$msg);
          } catch ( \Illuminate\Database\QueryException $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }
           
            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          } catch (Exception $ex) {
            $msg = $ex->getMessage();
            if(isset($ex->errorInfo[2])) {
              $msg = $ex->errorInfo[2];
            }
            
            $arr = array("status" => 400, "msg" =>$msg, "result" => array());
          }
        }

        return \Response::json($arr);
    }

    public function multidelete(Request $request)
    {

        try {
            if(!empty($request->weightids)){
                Weight::whereIn('id',$request->weightids)->delete();    
            }
            
            $arr = array("status" => 200, "msg" => 'Successfully deleted.');
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

    public function destroy($id)
    {
        try {
            Weight::find($id)->delete();
            $arr = array("status" => 200, "msg" => 'Successfully deleted.');
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

}
