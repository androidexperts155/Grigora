@extends('layouts.master')
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Add Promo Code
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
                    <form id="model-form" role="form" action="{{url('promocode/save')}}" onsubmit="return send_msg();" enctype="multipart/form-data" method="POST">
                    <div class="box-body">
                    {{csrf_field()}}
                              <table style="width: 100%">
                              <tr>
                                <td style="width: 376px;"><b>Name or Description</b></td>
                              </tr>
                              <tr>
                              <td> <input type="text" placeholder="Promo code Name and description" value="" class="form-control" name="name"></td>
                              </tr>
                              <tr>
                                <td></br><b>Code</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="code" placeholder="Promo code" value="" class="form-control" ></td>
                              </tr>
                              <tr>
                                <td></br><b>Image</b></td>
                              </tr>
                              <tr>
                                <td><input type="file" name="image" ></td>
                              </tr>
                              <tr>
                                <td></br><b>Percentage</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="percentage" placeholder="Percentage" value="" class="form-control" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>
                              <tr>
                                <td></br><b>No. Of times Used</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="no_of_attempts" onkeypress="javascript:return isNumber(event)" ></td>
                              </tr>
                              <tr>
                                <td></br><b>Description</b></td>
                              </tr>
                              <tr>
                                <td><textarea class="form-control" name="description" required></textarea></td>
                              </tr>
                              <tr>
                                <td></br><b>Min. Order Value</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="min_order_value" onkeypress="javascript:return isNumber(event)" required ></td>
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