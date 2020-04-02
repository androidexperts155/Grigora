@extends('layouts.master')
@section('content')
<section class="content"> 
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Quiz Details</h3>

                    @if (session()->has('message'))
                    <br>
                    <div class = "alert alert-success col-md-12">
                        <ul>
                          <li>{{Session::get('message')}}</li>
                      </ul>
                  </div>
                  @endif
              </div>
              <!-- /.box-header --> 
              <div class="box-body"  >

                 <div class="col-md-12">
                <table>
                <?php  
//echo'<pre>';print_r($order);die;

                ?>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Question</td>
                    <td>{{$quiz->question}}</td>
                  </tr>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Answer</td>
                    <td>{{$quiz->answer}}</td>
                  </tr>
                  <?php 
                  if($quiz->type == '1'){;
                  ?>
                  
                 <tr style="height: 50px;">
                    <td style="width: 200px;">Option 1</td>
                    <td>{{$quiz->option1}}</td>
                  </tr>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Option 2</td>
                    <td>{{$quiz->option2}}</td>
                  </tr>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Option 3</td>
                    <td>{{$quiz->option3}}</td>
                  </tr>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Option 4</td>
                    <td>{{$quiz->option4}}</td>
                  </tr>
                <?php } ?>
              
                <?php
                if($quiz->image == ''){
                  ?>
                <tr style="height: 50px;">
                    <td style="width: 200px;">Image</td>
                    <td></td>
                  </tr>
                <?php }else{ ?>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Image</td>
                    <td><img  width="100px"  height="100px" src="{{$quiz->image}}"></td>
                  </tr>
                <?php } ?>
                 
                 <tr style="height: 50px;">
                    <td style="width: 200px;">Description</td>
                    <td>{{$quiz->description}}</td>
                  </tr>
                   <tr style="height: 50px;">
                    <td style="width: 200px;">Coupon Code</td>
                    <td>{{$quiz->coupon_code}}</td>
                  </tr>
                
                  <tr style="height: 50px;">
                    <td style="width: 200px;">No. of Winners</td>
                    <td>{{$quiz->no_of_winners}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Offer Points</td>
                    <td>{{$quiz->offer_points}}</td>
                  </tr>
                   
                   <tr style="height: 50px;">
                    <td style="width: 200px;">Offer Expiry</td>
                    <td>{{$quiz->offer_expiry}}</td>
                  </tr>
                  </tr>
                </table>   
                </div>

              </div>
            </div>
          </div>


        </div>
<!-- /.row -->
</section>


@endsection
@section('page_scripts')
<script>
    var token = "{{ csrf_token() }}";
</script>
<script>
function goBack() {
  window.history.back();
}
</script>
<script>
    $(function () {
        $('#example1').DataTable();
        $('#example2').DataTable({
            'paging': true,
            'lengthChange': true,
            'searching': true,
            'ordering': false,
            'info': true,
            'autoWidth': false
        });
        
        $(".lp-change-password-btn").on("click", function () {
            var id = $(this).attr("data-id");
            var url = "{{url('admin/change-user-password')}}" + "/" + id;
            $("#change-password-modal form").attr('action', url);
            $("#change-password-modal").modal('show');
        });
    </script>
    @endsection