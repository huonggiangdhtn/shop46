@extends('backend.layouts.master')
@section('content')

<div class = 'content'>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Điều chỉnh người dùng
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
             <!-- BEGIN: Form Layout -->
             <form method="post" action="{{route('user.update',$user->id)}}">
                @csrf
                @method('patch')
                <div class="intro-y box p-5">
                    <div>
                        <label for="regular-form-1" class="form-label">Tên</label>
                        <input id="title" name="full_name" type="text" value="{{$user->full_name}}" class="form-control" placeholder="tên">
                    </div>
                    <!-- upload photo -->
                    <div class="mt-3">
                        <label for="" class="form-label">Photo</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                <i class="fa fa-picture-o"></i> Choose
                                </a>
                            </span>
                            <input id="thumbnail" value="{{$user->photo}}" class="form-control" type="text" name="photo">
                        </div>
                        <div id="holder" class="flex" style="margin-top:15px;max-height:150px;">
                                    <?php
                                        $photos = explode( ',', $user->photo);
                                        foreach($photos as $photo)
                                        {
                                            echo '<div class="w-10 h-10 image-fit zoom-in">
                                                <img class="tooltip rounded-full"  src="'.$photo.'"/>
                                            </div>';
                                        }
                                    ?>
                        </div>
                        <style>
                            #thumbnail{
                                pointer-events: none;
                            }
                            #holder img{
                                border-radius: 0.375rem;
                                margin:0.2rem;
                            }
                        </style>
                    </div>
                    <!-- end upload photo -->

                   
                    <div class="mt-3">
                        <label for="regular-form-1" class="form-label">Địa chỉ</label>
                        <input id="address" name="address" value="{{$user->address}}"  type="text" class="form-control" placeholder="địa chỉ">
                    </div>
                    <div class="mt-3">
                        <label for="regular-form-1" class="form-label">Email</label>
                        <input id="email" name="email" value="{{$user->email}}" type="text" class="form-control" placeholder="email">
                        
                    </div>
                    <div class="mt-3">
                        <label for="regular-form-1" class="form-label">Password</label>
                        <input id="password" name="password" type="text" class="form-control" placeholder="password">
                        <div class="form-help">Để trống nếu không reset mật khẩu</div>
                    </div>
                    <div class="mt-3">
                        
                        <label for="" class="form-label">Mô tả</label>
                       
                        <textarea class="editor"   id="editor1" name="description" >
                            <?php echo $user->description;?>
                        </textarea>
                    </div>
                   
                    <div class="mt-3">
                        <div class="flex flex-col sm:flex-row items-center">
                            <label style="min-width:70px  " class="form-select-label" for="">Vai trò</label><br/>
                            <select name="role"  class="form-select mt-2 sm:mr-2"   >
                                <option {{$user->role=='customer'?'selected':''}} value ="customer"> customer </option> 
                                <option  {{$user->role=='supplier'?'selected':''}} value ="supplier"> supplier </option> 
                                <option {{$user->role=='supcustomer'?'selected':''}} value ="supcustomer"> supcustomer </option> 
                                <option {{$user->role=='vendor'?'selected':''}} value ="vendor"> vendor </option>
                                <option {{$user->role=='manager'?'selected':''}} value ="manager"> manager </option>
                            </select>
                        </div>
                    </div>
                   <div class="mt-3">
                        <div class="flex flex-col sm:flex-row items-center">
                            <label style="min-width:70px  " class="form-select-label" for="status">Nhóm người dùng</label><br/>
                            <select name="ugroup_id"  class="form-select mt-2 sm:mr-2"   >
                                
                                @foreach($ugroups as $ugroup)
                                    <option value ="{{$ugroup->id}}" {{$ugroup->id == $user->ugroup_id?'selected':''}}> {{ $ugroup->title}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex flex-col sm:flex-row items-center">
                            <label style="min-width:70px  " class="form-select-label"  for="status">Tình trạng</label>
                           
                            <select name="status" class="form-select mt-2 sm:mr-2"   >
                                <option value ="active" {{$user->status=='active'?'selected':''}}>Active</option>
                                <option value = "inactive" {{$user->status =='inactive'?'selected':''}}>Inactive</option>
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