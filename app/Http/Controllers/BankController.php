<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Bankaccount;
use App\Models\BankTransaction;
use App\Models\FreeTransaction;
class BankController extends Controller
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
        $active_menu="bank_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Tài khoản </li>';
        $bankaccounts=Bankaccount::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.bankaccounts.index',compact('bankaccounts','breadcrumb','active_menu'));
  
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="bank_add";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('bankaccount.index').'">bankaccounts</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo tài khoản </li>';
        return view('backend.bankaccounts.create',compact('breadcrumb','active_menu'));
 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
          // return $request->all();
          $this->validate($request,[
            'title'=>'string|required',
            'banknumber'=>'string|nullable',
            'status'=>'required|in:active,inactive',
        ]);
        $data = $request->all();
        $data ['total'] = 0;
        
        $status = Bankaccount::create($data);
        if($status){
            $content = 'store bankaccount title: '.$data['title'] ;
            $user = auth()->user();
            \App\Models\Log::insertLog($content,$user->id);
         
            return redirect()->route('bankaccount.index')->with('success','Tạo bankaccount thành công!');
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
        $bankaccount = Bankaccount::find($id);
        if($bankaccount)
        {
            $active_menu="bank_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('bankaccount.index').'">Tài khoản</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh tài khoản </li>';
            return view('backend.bankaccounts.edit',compact('breadcrumb','bankaccount','active_menu'));
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
        $bankaccount = Bankaccount::find($id);
        if($bankaccount)
        {
            $this->validate($request,[
                'title'=>'string|required',
                'banknumber'=>'string|nullable',
                'status'=>'required|in:active,inactive',
            ]);
            $data = $request->all();
            $status = $bankaccount->fill($data)->save();
            if($status){
                $content = 'edit bankaccount title: '.$data['title'] ;
                $user = auth()->user();
                \App\Models\Log::insertLog($content,$user->id);
             
                return redirect()->route('bankaccount.index')->with('success','Cập nhật thành công');
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
        $bankaccount = Bankaccount::find($id);
        
        if($bankaccount)
        {
            $status = Bankaccount::deleteBankaccount($id);
            if($status){
                $content = 'delete bankaccount title: '.$bankaccount->title ;
                $user = auth()->user();
                \App\Models\Log::insertLog($content,$user->id);
                return redirect()->route('bankaccount.index')->with('success','Xóa thành công!');
            }
            else
            {
                return back()->with('error','Vẫn còn hàng trong kho hoặc hàng liên quan đến các phiếu nhập xuất không thể xóa!');
            }    
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function bankaccountStatus(Request $request)
    {
        if($request->mode =='true')
        {
            DB::table('bankaccounts')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            DB::table('bankaccounts')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        $content = 'change bankaccount status id: '.$request->id ;
        $user = auth()->user();
        \App\Models\Log::insertLog($content,$user->id);
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    public function banktransView()
    {
        $active_menu="bt_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Danh sách giao dịch tài khoản </li>';
        $banktrans=BankTransaction::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.bankaccounts.transview',compact('banktrans','breadcrumb','active_menu'));
  
    }
    public function banktransSort(Request $request)
    {
        $this->validate($request,[
            'field_name'=>'string|required',
            'type_sort'=>'required|in:DESC,ASC',
        ]);
    
        $active_menu="bt_list";
         
        $banktrans = DB::table('bank_transactions')->orderBy($request->field_name, $request->type_sort)
        ->paginate($this->pagesize)->withQueryString();;
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('bankaccount.viewtrans').'">Ds giao dịch tài khoản</a></li>';
        return view('backend.bankaccounts.transview',compact('banktrans','breadcrumb','active_menu'));
        
    }
    public function bankaccountTransfer($id)
    {
        $bankaccount = Bankaccount::find($id);
        if($bankaccount)
        {
            $active_menu="bank_t";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('bankaccount.index').'">Tài khoản</a></li>
            <li class="breadcrumb-item active" aria-current="page"> chuyển khoản </li>';
            $banklist = Bankaccount::where('id','<>',$bankaccount->id)
                    ->where('status','active')->orderBy('id','ASC')->get();
            return view('backend.bankaccounts.transfer',compact('breadcrumb','bankaccount','active_menu','banklist'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function banktransShow($id )
    {
        $active_menu="bank_list";
        $banktrans = Banktransaction::find($id);
        if($banktrans )
        {
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('bankaccount.viewtrans').'">Danh sách giao dịch</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Chi tiết </li>';
            return view('backend.bankaccounts.show',compact('breadcrumb','active_menu','banktrans'));
     
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
        
    }
    public function bankaccountTransferSave(Request $request)
    {
        $this->validate($request,[
            'firstbank_id'=>'numeric|required',
            'secondbank_id'=>'numeric|required',
            'total'=>'numeric|required',
        ]);
        $data = $request->all();

        // return $data;
        $firstbank = Bankaccount::find($data['firstbank_id']);
        $secondbank = bankaccount::find($data['secondbank_id']);
        if($data['total'] > $firstbank->total)
        {
            return back()->with('error','Số tiền chuyển lớn hơn tiền đang có');
        }
        if($firstbank && $secondbank)
        {
            $user = auth()->user();
            //tao phieu xuat tien và phieu nhan tien
            $fts= FreeTransaction::addFreeTrans($data['total'],$data['firstbank_id'],-1,'transfer',$user->id);
            BankTransaction::insertBankTrans($user->id,$data['firstbank_id'],-1,$fts->id,'fi',$data['total']);
            $fts= FreeTransaction::addFreeTrans($data['total'],$data['secondbank_id'],1,'transfer',$user->id);
            BankTransaction::insertBankTrans($user->id,$data['secondbank_id'],1,$fts->id,'fi',$data['total']);
            
            $content = 'transfer money from: '.$firstbank->title.'to '.$secondbank->title.' total: '.$data['total'];
            \App\Models\Log::insertLog($content,$user->id);
            return redirect()->route('bankaccount.index')->with('success','Chuyển khoản thành công!');
     
        }
    }
}
