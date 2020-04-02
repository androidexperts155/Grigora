@extends('layouts.master')
@section('content') 
<link rel="stylesheet" href="{{url('dist/css/wickedpicker.min.css')}}">
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Meal Notifications
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
                    <form id="model-form" role="form" action="{{route('send_mealnotifications')}}" enctype="multipart/form-data" method="POST">
                    <div class="box-body">
                    {{csrf_field()}}
                              <table style="width: 100%">
                              
                              <tr>
                                <td style="width: 366px;"><b>Title</b></td>
                              </tr>
                              <tr>
                                <td> 
                                  <input type="text" placeholder="Title" value="" class="form-control" name="title" required>
                                </td>
                              </tr>
                              <tr>
                                <td style="width: 366px;"><b>Message</b></td>
                              </tr>
                              <tr>
                                <td style="width: 366px;">   
                                  <textarea class="form-control" rows="5" name="message" id="comment"></textarea>
                       
                                </td> 
                              </tr>                    
                              <tr>
                                <td>
                                </br>
                                  <input type="submit" class="btn btn-info" name="submit" value="Submit">
                                </td>
                              </tr>
                              </table>


                    <div>
                            
                           </div>
                </div>

                </form>


                    
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>


@endsection
