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
use App\Models\UGroup;
class WarehouseinController extends Controller
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
        $active_menu="wi_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh sách nhập kho </li>';
        $warehouseins=Warehousein::orderBy('id','DESC')->paginate($this->pagesize);
      
        return view('backend.warehouseins.index',compact('warehouseins','breadcrumb','active_menu'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="wi_add";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousein.index').'">Nhập kho</a></li>
        <li class="breadcrumb-item active" aria-current="page"> thêm mới </li>';
        $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
        $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
        $ugroups=UGroup::where('status','active')->orderBy('id','ASC')->get();
        $user = auth()->user();
        $categories = \App\Models\Category::where('is_parent',0)
        ->where('status','active')->orderBy('title','ASC')->get();
        return view('backend.warehouseins.create',compact('breadcrumb','active_menu', 'warehouses','bankaccounts','user','ugroups','categories'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // return $request->all();
        $data = $request->importDoc;
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
        $cost_extra = ($data['shipcost'] -  $data['discount_amount'])/ $count_item ;
        $data['cost_extra'] = $cost_extra ;
        $wi = Warehousein::create($data);
        // return $wi;
        ////////////////////////////////////
        foreach ($details as $detail)
        {
            $product_detail['wi_id'] = $wi->id;
            $product_detail['product_id']= $detail['id'];
            $product_detail['quantity'] = $detail['quantity'];
            $product_detail['price'] = $detail['price'];
            $product_detail['wh_id'] = $data['wh_id'];
            //save expired days
            $product = Product::find($detail['id']);
            $start_date = date('Y-m-d H:i:s');
            if($product->expired)
            {
                $strday = '+' . $product->expired*30 .' days';
                $end_date = date("Y-m-d H:i:s", strtotime( $strday, strtotime($start_date)));
                $product_detail['expired_at'] = $end_date;
            }

            //  return $product_detail;
            WarehouseInDetail::create($product_detail);
            //increase stock
            Inventory::addProduct($product_detail['product_id'], $data['wh_id'],$product_detail['quantity'], $product_detail['price'] ,$cost_extra);
            ///update group price//////
            $product_prices = $detail['pricelist'];
            foreach ($product_prices as $product_price)
            {
                \App\Models\GroupPrice::updateProductPriceId($product_price['gpid'],$product_price['price']);
            }
        }
        ///create SupTransaction
        $sps = SupTransaction::createSubTrans($wi->id,'wi',1,$data['final_amount'], $data['supplier_id']);
        $wi->suptrans_id = $sps->id;
        ///create paid transaction
        if($data['paid_amount']> 0)
        {
            $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$wi->id,'wi',$data['paid_amount']);
            SupTransaction::createSubTrans($bank_doc->id,'fi',-1, $data['paid_amount'], $data['supplier_id']); 
            $in_ids=array();
            $in_id = new \App\Models\Number();
            $in_id->id = $bank_doc->id;
            array_push($in_ids,$in_id);
            $wi->paidtrans_ids = json_encode($in_ids);
        }
       ///create ship invocie ///////////
       if($data['shipcost'] > 0)
       {
            $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
            $wi->shiptrans_id = $fts->id;
            BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
       }
       
       $wi->save();
       ///create log /////////////
       $content = 'insert warehouse in stock: '.$data['wh_id'].' total: '.$data['final_amount'];
       \App\Models\Log::insertLog($content,$user->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        
        $warehousein = Warehousein::find($id);
        if($warehousein)
        {
            $active_menu="i_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousein.index').'">DS nhập kho</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Xem chi tiết</li>';
            $wi_details = WarehouseInDetail::where('wi_id',$id)->get();
            return view('backend.warehouseins.show',compact('breadcrumb','warehousein','active_menu','wi_details'));
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
        $warehousein = Warehousein::find($id);
        if($warehousein && $warehousein->status == 'active')
        {
            $active_menu="i_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousein.index').'">Danh sách nhập kho</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh phiếu nhập kho </li>';
            $warehouses = Warehouse::where('status','active')->orderBy('id','ASC')->get();
            $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
            $paid_trans = null;
            $ship_trans = null;
            $bank_id = 0;
            $ship_amount = 0;
            
            if($warehousein->paidtrans_ids)
            {
                $id_ins = json_decode($warehousein->paidtrans_ids); 
                $id_in = $id_ins[0];
                $paidtrans = BankTransaction::where('id', $id_in->id)->first();
                $bank_id = $paidtrans->bank_id;
            }   
            if($warehousein->shiptrans_id)
            {
                $shiptrans = FreeTransaction::where('id',$warehousein->shiptrans_id)->first();
                $bank_id = $shiptrans->bank_id;
                $ship_amount = $shiptrans->total;
            }   
            $user = auth()->user();
            return view('backend.warehouseins.edit',compact('breadcrumb','warehousein','active_menu','warehouses','bankaccounts','user','bank_id','ship_amount'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function getProductList(Request $request)
    {
        $this->validate($request,[
            'wi_id'=>'numeric|required',
        ]);
        $query = "(select id,photo, title from products ) as p";
        $products = DB::table('warehouse_in_details')
        ->select ('warehouse_in_details.price','warehouse_in_details.product_id','warehouse_in_details.quantity', 'p.title','p.photo','p.id')
        ->where('wi_id',$request->wi_id)
        ->leftJoin(\DB::raw($query),'warehouse_in_details.product_id','=','p.id')
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
        //
        if(!$this->checkRole(1))
        {
            return redirect()->route('unauthorized');
        }
        $data = $request->importDoc;
        $oldwarehousein = WarehouseIn::find($data['id']);
        // return $oldwarehousein;
        
        if($data['id']==null || $data['id']==0 || $oldwarehousein==null || $oldwarehousein->status == 'returned')
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
        $detailpros = WarehouseInDetail::where('wi_id',$data['id'])->get();
        $flag = 0;
        foreach($detailpros as $dtpro)
        {
            if($dtpro->qty_sold > 0)
                $flag = 1;
        }
        if($flag == 1)
        {
            return response()->json(['msg'=>'Đã xuất kho hàng hóa trong phiếu nhập!','status'=>false]);
        }
        $bank_docs = BankTransaction::where('doc_id',$oldwarehousein->id)
            ->where('doc_type','wi')->get();
        $sum_paid = 0;
        foreach ($bank_docs as $bank_doc)
        {
            $sum_paid += $bank_doc->total;
        }
        if($sum_paid != $oldwarehousein->paid_amount )
        {
            return response()->json(['msg'=>'Đã có nhiều giao dịch trả tiền cho phiếu nhập hàng. Không thể thay đổi thông tin!','status'=>false]);
        }
        //delete all old product detail
        
        foreach($detailpros as $dtpro)
        {
            WarehouseInDetail::deleteDetailPro($dtpro,$oldwarehousein->cost_extra,$oldwarehousein->wh_id);
        }
        ///delete sup trans 1 for importing
        SupTransaction::removeSubTrans($oldwarehousein->suptrans_id);
        ///
         ///delete paid transaction
        ///delete paid transaction
        if($oldwarehousein->paidtrans_ids)
        {
            $in_ids = json_decode($oldwarehousein->paidtrans_ids);
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
         
       if($oldwarehousein->shiptrans_id)
       {
            $fts = FreeTransaction::find($oldwarehousein->shiptrans_id);
            if($fts)
            {
                $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                if($banktrans)
                    BankTransaction::removeBankTrans($banktrans);
                $fts->delete();
            }
            
       }
      
        
        ///save new product detail ////////////
        ////average price///////////////////

        $details = $request->products;
        $count_item = 0;
        foreach ($details as $detail)
        {
            $count_item += $detail['quantity'];
        }
        $cost_extra = ($data['shipcost'] -  $data['discount_amount'])/ $count_item ;
        $data['cost_extra'] = $cost_extra;
        $status = $oldwarehousein->fill($data)->save();
        ////////////////////////////////////
        foreach ($details as $detail)
        {
            $product_detail['wi_id'] = $oldwarehousein->id;
            $product_detail['product_id']= $detail['id'];
            $product_detail['quantity'] = $detail['quantity'];
            $product_detail['price'] = $detail['price'];
            $product_detail['wh_id'] = $data['wh_id'];
             //save expired days
             $product = Product::find($detail['id']);
             $start_date = date('Y-m-d H:i:s');
             if($product->expired)
             {
                 $strday = '+' . $product->expired*30 .' days';
                 $end_date = date("Y-m-d H:i:s", strtotime( $strday, strtotime($start_date)));
                 $product_detail['expired_at'] = $end_date;
             }
 
            //  return $product_detail;
            WarehouseInDetail::create($product_detail);
            //increase stock
            Inventory::addProduct($product_detail['product_id'], $data['wh_id'],$product_detail['quantity'], $product_detail['price'] ,$cost_extra);
            ///update group price//////
            $product_prices = $detail['pricelist'];
            foreach ($product_prices as $product_price)
            {
                \App\Models\GroupPrice::updateProductPriceId($product_price['gpid'],$product_price['price']);
            }
        }
        ///create SupTransaction
        $sps = SupTransaction::createSubTrans($oldwarehousein->id,'wi',1,$data['final_amount'], $data['supplier_id']);
        $oldwarehousein->suptrans_id = $sps->id;
        ///create paid transaction
        if($data['paid_amount']> 0)
        {
            $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$oldwarehousein->id,'wi',$data['paid_amount']);
            SupTransaction::createSubTrans($bank_doc->id,'fi',-1, $data['paid_amount'], $data['supplier_id']); 
            // $oldwarehousein->paidtrans_id = $bank_doc->id;
            $in_ids=array();
            $in_id = new \App\Models\Number();
            $in_id->id = $bank_doc->id;
            array_push($in_ids,$in_id);
            $oldwarehousein->paidtrans_ids = json_encode($in_ids);
        }
       ///create ship invocie ///////////
       if($data['shipcost'] > 0)
       {
            $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
            $oldwarehousein->shiptrans_id = $fts->id;
            BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
       }
       
       $oldwarehousein->save();
       ///create log /////////////
       $content = 'update warehouse in stock: '.$data['wh_id'].' total: '.$data['final_amount'];
       \App\Models\Log::insertLog($content,$user->id);
       return response()->json(['msg'=>'Cập nhật thành công!','status'=>true]);
    }
    public function warehouseinReturn(Request $request )
    {
        //
      
        $this->validate($request,[
            'id'=>'numeric|required',
            
        ]);
        $id = $request->id;
        $oldwarehousein = WarehouseIn::find( $id);
        // return $oldwarehousein;
        if( $oldwarehousein==null || $oldwarehousein->status == 'returned' )
            return back()->with('error','Không tìm thấy phiếu nhập kho!');
        $user = auth()->user();
        //check detail product are exported
        $detailpros = WarehouseInDetail::where('wi_id', $id)->get();
        $flag = 0;
        foreach($detailpros as $dtpro)
        {
            if($dtpro->qty_sold > 0)
                $flag = 1;
        }
        if($flag == 1)
        {
            return back()->with('error','Đã xuất kho hàng hóa trong phiếu nhập!Không thể trả hàng');
        }
        
        
        //return all old product detail
        
        foreach($detailpros as $dtpro)
        {
            WarehouseInDetail::returnDetailPro($dtpro,$oldwarehousein->cost_extra,$oldwarehousein->wh_id);
        }
        ///add return sup trans 1 for importing
        $sps = SupTransaction::createSubTrans($oldwarehousein->id,'wi',-1,$oldwarehousein->final_amount, $oldwarehousein->supplier_id);
        ///
        $oldwarehousein->status = 'returned';
       $oldwarehousein->save();
       ///create log /////////////
       $content = 'return warehouse in stock: '. $id.' total: '.$oldwarehousein->final_amount;
       \App\Models\Log::insertLog($content,$user->id);
       return redirect()->route('warehousein.index')->with('success','Trả hàng thành công!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        if(!$this->checkRole(1))
        {
            return redirect()->route('unauthorized');
        }
        $oldwarehousein = WarehouseIn::find( $id);
        // return $oldwarehousein;
        if( $oldwarehousein==null || $oldwarehousein->status == 'returned')
              return back()->with('error','Không tìm thấy phiếu nhập kho!');
       
        $user = auth()->user();
       
        //check detail product are exported
        $detailpros = WarehouseInDetail::where('wi_id',$id)->get();
        $flag = 0;
        foreach($detailpros as $dtpro)
        {
            if($dtpro->qty_sold > 0)
                $flag = 1;
        }
        if($flag == 1)
        {
            return back()->with('error','Hàng trong phiếu nhập kho đã xuất, không thể xóa!');
      
        }
        $bank_docs = BankTransaction::where('doc_id',$oldwarehousein->id)
            ->where('doc_type','wi')->get();
        
        $sum_paid = 0;
        foreach ($bank_docs as $bank_doc)
        {
            $sum_paid += $bank_doc->total;
        }
        if($sum_paid != $oldwarehousein->paid_amount )
        {
            return back()->with('error','Đã có nhiều giao dịch trả tiền cho phiếu nhập hàng. Không thể xóa!');
        }
        //delete all old product detail
        
        foreach($detailpros as $dtpro)
        {
            WarehouseInDetail::deleteDetailPro($dtpro,$oldwarehousein->cost_extra,$oldwarehousein->wh_id);
        }
        ///delete sup trans 1 for importing
        SupTransaction::removeSubTrans($oldwarehousein->suptrans_id);
        ///
         ///delete paid transaction
        if($oldwarehousein->paidtrans_ids)
        {
            $in_ids = json_decode($oldwarehousein->paidtrans_ids);
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
        ///create ship invocie ///////////
        
             
       if($oldwarehousein->shiptrans_id )
       {
            $fts = FreeTransaction::find($oldwarehousein->shiptrans_id);
            if($fts)
            {
                $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                if($banktrans)
                    BankTransaction::removeBankTrans($banktrans);
                $fts->delete();
            }
            
       }
      
         ///create log /////////////
       $content = 'update warehouse in stock: '.$oldwarehousein->wh_id.' total: '.$oldwarehousein->final_amount;
      \App\Models\Log::insertLog($content,$user->id);
       $oldwarehousein->delete();
       return redirect()->route('warehousein.index')->with('success','Xóa thành công!');

        
    }
    public function warehouseinSavePaid(Request $request)
    {
        $this->validate($request,[
            'id'=>'numeric|required',
            'paid_amount'=>'numeric|required',
            'bank_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $wi = Warehousein::find($data['id']);
        $user = auth()->user();
       
        if( $wi && $wi->status == 'active')
        {
             ///create paid transaction
            if($data['paid_amount'] > $wi->final_amount - $wi->paid_amount)
            {
                return back()->with('error','Số tiền trả lớn hơn số tiền nợ!');
            }
            if($data['paid_amount'] ==0 || $wi->is_paid == 1)
            {
                return back()->with('error','Số tiền trả không hợp lệ!');
            }
            $bankaccount = Bankaccount::find($data['bank_id']);
            if(!$bankaccount || $bankaccount->total < $data['paid_amount'])
            {
                return back()->with('error','Tài khoản không đủ tiền trả!');
            }
            
            $bank_doc = BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$wi->id,'wi',$data['paid_amount']);
            SupTransaction::createSubTrans($bank_doc->id,'fi',-1, $data['paid_amount'], $wi->supplier_id); 
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
            $content = 'paid money for import invoice: '.$data['id'].' total: '.$data['paid_amount'];
            \App\Models\Log::insertLog($content,$user->id);
            
            return redirect()->route('warehousein.index')->with('success','Đã thêm thanh toán cho phiếu nhập hàng!');
            
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function warehouseinPaid($id)
    {
        // return $id;
         
        $wi = Warehousein::find($id);
         
        if( $wi && $wi->status == 'active')
        {
             $bankaccounts = Bankaccount::where('status','active')->get();
             $active_menu="i_list";
             
             $breadcrumb = '
             <li class="breadcrumb-item"><a href="#">/</a></li>
             <li class="breadcrumb-item  " aria-current="page"><a href="'.route('warehousein.index').'">Ds nhập kho</a></li>
             <li class="breadcrumb-item active" aria-current="page"> trả tiền phiếu nhập </li>';
             return view('backend.warehouseins.paid',compact('wi','breadcrumb','bankaccounts','active_menu'));
             
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
     
}
