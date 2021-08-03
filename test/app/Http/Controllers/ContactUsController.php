<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ContactUs;
use DataTables;

class ContactUsController extends Controller
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
        return view('upx.contact.index');
    }

    public function getall(Request $request) {

        $contacts = ContactUs::orderby('id','desc')->get();
        return DataTables::of($contacts)
        ->addColumn('date', function ($q) {
            
            return date('d-M-Y', strtotime($q->created_at)).' at '.date('h:i A', strtotime($q->created_at));
        }) 
        ->addIndexColumn()
        ->make(true);
    }
}
