@extends('layouts.master')
@section('content')
<section class="content"> 
    <div class="row">
        <div class="col-xs-6">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Order Details</h3>

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
                    <td style="width: 200px;">Order ID</td>
                    <td>{{$order['id']}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Customer Name</td>
                    <td>{{$order['customer_name']}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Restaurant Name</td>
                    <td>{{$order['restaurant_name']}}</td>
                  </tr>
                  <!-- <tr style="height: 50px;">
                    <td style="width: 200px;">Phone</td>
                    <td>{{$order->phone}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Provider Name</td>
                    <td>{{$order->provider_name}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Ratings</td>
                    <td>{{$order->ratings}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Delivery Address</td>
                    <td>{{$order->delivery_address}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Delivery Time</td>
                    <td>{{$order->delivery_time}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Date</td>
                    <td>{{date('d M Y h:i:s', strtotime($order->created_at))}}</td>
                  </tr> -->
                  <!-- <tr style="height: 50px;">
                    <td style="width: 200px;">Price</td>
                    <td>{{$order->original_price}}</td>
                  </tr> -->
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Price</td>
                    <td>{{$order->price_before_promo}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">App Fee</td>
                    <td>{{$order['app_fee']}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Delivery Fee</td>
                    <td>{{$order['delivery_fee']}}</td>
                  </tr>
                  <tr style="height: 50px;">
                    <td style="width: 200px;">Total Amount</td>
                    <td>{{$order->final_price}}</td>
                  </tr>
                </table>   
                </div>

              </div>
            </div>
          </div>


          <div class="col-xs-6">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Order Items</h3>
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
              <div class="box-body" >

                <div class="col-md-12">
                  <table>
                    @forelse($order['details'] as $key => $details)
                      <tr style="height: 50px;">
                        <td style="width: 200px;"></td>
                        <td></td>
                      </tr>                    
                    @empty
                    <tr><td colspan="">No Orders!!!</td></tr>
                    @endforelse
                       

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