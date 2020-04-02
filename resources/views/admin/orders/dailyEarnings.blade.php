@extends('layouts.master')
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header" >
                    <h3 class="box-title" style="width: 100%">All Orders
              </button>

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
                      
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                 <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                
               
                <td>Date: <input type="date" style="width: 160px;" id="search-date" placeholder="search date"></td>
                
                </tr>
                <tr>
                  <th>Sr. No.</th>
                  <th>Order Id</th>
                  <th>Restaurant Name</th>
                  <th>Total Price</th>
                  <th>App Commission</th>
                  <th>Order Status</th>
                  <th>Placed Date</th>
                </tr>
                </thead>
                <tbody>
                <?php $count = 0; ?>
                <?php //echo'<pre>';print_r($orders);die; ?>
                @forelse($orders as $key => $order)
                <?php $count++; ?>
                
                <tr>
                  <td>  
                            {{@$count}}
                  </td>
                  <td><a href="edit/{{$order['id']}}">{{$order['id']}}</a></td>
                  <td>{{$users[$order['restaurant_id']]}}</td>
                  <td>{{$order['final_price']}}</td>
                  <td></td>

                  @php
                    if($order['payment_method'] == '1')
                      $paymentMethod = 'Cash';
                    else
                      $paymentMethod = 'Card';                
                  @endphp

                  
                  
                  @php
                  if($order['order_status'] == 0)
                      $status = 'Order Placed';
                  elseif($order['order_status'] == 2)
                      $status = 'Preparing Order';
                  elseif($order['order_status'] == 3)
                      $status = 'Driver Assigned';
                  elseif($order['order_status'] == 4)
                      $status = 'Out of delivery';
                  elseif($order['order_status'] == 5)
                      $status = 'Deliverd';
                  elseif($order['order_status'] == 6)
                      $status = 'Restaurant Rejected Order';
                  else
                      $status = 'Completed';
                  
                  @endphp
                  <td>{{$status}}</td>
                  <td>{{$order['created_at']}}</td>
                  
                </tr>
                @empty
                <tr><td colspan="6">No Orders!!!</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr>
                  <th>Sr. No.</th>
                  <th>Order Id</th>
                  <th>Restaurant Name</th>
                  <th>Total Price</th>
                  <th>App Commission</th>
                  <th>Order Status</th>
                  <th>Placed Date</th>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>


                            
                           </div>
                </div>

                


                    
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>


@endsection
@section('page_scripts')
<!-- <script src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script> -->
<script>
    var token = "{{ csrf_token() }}";
</script>
<script type="text/javascript">
  $(function () {
    table = $('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  });

  $('#search-date').on('change', function(){
   
    table
    .column(5)
    .search(this.value)
    .draw();

  });
  $(document).ready( function() {
    var now = new Date();
    var month = (now.getMonth() + 1);               
    var day = now.getDate();
    if (month < 10) 
        month = "0" + month;
    if (day < 10) 
        day = "0" + day;
    var today = now.getFullYear() + '-' + month + '-' + day;
    $('#search-date').val(today);
      table
    .column(5)
    .search(today)
    .draw();
  });
</script>
@endsection