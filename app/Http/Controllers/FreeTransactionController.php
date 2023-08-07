<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FreeTransaction;
class FreeTransactionController extends Controller
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
            $active_menu='ft_list';
            $freetrans = FreeTransaction::orderBy('id','DESC')->paginate($this->pagesize);
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('freetransaction.index').'">Danh sách giao dịch</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Chi tiết </li>';
            return view('backend.freetransactions.index',compact('breadcrumb','active_menu','freetrans'));
     
         
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="ft_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('freetransaction.index').'">Danh sách thu chi</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo thu chi </li>';
        $banklist = \App\Models\Bankaccount::where('status','active')->orderBy('id','ASC')->get();
        return view('backend.freetransactions.create',compact('breadcrumb','active_menu','banklist'));
 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request,[
            'content'=>'string|required',
            'total'=>'numeric|required',
            'bank_id'=>'numeric|required',
            'operation'=>'required|in:1,-1',
        ]);
        $data = $request->all();
        $user = auth()->user();
        $data['user_id']= $user->id;
        $bank = \App\Models\Bankaccount::find($data['bank_id']);
        if($bank->total + $data['operation']*$data['total']<= 0)
            return back()->with('error','Không đủ tiền trong tài khoản để lập phiếu!');
        $fts= FreeTransaction::addFreeTrans($data['total'],$data['bank_id'],$data['operation'],$data['content'],$user->id);
        \App\Models\BankTransaction::insertBankTrans($user->id,$data['bank_id'],$data['operation'],$fts->id,'fi',$data['total']);
        $content = 'store freetransaction type '.$data['operation'].'content: '.$data['content'].' total: '.$data['total'] ;
        \App\Models\Log::insertLog($content,$user->id);
        return redirect()->route('freetransaction.index')->with('success','Tạo thu chi thành công!');
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $active_menu="ft_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('freetransaction.index').'">Danh sách thu chi</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo thu chi </li>';
        $freetrans = \App\Models\FreeTransaction::find($id);
        return view('backend.freetransactions.show',compact('breadcrumb','active_menu','freetrans'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
