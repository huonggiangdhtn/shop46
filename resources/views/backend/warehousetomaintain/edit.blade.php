@extends('backend.layouts.master')
@section('content')

<div class = 'content'>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Thêm chuyển kho bảo hành
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <!-- BEGIN: Form Layout -->
            <form method="post" action="{{route('warehousetomaintain.update',$warehousetomain->id)}}">
                @csrf
                @method('patch')
                <div class="intro-y box p-5">
                <div class="mt-3">
                        <div id="div_parent_id" class="  flex flex-col sm:flex-row items-center">
                            <label style="min-width:50px  " class="form-select-label" for="status">Kho</label>
                            <select id="warehouse_id" name="wh_id" class="form-select mt-2 sm:mr-2" aria-label="Default select example"   >
                                @foreach ($warehouses as $wh)
                                    <option value="{{$wh->id}}" {{$warehousetomain->wh_id==$wh->id?'selected':''}}>{{$wh->title}}</option>
                                    
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                         
                        <label for="regular-form-1" class="form-label">Sản phẩm</label>
                        <input id="product_search" value="{{\App\Models\Product::find($warehousetomain->product_id)->title}}"  
                                type="text" class="form-control" placeholder="tên">
                        <input type="hidden" id= "product_id" value="{{$warehousetomain->product_id}}" name="product_id"/>
                    </div>
                    
                    <div class="mt-3">
                        <label for="regular-form-1" class="form-label">Số lượng</label>
                        <input  onchange="updateQuantity()"   class="form-control" type="text" id= "quantity" name="quantity" value='{{$warehousetomain->quantity}}'/>
                        <div class="form-help">
                            (Tồn kho hiện tại: <span id="spstock"> {{\App\Models\Inventory::where('product_id',$warehousetomain->product_id)->where('wh_id',$warehousetomain->wh_id)->first()->quantity +$warehousetomain->quantity }}</span> )
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="regular-form-1" class="form-label">Đơn giá</label>
                        <input   class="form-control" type="text" id= "price" name="price" value='{{$warehousetomain->price}}'/>
                    </div>
                    <div class="mt-3">
                        @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>    {{$error}} </li>
                                    @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                    <div class="text-right mt-5">
                        <button type="submit" class="btn btn-primary w-24">Lưu</button>
                    </div>
                </div>
            </form>
            <!-- end form layout -->
        </div>
    </div>
</div>
@endsection

@section ('scripts')

<link href="http://code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css" rel="Stylesheet"> <script src="YourJquery source path"></script> 
<script src="http://code.jquery.com/ui/1.12.0/jquery-ui.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $.ajaxSetup({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
});
var product_id = {{$warehousetomain->product_id}};
var quantity = {{$warehousetomain->quantity}};
var stock = {{\App\Models\Inventory::where('product_id',$warehousetomain->product_id)->where('wh_id',$warehousetomain->wh_id)->first()->quantity +$warehousetomain->quantity }};
function updateQuantity( )
{
    ip = document.getElementById('quantity');
    // alert(ip.value);
    
    if(ip.value > stock)
    {
        Swal.fire(
            'Không hợp lệ!',
            'Số lượng lớn hơn số lượng tồn kho!',
            'error'
        );
        ip.value = stock;
    }
   
}

$(document).ready(function(){ //Your code here 
    
    var warehouse_id = $('#warehouse_id');
    warehouse_id.change(function(e){
        e.preventDefault();
        var whid = $(this).val();
         $('#product_id').val(0);
         $('#quantity').val(0);
    });
       
       
   
    var product_search = $('#product_search');
    product_search.autocomplete({
        source: function(request, response) {
            var warehouse_id = $('#warehouse_id').val();
            
            $.ajax({
                type: 'GET',
                url: '{{route('product.tsearch')}}',
                data: {
                    data: request.term,
                    warehouse_id: warehouse_id,
                
                },
                success: function(data) {
                    console.log(data);
                    response( jQuery.map( data.msg, function( item ) {
                        var imageurls = item.photo.split(",");
                    
                        return {
                        id: item.id,
                        value: item.title,
                        imgurl: imageurls[0],
                        qty: item.quantity,
                        price:item.price,
                        }
                    }));
                }
            });
        },
        response: function(event, ui) {
        
        },
        select: function(event, ui) {
            if(ui.item.qty == null)
             {
                $('#quantity').val(0);
                stock = 0;
             }  
            else
            {
                stock = ui.item.qty;
                $('#quantity').val(1);
            }    
            $('#product_id').val(ui.item.id);
            $('#price').val(ui.item.price);
            $('#spstock').html(stock);
        }
    }).data('ui-autocomplete')._renderItem = function(ul, item){
        $( ul ).addClass('dropdown-content overflow-y-auto h-52 ');
        return $("<li class='mt-10 dropdown-item  '></li>")
            .data("item.autocomplete", item )
            // .append('<div  style="clear:both"><div style="  pointer-events: none; width:50; float:left; "><img width="50" height="50" src="'+item.imgurl+'"/></div> <div style="float:left"> <span style=" pointer-events: none;">'+item.value+' </span> <br/> <span>số lượng: '+ item.qty +'</span> &nbsp;&nbsp;&nbsp;&nbsp; <span> giá: '+  Intl.NumberFormat('en-US').format(item.price)+'</div></div>' )
            .append('<table style=" border:none; background:none" > <tr><td><img class="rounded-full" width="50" height="50" src="'+item.imgurl
            +'"/></td><td style=" text-align: left;"><span class="font-medium">'+ item.value 
            +'</span><br/> <span class=" text-slate-500">No:' + (item.qty==null?0:item.qty) 
            +"</span></td></tr></table>")
            .appendTo(ul);
        };;
       

});
    

</script>
@endsection