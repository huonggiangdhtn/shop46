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
use App\Models\Warehousetransfer;

class WarehousetransferController extends Controller
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
        $active_menu="wi_trans";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh sách chuyển kho </li>';
        $warehousetrans=Warehousetransfer::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.warehousetransfers.index',compact('warehousetrans','breadcrumb','active_menu'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="wi_trans";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousetransfer.index').'">Ds chuyển kho</a></li>
        <li class="breadcrumb-item">Thêm chuyển kho</li>';
        $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
        $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
        $deliveries= User::where('role','delivery')->where('status','active')->orderBy('id','ASC')->get();
        
        $vendors = User:: where(function($query)  
        {
            $query->where('role', 'vendor')
                  ->orWhere('role', 'manager');
        })->where('status','active')->get();
         
        $user = auth()->user();
        return view('backend.warehousetransfers.create',compact('user','bankaccounts','warehouses','vendors','deliveries','breadcrumb','active_menu'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $user = auth()->user();
        $data = $request->importDoc;
        ////average price///////////////////
        $details = $request->products;
        $count_item = 0;
        foreach ($details as $detail)
        {
            $count_item += $detail['quantity'];
        }
        if($data['shipcost'])
        {
            $cost_extra = ($data['shipcost'])/ $count_item ;
            $data['cost_extra'] = $cost_extra ;
           
        }
        else
        {
            $data['cost_extra'] = 0;
        }
        $data['author_id'] = $user->id;
        $wf = Warehousetransfer::create($data);
        foreach ($details as $detail)
        {
            Inventory::transfer($wf->id,$detail['id'], $data['wh_id1'],$data['wh_id2'],$detail['quantity'], $detail['price'],$data['cost_extra']);
             
        }
         ///create ship invocie ///////////
       if($data['shipcost'] > 0)
       {
            $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
            $wf->shiptrans_id = $fts->id;
            BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
            $wf->save();
       }
        ///create log /////////////
        $content = 'create warehouse transfer id: '.$wf->id.' warehouse 1: '.$data['wh_id1'].' warehouse 2: '.$data['wh_id2'];
        \App\Models\Log::insertLog($content,$user->id);
        return response()->json(['msg'=>'Thêm thành công!','status'=>true]);
    }

    /**
     * Display the speci1800fied resource.
     */
    public function show(string $id)
    {
        //
        $warehousetrans = Warehousetransfer::find($id);
        if($warehousetrans)
        {
            $active_menu="wi_trans";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousetransfer.index').'">DS chuyển kho</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Xem chi tiết</li>';
            $wi_details = WarehouseInDetail::where('wti_id',$id)->get();
            return view('backend.warehousetransfers.show',compact('breadcrumb','warehousetrans','active_menu','wi_details'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        // if(!$this->checkRole(1))
        // {
        //     return redirect()->route('unauthorized');
        // }
        $warehousetrans = Warehousetransfer::find($id);
        if($warehousetrans  )
        {
            $wh1 = Warehouse::find( $warehousetrans->wh_id1);
            $wh2 = Warehouse::find( $warehousetrans->wh_id2);
            $vendor1 = User::find( $warehousetrans->vendor_id1);
            $vendor2 = User::find( $warehousetrans->vendor_id2);
            if($wh1 == null || $wh2 == null || $vendor1 == null || $vendor2 == null 
                ||$wh1->status =="inactive"||$wh2->status =="inactive"
                ||$vendor1->status =="inactive"||$vendor2->status =="inactive")
            {
                return back()->with('error','Đã có những dữ liệu liên quan không thể chỉnh sửa!');
            }
            $active_menu="wi_trans";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousetransfer.index').'">Danh sách chuyển kho</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh phiếu chuyển kho </li>';
            $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
            $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
            $deliveries= User::where('role','delivery')->where('status','active')->orderBy('id','ASC')->get();
            $vendors = User:: where(function($query)  
            {
                $query->where('role', 'vendor')
                      ->orWhere('role', 'manager');
            })
            ->where(function($query1)  use($warehousetrans)
            {
                $query1->where('status','active')
                      ->orWhere('id',$warehousetrans->vendor_id1 )
                      ->orWhere('id',$warehousetrans->vendor_id2 )
                      ->orWhere('id',$warehousetrans->author_id )
                      ;
            })
            ->get();
            $ship_trans = null;
            $bank_id = 0;
            $ship_amount = 0;
            if($warehousetrans->shiptrans_id)
            {
                $shiptrans = FreeTransaction::where('id',$warehousetrans->shiptrans_id)->first();
                $bank_id = $shiptrans->bank_id;
                $ship_amount = $shiptrans->total;
            }   
            $user = auth()->user();
            
            return view('backend.warehousetransfers.edit',compact('breadcrumb','warehousetrans','active_menu','warehouses','bankaccounts','user','bank_id','ship_amount','deliveries','vendors'));
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
        $warehousetrans = Warehousetransfer::find($id);
        $data = $request->importDoc;
         ////average price///////////////////
         $details = $request->products;
         $count_item = 0;
         foreach ($details as $detail)
         {
             $count_item += $detail['quantity'];
         }
         if($data['shipcost'])
         {
             $cost_extra = ($data['shipcost'])/ $count_item ;
             $data['cost_extra'] = $cost_extra ;
            
         }
         else
         {
             $data['cost_extra'] = 0;
         }
        if($warehousetrans)
        {
            $flag = 0;
            $detailpros = WarehouseInDetail::where('wti_id',$id)->get();
            foreach($detailpros as $dtpro)
            {
                if($dtpro->qty_sold > 0)
                    $flag = 1;
            }
            if($flag == 1)
            {
                return response()->json(['msg'=>'Đã xuất kho hàng hóa trong phiếu nhập!','status'=>false]);
            }
            $user = auth()->user();
            $data['author_id'] = $user->id;
            foreach($detailpros as $dtpro)
            {
                WarehouseInDetail::deleteDetailTransfer($dtpro,$warehousetrans->cost_extra,$warehousetrans->wh_id1,$warehousetrans->wh_id2);
            }
            if($warehousetrans->shiptrans_id)
            {
                
                $fts = FreeTransaction::find($warehousetrans->shiptrans_id);
                if($fts)
                {
                    $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                    if($banktrans)
                        BankTransaction::removeBankTrans($banktrans);
                    $fts->delete();
                }
            }
            //save the new 
            $warehousetrans->fill($data)->save();
            foreach ($details as $detail)
            {
                Inventory::transfer($warehousetrans->id,$detail['id'], $data['wh_id1'],$data['wh_id2'],$detail['quantity'], $detail['price'],$data['cost_extra']);
                 
            }
             ///create ship invocie ///////////
           if($data['shipcost'] > 0)
           {
                $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
                $warehousetrans->shiptrans_id = $fts->id;
                BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
                $warehousetrans->save();
           }
            ///create log /////////////
            $content = 'update warehouse transfer id: '.$warehousetrans->id.' warehouse 1: '.$data['wh_id1'].' warehouse 2: '.$data['wh_id2'];
            \App\Models\Log::insertLog($content,$user->id);
            return response()->json(['msg'=>'Cập nhật thành công!','status'=>true]);
        }
        else
            return response()->json(['msg'=>'Không tìm thấy!','status'=>false]);
         
    }
    public function getProductList(Request $request)
    {
        $this->validate($request,[
            'wti_id'=>'numeric|required',
        ]);
        $wo = Warehousetransfer::find($request->wti_id);
        $query = "(select id,photo, title from products ) as p";
        $query1 = "(select product_id ,quantity from inventories where wh_id = ".$wo->wh_id1.") as np";
               
        $products = DB::table('warehouse_in_details')
        ->select ('warehouse_in_details.price','warehouse_in_details.product_id','warehouse_in_details.quantity', 'p.title','p.photo','p.id','np.quantity as stock_qty')
        ->where('wti_id',$request->wti_id)
        ->leftJoin(\DB::raw($query),'warehouse_in_details.product_id','=','p.id')
        ->leftJoin(\DB::raw($query1),'warehouse_in_details.product_id','=','np.product_id')
        ->orderBy('id','ASC')->get();
        
        return response()->json(['msg'=>$products,'status'=>true]);

    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $warehousetrans = Warehousetransfer::find($id);
        
        if($warehousetrans)
        {
            $flag = 0;
            $detailpros = WarehouseInDetail::where('wti_id',$id)->get();
            foreach($detailpros as $dtpro)
            {
                if($dtpro->qty_sold > 0)
                    $flag = 1;
            }
            if($flag == 1)
            {
                return back()->with('error','Đã có sản phẩm xuất kho, không thể xóa!');
            }
            $user = auth()->user();
            $data['author_id'] = $user->id;
            foreach($detailpros as $dtpro)
            {
                WarehouseInDetail::deleteDetailTransfer($dtpro,$warehousetrans->cost_extra,$warehousetrans->wh_id1,$warehousetrans->wh_id2);
            }
            if($warehousetrans->shiptrans_id)
            {
                
                $fts = FreeTransaction::find($warehousetrans->shiptrans_id);
                if($fts)
                {
                    $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                    if($banktrans)
                        BankTransaction::removeBankTrans($banktrans);
                    $fts->delete();
                }
            }
            $content = 'delete warehouse transfer stock: '.$warehousetrans->wh_id1 .' to stock: '.$warehousetrans->wh_id2;
            \App\Models\Log::insertLog($content,$user->id);
             $warehousetrans->delete();
             return redirect()->route('warehousetransfer.index')->with('success','Xóa thành công!');
  
           
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu!');
         
        }
        
    }
    public function deliveryPrint($id)
    {
        $warehousetrans = Warehousetransfer::find($id);
        if($warehousetrans  )
        {
            $active_menu="wi_trans";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousetransfer.index').'">Danh sách chuyển kho</a></li>
            <li class="breadcrumb-item active" aria-current="page">phiếu gửi hàng </li>';
           
            return view('backend.warehousetransfers.deprint',compact('breadcrumb','warehousetrans','active_menu'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
}
