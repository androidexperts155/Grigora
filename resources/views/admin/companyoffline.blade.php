@extends('layouts.master')
@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
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

                    <div class="box-body">
               
                              <table>
                              
                              <tr>
                                <td></br><b>Company Offline</b></td>

                                <td style="margin-left: 20px;"></br>
                                  <?php if($offline->status == '0'){ ?>

                                      <a style="margin-left: 20px; float: left;margin-right: 10px;" title="Mark online" class="btn btn-primary" id="company_offline" data-status="1" ><span class="glyphicon">Mark online</span></a>
                                 <?php }else{ ?>

                                       <a style="margin-left: 20px; float: left;margin-right: 10px;" title="Mark online" class="btn btn-primary" id="company_offline" data-status="0" ><span class="glyphicon">Mark offline</span></a>

                                 <?php } ?>
                                    
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
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    var token = "{{ csrf_token() }}";
</script>
<script type="text/javascript">

  $('#company_offline').click(function(){

        var status = $(this).data('status');
        
         swal({
                title: "Are you sure",               
                buttons: true,              
              }).then((value) => {
              if(value == true){
                $.ajax({
                  url : "{{ route('company_offline_save') }}",
                  type: 'POST',
                  data :{
                    "status":status,
                     " _token":'{{csrf_token()}}'
                    },
                }).done(function(response){ 
                      console.log(response);
                      if(response.status == 1){
                        swal("Success!", response.message, "success").then((value) => {
                            location.reload(true);
                          });
                      }
                      
                });

                console.log('jdwd');
              }
            });

  });
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