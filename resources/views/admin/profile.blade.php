@extends('layouts.master')
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Profile
                    </h3>
                      @if(session()->has('message'))
                          <div class="alert alert-success">
                              {{ session()->get('message') }}
                          </div>
                      @endif
                      @if ($errors->any())
                          <div class="alert alert-danger">
                              <ul>
                                  @foreach ($errors->all() as $error)
                                      <li>{{ $error }}</li>
                                  @endforeach
                              </ul>
                          </div>
                      @endif
                  
                      <div class="container-fluid">
                         <div class="row">
                             <div class="col-md-3">
                                 <!-- Profile Image -->
                                 <div class="card card-primary card-outline">
                                     <div class="card-body box-profile">
                                         <div class="text-center">
                                              <img src="{{asset(Auth::user()->image == NULL && Auth::user()->image == '' ? asset('images/default_profile.png') : asset(Auth::user()->image) )}}" class="img-circle" alt="User Image" width="100px" height="100px">
                                         </div>
                                         <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>
                                         <!-- <p class="text-muted text-center">Software Engineer</p> -->
                                     </div>
                                 </div>
                             </div>
                         <div class="col-md-9">
                             <div class="card">
                                 <div class="card-body">
                         <div class="tab-content">
                             <!-- /.tab-pane -->
                             <div class="tab-pane active show" id="settings">
                                 <form class="form-horizontal" id="form_profile" action="{{route('saveprofile')}}" method="POST" enctype="multipart/form-data">
                                     {{ csrf_field() }}
                                     <?php //echo "<pre>";print_r($errors); ?>
                                     <div class="form-group">
                                         <label for="inputName" class="col-sm-2 control-label">Name</label>
                                         <div class="col-sm-10">
                                             <input type="text" class="form-control" id="inputName" placeholder="Name" name="name" value="{{ Auth::user()->name }}">
                                             @if ($errors->has('firstname'))
                                                 <span class="error">
                                                     <strong>{{ $errors->first('firstname') }}</strong>
                                                 </span>
                                             @endif
                                         </div>
                                     </div>
                                    <div class="form-group">
                                         <label for="inputEmail" class="col-sm-2 control-label">Email</label>
                                         <div class="col-sm-10">
                                             <input type="text" class="form-control" id="inputEmail" placeholder="Email" name="email" value="{{ Auth::user()->email }}" readonly>
                                             @if ($errors->has('email'))
                                                 <span class="error">
                                                     <strong>{{ $errors->first('email') }}</strong>
                                                 </span>
                                             @endif
                                         </div>
                                     </div>
                                  <div class="form-group">
                                             <label for="inputEmail" class="col-sm-2 control-label">New Password</label>
                                             <div class="col-sm-10">
                                                 <input type="password" class="form-control" id="inputp"  name="new_password" >
                                                 @if ($errors->has('new_password'))
                                                     <span class="error">
                                                         <strong>{{ $errors->first('new_password') }}</strong>
                                                     </span>
                                                 @endif
                                             </div>
                                         </div>

                                         <div class="form-group">
                                             <label for="inputEmail" class="col-sm-2 control-label">New Image</label>
                                             <div class="col-sm-10">
                                                 <input type="file" class="form-control" id="inputI"  name="new_image" >
                                                 @if ($errors->has('new_image'))
                                                     <span class="error">
                                                         <strong>{{ $errors->first('new_image') }}</strong>
                                                     </span>
                                                 @endif
                                             </div>
                                         </div>
                                                 
                                         <div class="form-group">
                                             <div class="col-sm-offset-2 col-sm-10">
                                                 <button type="submit" class="btn btn-danger">Submit</button>
                                             </div>
                                         </div>
                                     </form>
                                 </div>
                                 
                             </div>
                             
                         </div>
                         
                     </div>
                 </div>
               </div>
            </div>

                    
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>


@endsection
@section('page_scripts')
<script type="text/javascript">
  function isNumber(evt) {
        var iKeyCode = (evt.which) ? evt.which : evt.keyCode
        if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57))
            return false;

        return true;
    }
</script>
<script>
    var token = "{{ csrf_token() }}";
</script>
<script type="text/javascript">
  $('#model-form').validate({
            rules: {
                name: {
                    required: true,
                    minlength: 5
                },
                cat_image: {
                    required: true,
                }
            },
            messages: {
                name: 'Category Name field is required And minlength is 5',
                //email: 'Email field is required',
                //password: 'Password field is required',
                //confirm_password: 'Confirm password field is required'
            },
            errorElement: 'span',
            errorClass: 'error text-danger',
            highlight: function (element, errorClass, validClass) {
                $(element).parents("div.form-group").addClass("has-error");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents("div.form-group").removeClass("has-error");
            },
        });
</script>
@endsection