<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\User;
use App\Models\Warehousein;
use Illuminate\Support\Facades\DB;
class SupplierController extends Controller
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
        $active_menu="sup_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page">Nhà cung cấp </li>';
        $suppliers=User::where('role', 'supplier')->orwhere('role','supcustomer')
            ->orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.suppliers.index',compact('suppliers','breadcrumb','active_menu'));

    }
    public function supplierSort(Request $request)
    {
        $this->validate($request,[
            'field_name'=>'string|required',
            'type_sort'=>'required|in:DESC,ASC',
        ]);
    
        $active_menu="sup_list";
        $searchdata =$request->datasearch;
        $suppliers = DB::table('users')
        ->where('role', 'supplier')->orwhere('role','supcustomer')
        ->orderBy($request->field_name, $request->type_sort)
        ->paginate($this->pagesize)->withQueryString();;
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Nhà cung cấp</a></li>
         ';
        return view('backend.suppliers.index',compact('suppliers','breadcrumb','searchdata','active_menu'));
    }
    public function supplierStatus(Request $request)
    {
        if($request->mode =='true')
        {
            DB::table('users')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            DB::table('users')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    public function supplierJsearch(Request $request)
    {
        if($request->data  )
        { 
            $searchdata =$request->data;
             $suppliers = DB::table('users')
             ->select ('users.id','users.full_name as title' )
             ->where(function($query1) use($searchdata)  
             {
                 $query1->where('full_name','LIKE','%'.$searchdata.'%')
                       ->orwhere('phone','LIKE','%'.$searchdata.'%');
             })
             ->where(function($query)  
             {
                 $query->where('role', 'supplier')
                       ->orWhere('role', 'supcustomer');
             })
             ->get();
             return response()->json(['msg'=>$suppliers,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }

    }
    public function supplierSearch(Request $request)
    {
        if($request->datasearch)
        {
            $active_menu="sup_list";
            $searchdata =$request->datasearch;
            $suppliers = DB::table('users') 
            ->where(function($query) use ( $searchdata )
            {
                $query->where('phone','LIKE','%'.$searchdata.'%')
                      ->orWhere('full_name','LIKE','%'.$searchdata.'%');
            })
            ->where(function($query1)  
            {
                $query1->where('role', 'supplier')
                      ->orWhere('role', 'supcustomer');
            })
            ->paginate($this->pagesize)->withQueryString();
            // $query = "select * from users where role <>'admin' and (full_name like '%" 
            //             .$request->datasearch."%' or phone like '%".$request->datasearch."%')";
            // $users = DB::select($query)->paginate($this->pagesize)->withQueryString();;;
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Nhà cung cấp</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            return view('backend.suppliers.search',compact('suppliers','breadcrumb','searchdata','active_menu'));
        }
        else
        {
            return redirect()->route('supplier.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="sup_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Nhà cung cấp</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo nhà cung cấp </li>';
        return view('backend.suppliers.create',compact('breadcrumb','active_menu'));
  
    }

    /**
     * Store a newly created resource in storage.
     */
    public function supplierAdd(Request $request)
    {
        $this->validate($request,[
            'full_name'=>'string|required',
            'phone'=>'string|required',
            'address'=>'string|required',
            
        ]);
        // return $request->all();
        $data = $request->all();
        $olduser = User::where('phone',$data['phone'])->get();
        if(count($olduser) > 0)
            return response()->json(['msg'=>"số điện thoại đã tồn tại",'status'=>false]);
        $data['photo'] = asset('backend/assets/dist/images/profile-6.jpg');
        $data['email'] = $data['phone'].'@gmail.com';
        $data['password']=$data['phone'];
        $data['password'] = Hash::make($data['password']);
        $data['username'] = $data['phone'];
        $data['role'] = 'supplier';
        $data['status'] = 'inactive';
        $data['ugroup_id'] = 1;
        $status = User::create($data);
        if($status){
            return response()->json(['msg'=>$status,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>$status,'status'=>false]);
        }    
    }

    public function store(Request $request)
    {
        //
        $this->validate($request,[
            'full_name'=>'string|required',
            'description'=>'string|nullable',
            'phone'=>'string|required',
            'address'=>'string|required',
            'status'=>'nullable|in:active,inactive',
        ]);
        // return $request->all();

        $data = $request->all();
        //check user with phone
        $olduser = User::where('phone',$data['phone'])->get();
        if(count($olduser) > 0)
            return back()->with('error','Số điện thoại đã tồn tại!');

        $data['photo'] = asset('backend/assets/dist/images/profile-6.jpg');
        $data['email'] = $data['phone'].'@gmail.com';
        $data['password']=$data['phone'];
        $data['password'] = Hash::make($data['password']);
        $data['username'] = $data['phone'];
        $data['role'] = 'supplier';
        $status = User::create($data);
        if($status){
            return redirect()->route('supplier.index')->with('success','Tạo nhà cung cấp thành công!');
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
        $supplier = User::find($id);
        if($supplier)
        {
            $active_menu="sup_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Nhà cung cấp</a></li>
            <li class="breadcrumb-item active" aria-current="page"> xem công nợ cung cấp </li>';
            $suptrans = \App\Models\Suptransaction::where('supplier_id',$id)
                ->orderBy('id','DESC')
                ->paginate($this->pagesize*2)->withQueryString();;
            return view('backend.suppliers.show',compact('breadcrumb','active_menu','supplier','suptrans'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $supplier = User::find($id);
        if($supplier)
        {
            $active_menu="sup_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Nhà cung cấp</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh nhà cung cấp </li>';
            
            return view('backend.suppliers.edit',compact('breadcrumb','supplier','active_menu' ));
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
        $user = User::find($id);
        if($user)
        {
            $this->validate($request,[
                'full_name'=>'string|required',
                'description'=>'string|nullable',
                'address'=>'string|required',
                'status'=>'nullable|in:active,inactive',
            ]);
    
            $data = $request->all();
            $status = $user->fill($data)->save();
            if($status){
                return redirect()->route('supplier.index')->with('success','Cập nhật thành công');
            }
            else
            {
                return back()->with('error','Something went wrong!');
            }    
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
           
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $user = User::find($id);
        
        if($user)
        {
            $status = User::deleteUser($id);
            if($status){
                return redirect()->route('supplier.index')->with('success','Xóa thành công!');
            }
            else
            {
                return back()->with('error','Có lỗi xãy ra!');
            }    
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function supplierSavePaid(Request $request)
    {
        $this->validate($request,[
            'id'=>'numeric|required',
            'paid_amount'=>'numeric|required',
            'bank_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $supplier = User::find($data['id']);
        $user = auth()->user();
       
        if( $supplier)
        {
             ///create paid transaction
            
            if($data['paid_amount'] ==0 )
            {
                return back()->with('error','Số tiền trả không hợp lệ!');
            }
            $bankaccount = \App\Models\Bankaccount::find($data['bank_id']);
            if(!$bankaccount || $bankaccount->total < $data['paid_amount'])
            {
                return back()->with('error','Tài khoản không đủ tiền trả!');
            }
            
            $bank_doc = \App\Models\BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$supplier->id,'si',$data['paid_amount']);
            $subtrans = \App\Models\SupTransaction::createSubTrans($bank_doc->id,'fi',-1, $data['paid_amount'], $supplier->id); 
            $bank_doc->doc_id =  $subtrans->id;
            $bank_doc->save();
            //list all wi not paid order by time
            $warehouseins = \App\Models\Warehousein::where('supplier_id',$supplier->id)
            ->where('is_paid',false)->orderBy('id','ASC')->get();
            $paid_amount = $data['paid_amount'];
            foreach($warehouseins as $warehousein)
            {
                if($paid_amount >= ($warehousein->final_amount - $warehousein->paid_amount))
                {
                    $paid_amount -= ($warehousein->final_amount - $warehousein->paid_amount);
                    $warehousein->paid_amount = $warehousein->final_amount;
                    $warehousein->is_paid = true;
                    $warehousein->save();
                    
                }
                else
                {
                    $warehousein->paid_amount+= $paid_amount;
                    $warehousein->save();
                    $paid_amount = 0;
                }
                if($paid_amount == 0)
                    break;
            }
           
            ///create log /////////////
            $user = auth()->user();
            $content = 'paid money for supplier: '.$supplier->full_name.' total: '.$data['paid_amount'];
            \App\Models\Log::insertLog($content,$user->id);
            
            return redirect()->route('supplier.show',$supplier->id)->with('success','Đã nạp tiền nhà cung cấp!');
            
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function supplierMakeBalance($id)
    {
        $supplier = User::find($id );
        $user = auth()->user();
       
        if( $supplier)
        {
             //list all wi not paid order by time
            $warehouseins = \App\Models\Warehousein::where('supplier_id',$supplier->id)
            ->where('is_paid',false)->orderBy('id','ASC')->get();
            $unpaid_amount = 0;
            foreach($warehouseins as $warehousein)
            {
                $unpaid_amount += ($warehousein->final_amount - $warehousein->paid_amount);
            }
            if($unpaid_amount > $supplier->budget)
            {
                $paid_amount = $unpaid_amount -  $supplier->budget;
                foreach($warehouseins as $warehousein)
                {
                    if($paid_amount >= ($warehousein->final_amount - $warehousein->paid_amount))
                    {
                        $paid_amount -= ($warehousein->final_amount - $warehousein->paid_amount);
                        $warehousein->paid_amount = $warehousein->final_amount;
                        $warehousein->is_paid = true;
                        $warehousein->save();
                        
                    }
                    else
                    {
                        $warehousein->paid_amount+= $paid_amount;
                        $warehousein->save();
                        $paid_amount = 0;
                    }
                    if($paid_amount == 0)
                        break;
                }
                ///create log /////////////
                $user = auth()->user();
                $content = 'make balance for supplier: '.$supplier->full_name.' total: '.($unpaid_amount -  $supplier->budget) ;
                \App\Models\Log::insertLog($content,$user->id);
                
            }
            return redirect()->route('supplier.show',$supplier->id)->with('success','Đã khấu trừ công nợ nhà cung cấp!');
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function supplierPaid($id)
    {
        // return $id;
         
        $supplier = User::find($id);
         
        if( $supplier)
        {
             $bankaccounts = \App\Models\Bankaccount::where('status','active')->get();
             $active_menu="sup_list";
             
             $breadcrumb = '
             <li class="breadcrumb-item"><a href="#">/</a></li>
             <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Ds nhà cung cấp</a></li>
             <li class="breadcrumb-item active" aria-current="page"> chuyển tiền nhà cung cấp </li>';
             return view('backend.suppliers.paid',compact('supplier','breadcrumb','bankaccounts','active_menu'));
             
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function supplierReceived($id)
    {
        // return $id;
         
        $supplier = User::find($id);
         
        if( $supplier)
        {
             $bankaccounts = \App\Models\Bankaccount::where('status','active')->get();
             $active_menu="sup_list";
             
             $breadcrumb = '
             <li class="breadcrumb-item"><a href="#">/</a></li>
             <li class="breadcrumb-item  " aria-current="page"><a href="'.route('supplier.index').'">Ds nhà cung cấp</a></li>
             <li class="breadcrumb-item active" aria-current="page"> nhận tiền nhà cung cấp </li>';
             return view('backend.suppliers.received',compact('supplier','breadcrumb','bankaccounts','active_menu'));
             
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }

    public function supplierSaveReceived(Request $request)
    {
        $this->validate($request,[
            'id'=>'numeric|required',
            'paid_amount'=>'numeric|required',
            'bank_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $supplier = User::find($data['id']);
        $user = auth()->user();
       
        if( $supplier)
        {
             ///create paid transaction
            
            if($data['paid_amount'] ==0 )
            {
                return back()->with('error','Số tiền trả không hợp lệ!');
            }
            $bankaccount = \App\Models\Bankaccount::find($data['bank_id']);
            
            $bank_doc = \App\Models\BankTransaction::insertBankTrans($user->id,$data['bank_id'],1,$supplier->id,'si',$data['paid_amount']);
            $subtrans = \App\Models\SupTransaction::createSubTrans($bank_doc->id,'fi',1, $data['paid_amount'], $supplier->id); 
            $bank_doc->doc_id =  $subtrans->id;
            $bank_doc->save();
            //list all wo not paid order by time
           
            $warehouseouts = \App\Models\Warehouseout::where('customer_id',$supplier->id)
            ->where('is_paid',false)->orderBy('id','ASC')->get();
            $paid_amount = $data['paid_amount'];
            foreach($warehouseouts as $warehouseout)
            {
                if($paid_amount >= ($warehouseout->final_amount - $warehouseout->paid_amount))
                {
                    $paid_amount -= ($warehouseout->final_amount - $warehouseout->paid_amount);
                    $warehouseout->paid_amount = $warehouseout->final_amount;
                    $warehouseout->is_paid = true;
                    $warehouseout->save();
                    
                }
                else
                {
                    $warehouseout->paid_amount+= $paid_amount;
                    $warehouseout->save();
                    $paid_amount = 0;
                }
                if($paid_amount == 0)
                    break;
            }
            ///create log /////////////
            $user = auth()->user();
            $content = 'paid money for supplier: '.$supplier->full_name.' total: '.$data['paid_amount'];
            \App\Models\Log::insertLog($content,$user->id);
            return redirect()->route('supplier.show',$supplier->id)->with('success','Đã nạp tiền nhà cung cấp!');
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    
}
