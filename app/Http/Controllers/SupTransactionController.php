<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupTransactionController extends Controller
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
        $suptrans = \App\Models\Suptransaction::find($id);
        if($suptrans)
        {
            if($suptrans->operation == -1)
            {
                $active_menu="sup_list";
                $breadcrumb = '
                <li class="breadcrumb-item"><a href="#">/</a></li>
                <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Nhà cung cấp</a></li>
                <li class="breadcrumb-item active" aria-current="page"> xem giao dịch nhà cung cung </li>';
                return view('backend.suppliers.viewsuptrans',compact('breadcrumb','active_menu', 'suptrans'));

             }   
            else
            {
                $active_menu="customer_list";
                $breadcrumb = '
                <li class="breadcrumb-item"><a href="#">/</a></li>
                <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Khách hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page"> xem giao dịch nhà cung cung </li>';
                return view('backend.customers.viewsuptrans',compact('breadcrumb','active_menu', 'suptrans'));
            }    
        }
        else 
        echo 'ko';
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
