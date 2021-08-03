<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use App\AddressBook;
use App\Country;
use Validator;
use Auth;

class AddressBookController extends Controller
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
        return view('upx.addressbook.index');
    }

    /**
     * Get all agents using datatable ajax request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request)
    {

        $addressbook = AddressBook::with('country');
        if (Auth::user()->role == 'agent') {
            $addressbook = $addressbook->where('created_by', Auth::user()->id);
        }
        $addressbook = $addressbook->orderby('name', 'asc')->get();

        return DataTables::of($addressbook)
            ->addColumn('action', function ($q) {
                return '<a data-addressbookid="' . $q->id . '" data-toggle="modal" data-target=".modal_edit_list" title="Edit Address Book" class="openform"><i class="fa fa-pencil"></i></a> | <a class="delete_address" title="Delete Address Book" data-id="' . $q->id . '"><i class="fa fa-trash"></i></a>';
            })
            ->addColumn('added_by', function ($q) {
                return $q->addedby->name . ' ' . $q->addedby->lastname;
            })
            ->addColumn('country', function ($q) {
                return $q->country->name;
            })
            ->addIndexColumn()
            ->rawColumns(['status', 'action'])->make(true);
    }

    /**
     * Get model for add edit addressbook
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getmodel(Request $request)
    {
        $countries = Country::get();
        $addressbook = array();
        if (isset($request->addressbookid) && $request->addressbookid != '') {
            $addressbook = AddressBook::whereId($request->addressbookid)->first();
        }
        return view('upx.addressbook.modelopen', compact('addressbook', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'name' => "required|max:30",
            'company' => 'max:30',
            'address1' => 'required',
            'city' => 'required|string',
            'postalcode' => 'required',
            'country_id' => 'required|exists:countries,id',
            'phone_number' => 'numeric|nullable'

        );

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {

            try {
                $return = array();

                if (isset($request->addressbookid) && $request->addressbookid != '') {
                    AddressBook::find($request->addressbookid)->update($input);
                    $msg = 'AddressBook Updated Successfully.';

                } else {
                    $input['created_by'] = Auth::user()->id;
                    $addressbook = new AddressBook;
                    $addressbook->create($input);
                    $msg = 'Product Added Successfully.';

                }

                $arr = array("status" => 200, "msg" => $msg);
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


    public function destroy($id)
    {
        try {
            AddressBook::find($id)->delete();
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
