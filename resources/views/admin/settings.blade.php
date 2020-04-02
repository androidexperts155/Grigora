@extends('layouts.master')
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Settings
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
                    <form id="model-form" role="form" action="{{url('settings/save')}}" onsubmit="return send_msg();" enctype="multipart/form-data" method="POST">
                    <div class="box-body">
                    {{csrf_field()}}
                              <table>
                              
                              <tr>
                                <td></br><b>App Fee (In %)</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="app_fee" value="{{$setting['app_fee']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>

                              <tr>
                                <td></br><b>Delivery Fee</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="delivery_fee" value="{{$setting['delivery_fee']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>

                              <tr>
                                <td></br><b>Distance</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="distance" value="{{$setting['distance']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>

                              <tr>
                                <td></br><b>Minimum Order Value For Free Delivery</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="min_order" value="{{$setting['min_order']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>

                              <tr>
                                <td></br><b>Minimum Distance For Free Delivery (In Km)</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="min_km" value="{{$setting['min_km']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>
                              <tr>
                                <td></br><b>Minimum Wallet</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="min_wallet" value="{{$setting['min_wallet']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>

                              <tr>
                                <td></br><b>Maximum Wallet</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="max_wallet" value="{{$setting['max_wallet']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>

                              <tr>
                                <td></br><b>Sender Refer Earn</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="sender_refer_earn" value="{{$setting['sender_refer_earn']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>

                              <tr>
                                <td></br><b>Reciever Refer Earn</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="receiver_refer_earn" value="{{$setting['receiver_refer_earn']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>
                              <tr>
                                <td></br><b>Loyality Amount</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="loyality" value="{{$setting['loyality']}}" onkeypress="javascript:return isNumber(event)"></td>
                              </tr>
                              <tr>
                                <td></br><b>1 Naira EqualTo</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="naira_to_points" value="{{$setting['naira_to_points']}}" onkeypress="javascript:return isNumber(event)"></td>
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