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
                    <form id="model-form" role="form" action="{{url('quiz/editsave')}}"  enctype="multipart/form-data" method="POST">
                    <div class="box-body">
                    {{csrf_field()}}
                              <table width="100%">
                              
                              <tr>
                                <td></br><b>Question</b></td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="hidden" name="id" value="{{$quiz['id']}}">
                                  <input type="text" name="question" class="form-control" value="{{$quiz['question']}}" required></td>
                              </tr>
                              <?php 
                              if($quiz['type'] == '2'){ ?>


                              <tr style="display: none;">
                                <td ></br><b>Option 1</b></td>
                              </tr>
                              <tr style="display: none;">
                                <td><input type="text" name="option1" id="option1" class="form-control" value="{{$quiz['option1']}}" required ></td>
                              </tr >
                               <tr style="display: none;">
                                <td></br><b>Option 2</b></td>
                              </tr>
                              <tr style="display: none;">
                                <td><input type="text" name="option2" id="option2"  class="form-control" value="{{$quiz['option2']}}" required></td>
                              </tr>
                               <tr style="display: none;">
                                <td></br><b>Option 3</b></td>
                              </tr>
                              <tr style="display: none;">
                                <td><input type="text" name="option3"  id="option3" class="form-control" value="{{$quiz['option3']}}" required></td>
                              </tr>
                               <tr style="display: none;">
                                <td></br><b>Option 4</b></td>
                              </tr>
                              <tr style="display: none;">
                                <td><input type="text" name="option4"  id="option4" class="form-control" value="{{$quiz['option4']}}" required></td>
                              </tr>
                               <tr style="display: none;">
                                <td></br><b>Answer</b></td>
                              </tr>
                              <tr style="display: none;">
                                <td>
                                  <select  id="answer_select" name="answer" class="form-control">
                                     <option value="">Select Answer</option>
                                    <option id="option1_dropdown" value="{{$quiz['option1']}}"

                                    {{( $quiz['answer'] == $quiz['option1']) ? 'selected' : '' }} >{{$quiz['option1']}}</option>

                                    <option  id="option2_dropdown"  value="{{$quiz['option2']}}" 
                                    {{( $quiz['answer'] == $quiz['option2']) ? 'selected' : '' }}
                                    >{{$quiz['option2']}}</option>
                                    <option  id="option3_dropdown"  value="{{$quiz['option3']}}"
                                    {{( $quiz['answer'] == $quiz['option3']) ? 'selected' : '' }}
                                    >{{$quiz['option3']}}</option>
                                    <option id="option4_dropdown"  value="{{$quiz['option4']}}"
                                     {{( $quiz['answer'] == $quiz['option4']) ? 'selected' : '' }}
                                    >{{$quiz['option4']}}</option>
                                  </select></td>
                              </tr>

                              <?php }else{?>


                              <tr>
                                <td ></br><b>Option 1</b></td>
                              </tr>
                              <tr >
                                <td><input type="text" name="option1" id="option1" class="form-control" value="{{$quiz['option1']}}" required ></td>
                              </tr >
                               <tr >
                                <td></br><b>Option 2</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="option2" id="option2"  class="form-control" value="{{$quiz['option2']}}" required></td>
                              </tr>
                               <tr >
                                <td></br><b>Option 3</b></td>
                              </tr>
                              <tr >
                                <td><input type="text" name="option3"  id="option3" class="form-control" value="{{$quiz['option3']}}" required></td>
                              </tr>
                               <tr >
                                <td></br><b>Option 4</b></td>
                              </tr>
                              <tr >
                                <td><input type="text" name="option4"  id="option4" class="form-control" value="{{$quiz['option4']}}" required></td>
                              </tr>
                               <tr >
                                <td></br><b>Answer</b></td>
                              </tr>
                              <tr >
                                <td>
                                  <select  id="answer_select" name="answer" class="form-control">
                                     <option value="">Select Answer</option>
                                    <option id="option1_dropdown" value="{{$quiz['option1']}}"

                                    {{( $quiz['answer'] == $quiz['option1']) ? 'selected' : '' }} >{{$quiz['option1']}}</option>

                                    <option  id="option2_dropdown"  value="{{$quiz['option2']}}" 
                                    {{( $quiz['answer'] == $quiz['option2']) ? 'selected' : '' }}
                                    >{{$quiz['option2']}}</option>
                                    <option  id="option3_dropdown"  value="{{$quiz['option3']}}"
                                    {{( $quiz['answer'] == $quiz['option3']) ? 'selected' : '' }}
                                    >{{$quiz['option3']}}</option>
                                    <option id="option4_dropdown"  value="{{$quiz['option4']}}"
                                     {{( $quiz['answer'] == $quiz['option4']) ? 'selected' : '' }}
                                    >{{$quiz['option4']}}</option>
                                  </select></td>
                              </tr>


                              <?php } ?>
                                 <?php 
                              if($quiz['type'] == '1'){ ?>
                              
                              <tr class="single_view" style="display: none">
                                <td></br><b>Answer for today's quiz</b></td>
                              </tr>
                              <tr class="single_view" style="display: none">
                                <td>
                                  <input type="text" class="form-control" name="answertext" id="answertext" value="{{$quiz['answer']}}"required>
                                </td>
                              </tr>
                               <?php }else{?>

                                  <tr class="single_view" >
                                <td></br><b>Answer for today's quiz</b></td>
                              </tr>
                              <tr class="single_view" >
                                <td>
                                  <input type="text" class="form-control" name="answertext" id="answertext" value="{{$quiz['answer']}}" required>
                                </td>
                              </tr>

                               <?php }?>

                             
                            
                               <tr>
                                <td></br><b>Coupon Code</b></td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="text" pattern="^[a-zA-Z0-9]+$" class="form-control" value="{{$quiz['coupon_code']}}" name="coupon_code" >
                                </td>
                              </tr>

                              
                              <tr>
                                <td></br><b></b></td>
                              </tr>
                              <tr>
                                <td>
                                 <img src="{{$quiz['image']}}" width="100px" height="100px">
                               </td>
                              </tr>
                              <tr>
                                <td></br><b>Image</b></td>
                              </tr>
                              <tr>
                                <td>
                                 <input type="file" name="image"  class="form-control"  id="image">
                               </td>
                              </tr>
                              <tr>
                                <td></br><b>No. Of Winners</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="no_of_winners" value="{{$quiz['no_of_winners']}}" class="form-control"  onkeypress="javascript:return isNumber(event)" required></td>
                              </tr>

                              <tr>
                                <td></br><b>Offer Credit Points</b></td>
                              </tr>
                              <tr>
                                <td><input type="text" name="offer_percentage" class="form-control"  value="{{$quiz['offer_points']}}" onkeypress="javascript:return isNumber(event)" required></td>
                              </tr>
                              <tr>
                                <td></br><b>Offer Credit Description</b></td>
                              </tr>
                              <tr>
                                <td><textarea class="form-control"  name="description" value="{{$quiz['description']}}"required> {{$quiz['description']}}</textarea>
                                </td>
                              </tr>
                            
                              
                               <td></br><b>Offer Expiry</b></td>
                              </tr>
                              <tr>
                                <td>
                                  <input type="text" name="offer_expiry" class="form-control" value="{{$quiz['offer_expiry']}}"  id="datepicker" required>
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
  
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  </script>
@endsection