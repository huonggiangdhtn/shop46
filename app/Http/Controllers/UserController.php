<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
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
        $active_menu="ctm_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page">người dùng </li>';
        $users=User::where('role','<>','admin')->orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.users.index',compact('users','breadcrumb','active_menu'));
    }
    
    public function userSort(Request $request)
    {
        $this->validate($request,[
            'field_name'=>'string|required',
            'type_sort'=>'required|in:DESC,ASC',
        ]);
    
        $active_menu="ctm_list";
        $searchdata =$request->datasearch;
        $users = DB::table('users')->orderBy($request->field_name, $request->type_sort)
        ->paginate($this->pagesize)->withQueryString();;
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('user.index').'">Người dùng</a></li>
         ';
        return view('backend.users.index',compact('users','breadcrumb','searchdata','active_menu'));
    }

    public function userStatus(Request $request)
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
        $active_menu="ctm_add";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('user.index').'">Người dùng</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo người dùng </li>';
        $ugroups = UGroup::where('status','active')->orderBy('id','ASC')->get();
        return view('backend.users.create',compact('breadcrumb','active_menu','ugroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // 
        $this->validate($request,[
            'full_name'=>'string|required',
            'email'=>'email|nullable',
            'description'=>'string|nullable',
            'photo'=>'string|nullable',
            'phone'=>'string|required',
            'password'=>'string|nullable',
            'address'=>'string|required',
            'ugroup_id'=>'numeric|required',
             'role'=>'required|in:admin,vendor,manager,customer,supplier,supcustomer',
            'status'=>'nullable|in:active,inactive',
        ]);
        // return $request->all();
        $data = $request->all();
        //check user with phone
        $olduser = User::where('phone',$data['phone'])->get();
        if(count($olduser) > 0)
            return back()->with('error','Số điện thoại đã tồn tại!');
        if($request->photo == null)
            $data['photo'] = asset('backend/assets/dist/images/profile-6.jpg');
        if($request->photo != null)
        {
            $photos = explode(',', $data['photo']);
            if(count ($photos) > 0)
                $data['photo'] = $photos[0];
        }
        if($request->email == null)
            $data['email'] = $data['phone'].'@gmail.com';
        if($request->password == null)
            $data['password']=$data['phone'];
        $data['password'] = Hash::make($data['password']);
        $data['username'] = $data['phone'];
        $status = User::create($data);
        if($status){
            return redirect()->route('user.index')->with('success','Tạo người dùng thành công!');
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
    public function userDetail (Request $request)
    {
        //
        if($request->id)
        {
            $user = User::find($request->id);
            if($user)
            {
                return response()->json(['msg'=>$user,'status'=>true]);
            }
        }
        return response()->json(['msg'=>'','status'=>false]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $user = User::find($id);
        if($user)
        {
            $active_menu="ctm_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('user.index').'">Người dùng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh người dùng </li>';
            $ugroups = UGroup::where('status','active')->orderBy('id','ASC')->get();
            return view('backend.users.edit',compact('breadcrumb','user','active_menu','ugroups' ));
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
        // return $request->all();
        $user = User::find($id);
        if($user)
        {
            $this->validate($request,[
                'full_name'=>'string|required',
                'email'=>'email|required',
                'description'=>'string|nullable',
                'photo'=>'string|required',
                'password'=>'string|nullable',
                'address'=>'string|required',
                'ugroup_id'=>'numeric|required',
                 'role'=>'required|in:admin,vendor,manager,customer,supplier,supcustomer',
                'status'=>'nullable|in:active,inactive',
            ]);
    
            $data = $request->all();
            if($request->password == null)            
                $data['password'] = $user->password;
            else
                $data['password'] = Hash::make($data['password']);
            $status = $user->fill($data)->save();
            if($status){
                return redirect()->route('user.index')->with('success','Cập nhật thành công');
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
                return redirect()->route('user.index')->with('success','Xóa thành công!');
            }
            else
            {
                return back()->with('error','không thể xóa!');
            }    
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function userSearch(Request $request)
    {
        if($request->datasearch)
        {
            $active_menu="ctm_list";
            $searchdata =$request->datasearch;
            $users = DB::table('users')->where('role','<>','admin')
            ->where(function($query) use ( $searchdata )
            {
                $query->where('phone','LIKE','%'.$searchdata.'%')
                      ->orWhere('full_name','LIKE','%'.$searchdata.'%');
            })
            ->paginate($this->pagesize)->withQueryString();
            // $query = "select * from users where role <>'admin' and (full_name like '%" 
            //             .$request->datasearch."%' or phone like '%".$request->datasearch."%')";
            // $users = DB::select($query)->paginate($this->pagesize)->withQueryString();;;
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('user.index').'">Người dùng</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            return view('backend.users.search',compact('users','breadcrumb','searchdata','active_menu'));
        }
        else
        {
            return redirect()->route('user.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
}
