<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoryController extends Controller
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
        $active_menu="i_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> ds hàng hóa tồn kho</li>';
        $inventorys=Inventory::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.inventories.index',compact('inventorys','breadcrumb','active_menu'));

    }
    public function inventorySort(Request $request)
    {
        $this->validate($request,[
            'field_name'=>'string|required',
            'type_sort'=>'required|in:DESC,ASC',
        ]);
    
        $active_menu="i_list";
        $searchdata =$request->datasearch;
        $inventorys = DB::table('inventories')->orderBy($request->field_name, $request->type_sort)
        ->paginate($this->pagesize)->withQueryString();;
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('inventory.index').'">tồn kho</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
        return view('backend.inventories.index',compact('inventorys','breadcrumb','searchdata','active_menu'));
    }
    public function inventorySearch(Request $request)
    {
        if($request->datasearch)
        {
            $active_menu="bi_list";
            $searchdata =$request->datasearch;
            $query = "(select id as idpro, title from products where title like'%".$searchdata."%') as np";
            $inventorys = DB::table('inventories')
            ->select('inventories.*' )
            ->join(\DB::raw($query),
            'inventories.product_id', '=', 'np.idpro') 
            ->paginate($this->pagesize)->withQueryString();
            
            // $inventorys = DB::select($query)->paginate($this->pagesize)->withQueryString();;;
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('inventory.index').'">Tồn kho đầu kỳ</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            return view('backend.inventories.search',compact('inventorys','breadcrumb','searchdata','active_menu'));
        }
        else
        {
            return redirect()->route('inventory.index')->with('success','Không có thông tin tìm kiếm!');
        }

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
