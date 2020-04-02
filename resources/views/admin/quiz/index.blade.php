@extends('layouts.master')
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header" >
                    <h3 class="box-title" style="width: 100%">Quiz Question
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
                  <th>Question</th>
                  <th>Answer</th>
                  <th>Image</th>
                  <th>Offer Expiry</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
              
              <?php $count = 0; ?>
                
                @forelse($quiz as $key => $quizz)

                <?php 

                $count++; ?>

                <tr>
                  <td>  
                     {{$count}}
                  </td>
                  
                  <td>{{$quizz->question}}</td>
                  <td>{{$quizz->answer}}</td>
                  <?php 
                  if($quizz->image == ""){
                    ?>
                   <td ></td>
                 <?php  }else{ ?>

                    <td ><img src="{{$quizz->image}}" width="100px" height="100px"></td>

                 <?php  } ?>
                
                 
                 
                  <td>{{$quizz->offer_expiry}} </td>
                
                  <td>

                    <a style="float: left;margin-right: 10px;" title="View" class="btn btn-primary" href="{{url('quiz/view/'.@$quizz->id)}}"><span class="glyphicon glyphicon-eye-open"></span></a>

                    <a style="float: left;margin-right: 10px;" title="Edit" class="btn btn-primary" href="{{url('quiz/edit/'.@$quizz->id)}}"><span class="glyphicon glyphicon-edit"></span></a>

                    <a style="float: left;margin-right: 10px;" title="Delete" class="btn btn-primary" onclick="return confirm('Are you sure you want to delete this restaurant?')" href="{{url('quiz/delete/'.@$quizz->id)}}"><span class="glyphicon glyphicon-trash"></span></a>

                

                  </td>
                </tr>
                 @empty
                <tr><td colspan="4">No Records!!!</td></tr>
                @endforelse
               
                </tbody>
               <!--  <tfoot>
                <tr>
                <th>Sr. No.</th>
                  <th>Question</th>
                  <th>Answer</th>
                  <th>Image</th>
                  <th>Offer Expiry</th>
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