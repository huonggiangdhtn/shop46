@extends('backend.layouts.master')
@section('content')

<div class="content">
@include('backend.layouts.notification')
    <h2 class="intro-y text-lg font-medium mt-10">
        Danh sách giao dịch tài khoản
    </h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    
            
            <div class="hidden md:block mx-auto text-slate-500">Hiển thị trang {{$banktrans->currentPage()}} trong {{$banktrans->lastPage()}} trang</div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-slate-500">
                  
                </div>
            </div>
        </div>
        <div   class=" intro-y col-span-12 flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form action="{{route('banktransaction.sort')}}" method = "get" class="xl:flex sm:mr-auto" >
                <!-- @csrf -->
                <div class="sm:flex items-center sm:mr-4">
                    <label style="min-width:80px" class="w-12 flex-none xl:w-auto xl:flex-initial mr-5">Sắp xếp cột: </label>
                    <select name="field_name" id="tabulator-html-filter-field" class="form-select w-full sm:w-32 2xl:w-full mt-2 sm:mt-0 sm:w-auto">
                        <option value="bank_id">Tài khoản</option>
                        <option value="id">Thời gian</option>
                        <option value="doc_type">Loại</option>
                        <option value="total">Số tiền</option>
                    </select>
                </div>
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Loại</label>
                    <select name="type_sort" id="tabulator-html-filter-type" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                        <option value="ASC" selected>tăng</option>
                        <option value="DESC" selected>giảm</option>
                    </select>
                </div>
               
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go" type="submit" class="btn btn-primary w-full sm:w-16" >Go</button>
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                
            </div>
        </div>
        
        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">TÀI KHOẢN</th>
                        <th class="whitespace-nowrap">SỐ TIỀN</th>
                        <th class="text-center whitespace-nowrap">LOẠI</th>
                        <th class="text-center whitespace-nowrap">HÓA ĐƠN</th>
                        <th class="text-center whitespace-nowrap">NGÀY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($banktrans as $item)
                    <tr class="intro-x">
                        <td>
                           {{\App\Models\Bankaccount::where('id',$item->bank_id)->value('title')}} 
                        </td>
                        <td class="text-right">
                            <span class="{{$item->operation==-1?"text-danger":"text-success"}}">
                           {{number_format($item->total,0,'.',',')}} 
                            </span>
                        </td>
                        <td class="text-right">
                           {{$item->operation==1?'thu':'chi'}} 
                        </td>
                         <td class="text-right">
                            <?php
                                if($item->doc_type=='wi')
                                {
                                    echo '<a class="font-medium" href ="'.route('warehousein.show',$item->doc_id)
                                    .'"> phiếu nhập: '.$item->doc_id.'</a>';
                                }
                                if($item->doc_type=="fi")
                                {
                                    echo '<a class="font-medium" href ="'.route('freetransaction.show',$item->doc_id)
                                    .'"> phiếu chi: '.$item->doc_id.'('.
                                    \App\Models\FreeTransaction::where('id',$item->doc_id)->value('content').')</a>';
                                }
                                if($item->doc_type =='si')
                                {
                                    echo '<a class="font-medium" href ="'.route('suptransaction.show',$item->doc_id)
                                    .'"> phiếu nạp tiền nhà cung cấp: '.$item->doc_id.'</a>';
                                }
                            ?>
                        </td>
                         
                        <td class="table-report__action w-56">
                              {{$item->created_at}}
                        </td>
                    </tr>

                    @endforeach
                    
                </tbody>
            </table>
            
        </div>
    </div>
    <!-- END: HTML Table Data -->
        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
            <nav class="w-full sm:w-auto sm:mr-auto">
                {{$banktrans->links('vendor.pagination.tailwind')}}
            </nav>
           
        </div>
        <!-- END: Pagination -->
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('backend/assets/vendor/js/bootstrap-switch-button.min.js')}}"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });
    $('.dltBtn').click(function(e)
    {
        var form=$(this).closest('form');
        var dataID = $(this).data('id');
        e.preventDefault();
        Swal.fire({
            title: 'Bạn có chắc muốn xóa không?',
            text: "Bạn không thể lấy lại dữ liệu sau khi xóa",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Vâng, tôi muốn xóa!'
            }).then((result) => {
            if (result.isConfirmed) {
                // alert(form);
                form.submit();
                // Swal.fire(
                // 'Deleted!',
                // 'Your file has been deleted.',
                // 'success'
                // );
            }
        });
    });
</script>
<script>
     

    $("[name='toogle']").change(function() {
        var mode = $(this).prop('checked');
        var id=$(this).val();
        $.ajax({
            url:"{{route('bankaccount.status')}}",
            type:"post",
            data:{
                _token:'{{csrf_token()}}',
                mode:mode,
                id:id,
            },
            success:function(response){
                Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: response.msg,
                showConfirmButton: false,
                timer: 1000
                });
                console.log(response.msg);
            }
            
        });
  
});  
    
</script>
<!-- <script>

// Example usage of the Product class
const product1 = new Product("Laptop", 1000, 2);
product1.displayInfo(); // Output the product information

// Update price and quantity
product1.updatePrice(1200);
product1.updateQuantity(3);
product1.displayInfo(); 
</script> -->
@endsection