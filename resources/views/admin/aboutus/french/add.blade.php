@extends('layouts.master')

@section('content')
 <!-- Content Header (Page header) -->

 <script src="https://cdn.ckeditor.com/4.11.1/standard/ckeditor.js"></script>
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">About Us</h1>           
          </div><!-- /.col -->
        </div><!-- /.row -->      
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
 @if ($errors->any())

  <div class="alert alert-danger" role="alert">
      <ul>
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
@endif
<div class="flash-message">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
      @if(Session::has('alert-' . $msg))

      <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p> 
      @endif
    @endforeach
  </div>

<div class="container-fluid">
    <form method="POST" action="{{route('add_about_us')}}" enctype="multipart/form-data" >
      {{csrf_field()}}

          <input type="hidden" name="language" id="language" value="2">
                         
          <div class="form-group">
              <br>
             <label for="question">Title:</label>
             <input type="text" name="title" class="form-control" value="À propos de nous" readonly>           
          </div>  
          <div class="form-group">
           <label for="answer">Description:</label>
            <textarea name="description_about_us">{{$data->description}}</textarea>      
          </div>      
          <button type="submit" class="btn btn-primary">Submit</button>
      </form>
  </div>

</br>
</br>
</br>

@endsection('content')
@section('page_scripts')
<!-- <script src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script> -->
<script>
            CKEDITOR.replace( 'description_about_us' );
        </script>
        <script type="text/javascript">
          $('#english').click(function () {
              $('#language').val('1');
          })
          $('#french').click(function () {
              $('#language').val('2');
          })

        </script>

@endsection
