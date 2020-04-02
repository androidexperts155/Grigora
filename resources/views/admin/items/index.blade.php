@extends('layouts.master')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
<style type="text/css">
 span.star i {
    font-size: 15px;
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
                <div class="box-header" >
                    <h3 class="box-title" style="width: 100%">Items
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
                  <th>Restaurant</th>
                  <th>Image</th>
                  <th>Approved</th> 
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php $count = 0; ?>
                
                @forelse($users as $key => $user)
                <?php $count++; ?>
                
                <tr>
                  <td>  
                            {{@$count}}
                  </td>
                  <td>{{$user['name']}}</td>
                  <td><a href="{{url('restaurant/view/'.@$user['restaurant_id'])}}">{{$user['restaurant']}}</a></td>
                  <td><a href="{{$user['image']}}" target="_blank"><img src="{{$user['image']}}" style="height: 100px;width: 100px;"></a></td>
              
             
                  @php 
                  if($user['approved'] == '0'){
                    $approved = "Unapproved";
                  }
                  if($user['approved'] == '1'){
                    $approved = "Approved";
                  }
                  @endphp

                  <td>{{$approved}}</td>
                  <td>
                    @php 
                    if($user['approved'] == '0'){
                    @endphp
                    <a style="float: left;margin-right: 10px;" title="Approve" class="btn btn-primary" onclick="return confirm('Are you sure you want to approve this item?')" href="approve/{{$user['id']}}"><span class="glyphicon glyphicon-ok"></span></a>
                    @php
                    }
                    @endphp

                  </td>
                </tr>
                @empty
                <tr><td colspan="4">No Records!!!</td></tr>
                @endforelse
                </tbody>
          
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>
<script> 
    $(document).ready(function() {
      $(".input-id").rating({disabled:true});
     var value =  $(".input-id").rating().val();

     $(".input-id").parent().each(function () {
         if($(this).find('.input-id').val() < '3'){
            console.log($(this).find('span.filled-stars').css('color','red'));
         }else if($(this).find('.input-id').val() >= '3' && $(this).find('.input-id').val() <= '4'){
           $(this).find('span.filled-stars').css('color','yellow');
         }else if($(this).find('.input-id').val() > '4'){
           $(this).find('span.filled-stars').css('color','green');
         }   
     });
 
     
    });
</script>
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