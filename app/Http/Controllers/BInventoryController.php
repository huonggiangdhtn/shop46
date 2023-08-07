<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BInventory;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
class BInventoryController extends Controller
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
        $active_menu="bi_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> ds hàng hóa đầu kỳ </li>';
        $binventorys=binventory::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.binventories.index',compact('binventorys','breadcrumb','active_menu'));

    }
    public function binventorySort(Request $request)
    {
        $this->validate($request,[
            'field_name'=>'string|required',
            'type_sort'=>'required|in:DESC,ASC',
        ]);
    
        $active_menu="bi_list";
        $searchdata =$request->datasearch;
        $binventorys = DB::table('b_inventories')->orderBy($request->field_name, $request->type_sort)
        ->paginate($this->pagesize)->withQueryString();;
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('binventory.index').'">tồn kho đầu kì</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
        return view('backend.binventories.index',compact('binventorys','breadcrumb','searchdata','active_menu'));
    }


    public function binventorySearch(Request $request)
    {
        if($request->datasearch)
        {
            $active_menu="bi_list";
            $searchdata =$request->datasearch;
            $query = "(select id as idpro, title from products where title like'%".$searchdata."%') as np";
            $binventorys = DB::table('b_inventories')
            ->select('b_inventories.*' )
            ->join(\DB::raw($query),
            'b_inventories.product_id', '=', 'np.idpro') 
            ->paginate($this->pagesize)->withQueryString();
            
            // $binventorys = DB::select($query)->paginate($this->pagesize)->withQueryString();;;
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('binventory.index').'">Tồn kho đầu kỳ</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            return view('backend.binventories.search',compact('binventorys','breadcrumb','searchdata','active_menu'));
        }
        else
        {
            return redirect()->route('binventory.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }

    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="bi_add";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('user.index').'">Tồn kho đầu kì</a></li>
        <li class="breadcrumb-item active" aria-current="page"> thêm mới </li>';
        $products = Product::where('status','active')->orderBy('title','ASC')->get();
        $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
        return view('backend.binventories.create',compact('breadcrumb','active_menu','products','warehouses'));
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
        $binventory = BInventory::where('product_id',$data['product_id'])
                        ->where('wh_id',$data['wh_id'])->first();
        $inventory = Inventory::where('product_id',$data['product_id'])
                        ->where('wh_id',$data['wh_id'])->first();
        $product = Product::find($data['product_id']);
        
        $product->price_avg =  ($product->price_avg *  $product->stock +   $data['price'] *   $data['quantity'])/( $product->stock +  $data['quantity']);
        $product->stock += $data['quantity'];
        $product->save();
        if($binventory != null)
        {
            $binventory->price = $data['price'];
            $binventory->quantity += $data['quantity'];
            $binventory->save();
            
            $status = true;
        }
        else
        {
            $status = BInventory::create($data);
        }
        if($inventory != null)
        {
           
            $inventory->quantity += $data['quantity'];
            $inventory->save();
            
            $status = true;
        }
        else
        {
            $status = Inventory::create($data);
        }
        // return $data;
        //create Detailwarehouse in with wi_id = 0 and wti_id = 0
        $widetail = \App\Models\WarehouseInDetail::where('wi_id',0)
            ->where('wti_id',0)->first();
        
        $product_detail['wi_id'] =0;
        $product_detail['wti_id'] =0;
        $product_detail['product_id']= $data['product_id'];
        $product_detail['quantity'] = $data['quantity'];
        $product_detail['price'] = $data['price'];
        //save expired days
        $product = Product::find($data['product_id']);
        $start_date = date('Y-m-d H:i:s');
        if($product->expired)
        {
            $strday = '+' . $product->expired*30 .' days';
            $end_date = date("Y-m-d 23:59:59", strtotime( $strday, strtotime($start_date)));
            $product_detail['expired_at'] = $end_date;
        }

        //  return $product_detail;
        if($widetail == null)
            \App\Models\WarehouseInDetail::create($product_detail);
        else
         {
            $product_detail['quantity'] = $product_detail['quantity'] + $widetail->quantity;
            $widetail->fill($product_detail)->save();
         }  

        if($status){

            $content = 'store binventory pro_id: '.$data['product_id'].' at stock id: '.$data['wh_id'];
            $user = auth()->user();
            \App\Models\Log::insertLog($content,$user->id);
            return redirect()->route('binventory.index')->with('success','Tạo hàng hóa thành công!');
        }
        else
        {
            return back()->with('error','Something went wrong!');
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
        $binventory = Binventory::find($id);
        if($binventory)
        {
            $active_menu="pro_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('binventory.index').'">Tồn kho đầu kỳ</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh tồn kho đầu kỳ</li>';
            $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
            return view('backend.binventories.edit',compact('breadcrumb','binventory','active_menu','warehouses'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
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
        $binventory = BInventory::where('product_id',$data['product_id'])
                        ->where('wh_id',$data['wh_id'])->first();
        $inventory = Inventory::where('product_id',$data['product_id'])
                        ->where('wh_id',$data['wh_id'])->first();
        if($inventory != null)
        {
            $inventory->quantity = $inventory->quantity - $binventory->quantity + $data['quantity'];
            $inventory->save();
        }
        else
        {
            Inventory::create($data);
        }
        //save warehousein_detail
        $widetail = \App\Models\WarehouseInDetail::where('wi_id',0)
        ->where('wti_id',0)->first();
    
        $product_detail['wi_id'] =0;
        $product_detail['wti_id'] =0;
        $product_detail['product_id']= $data['product_id'];
        $product_detail['quantity'] = $data['quantity'];
        $product_detail['price'] = $data['price'];
        //save expired days
        $product = Product::find($data['product_id']);
        $start_date = date('Y-m-d H:i:s');
        if($product->expired)
        {
            $strday = '+' . $product->expired*30 .' days';
            $end_date = date("Y-m-d 23:59:59", strtotime( $strday, strtotime($start_date)));
            $product_detail['expired_at'] = $end_date;
        }

        //  return $product_detail;
        if($widetail == null)
            \App\Models\WarehouseInDetail::create($product_detail);
        else
        {
             $widetail->fill($product_detail)->save();
        } 
        //save binventory
        if($binventory != null)
        {
            $product = Product::find($binventory->product_id);
            if($product->stock != $binventory->quantity)
            {
                $product->price_avg =  ($product->price_avg *  $product->stock -   $binventory->price *  $binventory->quantity)/( $product->stock -  $binventory->quantity);
                $product->price_avg =  ($product->price_avg *  $product->stock +   $data['price'] *   $data['quantity'])/( $product->stock +  $data['quantity']);
            } 
            else
            {
                $product->price_avg = $data['price'];
            }   
            $product->stock -= $binventory->quantity;
            // $product->save();
            $binventory->quantity = $data['quantity'];
            $binventory->price = $data['price'];

            $product->stock += $binventory->quantity;
            $product->save();
            $binventory->save();
            $content = 'update binventory pro_id: '.$data['product_id'].' at stock id: '.$data['wh_id'];
            $user = auth()->user();
            \App\Models\Log::insertLog($content,$user->id);

            return redirect()->route('binventory.index')->with('success','Cập nhật thành công!');
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
