<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\User;
use App\Models\Warehousein;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
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
        $active_menu="customer_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page">Khách hàng </li>';
        $customers=User::where('role', 'customer')->orwhere('role','supcustomer')
            ->orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.customers.index',compact('customers','breadcrumb','active_menu'));

    }
    public function customerSort(Request $request)
    {
        $this->validate($request,[
            'field_name'=>'string|required',
            'type_sort'=>'required|in:DESC,ASC',
        ]);
    
        $active_menu="customer_list";
        $searchdata =$request->datasearch;
        $customers = DB::table('users')
        ->where('role', 'customer')->orwhere('role','supcustomer')
        ->orderBy($request->field_name, $request->type_sort)
        ->paginate($this->pagesize)->withQueryString();;
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Khách hàng</a></li>
         ';
        return view('backend.customers.index',compact('customers','breadcrumb','searchdata','active_menu'));
    }

    public function customerStatus(Request $request)
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="customer_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Khách hàng</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo khách hàng </li>';
        return view('backend.customers.create',compact('breadcrumb','active_menu'));
  
    }
    public function customerJsearch(Request $request)
    {
        if($request->data  )
        { 
            $searchdata =$request->data;
             $customers = DB::table('users')
             ->select ('users.id','users.full_name as title' )
             ->where(function($query1)  use ($searchdata) 
             {
                 $query1 ->where('full_name','LIKE','%'.$searchdata.'%')
                 ->orwhere('phone','LIKE','%'.$searchdata.'%');
             })
            
             ->where(function($query)  
             {
                 $query->where('role', 'customer')
                       ->orWhere('role', 'supcustomer');
             })
             
             ->get();
             
             return response()->json(['msg'=>$customers,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }

    }
    public function customerSearch(Request $request)
    {
        if($request->datasearch)
        {
            $active_menu="customer_list";
            $searchdata =$request->datasearch;
            $customers = DB::table('users') 
            ->where(function($query) use ( $searchdata )
            {
                $query->where('phone','LIKE','%'.$searchdata.'%')
                      ->orWhere('full_name','LIKE','%'.$searchdata.'%');
            })
            ->where(function($query1)  
            {
                $query1->where('role', 'customer')
                      ->orWhere('role', 'supcustomer');
            })
            ->paginate($this->pagesize)->withQueryString();
            // $query = "select * from users where role <>'admin' and (full_name like '%" 
            //             .$request->datasearch."%' or phone like '%".$request->datasearch."%')";
            // $users = DB::select($query)->paginate($this->pagesize)->withQueryString();;;
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Khách hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            return view('backend.customers.search',compact('customers','breadcrumb','searchdata','active_menu'));
        }
        else
        {
            return redirect()->route('customer.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
    /**
     * Store a newly created resource in storage.
     */
    public function customerAdd(Request $request)
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
        $data['role'] = 'customer';
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
        $data['role'] = 'customer';
        $status = User::create($data);
        if($status){
            return redirect()->route('customer.index')->with('success','Tạo khách hàng thành công!');
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
        $customer = User::find($id);
        if($customer)
        {
            $active_menu="customer_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Khách hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> xem công nợ khách hàng </li>';
            $suptrans = \App\Models\Suptransaction::where('supplier_id',$id)
                ->orderBy('id','DESC')
                ->paginate($this->pagesize*2)->withQueryString();;
            return view('backend.customers.show',compact('breadcrumb','active_menu','customer','suptrans'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $customer = User::find($id);
        if($customer)
        {
            $active_menu="sup_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Khách hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh khách hàng </li>';
            
            return view('backend.customers.edit',compact('breadcrumb','customer','active_menu' ));
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
                return redirect()->route('customer.index')->with('success','Cập nhật thành công');
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
                return redirect()->route('customer.index')->with('success','Xóa thành công!');
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
    public function customerPaid($id)
    {
        // return $id;
         
        $customer = User::find($id);
         
        if( $customer)
        {
             $bankaccounts = \App\Models\Bankaccount::where('status','active')->get();
             $active_menu="sup_list";
             
             $breadcrumb = '
             <li class="breadcrumb-item"><a href="#">/</a></li>
             <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Ds nhà cung cấp</a></li>
             <li class="breadcrumb-item active" aria-current="page"> nạp tiền nhà cung cấp </li>';
             return view('backend.customers.paid',compact('customer','breadcrumb','bankaccounts','active_menu'));
             
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }

    public function customerSavePaid(Request $request)
    {
        $this->validate($request,[
            'id'=>'numeric|required',
            'paid_amount'=>'numeric|required',
            'bank_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $customer = User::find($data['id']);
        $user = auth()->user();
       
        if( $customer)
        {
             ///create paid transaction
            
            if($data['paid_amount'] ==0 )
            {
                return back()->with('error','Số tiền trả không hợp lệ!');
            }
           
            
            $bank_doc = \App\Models\BankTransaction::insertBankTrans($user->id,$data['bank_id'],1,$customer->id,'si',$data['paid_amount']);
            $subtrans = \App\Models\SupTransaction::createSubTrans($bank_doc->id,'fi',1, $data['paid_amount'], $customer->id); 
            $bank_doc->doc_id =  $subtrans->id;
            $bank_doc->save();
            //list all wi not paid order by time
            $warehouseouts = \App\Models\Warehouseout::where('customer_id',$customer->id)
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
            
            $content = 'get money from customer: '.$customer->full_name.' total: '.$data['paid_amount'];
            \App\Models\Log::insertLog($content,$user->id);
            
            return redirect()->route('customer.show',$customer->id)->with('success','Đã nạp tiền nhà cung cấp!');
            
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }

    public function customerMakeBalance($id)
    {
        $customer = User::find($id );
        $user = auth()->user();
       
        if( $customer)
        {
             //list all wi not paid order by time
            $warehouseouts = \App\Models\Warehouseout::where('customer_id',$customer->id)
            ->where('is_paid',false)->orderBy('id','ASC')->get();
            $unpaid_amount = 0;
            foreach($warehouseouts as $warehouseout)
            {
                $unpaid_amount += ($warehouseout->final_amount - $warehouseout->paid_amount);
            }
            $unpaid_amount = - $unpaid_amount;
            if(  $unpaid_amount < $customer->budget)
            {
                $paid_amount =  $customer->budget - $unpaid_amount ;
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
                $content = 'make balance for customer: '.$customer->full_name.' total: '.($unpaid_amount -  $customer->budget) ;
                \App\Models\Log::insertLog($content,$user->id);
                
            }
            return redirect()->route('customer.show',$customer->id)->with('success','Đã khấu trừ công nợ nhà cung cấp!');
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function customerReceived($id)
    {
        // return $id;
         
        $customer = User::find($id);
         
        if( $customer)
        {
             $bankaccounts = \App\Models\Bankaccount::where('status','active')->get();
             $active_menu="customer_list";
             
             $breadcrumb = '
             <li class="breadcrumb-item"><a href="#">/</a></li>
             <li class="breadcrumb-item  " aria-current="page"><a href="'.route('customer.index').'">Ds khách hàng</a></li>
             <li class="breadcrumb-item active" aria-current="page"> trả tiền khách hàng </li>';
             return view('backend.customers.received',compact('customer','breadcrumb','bankaccounts','active_menu'));
             
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }

    public function customerSaveReceived(Request $request)
    {
        $this->validate($request,[
            'id'=>'numeric|required',
            'paid_amount'=>'numeric|required',
            'bank_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $customer = User::find($data['id']);
        $user = auth()->user();
       
        if( $customer)
        {
             ///create paid transaction
            
            if($data['paid_amount'] ==0 )
            {
                return back()->with('error','Số tiền trả không hợp lệ!');
            }
            $bankaccount = \App\Models\Bankaccount::find($data['bank_id']);
            
            $bank_doc = \App\Models\BankTransaction::insertBankTrans($user->id,$data['bank_id'],-1,$customer->id,'si',$data['paid_amount']);
            $subtrans = \App\Models\SupTransaction::createSubTrans($bank_doc->id,'fi',-1, $data['paid_amount'], $customer->id); 
            $bank_doc->doc_id =  $subtrans->id;
            $bank_doc->save();
            //list all wo not paid order by time
           
            $warehouseins = \App\Models\Warehousein::where('supplier_id',$customer->id)
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
            $content = 'paid money for customer: '.$customer->full_name.' total: '.$data['paid_amount'];
            \App\Models\Log::insertLog($content,$user->id);
            return redirect()->route('customer.show',$customer->id)->with('success','Đã nạp tiền nhà cung cấp!');
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
}
