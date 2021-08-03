<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Zone;
use DataTables;
use App\Country;
use Validator;
use App\ZoneCountry;
use App\Service;

class ZoneController extends Controller
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
        $services = Service::where('status','active')->get();
        return view('upx.zone.index',compact('services'));
    }

    /**
     * Get all agents using datatable ajax request
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response Datatable json for draw
     */
    public function getall(Request $request)
    {
        //$service_id =1 ; //default door to door service
        /*if(isset($request->service_id) && !empty($request->service_id)){
            $service_id = $request->service_id;
        }*/

        $zones = Zone::with('countries')->orderby('name', 'asc');
        if(isset($request->service_id) && !empty($request->service_id)){
            $zones = $zones->where('service_id',$request->service_id);
        }
        $zones = $zones->get();
        return DataTables::of($zones)
            ->addColumn('service', function ($q) {
                return $q->service->name;
            })
            ->addColumn('action', function ($q) {

                return '<a data-zoneid="' . $q->id . '" data-toggle="modal" data-target=".modal_edit_list" title="Edit Zone" class="openform"><i class="fa fa-pencil"></i></a> | <a class="delete_zone" title="Delete Zone" data-id="' . $q->id . '"><i class="fa fa-trash"></i></a>';
            })
            ->addColumn('country', function ($q) {
                $return = '';
                if (!empty($q->countries)) {
                    foreach ($q->countries as $coutry) {
                        $return .= '<span class="label label-default country_tag">' . $coutry->name . '</span>';
                    }
                }
                return $return;
            })
            ->addIndexColumn()
            ->rawColumns(['status', 'action', 'country'])->make(true);
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
        $rules = [
            "country_id" => "required|array|min:1",
            "service_id" => "required",
        ];

        $message = [
            "country_id.required" => "The Country field is required.",
            "country_id.min" => "The country id must have at least 1 items.",
        ];

        if (isset($request->zoneid) && $request->zoneid != '') {
            $rules['name'] = 'required|unique:zones,name,NULL,service_id,service_id,'.$request->service_id.',id,id'. $request->zoneid;

        } else {
            $rules['name'] = 'required|unique:zones,name,NULL,id,service_id,'.$request->service_id;
        }

        $validator = Validator::make($input, $rules, $message);
        if ($validator->fails()) {
            $arr = array("status" => 400, "msg" => $validator->errors()->first(), "result" => array());
        } else {

            begin();
            try {
                $return = array();
                if (isset($request->zoneid) && $request->zoneid != '') {
                    Zone::find($request->zoneid)->update($input);
                    $msg = 'Zone Updated Successfully.';
                    $zoneid = $request->zoneid;
                } else {
                    $zone = new Zone;
                    $zoneid = $zone->create($input)->id;
                    $msg = 'Zone Added Successfully.';

                }
                $country_ids = [];
                if (!empty($request->country_id)) {
                    ZoneCountry::where('zone_id',$zoneid)->delete();
                    foreach ($request->country_id as $index => $value) {

                        /*$ZoneCountry = ZoneCountry::firstOrCreate([
                            'zone_id' => $zoneid,
                            'country_id' => $value,
                            'service_id' => $request->service_id
                        ])->id;*/
                        $ZoneCountry = ZoneCountry::Create(
                            ['zone_id' => $zoneid,
                                'country_id' => $value,
                                'service_id' =>$request->service_id
                            ]);
                    }
                }
              //  dd($countries);


               // Zone::find($zoneid)->countries()->sync($input['country_id']);

                commit();
                $arr = array("status" => 200, "msg" => $msg);
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


    /**
     * Get model for add edit addressbook
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getmodel(Request $request)
    {
        $service_id = 1;
        if (isset($request->zoneid) && $request->zoneid != '') {
            $zone = Zone::whereId($request->zoneid)->first();
            $service_id = $zone->service_id;
        }
        $countryids = ZoneCountry::where('service_id',$service_id)->pluck('country_id')->toArray();

        $countries = Country::whereNotIn('id', $countryids);

        $services = Service::where('status', 'active')->get();
        $zone = array();
        if (isset($request->zoneid) && $request->zoneid != '') {
            $zone = Zone::whereId($request->zoneid)->first();
            $existscountry = $zone->countries->pluck('id')->toArray();
            $countries = $countries->orWhereIn('id', $existscountry);
        }
        $countries = $countries->get();
        return view('upx.zone.modelopen', compact('countries', 'zone', 'services'));
    }

    public function destroy($id)
    {
        try {
            Zone::find($id)->delete();
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
    public function getcountries(Request $request){
        $countryids = ZoneCountry::query();
        if (isset($request->service_id) && $request->service_id != '') {
            $countryids = $countryids->where('service_id',$request->service_id);
        }
        $countryids = $countryids->pluck('country_id')->toArray();
        $countries = Country::whereNotIn('id', $countryids);
        if (isset($request->zoneid) && $request->zoneid != '') {
            $zone = Zone::whereId($request->zoneid)->first();
            $existscountry = $zone->countries->where('service_id',$zone->service_id)->pluck('id')->toArray();
            $countries = $countries->orWhereIn('id', $existscountry);
        }

       // \DB::enableQueryLog();
        $countries = $countries->get();

        $json = [];
        foreach ($countries as $key => $value) {
            $json[] = ['id'=>$value['id'], 'name'=>$value['name']];
        }
        //echo json_encode($json);
       // dd(\DB::getQueryLog());
        $country_html = '';
        foreach($countries as $country){
            $country_html .= '<option value="'.$country->id.'">'.$country->name .'</option>';
        }
        return $country_html;

    }
}
