<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Warehousein;
use App\Models\SupTransaction; 
use App\Models\WarehouseInDetail;
use App\Models\Bankaccount;
use App\Models\BankTransaction;
use App\Models\FreeTransaction;
use App\Models\MaintainSent;
use App\Models\MaintainSentDetail;

class MaintainSentController extends Controller
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
        $active_menu="ms_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh gửi nhận bảo hành </li>';
        $maintainsents=MaintainSent::orderBy('id','DESC')->paginate($this->pagesize);
        
        return view('backend.maintainsents.index',compact('maintainsents','breadcrumb','active_menu'));

    }
    public function deliveryPrint($id)
    {
        $ms = MaintainSent::find($id);
        if($ms && $ms->status == 'sent')
        {
            $active_menu="ms_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('maintainsent.index').'">Danh sách gửi bảo hành</a></li>
            <li class="breadcrumb-item active" aria-current="page">phiếu gửi hàng </li>';
           
            return view('backend.maintainsents.deprint',compact('breadcrumb','ms','active_menu'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="ms_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Thêm gửi bảo hành </li>';
        $deliveries = \App\Models\User::where('role','delivery')
            ->where('status','active')->orderBy('full_name','ASC')->get();
        $bankaccounts = Bankaccount::where('status','active')
            ->orderBy('id','ASC')->get();
        return view('backend.maintainsents.create',compact( 'breadcrumb','active_menu','deliveries','bankaccounts'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->importDoc;
        // return $data;
        
        $user = auth()->user();
        $data['vendor_id'] = $user->id;
       
        ////average price///////////////////
        $details = $request->products;
        $count_item = 0;
        foreach ($details as $detail)
        {
            $count_item += $detail['quantity'];
        }
        $cost_extra = ($data['shipcost'])/ $count_item ;
        $data['cost_extra'] = $cost_extra ;
        $ms = MaintainSent::create($data);
        //save detail
        foreach ($details as $detail)
        {
            $product_detail['ms_id'] = $ms->id;
            $product_detail['product_id']= $detail['id'];
            $product_detail['quantity'] = $detail['quantity'];
            $in_ids = \App\Models\InventoryMaintenance::sendPro($product_detail['product_id'],$product_detail['quantity'],$data['cost_extra']);
            $product_detail['in_ids'] = json_encode($in_ids);
            MaintainSentDetail::create($product_detail);
        }
        if($data['shipcost'] && $data['shipcost'] > 0)
        {
             $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
             $ms->shiptrans_id = $fts->id;
             BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
             $ms->save();
        }
        ///create log /////////////
        $content = 'save new maintainsent id: '.$ms->id ;
        \App\Models\Log::insertLog($content,$user->id);
        return response()->json(['msg'=>'Thêm gửi bảo hành thành công!','status'=>true]);
  
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $ms = MaintainSent::find($id);
        if($ms)
        {
            $active_menu="ms_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('maintainsent.index').'">Danh sách gửi bảo hành</a></li>
            <li class="breadcrumb-item active" aria-current="page"> cập nhật phiếu gửi bảo hành </li>';
            $ms_details = MaintainSentDetail::where('ms_id',$id)->get();   
            return view('backend.maintainsents.show',compact('breadcrumb','active_menu',  'ms' ,'ms_details' ));
    
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
        $ms = MaintainSent::find($id);
        if($ms)
        {
            $active_menu="ms_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('maintainsent.index').'">Danh sách gửi bảo hành</a></li>
            <li class="breadcrumb-item active" aria-current="page"> cập nhật phiếu gửi bảo hành </li>';
             $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
             $deliveries = \App\Models\User::where('role','delivery')
             ->where('status','active')->orderBy('full_name','ASC')->get();
             $bank_id = 0;
             $ship_amount = 0;
             if($ms->shiptrans_id)
             {
                 $shiptrans = FreeTransaction::where('id',$ms->shiptrans_id)->first();
                 $bank_id = $shiptrans->bank_id;
             }   
             return view('backend.maintainsents.edit',compact('breadcrumb','active_menu',  'bankaccounts' ,'deliveries','bank_id' ,'ms'));
    
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
        $ms = MaintainSent::find($id);
        if($ms)
        {
            $details = MaintainSentDetail::where('ms_id',$ms->id)->get();
            $flag = 0;
            foreach ($details as $detail)
            {
               if($detail->back > 0)
                $flag = 1;
            }
            if($flag == 1)
            {
                return response()->json(['msg'=>'Đã có sản phẩm trả về từ nhà cung cấp. Không thể điều chỉnh!','status'=>false]);
            }
            //remove detail
            
            foreach ($details as $detail)
            {
                \App\Models\InventoryMaintenance::deletesendPro($detail,$ms->cost_extra);
            }
              ///delete ship invoice
            if($ms->shiptrans_id)
            {
                $fts = FreeTransaction::find($ms->shiptrans_id);
                if($fts)
                {
                    $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                    if($banktrans)
                        BankTransaction::removeBankTrans($banktrans);
                    $fts->delete();
                }
            }
            //save new
            $data = $request->importDoc;
            $user = auth()->user();
            $data['vendor_id'] = $user->id;
        
            ////average price///////////////////
            $details = $request->products;
            $count_item = 0;
            foreach ($details as $detail)
            {
                $count_item += $detail['quantity'];
            }
            $cost_extra = ($data['shipcost'])/ $count_item ;
            $data['cost_extra'] = $cost_extra ;
            $ms->fill($data)->save();
            //save detail
            foreach ($details as $detail)
            {
                $product_detail['ms_id'] = $ms->id;
                $product_detail['product_id']= $detail['id'];
                $product_detail['quantity'] = $detail['quantity'];
                $in_ids = \App\Models\InventoryMaintenance::sendPro($product_detail['product_id'],$product_detail['quantity'],$data['cost_extra']);
                $product_detail['in_ids'] = json_encode($in_ids);
                MaintainSentDetail::create($product_detail);
            }
            if($data['shipcost'] && $data['shipcost'] > 0)
            {
                $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
                $ms->shiptrans_id = $fts->id;
                BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
                $ms->save();
            }
            ///create log /////////////
            $content = 'update maintainsent id: '.$ms->id ;
            \App\Models\Log::insertLog($content,$user->id);
            return response()->json(['msg'=>'Thêm gửi bảo hành thành công!','status'=>true]);
  
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $ms = MaintainSent::find($id);
        if($ms)
        {
            //remove detail
            $details = MaintainSentDetail::where('ms_id',$ms->id)->get();
            $flag = 0;
            foreach ($details as $detail)
            {
               if($detail->back > 0)
                $flag = 1;
            }
            if($flag == 1)
            {
                return back()->with('error','Đã có sản phẩm trả về từ nhà cung cấp. Không thể điều chỉnh!');
            }
            foreach ($details as $detail)
            {
                \App\Models\InventoryMaintenance::deletesendPro($detail,$ms->cost_extra);
            }
              ///delete ship invoice
            if($ms->shiptrans_id)
            {
                $fts = FreeTransaction::find($ms->shiptrans_id);
                if($fts)
                {
                    $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                    if($banktrans)
                        BankTransaction::removeBankTrans($banktrans);
                    $fts->delete();
                }
            }
             ///create log /////////////
            $user = auth()->user();
            $content = 'delete maintainsent id: '.$ms->id ;
            \App\Models\Log::insertLog($content,$user->id);
            $ms->delete(); 
            return redirect()->route('maintainsent.index')->with('success','Xóa thành công!'); 

        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function getProductList(Request $request)
    {
        $this->validate($request,[
            'ms_id'=>'numeric|required',
        ]);
        $ms = MaintainSent::find($request->ms_id);
        $query = "(select id,photo, title,price_avg from products ) as p";
        $query1 = "(select product_id ,quantity from inventory_maintenances ) as np";
               
        $products = DB::table('maintain_sent_details')
        ->select ( 'maintain_sent_details.product_id','maintain_sent_details.quantity', 'p.title','p.photo','p.id','p.price_avg as price','np.quantity as stock_qty')
        ->where('ms_id',$request->ms_id)
        ->leftJoin(\DB::raw($query),'maintain_sent_details.product_id','=','p.id')
        ->leftJoin(\DB::raw($query1),'maintain_sent_details.product_id','=','np.product_id')
        ->orderBy('id','ASC')->get();
        
        return response()->json(['msg'=>$products,'status'=>true]);

    }
}
