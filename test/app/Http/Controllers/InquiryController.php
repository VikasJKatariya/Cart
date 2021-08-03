<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use App\User;
use App\Inquiry;
use Validator;
use Auth;

class InquiryController extends Controller
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
        return view('upx.inquiry.index');
    }
    /**
     * Get all agents using datatable ajax request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request)
    {

        $inquiry = Inquiry::with('getcountry')->orderby('id', 'desc')->get();

        return DataTables::of($inquiry)
            ->addColumn('action', function ($q) {
                return '<a href="" data-id="'.$q->id.'" class="checkhistory" data-toggle="modal" data-target=".transationhistory" title="View Inquiry"><span class="fa fa-eye"></span></a>';
            })
            ->addColumn('country', function ($q) {
                return $q->getcountry->name;
            })
            ->addColumn('service', function ($q) {
                return $q->getservice->name;
            })
            ->addColumn('service_type', function ($q) {
                $service_type = $q->service_type;
                if($q->service == 3){
                    $service_type = $q->service_type.'KG';
                }
                return $service_type;
            })
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * @param Request $request
     * @return mixed
     * display inquiry details
     * @throws \Throwable
     */
    public function getdetails(Request $request) {

        $id = $request->id;
        $data = Inquiry::whereId($id)->first();
        $viewinquiry= view('upx.inquiry.modelopen',compact('data'))->render();
        $return = array('view'=>$viewinquiry);
        return \Response::json($return);
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
        //
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
