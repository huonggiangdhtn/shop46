<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Warehouseout;
use App\Models\SupTransaction; 
use App\Models\WarehouseInDetail;
use App\Models\Bankaccount;
use App\Models\BankTransaction;
use App\Models\FreeTransaction;
use App\Models\UGroup;
use App\Models\User;
use App\Models\Warehousetomaintain;
 

class WarehousetomaintainController extends Controller
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
        $active_menu="wm_trans";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh sách chuyển kho bảo hành </li>';
        $warehousemains=Warehousetomaintain::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.warehousetomaintain.index',compact('warehousemains','breadcrumb','active_menu'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="wm_trans";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousetomaintain.index').'">Ds chuyển bảo hành/a></li>
        <li class="breadcrumb-item">Thêm chuyển bảo hành</li>';
        $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
        $user = auth()->user();
        return view('backend.warehousetomaintain.create',compact('user', 'warehouses', 'breadcrumb','active_menu'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request,[
            'product_id'=>'numeric|required',
            'wh_id'=>'numeric|required',
            'quantity'=>'numeric|required',
            'price'=>'numeric|required',
        ]);
        $data = $request->all();
        $inventory = \App\Models\Inventory::where('product_id',$data['product_id'])
            ->where('wh_id',$data['wh_id'])->first();
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$data['product_id'])
            ->first();
        if($inventory && $inventory->quantity >= $data['quantity'])
        {

            $in_ids = \App\Models\Inventory::mainTransfer($data['product_id'],$data['wh_id'],$data['quantity'],$data['price'] );
            $data['in_ids'] = json_encode($in_ids);
            if ($minventory)
            {
                $minventory->quantity += $data['quantity'];
                $minventory->save();
            }
            else
            {
                $minven['product_id'] = $data['product_id'];
                $minven['quantity'] =  $data['quantity'];
                $minventory = \App\Models\InventoryMaintenance::create($minven);
            }
            $user = auth()->user();
            $data['total'] = $data['price'] * $data['quantity'];
            $data['vendor_id'] = $user->id;
            $warehousemain = Warehousetomaintain::create($data);
            ///create log /////////////
            $content = 'create warehouse maintain id: '.$warehousemain ->id.'product id: '.$data['product_id'] ;
            \App\Models\Log::insertLog($content,$user->id);
            return redirect()->route('warehousetomaintain.index')->with('success','Tạo chuyển kho bảo hành thành công!');
     
        }
        else
        {
            return back()->with('error','Không tìm thấy tồn kho!');
        }
        
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
        $warehousetomain = Warehousetomaintain::find($id);
        if(!$warehousetomain)
            return back()->with('error','Không tìm thấy dữ liệu!');
        $active_menu="wm_trans";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item " aria-current="page"><a href="'.route('warehousetomaintain.index').'">Ds chuyển bảo hành</a></li>
        <li class="breadcrumb-item">Điều chỉnh chuyển kho bảo hành</li>';
        $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
        
        return view('backend.warehousetomaintain.edit',compact('warehousetomain', 'warehouses', 'breadcrumb','active_menu'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $this->validate($request,[
            'product_id'=>'numeric|required',
            'wh_id'=>'numeric|required',
            'quantity'=>'numeric|required',
            'price'=>'numeric|required',
        ]);
        $data = $request->all();
        $inventory = \App\Models\Inventory::where('product_id',$data['product_id'])
            ->where('wh_id',$data['wh_id'])->first();
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$data['product_id'])
            ->first();
        $warehousetomain = Warehousetomaintain::find($id);
        if($inventory && $inventory->quantity + $warehousetomain->quantity >= $data['quantity'])
        {

            \App\Models\Warehousetomaintain::deleteDetail($warehousetomain);
            
            $in_ids = \App\Models\Inventory::mainTransfer($data['product_id'],$data['wh_id'],$data['quantity'],$data['price'] );
            $data['in_ids'] = json_encode($in_ids);
            if ($minventory)
            {
                $minventory->quantity -= $warehousetomain->quantity;
                $minventory->quantity += $data['quantity'];
                $minventory->save();
            }
            else
            {
                $minven['product_id'] = $data['product_id'];
                $minven['quantity'] =  $data['quantity'];
                $minventory = \App\Models\InventoryMaintenance::create($minven);
            }
            $user = auth()->user();
            $data['total'] = $data['price'] * $data['quantity'];
            $data['vendor_id'] = $user->id;
            $warehousetomain->fill($data)->save();
            ///create log /////////////
            $content = 'update warehouse maintain id: '.$warehousetomain ->id.'product id: '.$data['product_id'] ;
            \App\Models\Log::insertLog($content,$user->id);
            return redirect()->route('warehousetomaintain.index')->with('success','Cập nhật chuyển kho bảo hành thành công!');
     
        }
        else
        {
            return back()->with('error','Không tìm thấy tồn kho!');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $warehousetomain = Warehousetomaintain::find($id);
        
        if($warehousetomain)
        {
            $minventory = \App\Models\InventoryMaintenance::where('product_id',$warehousetomain->product_id)
            ->first();
            \App\Models\Warehousetomaintain::deleteDetail($warehousetomain);
            
            if ($minventory)
            {
                $minventory->quantity -= $warehousetomain->quantity;
                $minventory->save();
            }
            
            $user = auth()->user();
            
            ///create log /////////////
            $content = 'delete warehouse maintain id: '.$warehousetomain ->id.'product id: '.$warehousetomain->product_id ;
            \App\Models\Log::insertLog($content,$user->id);
            $warehousetomain->delete();
            return redirect()->route('warehousetomaintain.index')->with('success','Xóa chuyển kho bảo hành thành công!');
    
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu!');
        }
    }
}
