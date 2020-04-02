@extends('layouts.master')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
<style type="text/css">
 span.star i {
    font-size: 25px;
}
i.glyphicon.glyphicon-minus-sign {
    display: none;
}
.caption span {
    display: none;
}

</style>
<section class="content"> 
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Driver Details</h3>

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
                    <td style="width: 200px;">Name</td>
                    <td>{{$users->name}}</td>
                  </tr>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Image</td>
                    <td><a href="{{$users->image}}" target="_blank"><img src="{{$users->image}}" style="height: 100px;width: 100px;"></a></td>
                  </tr>
                  
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Email</td>
                    <td>{{$users->email}}</td>
                  </tr>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Id Proof</td>
                    <td><a href="{{$users->id_proof}}" target="__blank"><img src="{{$users->id_proof}}" style="height: 100px;width: 100px;"></a></td>
                  </tr>

                  <tr style="height: 50px;">
                    <td style="width: 200px;">Phone</td>
                    <td>{{$users->phone}}</td>
                  </tr>
                  <!--  sat work -->
                  <tr style="height: 50px;">
                    <td style="width: 200px;">License Image</td>
                    <td><a href="{{$users->id_proof}}" target="__blank"><img src="{{$users->license_image}}" style="height: 100px;width: 100px;"></a></td>
                    
                  </tr>
                  <!--  sat work -->
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Average Rating</td>
                    <td> <input id="input-1" name="input-1" class="rating rating-loading input-id" value="{{$users->average_rating}}" data-min="0" data-max="5" data-step="0.5"  data-size="xs" readonly>
                      </td>
                  </tr>

                  <!-- <tr style="height: 50px;">
                    <td style="width: 200px;">Total Rating</td>
                    <td>{{$users->total_rating}}</td>
                  </tr> -->
                  
                  
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>
<script> 
    $(document).ready(function() {
      $(".input-id").rating();
       var value =  $(".input-id").rating().val();

     if(value < '3'){
       $('span.filled-stars').css('color','red');
     }else if(value >= '3' && value <= '4'){
       $('span.filled-stars').css('color','yellow');
     }else if(value > '4'){
       $('span.filled-stars').css('color','green');
     }
    });
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