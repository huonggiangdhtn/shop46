<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Warehouseout;
use App\Models\SupTransaction; 
use App\Models\WarehouseoutDetail;
use App\Models\Bankaccount;
use App\Models\BankTransaction;
use App\Models\FreeTransaction;
use App\Models\UGroup;
use App\Models\User;
class WarehouseoutController extends Controller
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
        $active_menu="wo_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh sách bán hàng </li>';
        $warehouseouts=warehouseout::orderBy('id','DESC')->paginate($this->pagesize);
      
        return view('backend.warehouseouts.index',compact('warehouseouts','breadcrumb','active_menu'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="wo_add";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehouseout.index').'">Ds bán hàng</a></li>
        <li class="breadcrumb-item active" aria-current="page"> thêm mới </li>';
        $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
        $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
        $deliveries= User::where('role','delivery')->where('status','active')->orderBy('id','ASC')->get();
        // $ugroups=UGroup::where('status','active')->orderBy('id','ASC')->get();
        $user = auth()->user();
        return view('backend.warehouseouts.create',compact('breadcrumb','active_menu', 'warehouses','bankaccounts','user','deliveries'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->importDoc;
        // return $data;
        if($data['paid_amount'] == $data['final_amount'])
            $data['is_paid'] = 1;
        else
            $data['is_paid'] = 0;
       
        $user = auth()->user();
        $data['vendor_id'] = $user->id;
        if ($data['discount_amount'] == null)
            $data['discount_amount']=0;
        ///save product detail ////////////
        ////average price///////////////////
        $details = $request->products;
        $count_item = 0;
        foreach ($details as $detail)
        {
            $count_item += $detail['quantity'];
        }
        $cost_extra = ($data['discount_amount'])/ $count_item ;
        $data['cost_extra'] = $cost_extra ;
        $wo = Warehouseout::create($data);
        // return $wi;
        ////////////////////////////////////
        foreach ($details as $detail)
        {
            $product_detail['wo_id'] = $wo->id;
            $product_detail['product_id']= $detail['id'];
            $product_detail['quantity'] = $detail['quantity'];
            $product_detail['price'] = $detail['price'];
             //save expired days
            $product = Product::find($detail['id']);
            $start_date = date('Y-m-d H:i:s');
            if($product->expired)
            {
                $strday = '+' . $product->expired*30 .' days';
                $end_date = date("Y-m-d 23:59:59", strtotime( $strday, strtotime($start_date)));
                $product_detail['expired_at'] = $end_date;
            }
            $in_ids = Inventory::subProduct($product_detail['product_id'], $data['wh_id'],$product_detail['quantity'], $product_detail['price'] ,$cost_extra);
            // return ($in_ids);
            $product_detail['in_ids'] = json_encode($in_ids);
            WarehouseoutDetail::create($product_detail);
            //decrease stock
             
        }
        ///create SupTransaction
        $sps = SupTransaction::createSubTrans($wo->id,'wo',-1,$data['final_amount'], $data['customer_id']);
        $wo->suptrans_id = $sps->id;
        ///create paid transaction
        if($data['paid_amount']> 0)
        {
            $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'],1,$wo->id,'wo',$data['paid_amount']);
            SupTransaction::createSubTrans($bank_doc->id,'fi',1, $data['paid_amount'], $data['customer_id']); 
            $in_ids=array();
            $in_id = new \App\Models\Number();
            $in_id->id = $bank_doc->id;
            array_push($in_ids,$in_id);
            $wo->paidtrans_ids = json_encode($in_ids);
 
        }
       ///create ship invocie ///////////
       if($data['shipcost'] > 0)
       {
            $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
            $wo->shiptrans_id = $fts->id;
            BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
       }
       
       $wo->save();
       ///create log /////////////
       $content = 'insert warehouse out stock: '.$data['wh_id'].' total: '.$data['final_amount'];
       \App\Models\Log::insertLog($content,$user->id);
       return response()->json(['msg'=>'Thêm đơn hàng thành công!','status'=>true]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $warehouseout = Warehouseout::find($id);
        if($warehouseout)
        {
            $active_menu="i_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehouseout.index').'">DS bán hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Xem chi tiết</li>';
            $wo_details = WarehouseoutDetail::where('wo_id',$id)->get();
            return view('backend.warehouseouts.show',compact('breadcrumb','warehouseout','active_menu','wo_details'));
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
        if(!$this->checkRole(1))
        {
            return redirect()->route('unauthorized');
        }
        $warehouseout = Warehouseout::find($id);
        if($warehouseout && $warehouseout->status == 'active')
        {
            $active_menu="wo_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehouseout.index').'">Danh sách bán hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh phiếu bán hàng </li>';
            $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
            $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
            $deliveries= User::where('role','delivery')->where('status','active')->orderBy('id','ASC')->get();
        
            $paid_trans = null;
            $ship_trans = null;
            $bank_id = 0;
            $ship_amount = 0;
            if($warehouseout->paidtrans_ids)
            {
                $id_ins = json_decode($warehouseout->paidtrans_ids); 
                $id_in = $id_ins[0];
                $paidtrans = BankTransaction::where('id',$id_in->id)->first();
                $bank_id = $paidtrans->bank_id;
            }   
            if($warehouseout->shiptrans_id)
            {
                $shiptrans = FreeTransaction::where('id',$warehouseout->shiptrans_id)->first();
                $bank_id = $shiptrans->bank_id;
                $ship_amount = $shiptrans->total;
            }   
            $user = auth()->user();
            
            return view('backend.warehouseouts.edit',compact('breadcrumb','warehouseout','active_menu','warehouses','bankaccounts','user','bank_id','ship_amount','deliveries'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }

    }
    public function deliveryPrint($id)
    {
        $warehouseout = Warehouseout::find($id);
        if($warehouseout && $warehouseout->status == 'active')
        {
            $active_menu="wo_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehouseout.index').'">Danh sách bán hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">phiếu gửi hàng </li>';
           
            return view('backend.warehouseouts.deprint',compact('breadcrumb','warehouseout','active_menu'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function getProductList(Request $request)
    {
        $this->validate($request,[
            'wo_id'=>'numeric|required',
        ]);
        $wo = Warehouseout::find($request->wo_id);
        $query = "(select id,photo, title from products ) as p";
        $query1 = "(select product_id ,quantity from inventories where wh_id = ".$wo->wh_id.") as np";
               
        $products = DB::table('warehouseout_details')
        ->select ('warehouseout_details.price','warehouseout_details.product_id','warehouseout_details.quantity', 'p.title','p.photo','p.id','np.quantity as stock_qty')
        ->where('wo_id',$request->wo_id)
        ->leftJoin(\DB::raw($query),'warehouseout_details.product_id','=','p.id')
        ->leftJoin(\DB::raw($query1),'warehouseout_details.product_id','=','np.product_id')
        ->orderBy('id','ASC')->get();
        foreach($products as $product)
        {
            $query = "select b.*,c.id as idg, c.title from (select id, price, ugroup_id from group_prices where product_id = ".$product->id
            ." ) as b left join (select id,title from u_groups) as c on b.ugroup_id = c.id  order by c.id ASC";
            $prices = DB::select($query) ;
      
            $product->groupprice=$prices;
        }
        return response()->json(['msg'=>$products,'status'=>true]);

    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if(!$this->checkRole(1))
        {
            return redirect()->route('unauthorized');
        }
        // return $request->all();
        $data = $request->importDoc;
        $oldwarehouseout = WarehouseOut::find($id);
        // return $oldwarehouseout;
        if($data['id']==null || $data['id']==0 || $oldwarehouseout==null || $oldwarehouseout->status == 'return')
            return response()->json(['msg'=>'không tìm thấy!','status'=>false]);
       
        if($data['paid_amount'] == $data['final_amount'])
            $data['is_paid'] = 1;
        else
            $data['is_paid'] = 0;
       
        $user = auth()->user();
        $data['vendor_id'] = $user->id;
        if ($data['discount_amount'] == null)
            $data['discount_amount']=0;
        //check detail product are exported
        $detailpros = WarehouseoutDetail::where('wo_id',$data['id'])->get();
        
        $bank_docs = BankTransaction::where('doc_id',$oldwarehouseout->id)
            ->where('doc_type','wo')->get();
        
        $sum_paid = 0;
        foreach ($bank_docs as $bank_doc)
        {
            $sum_paid += $bank_doc->total;
        }
        if($sum_paid != $oldwarehouseout->paid_amount )
        {
            return response()->json(['msg'=>'Đã có nhiều giao dịch trả tiền cho phiếu xuất hàng. Không thể thay đổi thông tin!','status'=>false]);
        }
        //delete all old product detail
        
        foreach($detailpros as $dtpro)
        {
            WarehouseoutDetail::deleteDetailPro($dtpro,$oldwarehouseout->cost_extra,$oldwarehouseout->wh_id);
        }
        ///delete sup trans 1 for importing
        SupTransaction::removeSubTrans($oldwarehouseout->suptrans_id);
        ///
         ///delete paid transaction
        if($oldwarehouseout->paidtrans_ids)
        {
            $in_ids = json_decode($oldwarehouseout->paidtrans_ids);
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
         
       if($oldwarehouseout->shiptrans_id)
       {
            $fts = FreeTransaction::find($oldwarehouseout->shiptrans_id);
            if($fts)
            {
                $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                if($banktrans)
                    BankTransaction::removeBankTrans($banktrans);
                $fts->delete();
            }
            
       }
        
         ///save product detail ////////////
        ////average price///////////////////
        $details = $request->products;
        $count_item = 0;
        foreach ($details as $detail)
        {
            $count_item += $detail['quantity'];
        }
        $cost_extra = ($data['discount_amount'])/ $count_item ;
        $data['cost_extra'] = $cost_extra ;
        $oldwarehouseout->fill($data)->save();

        // return $wi;
        ////////////////////////////////////
        foreach ($details as $detail)
        {
            $product_detail['wo_id'] = $oldwarehouseout->id;
            $product_detail['product_id']= $detail['id'];
            $product_detail['quantity'] = $detail['quantity'];
            $product_detail['price'] = $detail['price'];
            $product = Product::find($detail['id']);
            $start_date = date('Y-m-d H:i:s');
            if($product->expired)
            {
                $strday = '+' . $product->expired*30 .' days';
                $end_date = date("Y-m-d 23:59:59", strtotime( $strday, strtotime($start_date)));
                $product_detail['expired_at'] = $end_date;
            }
            $in_ids = Inventory::subProduct($product_detail['product_id'], $data['wh_id'],$product_detail['quantity'], $product_detail['price'] ,$cost_extra);
            // return ($in_ids);
            $product_detail['in_ids'] = json_encode($in_ids);
            WarehouseoutDetail::create($product_detail);
            //decrease stock
             
        }
        ///create SupTransaction
        $sps = SupTransaction::createSubTrans($oldwarehouseout->id,'wo',-1,$data['final_amount'], $data['customer_id']);
        $oldwarehouseout->suptrans_id = $sps->id;
        ///create paid transaction
        if($data['paid_amount']> 0)
        {
            $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'],1,$oldwarehouseout->id,'wo',$data['paid_amount']);
            SupTransaction::createSubTrans($bank_doc->id,'fi',1, $data['paid_amount'], $data['customer_id']); 
            $in_ids=array();
            $in_id = new \App\Models\Number();
            $in_id->id = $bank_doc->id;
            array_push($in_ids,$in_id);
            $oldwarehouseout->paidtrans_ids = json_encode($in_ids);
        }
       ///create ship invocie ///////////
       if($data['shipcost'] > 0)
       {
            $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
            $oldwarehouseout->shiptrans_id = $fts->id;
            BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
       }
       
       $oldwarehouseout->save();
       ///create log /////////////
       $content = 'update warehouse out stock: '.$data['wh_id'].' total: '.$data['final_amount'];
       \App\Models\Log::insertLog($content,$user->id);
       return response()->json(['msg'=>'Cập nhật đơn hàng thành công!','status'=>true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
           // return $request->all();
            if(!$this->checkRole(1))
            {
                return redirect()->route('unauthorized');
            }
            $oldwarehouseout = WarehouseOut::find($id);
           // return $oldwarehouseout;
            if(  $oldwarehouseout==null || $oldwarehouseout->status == 'return')
                return back()->with('error','Không tìm thấy dữ liệu');
            $user = auth()->user();
           //check detail product are exported
            $detailpros = WarehouseoutDetail::where('wo_id',$oldwarehouseout->id)->get();
            $bank_docs = BankTransaction::where('doc_id',$oldwarehouseout->id)
               ->where('doc_type','wo')->get();
            $sum_paid = 0;
            foreach ($bank_docs as $bank_doc)
            {
                $sum_paid += $bank_doc->total;
            }
            if($sum_paid != $oldwarehouseout->paid_amount )
            {
                return back()->with('error','Đã có nhiều giao dịch trả tiền cho phiếu nhập hàng. Không thể xóa!');
            }
           //delete all old product detail
           
           foreach($detailpros as $dtpro)
           {
               WarehouseoutDetail::deleteDetailPro($dtpro,$oldwarehouseout->cost_extra,$oldwarehouseout->wh_id);
           }
           ///delete sup trans 1 for importing
           SupTransaction::removeSubTrans($oldwarehouseout->suptrans_id);
           ///
            ///delete paid transaction
           if($oldwarehouseout->paidtrans_ids)
           {
                $in_ids = json_decode($oldwarehouseout->paidtrans_ids);
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
            
          if($oldwarehouseout->shiptrans_id)
          {
               $fts = FreeTransaction::find($oldwarehouseout->shiptrans_id);
               if($fts)
               {
                   $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                   if($banktrans)
                       BankTransaction::removeBankTrans($banktrans);
                   $fts->delete();
               }
               
          }
          $content = 'delete warehouse out stock: '.$oldwarehouseout->wh_id.' total: '.$oldwarehouseout->final_amount;
          \App\Models\Log::insertLog($content,$user->id);
          $oldwarehouseout->delete();
          return redirect()->route('warehouseout.index')->with('success','Xóa thành công!'); 
    }
    public function warehouseoutPaid($id)
    {
        // return $id;
         
        $wo = Warehouseout::find($id);
         
        if( $wo)
        {
             $bankaccounts = Bankaccount::where('status','active')->get();
             $active_menu="wo_list";
             
             $breadcrumb = '
             <li class="breadcrumb-item"><a href="#">/</a></li>
             <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehouseout.index').'">Ds bán hàng</a></li>
             <li class="breadcrumb-item active" aria-current="page">  </li>';
             return view('backend.warehouseouts.paid',compact('wo','breadcrumb','bankaccounts','active_menu'));
             
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function warehouseoutSavePaid(Request $request)
    {
        $this->validate($request,[
            'id'=>'numeric|required',
            'paid_amount'=>'numeric|required',
             
        ]);
        $data = $request->all();
        $wi = Warehouseout::find($data['id']);
        $user = auth()->user();
       
        if( $wi)
        {
             ///create paid transaction
            
            if($data['paid_amount'] ==0 || $wi->is_paid == 1)
            {
                return back()->with('error','Số tiền trả không hợp lệ!');
            }
            $bankaccount = Bankaccount::find($data['bank_id']);
           
            
            $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'], 1,$wi->id,'wo',$data['paid_amount']);
            SupTransaction::createSubTrans($bank_doc->id,'fi', 1, $data['paid_amount'], $wi->customer_id); 
            $wi->paid_amount += $data['paid_amount'];
            if($wi->paid_amount == $wi->final_amount)
                $wi->is_paid = true;

              //save ids in paid_ids
              $in_ids = array();
              if($wi->paidtrans_ids )
              {
                  $in_ids = json_decode($wi->paidtrans_ids);
              }
              $in_id = new \App\Models\Number();
              $in_id->id = $bank_doc->id;
              array_push($in_ids,$in_id);
              $wi->paidtrans_ids = json_encode($in_ids);
              
            $wi->save();
            ///create log /////////////
            $user = auth()->user();
            $content = 'paid money for selling invoice: '.$data['id'].' total: '.$data['paid_amount'];
            \App\Models\Log::insertLog($content,$user->id);
            
            return redirect()->route('warehouseout.index')->with('success','Đã thêm thanh toán cho phiếu bán hàng!');
            
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function warehouseoutReturn(Request $request )
    {
        //
      
        $this->validate($request,[
            'id'=>'numeric|required',
            
        ]);
        $id = $request->id;
        $oldwarehouseout = Warehouseout::find( $id);
        // return $oldwarehousein;
        if( $oldwarehouseout==null || $oldwarehouseout->status == 'returned' )
            return back()->with('error','Không tìm thấy phiếu nhập kho!');
        $user = auth()->user();
        //check detail product are exported
        $detailpros = WarehouseoutDetail::where('wo_id', $id)->get();
        
        //return all old product detail
        
        foreach($detailpros as $dtpro)
        {
            WarehouseOutDetail::returnDetailPro($dtpro,$oldwarehouseout->cost_extra,$oldwarehouseout->wh_id);
        }
        ///add return sup trans 1 for importing
        $sps = SupTransaction::createSubTrans($oldwarehouseout->id,'wo',+1,$oldwarehouseout->final_amount, $oldwarehouseout->customer_id);
        ///
        $oldwarehouseout->status = 'returned';
       $oldwarehouseout->save();
       ///create log /////////////
       $content = 'return warehouseout stock: '. $id.' total: '.$oldwarehouseout->final_amount;
       \App\Models\Log::insertLog($content,$user->id);
       return redirect()->route('warehouseout.index')->with('success','Trả hàng thành công!');

    }
    public function warehouseoutReturnall(Request $request )
    {
        $this->validate($request,[
            'id'=>'numeric|required',
            
        ]);
        $id = $request->id;
        $wo = Warehouseout::find( $id);
        if($wo && $wo->status == 'active')
        {
            $active_menu="wo_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehouseout.index').'">Danh sách bán hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> trả hàng hoàn tiền </li>';
             $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
            $user = auth()->user();
            return view('backend.warehouseouts.returnpaid',compact('breadcrumb','wo','active_menu', 'bankaccounts','user'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function warehouseoutSaveReturnall(Request $request )
    {
        //
      
        $this->validate($request,[
            'id'=>'numeric|required',
            'bank_id'=>'numeric|required',
            'paid_amount'=>'numeric|required',
        ]);
        $data = $request->all();
        $id = $request->id;
        $oldwarehouseout = Warehouseout::find( $id);
        // return $oldwarehousein;
        if( $oldwarehouseout==null || $oldwarehouseout->status == 'returned' )
            return back()->with('error','Không tìm thấy phiếu nhập kho!');
        $user = auth()->user();
        //check detail product are exported
        $detailpros = WarehouseoutDetail::where('wo_id', $id)->get();
        
        //return all old product detail
        
        foreach($detailpros as $dtpro)
        {
            WarehouseOutDetail::returnDetailPro($dtpro,$oldwarehouseout->cost_extra,$oldwarehouseout->wh_id);
        }
        ///add return sup trans 1 for importing
        $sps = SupTransaction::createSubTrans($oldwarehouseout->id,'wo',1,$oldwarehouseout->final_amount, $oldwarehouseout->customer_id);
      ///add return money sup
        if($oldwarehouseout->paid_amount > 0)
        {
            $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'], -1,$oldwarehouseout->id,'wo',$data['paid_amount']);
            SupTransaction::createSubTrans($bank_doc->id,'fi', -1, $data['paid_amount'], $oldwarehouseout->customer_id); 
          
        }
        $oldwarehouseout->status = 'returned';
        $oldwarehouseout->save();
       ///create log /////////////
       $content = 'return warehouseout and paid id : '. $id.' total: '.$oldwarehouseout->final_amount;
       \App\Models\Log::insertLog($content,$user->id);
       return redirect()->route('warehouseout.index')->with('success','Trả hàng thành công!');
    }
}
