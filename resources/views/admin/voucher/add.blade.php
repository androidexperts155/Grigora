@extends('layouts.master')
@section('content')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger"> 
                <div class="box-header">
                    <h3 class="box-title">GENERATE VOUCHER CODE
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
                    <form id="model-form" role="form" action="{{url('quiz/save')}}
" onsubmit="return send_msg();" enctype="multipart/form-data" method="POST">
                    <div class="box-body">
                    {{csrf_field()}}
                              <table width="100%">
                                                            
                              <tr>
                                <td></br><b>Add Voucher Image </b></td>
                              </tr>
                              <tr>
                                <td>
                                 <input type="file" name="voucher_image"  class="form-control"  id="voucher_image">
                               </td>
                              </tr>

                               <tr>
                                <td></br><b>Voucher Valid(In times) </b></td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="text" class="form-control" name="voucher_valid" id="voucher_valid" required>
                               </td>
                              </tr>

                                <tr>
                                <td></br><b>Voucher Amount </b></td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="text" class="form-control" name="voucher_valid" id="voucher_valid" required>
                               </td>
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
$('#option1').change(function(){ 

   var option1 = $('#option1').val();
   $('#option1_dropdown').val(option1);
   $('#option1_dropdown').text(option1);

});
$('#option2').change(function(){ 

   var option2 = $('#option2').val();
   $('#option2_dropdown').val(option2);
   $('#option2_dropdown').text(option2);

});
$('#option3').change(function(){ 

   var option3 = $('#option3').val();
   $('#option3_dropdown').val(option3);
   $('#option3_dropdown').text(option3);

});
$('#option4').change(function(){ 

   var option4 = $('#option4').val();
   $('#option4_dropdown').val(option4);
   $('#option4_dropdown').text(option4);

});

$('#multiple_choice').click(function(){
   $('.single_view').hide();
     $('.multiple_choice_view').show();
     $('#type').val('multiple_choice');
});
$('#single').click(function(){
     $('.multiple_choice_view').hide();
     $('.single_view').show();
       $('#type').val('single');
});


  $('#model-form').validate({
            rules: {
                name: {
                    required: true,
                    minlength: 5
                },
                
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
  
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  </script>
@endsection