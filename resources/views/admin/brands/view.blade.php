@extends('layouts.master')
@section('content')
<section class="content"> 
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Brands Details</h3>

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
                    <td><a href="{{$users->image}}" target="__blank"><img src="{{$users->image}}" style="height: 94px;width: 100px;"></a></td>
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