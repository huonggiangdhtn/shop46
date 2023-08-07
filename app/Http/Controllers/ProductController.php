<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
class ProductController extends Controller
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
    //
    public function index()
    {
        //
        $active_menu="pro_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page">Hàng hóa </li>';
        $products=Product::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.products.index',compact('products','breadcrumb','active_menu'));
    }
    public function productSort(Request $request)
    {
        $this->validate($request,[
            'field_name'=>'string|required',
            'type_sort'=>'required|in:DESC,ASC',
        ]);
    
        $active_menu="pro_list";
        $searchdata =$request->datasearch;
        $products = DB::table('products')->orderBy($request->field_name, $request->type_sort)
        ->paginate($this->pagesize)->withQueryString();;
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('product.index').'">hàng hóa</a></li>
        ';
        return view('backend.products.index',compact('products','breadcrumb','searchdata','active_menu'));
        
        

    }
    public function productSearch(Request $request)
    {
        if($request->datasearch)
        {
            $active_menu="pro_list";
            $searchdata =$request->datasearch;
            $products = DB::table('products')->where('title','LIKE','%'.$request->datasearch.'%')
            ->paginate($this->pagesize)->withQueryString();;;
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('product.index').'">hàng hóa</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            return view('backend.products.search',compact('products','breadcrumb','searchdata','active_menu'));
        }
        else
        {
            return redirect()->route('product.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }

    public function productMsearch(Request $request)
    {
        if($request->data  )
        { 
            $searchdata =$request->data;
            $products = DB::table('products')->where('title','LIKE','%'.$searchdata.'%')
            ->where('status','active')->get();
             return response()->json(['msg'=>$products,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }
    }

    public function productJsearch(Request $request)
    {
        if($request->data && $request->warehouse_id)
        { 
            $searchdata =$request->data;
            $wh_id = $request->warehouse_id;
            // $binventorys = DB::table('b_inventories')
            // ->select('b_inventories.*' )
            // ->join(\DB::raw($query),
            // 'b_inventories.product_id', '=', 'np.idpro') 
            // ->paginate($this->pagesize)->withQueryString();
            $query = "(select product_id ,quantity from b_inventories where wh_id = ".$wh_id.") as np";
            $products = DB::table('products')
             ->select ('products.id','products.title','products.photo','np.quantity','products.expired')
             ->where('title','LIKE','%'.$searchdata.'%')
             ->leftJoin(\DB::raw($query),'products.id','=','np.product_id')
             ->orderBy('products.title','asc')
             ->get();
             
             return response()->json(['msg'=>$products,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }

    }

    public function productTsearch(Request $request)
    {
        if($request->data && $request->warehouse_id)
        { 
            $searchdata =$request->data;
            $wh_id = $request->warehouse_id;
            // $binventorys = DB::table('b_inventories')
            // ->select('b_inventories.*' )
            // ->join(\DB::raw($query),
            // 'b_inventories.product_id', '=', 'np.idpro') 
            // ->paginate($this->pagesize)->withQueryString();
            $query = "(select product_id ,quantity from  inventories where wh_id = ".$wh_id.") as np";
            $products = DB::table('products')
             ->select ('products.id','products.title','products.photo','products.price_avg as price','np.quantity','products.expired')
             ->where('title','LIKE','%'.$searchdata.'%')
             ->where('np.quantity','>',0)
             ->leftJoin(\DB::raw($query),'products.id','=','np.product_id')
             ->orderBy('products.title','asc')
             ->get();
             
             return response()->json(['msg'=>$products,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }

    }
    public function productGPriceSearch(Request $request)
    {
        $this->validate($request,[
            'product_id'=>'numeric|required',
        ]);
        $query = "(select id, price, ugroup_id from group_prices where product_id = ".$request->product_id.") as np";
        $groupprices = DB::table('u_groups')
        ->select ('u_groups.id','u_groups.title', 'np.price','np.id as gpid')
        ->where('status','active')
        ->leftJoin(\DB::raw($query),'u_groups.id','=','np.ugroup_id')
        ->orderBy('id','ASC')->get();
        foreach($groupprices as $grouppice)
        {
            if( $grouppice->gpid == null)
            {
                $data['ugroup_id'] = $grouppice->id;
                $data['price'] = 0;
                $data['product_id'] = $request->product_id;
                \App\Models\GroupPrice::create($data);
            }
        }
        $groupprices = DB::table('u_groups')
        ->select ('u_groups.id','u_groups.title', 'np.price','np.id as gpid')
        ->where('status','active')
        ->leftJoin(\DB::raw($query),'u_groups.id','=','np.ugroup_id')
        ->orderBy('id','ASC')->get();
        return response()->json(['msg'=>$groupprices,'status'=>true]);
    }
    
    public function productJsearchwf(Request $request)
    {
        if($request->data && $request->warehouse_id)
        { 
            $searchdata =$request->data;
            $wh_id = $request->warehouse_id;
            
            $query = "(select product_id ,quantity from inventories where wh_id = ".$wh_id."  ) as np";
            $products = DB::table('products')
             ->select ('products.id','products.title','products.photo','products.price_avg as price','np.quantity','products.expired')
             ->where('title','LIKE','%'.$searchdata.'%') 
             ->leftJoin(\DB::raw($query),'products.id','=','np.product_id')
             ->where('np.quantity','>',0)
             ->where('stock','>',0)
             ->orderBy('products.title','asc')
             ->get();
             return response()->json(['msg'=>$products,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }
    }
    public function productJsearchwi(Request $request)
    {
        if($request->data && $request->warehouse_id)
        { 
            $searchdata =$request->data;
            $wh_id = $request->warehouse_id;
            // $binventorys = DB::table('b_inventories')
            // ->select('b_inventories.*' )
            // ->join(\DB::raw($query),
            // 'b_inventories.product_id', '=', 'np.idpro') 
            // ->paginate($this->pagesize)->withQueryString();
            $query = "(select product_id ,quantity from inventories where wh_id = ".$wh_id.") as np";
            $products = DB::table('products')
             ->select ('products.id','products.title','products.photo','products.price_in as price','np.quantity','products.expired')
             ->where('title','LIKE','%'.$searchdata.'%')
             ->leftJoin(\DB::raw($query),'products.id','=','np.product_id')
             ->orderBy('products.title','asc')
             ->get();
             return response()->json(['msg'=>$products,'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }
    }
    public function productJsearchwo(Request $request)
    {
        if($request->data && $request->warehouse_id)
        { 
            $searchdata =$request->data;
            $wh_id = $request->warehouse_id;
            $customer_id = $request->customer_id;
            $customer = \App\Models\User::find($customer_id);
            // return $customer;
            if(  $customer == null || $customer->ugroup_id ==null)
            {
                //  return 'b';
                $query = "(select product_id ,quantity from inventories where wh_id = ".$wh_id.") as np";
                $query1 = "(select product_id ,price from group_prices where wh_id = ".$wh_id.") as np";
                $products = DB::table('products')
                 ->select ('products.id','products.title','products.photo','products.price_out as price','np.quantity','products.expired')
                 ->where('title','LIKE','%'.$searchdata.'%')
                 ->where('np.quantity','>',0)
                 ->where('stock','>',0)
                 ->leftJoin(\DB::raw($query),'products.id','=','np.product_id')
                 ->orderBy('products.title','asc')
                 ->get();
                 return response()->json(['msg'=>$products,'status'=>true]);
            }
            else
            {
                // return 'a';
                $query = "(select product_id ,quantity from inventories where wh_id = ".$wh_id.") as np";
                $query1 = "(select product_id ,price from group_prices where ugroup_id = ".$customer->ugroup_id.") as up";
                $products = DB::table('products')
                 ->select ('products.id','products.title','products.photo','up.price as price','np.quantity','products.expired')
                 ->where('title','LIKE','%'.$searchdata.'%')
                 ->where('np.quantity','>','0')
                 ->where('stock','>',0)
                 ->leftJoin(\DB::raw($query),'products.id','=','np.product_id')
                 ->leftJoin(\DB::raw($query1),'products.id','=','up.product_id')
                 ->orderBy('products.title','asc')
                 ->get();
                 return response()->json(['msg'=>$products,'status'=>true]);
            }
            
            
           
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }
    }

    public function productJsearchms(Request $request)
    {
        if($request->data )
        { 
            $searchdata =$request->data;
 
                $query = "select a.id, a.title , a.photo, a.price_out as price, b.quantity from (select * from products where title like '%".
                            $searchdata."%' and status = 'active') as a left join (select product_id ,quantity from inventory_maintenances ) as b on a.id = b.product_id where b.quantity > 0 order by a.title asc;";
                $products = DB::select($query);
                 return response()->json(['msg'=>$products,'status'=>true]);
            
           
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }
    }

    public function productStock_quantity(Request $request)
    {
        if($request->product_id && $request->warehouse_id)
        { 
            $pro_id =$request->product_id;
            $wh_id = $request->warehouse_id;
         
            $binventory = DB::table('b_inventories')->where('product_id',$pro_id)->where('wh_id',$wh_id)->first();
            if($binventory)
                return response()->json(['msg'=>$binventory->quantity,'status'=>true]);
            else
                {
                    return response()->json(['msg'=>'','status'=>false]);
                }
        }
        else
        {
            return response()->json(['msg'=>'','status'=>false]);
        }
    }
    public function productStatus(Request $request)
    {
        if($request->mode =='true')
        {
            DB::table('products')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            DB::table('products')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $active_menu="pro_add";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('product.index').'">hàng hóa</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo hàng hóa </li>';
        $categories = Category::where('is_parent',0)
            ->where('status','active')->orderBy('title','ASC')->get();
        $brands = Brand::where('status','active')
            ->orderBy('title','ASC')->get();
        return view('backend.products.create',compact('breadcrumb','active_menu','categories','brands'));
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
            'summary'=>'string|nullable',
            'description'=>'string|nullable',
            'photo'=>'string|nullable',
            'brand_id'=>'numeric|nullable',
            'cat_id'=>'numeric|nullable',
            'size'=>'string|nullable',
            'weight'=>'numeric|nullable',
           
            'status'=>'nullable|in:active,inactive',
        ]);
        $data = $request->all();
        $parent_cat = Product::find($data['cat_id'])->value('id');
        if($parent_cat != null)
            $data['parent_cat_id'] = $parent_cat;
        if($request->photo == null)
            $data['photo'] = asset('backend/assets/dist/images/profile-6.jpg');
        $data['stock'] = 0;
        // return $data;
        $slug = Str::slug($request->input('title'));
        $slug_count = Product::where('slug',$slug)->count();
        if($slug_count > 0)
        {
            $slug .= time().'-'.$slug;
        }
        $data['slug'] = $slug;
        
        $status = Product::create($data);
        if($status){
            return redirect()->route('product.index')->with('success','Tạo hàng hóa thành công!');
        }
        else
        {
            return back()->with('error','Something went wrong!');
        }    
    }
    public function productAdd(Request $request)
    {
        //
        // return $request->all();
        $this->validate($request,[
            'title'=>'string|required',
            'expired'=>'numeric|nullable',
            'cat_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $parent_cat = Product::find($data['cat_id'])->value('id');
        if($parent_cat != null)
            $data['parent_cat_id'] = $parent_cat;
        if($request->photo == null)
            $data['photo'] = asset('backend/assets/dist/images/profile-6.jpg');
        $data['stock'] = 0;
        $data['summary']="-";
        // return $data;
        $slug = Str::slug($request->input('title'));
        $slug_count = Product::where('slug',$slug)->count();
        if($slug_count > 0)
        {
            $slug .= time().'-'.$slug;
        }
        $data['slug'] = $slug;
        $status = Product::create($data);
        if($status){
            return response()->json(['msg'=>"Đã thêm sản phẩm!",'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'Lỗi trong quá trình lưu!','status'=>false]);
        }    
    }
    public function productAddm(Request $request)
    {
        //
        // return $request->all();
        $this->validate($request,[
            'title'=>'string|required',
            'expired'=>'numeric|nullable',
            'cat_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $parent_cat = Product::find($data['cat_id'])->value('id');
        if($parent_cat != null)
            $data['parent_cat_id'] = $parent_cat;
        if($request->photo == null)
            $data['photo'] = asset('backend/assets/dist/images/profile-6.jpg');
        $data['stock'] = 0;
        $data['summary']="-";
        // return $data;
        $slug = Str::slug($request->input('title'));
        $slug_count = Product::where('slug',$slug)->count();
        if($slug_count > 0)
        {
            $slug .= time().'-'.$slug;
        }
        $data['slug'] = $slug;
        $data['is_sold'] = 0; 
        $status = Product::create($data);
        if($status){
            return response()->json(['msg'=>"Đã thêm sản phẩm!",'status'=>true]);
        }
        else
        {
            return response()->json(['msg'=>'Lỗi trong quá trình lưu!','status'=>false]);
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
            $product = Product::find($id);
            if($product)
            {
                $active_menu="pro_list";
                $breadcrumb = '
                <li class="breadcrumb-item"><a href="#">/</a></li>
                <li class="breadcrumb-item  " aria-current="page"><a href="'.route('product.index').'">products</a></li>
                <li class="breadcrumb-item active" aria-current="page"> điều chỉnh products </li>';
                $categories = Category::where('is_parent',0)->orderBy('title','ASC')->get();
                $brands = Brand::orderBy('title','ASC')->get();
                return view('backend.products.edit',compact('breadcrumb','product','active_menu','categories','brands'));
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
        $product = Product::find($id);
        if($product)
        {
            $this->validate($request,[
                'title'=>'string|required',
                'summary'=>'string|nullable',
                'description'=>'string|nullable',
                'photo'=>'string|required',
                'brand_id'=>'numeric|nullable',
                'cat_id'=>'numeric|nullable',
                'size'=>'string|nullable',
                'weight'=>'numeric|nullable',
                'photo'=>'string|required',
                'status'=>'nullable|in:active,inactive',
            ]);
    
            $data = $request->all();

            $parent_cat = Product::find($data['cat_id'])->value('id');
            if($parent_cat != null)
                $data['parent_cat_id'] = $parent_cat;

            $status = $product->fill($data)->save();
            if($status){
                return redirect()->route('product.index')->with('success','Cập nhật thành công');
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
        $product = Product::find($id);
        
        if($product)
        {
            $status = Product::deleteProduct($id);
            if($status){
                return redirect()->route('product.index')->with('success','Xóa thành công!');
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
}
