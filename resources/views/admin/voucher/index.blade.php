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
                  <th>Image</th>
                  <th>Action (Generate Code)</th>
                  <th>Voucher Code</th>
                </tr>
                </thead>
                <tbody>
              
              <?php $count = 0; ?>
                
                @forelse($voucher as $key => $quizz)

                <?php 

                $count++; ?>

                <tr>
                  <td>  
                     {{$count}}
                  </td>
                  <?php 
                  if($quizz->voucher_image == ""){
                    ?>
                   <td ></td>
                 <?php  }else{ ?>

                    <td ><img src="{{$quizz->voucher_image}}" width="200px" height="100px"></td>

                 <?php  } ?>
                
                 
                 
                
                  <td>
                    <input type="text" style="float: left;" id="no_of_vouchers" class="form-control" name="no_of_vouchers" placeholder="Enter No of voucher codes to generate">

                    <a style="float: left; margin-left: 10px;" title="Generate Code" data-id="{{$quizz->id}}" class="btn btn-primary generate_code" id="generate_code"><i class="fa fa-refresh generate_code" aria-hidden="true"></i></a>

                
                  </td>
                  <td>

                    <a style="float: right;margin-right: 10px;" title="Generate Code" data-id="{{$quizz->id}}" href="{{url('voucher_code/view/'.@$quizz->id)}}" class="btn btn-primary " > View Codes</a>

                
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
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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

  $(document).ready(function(){


       $('.generate_code').on('click',function(){
          var id = $(this).data('id');
          var number = $(this).parent().find('#no_of_vouchers').val();
      
              if(number == ""){
            confirm("Please fill Number of vouchers you want to generate!");
            return false;
          }
            
          

            swal({
                title: "Generate Voucher Code",               
                buttons: true,              
              }).then((value) => {
              if(value == true){
                $.ajax({
                  url : "{{ route('generate_code') }}",
                  type: 'POST',
                  data :{
                    "id":id,
                    'number':number,
                     " _token":'{{csrf_token()}}'
                    },
                }).done(function(response){ 
                      console.log(response);
                      if(response.status == 1){
                        swal("Success!", "Code Generated successfully!", "success").then((value) => {
                            location.reload(true);
                          });
                      }
                      
                });

                console.log('jdwd');
              }
            });
        });
     });

</script>

@endsection