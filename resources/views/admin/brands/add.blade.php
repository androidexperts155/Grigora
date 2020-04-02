@extends('layouts.master')
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Add Brand
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
                    <form id="model-form" role="form" action="{{url('brand/save')}}" enctype="multipart/form-data" method="POST">
                    <div class="box-body">
                    {{csrf_field()}}
                              <table style="width: 100%">
                              
                              <tr>
                                <td style="width: 366px;"><b>Name</b></td>
                              </tr>
                              <tr>
                              <td> <input type="text" placeholder="Name" value="" class="form-control" name="name" required></td>
                                
                              </tr>
                              
                              <tr>
                                <td><br/><b>Image</b></td>
                              </tr>
                              <tr>
                                
                                 <td> <input type="file" value="" name="image" required></td>
                              </tr>
                         

                              <tr>
                                <td>
                                </br>
                                  <input type="submit" class="btn btn-info" name="submit" value="Submit">
                                </td>
                              </tr>
                              </table>


                    <div>
                            
                           </div>
                </div>

                </form>


                    
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>


@endsection
@section('page_scripts')
<script>
    var token = "{{ csrf_token() }}";
</script>
<script type="text/javascript">
function isNumber(evt) {
        var iKeyCode = (evt.which) ? evt.which : evt.keyCode
        if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57))
            return false;

        return true;
    }
  $('#model-form').validate({
            rules: {
                name: {
                    required: true,
                    //minlength: 5
                },
                cat_image: {
                    required: true,
                }
            },
            messages: {
                name: 'Name field is required',
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