@extends('layouts.master')
@section('content')

<link rel="stylesheet" href="{{url('dist/css/wickedpicker.min.css')}}">
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Edit Restaurant
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
                    <form id="model-form" role="form" action="{{url('restaurant/update')}}" enctype="multipart/form-data" method="POST">
                    <div class="box-body">
                    {{csrf_field()}}
                              <table style="width: 100%">
                              <tr><td><input type="hidden" name="id" value="{{$users->id}}"></td></tr>
                              <tr>
                                <td style="width: 366px;"><b>Name</b></td>
                              </tr>
                              <tr>
                              <td> <input type="text" placeholder="Name" value="{{ $users->name }}" class="form-control" name="name" required readonly></td>
                               <td>
                                <label style="margin-left: 206px;">Image</label>
                                  <div class="form-group" style="margin-left: 206px;">
                                            
                                            <input type="file" name="image" id="image">

                                            <p class="help-block">Add profile Image.</p>
                                          </div>
                                </td> 
                              </tr>
                              <tr>
                                <td style="width: 366px;"><b>Id Proof</b></td>
                              </tr>
                              <tr>
                                <td style="width: 366px;">
                                
                                  <div class="form-group">
                                            
                                            <input type="file" name="id_proof" id="id_proof">

                                            <p class="help-block">Add Id Proof Image.</p>
                                          </div>
                                </td> 
                              </tr>


                               <tr>
                                <td style="width: 366px;"><b>Franchise Proof</b></td>
                              </tr>
                              <tr>
                                <td style="width: 366px;">
                                
                                  <div class="form-group">
                                            
                                            <input type="file" name="franchisee_proof" id="franchisee_proof">

                                            <p class="help-block">Add Franchisee Proof Image.</p>
                                          </div>
                                </td> 
                              </tr>

                               <tr><br/>
                                <td style="width: 366px;"><br/><b>Address</b><br/></td>                                
                              </tr>
                              <tr>
                              <td> 
                                  <input type="text" name="address" id="address" class="form-control">
                                  <input type="hidden" class="form-control" id="lat"  name="lat">

                                  <input type="hidden" class="form-control" id="lng"  name="long">
                              </td>

                              </tr>



                              <tr>
                                <td><br/><b>Email</b></td>
                              </tr>
                              <tr>
                                
                                 <td> <input type="text" placeholder="Email" value="{{$users->email}}" class="form-control" name="email" required readonly></td>
                              </tr>
                             
                              <tr>
                                <td><br/><b>Phone</b></td>
                              </tr>
                              <tr>
                                
                                 <td> <input type="text" placeholder="Phone Number" value="{{$users->phone}}" class="form-control" name="phone" onkeypress="javascript:return isNumber(event)" required></td>
                              </tr>

                               <tr><td><br/></td></tr>
                              <tr>
                             <br/>
                              <td><label><input type="checkbox" name="pure_veg" value="1"> Pure Veg</label></td>
                              </tr>  

                               <tr><td><br/></td></tr>
                              <tr>
                             <br/>
                              <td><label><input type="checkbox" name="pickup" value="1"> Pickup Service</label></td>
                              </tr> 

                              <tr><td><br/></td></tr>
                              <tr>
                             <br/>
                              <td><label><input type="checkbox" name="full_time" value="1"> 24 Hour Service</label></td>
                              </tr> 

                              <tr><td><br/></td></tr>
                              <tr>
                              <br/>
                               <td><label><input type="checkbox" name="full_time" value="1"> 24 Hour Service</label></td>
                              </tr>

                                <tr>
                                <td style="width: 366px;"><b>Opening Time</b></td>
                                <td style="width: 366px;"><b>Closing Time</b></td>
                              </tr>
                              <tr>
                              <td> 
                                  <input type="text" id="timepicker" name="opening_time" class="timepicker_in">
                              </td>

                              <td> 
                                  <input type="text" id="timepicker" name="closing_time" class="timepicker_out">
                              </td>

                              </tr>

                              <tr><br/>
                                <td style="width: 366px;"><br/><b>Approx Preperation Time</b><br/></td>                                
                              </tr>
                              <tr>
                              <td> 
                                  <input type="text" placeholder="Enter Time" value="" class="form-control" name="preparing_time" required>
                              </td>

                              </tr>


                              <tr>
                                <td>
                                </br>
                                  <input type="submit" class="btn btn-info" name="submit" value="Update">
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
@section('page_scripts')
<script>
    var token = "{{ csrf_token() }}";
</script>

<script type="text/javascript" src="{{url('dist/js/wickedpicker.min.js')}}"></script>
<script type="text/javascript">
function isNumber(evt) {
        var iKeyCode = (evt.which) ? evt.which : evt.keyCode
        if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57))
            return false;

        return true;
    }
  $('#model-form').validate({
            rules: {
                name: {
                    required: true,
                    //minlength: 5
                },
                cat_image: {
                    required: true,
                }
            },
            messages: {
                name: 'Name field is required',
                //email: 'Email field is required',
                //password: 'Password field is required',
                //confirm_password: 'Confirm password field is required'
            },
            errorElement: 'span',
            errorClass: 'error text-danger',
            highlight: function (element, errorClass, validClass) {
                $(element).parents("div.form-group").addClass("has-error");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents("div.form-group").removeClass("has-error");
            },
        });
</script>

<script type="text/javascript">
      $('.timepicker_in').wickedpicker();
      $('.timepicker_out').wickedpicker();
 </script>

 <script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places&language=en-AU&key=AIzaSyC29SOMdVFnuCgpVR9VFbeARVJqDJ7tJ-w&callback=initialise"></script>

 <script>
            var autocomplete = new google.maps.places.Autocomplete($("#address")[0], {});

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                var place = autocomplete.getPlace();
                console.log(place.address_components);
                var latitude = place.geometry.location.lat();
                var longitude = place.geometry.location.lng(); 
                $("#lat").val(latitude);
                $("#lng").val(longitude);
                initialise();

            });
        </script>
@endsection