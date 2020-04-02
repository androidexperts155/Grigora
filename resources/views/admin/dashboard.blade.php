@extends('layouts.master')

@section('content')
<section class="content">
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
    <?php 
      $permissions = Session::all();
    //echo'<pre>';print_r($permissions);die;
     ?>
     
      <div class="col-lg-3 col-xs-4">
        <div class="small-box bg-aqua">
          <div class="inner">
            <b>{{$restaurants}}</b>
            <p>Restaurants</p>
          </div>
          <div class="icon">
            <i class="ion ion-ios-people-outline"></i>
          </div>
          <a href="restaurant/list" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>

        <div class="col-lg-3 col-xs-4">
          <div class="small-box bg-aqua">
            <div class="inner">
              <b>{{$drivers}}</b>
              <p>Drivers</p>
            </div>
            <div class="icon">
              <i class="ion ion-ios-people-outline"></i>
            </div>
            <a href="driver/list" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-xs-4">
          <div class="small-box bg-aqua">
            <div class="inner">
              <b>{{$customers}}</b>
              <p>Customers</p>
            </div>
            <div class="icon">
              <i class="ion ion-ios-people-outline"></i>
            </div>
            <a href="customer/list" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        @php
        if(Auth::user()->role == 1){
        @endphp
        <div class="col-lg-3 col-xs-4">
           <div class="small-box bg-aqua">
              <div class="inner">
                <b>{{$ongoingOrders}}</b>
                <p>OnGoing Orders</p>
              </div>
              <div class="icon">
                  <i class="ion ion-ios-people-outline"></i>
              </div>
            <a href="orders/list" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-4">
          <div class="small-box bg-aqua">
              <div class="inner">
                <b>{{$deliveredOrders}}</b>
                <p>Past Orders</p>
              </div>
              <div class="icon">
                <i class="ion ion-ios-people-outline"></i>
              </div>
              <a href="orders/list" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-4">
          <div class="small-box bg-aqua">
              <div class="inner">
                <b>{{$cuisineRequest}}</b>
                <p>Cuisine Approve Request</p>
              </div>
              <div class="icon">
                <i class="ion ion-ios-people-outline"></i>
              </div>
              <a href="cuisine/list" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        @php
        }
        @endphp
        
        
</section>
<!-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div> -->
@endsection
