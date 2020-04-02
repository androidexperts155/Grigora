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
                       <p id="date_filter">
    <span id="date-label-from" class="date-label">From: </span><input class="date_range_filter date" type="text" id="datepicker_from" />
    
</p>
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Order Id</th>
                  <th>Customer Name</th>
                  <th>Restaurant Name</th>
                  <th>Price</th>
                  <th>Payment Method</th>
                  <th>Order Status</th>
                  <th>Action</th>
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
                  <td>{{$order['customer_name']}}</td>
                  <td>{{$order['restaurant_name']}}</td>
                  <td>{{$order['final_price']}}</td>

                  @php
                    if($order['payment_method'] == '1')
                      $paymentMethod = 'Cash';
                    else
                      $paymentMethod = 'Card';                
                  @endphp

                  <td>{{$paymentMethod}}</td>
                  
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
                  
                  <td>
                  <a style="float: left;margin-right: 5px;" title="view order detail" class="btn btn-primary" href="details/{{$order['id']}}"><span class="glyphicon glyphicon-th-list"></span></a>

                  <!-- <a style="float: left;margin-right: 5px;" title="Edit" class="btn btn-primary" href="edit/{{$order['id']}}"><span class="glyphicon glyphicon-edit"></span></a> -->
                  </td>
                </tr>
                @empty
                <tr><td colspan="6">No Orders!!!</td></tr>
                @endforelse
                </tbody>
              <!--   <tfoot>
                <tr>
                  <th>Sr. No.</th>
                  <th>Order Id</th>
                  <th>Customer Name</th>
                  <th>Restaurant Name</th>
                  <th>Price</th>
                  <th>Payment Method</th>
                  <th>Order Status</th>
                  <th>Action</th>
                </tr>
                </tfoot> -->
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
    //$('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  });
</script>

<script type="text/javascript">
  $(function() {
  var oTable = $('#example1').DataTable({
    "oLanguage": {
      "sSearch": "Filter Data"
    },
    "iDisplayLength": -1,
    "sPaginationType": "full_numbers",

  });




  $("#datepicker_from").datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: false,
    "onSelect": function(date) {
      minDateFilter = new Date(date).getTime();
      oTable.fnDraw();
    }
  }).keyup(function() {
    minDateFilter = new Date(this.value).getTime();
    oTable.fnDraw();
  });

  

});

// Date range filter
minDateFilter = "";
maxDateFilter = "";

$.fn.dataTableExt.afnFiltering.push(
  function(oSettings, aData, iDataIndex) {
    if (typeof aData._date == 'undefined') {
      aData._date = new Date(aData[0]).getTime();
    }

    if (minDateFilter && !isNaN(minDateFilter)) {
      if (aData._date < minDateFilter) {
        return false;
      }
    }

    

    return true;
  }
);
</script>

@endsection