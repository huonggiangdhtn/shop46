<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $setting = \App\Models\SettingDetail::find(1);
        if($setting!= null)
        {
            $active_menu="setting_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
             ';
            return view('backend.setting.edit',compact('breadcrumb','setting','active_menu' ));
    
        }
       
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        // return $request->all();
        $this->validate($request,[
            'company_name'=>'string|required',
            'phone'=>'string|required',
            'address'=>'string|required',
             
        ]);
        $setting = \App\Models\SettingDetail::find(1);
        // return $request->all();
        $data = $request->all();
        if(!$data['logo'])
        {
            $data['logo'] = asset('backend/assets/dist/images/profile-6.jpg');
        }
        $status = $setting->fill($data)->save();
        if($status){
            return redirect()->route('setting.edit',1);
        }
        else
        {
            return back()->with('error','Something went wrong!');
        }    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
