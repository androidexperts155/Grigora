@extends('layouts.master')
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header" >
                    <h3 class="box-title" style="width: 100%">Location Types
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
                  <th>Sr. No.</th>
                  <th>Name</th>
                  <th>Description</th>
                  <th>Image</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php $count = 0; ?>
                
                @forelse($locations as $key => $user)
                <?php $count++; ?>
                
                <tr>
                  <td>  
                            {{@$count}}
                  </td>
                  <td>{{$user['name']}}</td>
                  <td>{{$user['description']}}</td>
                  <td><a href="{{$user['image']}}" target="__blank"><img src="{{$user['image']}}" style="width: 100px;height: 100px;"></a></td>

                  <td>

                    <!-- <a style="float: left;margin-right: 10px;" title="View" class="btn btn-primary" href="view/{{$user['id']}}"><span class="glyphicon glyphicon-eye-open"></span></a> -->

                    <a style="float: left;margin-right: 10px;" title="Edit" class="btn btn-primary" href="edit/{{$user['id']}}"><span class="glyphicon glyphicon-edit"></span></a>

                    <a style="float: left;margin-right: 10px;" title="Delete" class="btn btn-primary" onclick="return confirm('Are you sure you want to delete this Location Type?')" href="delete/{{$user['id']}}"><span class="glyphicon glyphicon-trash"></span></a>
                  

                  </td>
                </tr>
                @empty
                <tr><td colspan="4">No Records!!!</td></tr>
                @endforelse
                </tbody>
                <!-- <tfoot>
                <tr>
                  <th>Sr. No.</th>
                  <th>Name</th>
                  <th>Description</th>
                  <th>Image</th>
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
    $('#example1').DataTable()
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

@endsection