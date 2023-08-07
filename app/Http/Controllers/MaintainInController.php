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
use App\Models\MaintenanceIn;
class MaintainInController extends Controller
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
        $active_menu="mainin_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh sách nhận bảo hành </li>';
        $maintainins=MaintenanceIn::orderBy('id','DESC')->paginate($this->pagesize);
      
        return view('backend.maintainins.index',compact('maintainins','breadcrumb','active_menu'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="mainin_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('maintainin.index').'">Danh sách nhận bảo hành</a></li>
        <li class="breadcrumb-item active" aria-current="page"> thêm nhận bảo hành </li>';
         $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
         $categories = \App\Models\Category::where('status','active')->orderBy('id','ASC')->get();
        return view('backend.maintainins.create',compact('breadcrumb','active_menu',  'bankaccounts' ,'categories'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request,[
            'customer_id'=>'numeric|required',
            'product_id'=>'numeric|required',
            'quantity'=>'numeric|required',
            'description'=>'string|nullable',
            'bank_id'=>'numeric|required',
            'shipcost'=>'numeric|nullable',
            
        ]);
        $data= $request->all();
        // return $data;
        $data['status']="received";
        $data['result']="pending";

        // $data['quantity']=$this->absnumber($data['quantity']);
        // $data['shipcost']=$this->absnumber($data['shipcost']);
        $product = \App\Models\Product::find($data['product_id']);
        if(!$product)
        {
            return back()->with('error','Không tìm thấy sản phẩm!');
        }
        if($data['shipcost'] && $data['shipcost']  > 0)
        {
            $data['final_amount'] = $data['shipcost'];
        }
        else
        {
            $data['final_amount'] = 0;
        }
        $data['paid_amount'] = 0;
        $user = auth()->user();
        $data['vendor_id'] = $user->id;
        $bank = \App\Models\Bankaccount::find($data['bank_id']);
        if($data['shipcost'] && $bank->total < $data['shipcost'])
        {
            return back()->with('error','Không đủ tiền trả phí vận chuyển!');
        }
        $maintainin = MaintenanceIn::create($data);

        if($maintainin){

            //add inventory maintenance
            \App\Models\InventoryMaintenance::addPro($data['product_id'],$data['quantity']);
            ////////
            if($data['shipcost'] && $data['shipcost'] > 0)
            {
                 $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
                 $maintainin->shiptrans_id = $fts->id;
                 BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
            }
            ///create log /////////////
            $content = 'save new maintainin product_id: '.$data['product_id'].' quantity: '.$data['quantity'];
            \App\Models\Log::insertLog($content,$user->id);

            return redirect()->route('maintainin.index')->with('success','thành công!');
        }
        else
        {
            return back()->with('error','Lỗi xãy ra!');
        }    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $maintainin = MaintenanceIn::find($id);
        if($maintainin)
        {
            $active_menu="mainin_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('maintainin.index').'">Danh sách nhận bảo hành</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Xem phiếu nhập bảo hành </li>';
            return view('backend.maintainins.show',compact('breadcrumb','active_menu',   'maintainin'));
    
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
        $maintainin = MaintenanceIn::find($id);
        if($maintainin)
        {
            $active_menu="mainin_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('maintainin.index').'">Danh sách nhận bảo hành</a></li>
            <li class="breadcrumb-item active" aria-current="page"> cập nhật phiếu nhận bảo hành </li>';
             $bankaccounts = Bankaccount::where('status','active')->orderBy('id','ASC')->get();
             $categories = \App\Models\Category::where('status','active')->orderBy('id','ASC')->get();
             $bank_id = 0;
             $ship_amount = 0;
             if($maintainin->shiptrans_id)
             {
                 $shiptrans = FreeTransaction::where('id',$maintainin->shiptrans_id)->first();
                 $bank_id = $shiptrans->bank_id;
                 $ship_amount = $shiptrans->total;
             }   
             return view('backend.maintainins.edit',compact('breadcrumb','active_menu',  'bankaccounts' ,'categories','bank_id','ship_amount','maintainin'));
    
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
            'customer_id'=>'numeric|required',
            'product_id'=>'numeric|required',
            'quantity'=>'numeric|required',
            'description'=>'string|nullable',
            'bank_id'=>'numeric|required',
            'shipcost'=>'numeric|nullable',
            
        ]);
        $maintainin = MaintenanceIn::find($id);
        if($maintainin && $maintainin->status =='received' && $maintainin->result == 'pending')
        {
            //process new data
            $data= $request->all();
            $data['status']="received";
            $data['result']="pending";
            if($data['shipcost'] && $data['shipcost']  > 0)
            {
                $data['final_amount'] = $data['shipcost'];
            }
            else
            {
                $data['final_amount'] = 0;
            }
            $data['paid_amount'] = 0;
            $user = auth()->user();
            $data['vendor_id'] = $user->id;
            $bank = \App\Models\Bankaccount::find($data['bank_id']);
            if($data['shipcost'] && $bank->total +$maintainin->shipcost < $data['shipcost'])
            {
                return back()->with('error','Không đủ tiền trả phí vận chuyển!');
            }
            //remove privious action
            \App\Models\InventoryMaintenance::removePro($maintainin->product_id,$maintainin->quantity);
            ///delete ship invoice
            if($maintainin->shiptrans_id)
            {
                    $fts = FreeTransaction::find($maintainin->shiptrans_id);
                    if($fts)
                    {
                        $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                        if($banktrans)
                            BankTransaction::removeBankTrans($banktrans);
                        $fts->delete();
                    }
            }
            //save new data
            //add inventory maintenance
            \App\Models\InventoryMaintenance::addPro($data['product_id'],$data['quantity']);
            ////////
            $maintainin->fill($data);
            if($data['shipcost'] && $data['shipcost'] > 0)
            {
                $fts= FreeTransaction::addFreeTrans($data['shipcost'],$data['bank_id'],-1,'ship',$user->id);
                $maintainin->shiptrans_id = $fts->id;
                BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$fts->id,'fi',$data['shipcost']);
            }
            $maintainin->save();

            ///create log /////////////
            $content = 'update maintainin id'.$maintainin->id.' product_id: '.$data['product_id'].' quantity: '.$data['quantity'];
            \App\Models\Log::insertLog($content,$user->id);

            return redirect()->route('maintainin.index')->with('success','thành công!');
    

        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu hoặc hàng hóa đã được xữ lý!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $maintainin = MaintenanceIn::find($id);
        if($maintainin && $maintainin->status =='received' && $maintainin->result == 'pending')
        {
             //remove privious action
             \App\Models\InventoryMaintenance::removePro($maintainin->product_id,$maintainin->quantity);
             ///delete ship invoice
             if($maintainin->shiptrans_id)
             {
                     $fts = FreeTransaction::find($maintainin->shiptrans_id);
                     if($fts)
                     {
                         $banktrans = BankTransaction::where('doc_type','fi')->where('doc_id',$fts->id)->first();
                         if($banktrans)
                             BankTransaction::removeBankTrans($banktrans);
                         $fts->delete();
                     }
             }
             $user = auth()->user();
               ///create log /////////////
            $content = 'delete maintainin id'.$maintainin->id.' product_id: '.$maintainin->product_id.' quantity: '.$maintainin->quantity;
            \App\Models\Log::insertLog($content,$user->id);

            $maintainin->delete();
            return redirect()->route('maintainin.index')->with('success','Xóa thành công!'); 

        }
        else
        {

            return back()->with('error','Không tìm thấy dữ liệu hoặc hàng hóa đã được xữ lý!');
    
        }
        
    }
}
