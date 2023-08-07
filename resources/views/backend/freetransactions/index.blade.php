@extends('backend.layouts.master')
@section('content')

<div class="content">
@include('backend.layouts.notification')
    <h2 class="intro-y text-lg font-medium mt-10">
        Danh sách thu chi
    </h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    
            <a href="{{route('freetransaction.create')}}" class="btn btn-primary shadow-md mr-2">Thêm thu chi</a>
           
            <div class="hidden md:block mx-auto text-slate-500">Hiển thị trang {{$freetrans->currentPage()}} trong {{$freetrans->lastPage()}} trang</div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-slate-500">
                  
                </div>
            </div>
        </div>
        
        
        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">CONTENT</th>
                        <th class="whitespace-nowrap">SỐ TIỀN</th>
                        <th class="whitespace-nowrap">TÀI KHOẢN</th>
                        <th class="text-center whitespace-nowrap">LOẠI</th>
                        <th class="text-center whitespace-nowrap">NGƯỜI LẬP</th>
                        <th class="text-center whitespace-nowrap">NGÀY</th>
                        <th class="text-center whitespace-nowrap"> </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($freetrans as $item)
                    <tr class="intro-x">
                        <td>
                           {{$item->content}} 
                        </td>
                        <td class="text-right">
                            {{Number_format($item->total,0,'.',',')}} 
                        </td>
                        <td>
                           {{\App\Models\Bankaccount::find($item->bank_id)->title}} 
                        </td>
                        <td class="text-right">
                           {{$item->operation==1?'thu':'chi'}} 
                        </td>
                         <td class="text-right">
                         {{\App\Models\User::find($item->user_id)->full_name}} 
                             
                        </td>
                        <td class="text-right">
                            {{$item->created_at}}
                             
                        </td>
                        <td class="table-report__action ">
                         <a   href="{{route('freetransaction.show',$item->id)}}" 
                                    class="flex items-center mr-3" href="javascript:;"> 
                            <i data-lucide="eye" class="w-4 h-4 mr-1"></i> Xem 
                        </a> 
                                   
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
                {{$freetrans->links('vendor.pagination.tailwind')}}
            </nav>
           
        </div>
        <!-- END: Pagination -->
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('backend/assets/vendor/js/bootstrap-switch-button.min.js')}}"></script>
  
 
 
@endsection