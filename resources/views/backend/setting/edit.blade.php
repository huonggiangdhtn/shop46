@extends('backend.layouts.master')
@section('content')

<div class = 'content'>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Điều chỉnh setting
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <!-- BEGIN: Form Layout -->
            <form method="post" action="{{route('setting.update',$setting->id)}}">
                @csrf
                @method('patch')
                <div class="intro-y box p-5">
                    <div>
                        <label for="regular-form-1" class="form-label">Tiêu đề</label>
                        <input id="company_name" name="company_name" type="text" value="{{$setting->company_name}}" class="form-control" placeholder="computer_name">
                    </div>
                    <div>
                        <label for="regular-form-1" class="form-label">Điện thoại</label>
                        <input id="phone" name="phone" type="text" value="{{$setting->phone}}" class="form-control" placeholder="computer_name">
                    </div>
                    <div>
                        <label for="regular-form-1" class="form-label">Địa chỉ</label>
                        <input id="address" name="address" type="text" value="{{$setting->address}}" class="form-control" placeholder="address">
                    </div>
                    <div class="mt-3">
                        <label for="" class="form-label">Photo</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                                </a>
                            </span>
                            <input id="thumbnail" class="form-control" type="text" name="logo" value ="{{$setting->photo}}">
                        </div>
                        <div id="holder" style="margin-top:15px;max-height:100px;">
                            <img src ="{{$setting->logo}}" style="margin-top:15px;max-height:100px;"/>
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
                        <button type="submit" class="btn btn-primary w-24">Cập nhật</button>
                    </div>
                </div>
            </form>
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