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
use App\Models\MaintainBack;
use App\Models\MaintainBackDetail;

class MaintainBackController extends Controller
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
        $active_menu="mb_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh trả bảo hành từ đối tác </li>';
        $maintainbacks=MaintainBack::orderBy('id','DESC')->paginate($this->pagesize);
        
        return view('backend.maintainbacks.index',compact('maintainbacks','breadcrumb','active_menu'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="mb_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Thêm trả bảo hành từ đối tác</li>';
        $categories = \App\Models\Category::where('status','active')->orderBy('id','ASC')->get();
            
        $bankaccounts = Bankaccount::where('status','active')
            ->orderBy('id','ASC')->get();
        return view('backend.maintainbacks.create',compact( 'breadcrumb','active_menu','bankaccounts','categories'));

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
        $mb = MaintainBack::create($data);
        //save detail
        foreach ($details as $detail)
        {
            $product_detail['mb_id'] = $mb->id;
            $product_detail['product_id']= $detail['id'];
            $product_detail['quantity'] = $detail['quantity'];
            $in_ids = \App\Models\InventoryMaintenance::backPro($product_detail['product_id'],$product_detail['quantity'],$data['cost_extra'],$data['supplier_id']);
            $product_detail['in_ids'] = json_encode($in_ids);
            MaintainBackDetail::create($product_detail);
        }
        if($data['shipcost'] && $data['shipcost'] > 0)
        {
             $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
             $mb->shiptrans_id = $fts->id;
             BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
             $mb->save();
        }
        if($data['final_amount'] > 0 )
        {
              ///create SupTransaction
            $sps = SupTransaction::createSubTrans($mb->id,'mi',1,$data['final_amount'], $data['supplier_id']);
            $mb->suptrans_id = $sps->id;
            ///create paid transaction
            if($data['paid_amount']> 0)
            {
                $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$mb->id,'mi',$data['paid_amount']);
                SupTransaction::createSubTrans($bank_doc->id,'fi',-1, $data['paid_amount'], $data['supplier_id']); 
                $in_ids=array();
                $in_id = new \App\Models\Number();
                $in_id->id = $bank_doc->id;
                array_push($in_ids,$in_id);
                $mb->paidtrans_ids = json_encode($in_ids);
                $mb->save();
            }
        }
        ///create log /////////////
        $content = 'save new maintainback id: '.$mb->id ;
        \App\Models\Log::insertLog($content,$user->id);
        return response()->json(['msg'=>'Thêm nhận bảo hành thành công!','status'=>true]);
  
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
        $mb = MaintainBack::find($id);
        if($mb)
        {
            $active_menu="mb_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('maintainback.index').'">Danh sách trả bảo hành</a></li>
            <li class="breadcrumb-item active" aria-current="page"> cập nhật phiếu trả bảo hành </li>';
            $categories = \App\Models\Category::where('status','active')->orderBy('id','ASC')->get();
            $bankaccounts = Bankaccount::where('status','active')
                ->orderBy('id','ASC')->get();
             $bank_id = 0;
             $ship_amount = 0;
             if($mb->shiptrans_id)
             {
                 $shiptrans = FreeTransaction::where('id',$mb->shiptrans_id)->first();
                 $bank_id = $shiptrans->bank_id;
             }  
             if($mb->paidtrans_ids)
             {
                 $id_ins = json_decode($mb->paidtrans_ids); 
                 $id_in = $id_ins[0];
                 $paidtrans = BankTransaction::where('id',$id_in->id)->first();
                 $bank_id = $paidtrans->bank_id;
                 
             }   
             return view('backend.maintainbacks.edit',compact('breadcrumb','active_menu',  'bankaccounts' ,'categories','bank_id' ,'mb'));
    
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
        $mb = MaintainBack::find($id);
        if($mb)
        {
            $details = MaintainBackDetail::where('mb_id',$mb->id)->get();
            
            //remove detail
            
            foreach ($details as $detail)
            {
                \App\Models\InventoryMaintenance::deletebackPro($detail,$mb->cost_extra);
            }
                ///delete sup trans 1 for importing
            if($mb->final_amount > 0)
                SupTransaction::removeSubTrans($mb->suptrans_id);
            ///
            ///delete paid transaction
            if($mb->paidtrans_ids)
            {
                $in_ids = json_decode($mb->paidtrans_ids);
                foreach ($in_ids as $in_id)
                {
                    $bank_doc = BankTransaction::find( $in_id->id );
                    if($bank_doc)
                    {
                        $suptrans = SupTransaction::where('doc_id',$bank_doc->id)->where('doc_type','fi')->first();
                        if($suptrans)
                            SupTransaction::removeSubTrans( $suptrans->id);
                        BankTransaction::removeBankTrans($bank_doc);
                    }
                }
            }
              ///delete ship invoice
            if($mb->shiptrans_id)
            {
                $fts = FreeTransaction::find($mb->shiptrans_id);
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
            $mb->fill($data)->save();
            //save detail
            foreach ($details as $detail)
            {
                $product_detail['mb_id'] = $mb->id;
                $product_detail['product_id']= $detail['id'];
                $product_detail['quantity'] = $detail['quantity'];
                $in_ids = \App\Models\InventoryMaintenance::backPro($product_detail['product_id'],$product_detail['quantity'],$data['cost_extra'],$data['supplier_id']);
                $product_detail['in_ids'] = json_encode($in_ids);
                MaintainBackDetail::create($product_detail);
            }
            if($data['shipcost'] && $data['shipcost'] > 0)
            {
                $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
                $mb->shiptrans_id = $fts->id;
                BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
                $mb->save();
            }
            if($data['final_amount'] > 0 )
            {
                ///create SupTransaction
                $sps = SupTransaction::createSubTrans($mb->id,'mi',1,$data['final_amount'], $data['supplier_id']);
                $mb->suptrans_id = $sps->id;
                ///create paid transaction
                if($data['paid_amount']> 0)
                {
                    $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$mb->id,'mi',$data['paid_amount']);
                    SupTransaction::createSubTrans($bank_doc->id,'fi',-1, $data['paid_amount'], $data['supplier_id']); 
                    $in_ids=array();
                    $in_id = new \App\Models\Number();
                    $in_id->id = $bank_doc->id;
                    array_push($in_ids,$in_id);
                    $mb->paidtrans_ids = json_encode($in_ids);
                    $mb->save();
                }
            }
        
            ///create log /////////////
            $content = 'update MaintainBack id: '.$mb->id ;
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
        $mb = MaintainBack::find($id);
        if($mb)
        {
            $details = MaintainBackDetail::where('mb_id',$mb->id)->get();
            
            //remove detail
            
            foreach ($details as $detail)
            {
                \App\Models\InventoryMaintenance::deletebackPro($detail,$mb->cost_extra);
            }
                ///delete sup trans 1 for importing
            if($mb->final_amount > 0)
                SupTransaction::removeSubTrans($mb->suptrans_id);
            ///
            ///delete paid transaction
            if($mb->paidtrans_ids)
            {
                $in_ids = json_decode($mb->paidtrans_ids);
                foreach ($in_ids as $in_id)
                {
                    $bank_doc = BankTransaction::find( $in_id->id );
                    if($bank_doc)
                    {
                        $suptrans = SupTransaction::where('doc_id',$bank_doc->id)->where('doc_type','fi')->first();
                        if($suptrans)
                            SupTransaction::removeSubTrans( $suptrans->id);
                        BankTransaction::removeBankTrans($bank_doc);
                    }
                }
            }
              ///delete ship invoice
            if($mb->shiptrans_id)
            {
                $fts = FreeTransaction::find($mb->shiptrans_id);
                if($fts)
                {
                    $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                    if($banktrans)
                        BankTransaction::removeBankTrans($banktrans);
                    $fts->delete();
                }
            }
        
            ///create log /////////////
            $user= auth()->user();
            $content = 'delete MaintainBack id: '.$mb->id ;
            \App\Models\Log::insertLog($content,$user->id);
            $mb->delete();
            return redirect()->route('maintainback.index')->with('success','Xóa thành công!'); 

        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function getProductList(Request $request)
    {
        $this->validate($request,[
            'mb_id'=>'numeric|required',
        ]);
        // $mb = MaintainBack::find($request->mb_id);
        $query = "(select id,photo, title,price_avg from products ) as p";
        $query1 = "(select product_id ,quantity from inventory_maintenances ) as np";
               
        $products = DB::table('maintain_back_details')
        ->select ( 'maintain_back_details.product_id','maintain_back_details.quantity', 'p.title','p.photo','p.id','p.price_avg as price','np.quantity as stock_qty')
        ->where('mb_id',$request->mb_id)
        ->leftJoin(\DB::raw($query),'maintain_back_details.product_id','=','p.id')
        ->leftJoin(\DB::raw($query1),'maintain_back_details.product_id','=','np.product_id')
        ->orderBy('id','ASC')->get();
        
        return response()->json(['msg'=>$products,'status'=>true]);

    }
}
