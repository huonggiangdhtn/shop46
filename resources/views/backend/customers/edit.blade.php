@extends('backend.layouts.master')
@section('content')

<div class = 'content'>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Điều chỉnh khách hàng
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
             <!-- BEGIN: Form Layout -->
             <form method="post" action="{{route('customer.update',$customer->id)}}">
                @csrf
                @method('patch')
                <div class="intro-y box p-5">
                    <div>
                        <label for="regular-form-1" class="form-label">Tên</label>
                        <input id="title" name="full_name" type="text" value="{{$customer->full_name}}" class="form-control" placeholder="tên">
                    </div>
                     

                   
                    <div class="mt-3">
                        <label for="regular-form-1" class="form-label">Địa chỉ</label>
                        <input id="address" name="address" value="{{$customer->address}}"  type="text" class="form-control" placeholder="địa chỉ">
                    </div>
                    
                    
                    <div class="mt-3">
                        
                        <label for="" class="form-label">Mô tả</label>
                       
                        <textarea class="editor"   id="editor1" name="description" >
                            <?php echo $customer->description;?>
                        </textarea>
                    </div>
                   
                    
                    
                    <div class="mt-3">
                        <div class="flex flex-col sm:flex-row items-center">
                            <label style="min-width:70px  " class="form-select-label"  for="status">Tình trạng</label>
                           
                            <select name="status" class="form-select mt-2 sm:mr-2"   >
                              
                                <option value ="active" {{$customer->status=='active'?'selected':''}}>Active</option>
                                <option value = "inactive" {{$customer->status =='inactive'?'selected':''}}>Inactive</option>
                            </select>
                        </div>
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
           <!-- end form -->
             
        </div>
    </div>
</div>
@endsection

@section ('scripts')


<!-- <script src="{{asset('backend/assets/dist/js/ckeditor-classic.js')}}"></script> -->
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script>
    $('#lfm').filemanager('image');
</script>
<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/38.1.1/classic/ckeditor.js"></script> -->
<script>
    

    var options = {
        filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
        filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
        filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
        filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}'
    };
    
    setTimeout(function(){
        CKEDITOR.replace( 'editor1', options );
    },100);
    
 
    
</script>
@endsection