@extends('layouts.master')
@section('content')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger"> 
                <div class="box-header">
                    <h3 class="box-title">Quiz Question
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
                                <td></br><b>Select the Choice of Quiz</b></td>
                              </tr>
                               <tr>
                                <td ></br><b><label class="radio-inline"><input type="radio" name="quiz_type" id="multiple_choice" checked>Multiple Choice </label></b><label style="margin-left: 50px " class="radio-inline"><input type="radio"  id="single" name="quiz_type">Single</label></td>
                                <input type="hidden" id="type" name="type" value="multiple_choice">
                               
                              </tr>
                              <tr>
                                <td></br><b>Add Image For Quiz Question(Optional)</b></td>
                              </tr>
                              <tr>
                                <td>
                                 <input type="file" name="image"  class="form-control"  id="image">
                               </td>
                              </tr>
                              <tr>
                                <td></br><b>Question for today's quiz</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="question" class="form-control" value="" required></td>
                              </tr>
                              <div id="multiple_choice_view" style="display: block;">
                              <tr class="multiple_choice_view">
                                <td></br><b></b></td>
                              </tr>

                              <tr class="multiple_choice_view">
                                <td></br><b>Option 1</b></td>
                              </tr>
                              <tr class="multiple_choice_view">
                                <td><input type="text" name="option1" id="option1" class="form-control" value="" required ></td>
                              </tr>
                               <tr class="multiple_choice_view">
                                <td></br><b>Option 2</b></td>
                              </tr>
                              <tr class="multiple_choice_view">
                                <td><input type="text" name="option2" id="option2"  class="form-control" value="" required></td>
                              </tr>
                               <tr class="multiple_choice_view">
                                <td></br><b>Option 3</b></td>
                              </tr>
                              <tr class="multiple_choice_view">
                                <td><input type="text" name="option3"  id="option3" class="form-control" value="" required></td>
                              </tr>
                               <tr class="multiple_choice_view">
                                <td></br><b>Option 4</b></td>
                              </tr>
                              <tr class="multiple_choice_view">
                                <td><input type="text" name="option4"  id="option4" class="form-control" value="" required></td>
                              </tr>
                               <tr class="multiple_choice_view">
                                <td></br><b>Answer for today's quiz</b></td>
                              </tr>
                              <tr class="multiple_choice_view">
                                <td >
                                  <select  id="answer_select" name="answer" class="form-control">
                                     <option value="">Select Answer for today's quiz</option>
                                    <option id="option1_dropdown" value=""
                                       ></option>

                                    <option  id="option2_dropdown"  value="" ></option>
                                    <option  id="option3_dropdown"  value="" ></option>
                                    <option id="option4_dropdown"  value="" ></option>
                                  </select></td>
                              </tr>
                              </div>
                             
                                 <tr class="single_view" style="display: none">
                                <td></br><b>Answer for today's quiz</b></td>
                              </tr>
                              <tr class="single_view" style="display: none">
                                <td>
                                  <input type="text" class="form-control" name="answertext" id="answertext" required>
                                </td>
                              </tr>

                                
                            
                               <tr>
                                <td></br><b>Coupon Code</b></td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="text" pattern="^[a-zA-Z0-9]+$" class="form-control" name="coupon_code" >
                                </td>
                              </tr>

                              <tr>
                                <td></br><b>No. Of Winners</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="no_of_winners" value="" class="form-control"  onkeypress="javascript:return isNumber(event)" required></td>
                              </tr>

                              <tr>
                                <td></br><b>Offer Credit Points (It is equal to money value i.e. Grigora points)</b></td>
                              </tr>
                              <tr>

                                <td><input type="text" name="offer_points" class="form-control"  value="" onkeypress="javascript:return isNumber(event)" required></td>
                              </tr>
                              <tr>
                                <td></br><b>Offer Credit Description</b></td>
                              </tr>
                              <tr>
                                <td><textarea class="form-control"  name="description" required> </textarea>
                                </td>
                              </tr>

                           <!--    <tr>
                                <td></br><b>Min Order Value</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="min_order_value" value="" class="form-control"  onkeypress="javascript:return isNumber(event)" required></td>
                              </tr>

                                <tr>
                                <td></br><b>Max Order Value</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="max_order_value" value="" class="form-control"  onkeypress="javascript:return isNumber(event)" required></td>
                              </tr> -->
                              
                               <td></br><b>Offer Expiry</b></td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="text" name="offer_expiry" class="form-control" value=""  id="datepicker" required>
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