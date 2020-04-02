@extends('layouts.master')
@section('content')
<section class="content"> 
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Voucher Card Details</h3>

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
             
                if($voucher->voucher_image == ''){
                  ?>
                <tr style="height: 50px;">
                    <td style="width: 200px;"><b>Voucher Image </b></td>
                    <td></td>
                  </tr>
                <?php }else{ ?>

                  <tr style="height: 50px;">
                    <td style="width: 200px;"><b>Voucher Image</b></td>
                    <td><img  width="200px"  height="100px" src="{{$voucher->voucher_image}}"><br/><br/></td>
                  </tr>

                <?php } ?>
                <tr style="height: 50px;">
                    <td style="width: 200px;"><b>Voucher Code</b></td>
                    <td>{{$code}}<br/><br/></td>
                </tr>
                </table>   
                </div>

              <table id="example1" class="table table-bordered table-striped" >
                <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>User </th>
                   <th>Voucher Amount </th>
                  <th>Reedemed Status</th>
                 
                </tr>
                </thead>
                <tbody>
                  <?php if($redemedcard != ""){?>
                <tr>
                  <td>  
                     1
                  </td>
                   <td >{{$user->name}}</td>
                   <td >{{$redemedcard->amount}}</td>
                   <?php if($redemedcard->redemed == '1'){
                     $redemedcardstatus = 'Yes';
                   } ?>
                  <td >Yes</td>
                  
                </tr>
              <?php }else{ ?>
             
                <tr><td colspan="3">No Records!!!</td></tr>
              <?php }?>
               
                </tbody>
             </table>
           


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