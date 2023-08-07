@extends('backend.layouts.master')
@section('content')

<div class = 'content'>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Thêm danh mục
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <!-- BEGIN: Form Layout -->
            <form method="post" action="{{route('category.store')}}">
                @csrf
                <div class="intro-y box p-5">
                    <div>
                        <label for="regular-form-1" class="form-label">Tiêu đề</label>
                        <input id="title" name="title" type="text" class="form-control" placeholder="title">
                    </div>
                    <div class="mt-3">
                        <label for="" class="form-label">Photo</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                                </a>
                            </span>
                            <input id="thumbnail" class="form-control" type="text" name="photo">
                        </div>
                        <div id="holder" style="margin-top:15px;max-height:100px;">
                        </div>
                    </div>
                    <div class="mt-3">
                        
                        <label for="" class="form-label">Mô tả</label>
                       
                        <textarea class="editor" name="summary" id="editor1"  >
                            {{old('summary')}}
                        </textarea>
                    </div>
                    <div class="mt-3">  
                        <div class="flex flex-col sm:flex-row mt-2">
                            <div class="form-check mr-2"> 
                                <input id="is_parent" name="is_parent" id="checkbox-switch-4" class="form-check-input" type="checkbox" value="1"> 
                                <label class="form-check-label" for="checkbox-switch-4">là danh mục cha</label> 
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div id="div_parent_id" class="  flex flex-col sm:flex-row items-center">
                            <label style="min-width:70px  " class="form-select-label" for="status">Danh mục cha</label>
                           
                            <select name="parent_id" class="form-select mt-2 sm:mr-2" aria-label="Default select example"   >
                                <option value =""> --chọn danh mục cha-- </option>
                                @foreach ($parent_cats as $pcat)
                                    <option value="{{$pcat->id}}">{{$pcat->title}}</option>
                                    
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex flex-col sm:flex-row items-center">
                            <label style="min-width:70px  " class="form-select-label" for="status">Tình trạng</label>
                           
                            <select name="status" class="form-select mt-2 sm:mr-2" aria-label="Default select example"   >
                                <option value =""> --tình trạng-- </option>
                                <option value ="active" {{old('status')=='active'?'selected':''}}>Active</option>
                                <option value = "inactive" {{old('status')=='inactive'?'selected':''}}>Inactive</option>
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
        </div>
    </div>
</div>
@endsection

@section ('scripts')



<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<!-- <script src="{{asset('backend/assets/vendor/js/ckeditor4.js')}}"></script> -->
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

<script>
    var is_checked = $('#is_parent').prop('checked');
        // alert(is_checked);
        if(is_checked){
            $('#div_parent_id').addClass('md:hidden');
        }
        else
        {
            $('#div_parent_id').removeClass('md:hidden');
        }
        
    $('#is_parent').change(function(e){
        e.preventDefault();
        var is_checked = $('#is_parent').prop('checked');
        // alert(is_checked);
        if(is_checked){
            $('#div_parent_id').addClass('md:hidden');
        }
        else
        {
            $('#div_parent_id').removeClass('md:hidden');
        }
    });

</script>
@endsection