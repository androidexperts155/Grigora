@extends('layouts.master')

@section('content')

<style>
.fa-2x {
  font-size: 1.5em;
}

.app {
  position: relative;
  overflow: hidden;
  top: 19px;
  height: calc(100% - 38px);
  margin: auto;
  padding: 0;
  box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .06), 0 2px 5px 0 rgba(0, 0, 0, .2);
}

.app-one {
  background-color: #f7f7f7;
  height: 100%;
  overflow: hidden;
  margin: 0;
  padding: 0;
  box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .06), 0 2px 5px 0 rgba(0, 0, 0, .2);
}

.side {
  padding: 0;
  margin: 0;
  height: 100%;
}
.side-one {
  padding: 0;
  margin: 0;
  height: 100%;
  width: 100%;
  z-index: 1;
  position: relative;
  display: block;
  top: 0;
}

.side-two {
  padding: 0;
  margin: 0;
  height: 100%;
  width: 100%;
  z-index: 2;
  position: relative;
  top: -100%;
  left: -100%;
  -webkit-transition: left 0.3s ease;
  transition: left 0.3s ease;

}


.heading {
  padding: 10px 16px 10px 15px;
  margin: 0;
  height: 60px;
  width: 100%;
  background-color: #eee;
  z-index: 1000;
}

.heading-avatar {
  padding: 0;
  cursor: pointer;

}

.heading-avatar-icon img {
  border-radius: 50%;
  height: 40px;
  width: 40px;
}

.heading-name {
  padding: 0 !important;
  cursor: pointer;
}

.heading-name-meta {
  font-weight: 700;
  font-size: 100%;
  padding: 5px;
  padding-bottom: 0;
  text-align: left;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #000;
  display: block;
}
.heading-online {
  display: none;
  padding: 0 5px;
  font-size: 12px;
  color: #93918f;
}
.heading-compose {
  padding: 0;
}

.heading-compose i {
  text-align: center;
  padding: 5px;
  color: #93918f;
  cursor: pointer;
}

.heading-dot {
  padding: 0;
  margin-left: 10px;
}

.heading-dot i {
  text-align: right;
  padding: 5px;
  color: #93918f;
  cursor: pointer;
}

.searchBox {
  padding: 0 !important;
  margin: 0 !important;
  height: 60px;
  width: 100%;
}

.searchBox-inner {
  height: 100%;
  width: 100%;
  padding: 10px !important;
  background-color: #fbfbfb;
}


/*#searchBox-inner input {
  box-shadow: none;
}*/


.sideBar {
  padding: 0 !important;
  margin: 0 !important;
  background-color: #fff;
  overflow-y: auto;
  border: 1px solid #f7f7f7;
  height: calc(100% - 120px);
}

.sideBar-body {
  position: relative;
  padding: 10px !important;
  border-bottom: 1px solid #f7f7f7;
  height: 72px;
  margin: 0 !important;
  cursor: pointer;
}

.sideBar-body:hover {
  background-color: #f2f2f2;
}

.sideBar-avatar {
  text-align: center;
  padding: 0 !important;
}

.avatar-icon img {
  border-radius: 50%;
  height: 49px;
  width: 49px;
}

.sideBar-main {
  padding: 0 !important;
}

.sideBar-main .row {
  padding: 0 !important;
  margin: 0 !important;
}

.sideBar-name {
  padding: 10px !important;
}

.name-meta {
  font-size: 100%;
  padding: 1% !important;
  text-align: left;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #000;
}

.sideBar-time {
  padding: 10px !important;
}

.time-meta {
  text-align: right;
  font-size: 12px;
  padding: 1% !important;
  color: rgba(0, 0, 0, .4);
  vertical-align: baseline;
}




}
      </style> 
<!--- *************Main section ************* -->

<!------ Include the above in your HEAD tag ---------->
    <div class="col-sm-12 ">
      <div class="side-one">
      
      
         <div class="col-sm-2 col-xs-2 sideBar-name">
               <span class="time-meta ">USER IMAGE
                </span>
            </div>
            <div class="col-sm-10 col-xs-10 sideBar-main">
              <div class="row">
                <div class="col-sm-4 col-xs-4 sideBar-name">
                  <span class="time-meta ">TICKET ID
                </span>
                </div>
                <div class="col-sm-5 col-xs-5 sideBar-name" >
                  <span class="time-meta ">ISSUE
                </span>
                </div>
                <div class="col-sm-3 col-xs-3 sideBar-name">
                  <span class="time-meta ">TIME
                </span>
                </div>
              </div>
            </div>
        <div class="row sideBar">
         
          
          @foreach($data as $key => $chat_user)
         <a href="{{url('chat/'.@$chat_user['user_id'].'/'.@$chat_user['ticket_id'])}}" > <div class="row sideBar-body">
            <div class="col-sm-2 col-xs-2 sideBar-avatar" style="text-align: left;">
              <div class="avatar-icon">
                <img src="{{$chat_user['user_image']}}">
                 <span style="margin-left:10px;" class="name-meta">{{$chat_user['user_name']}}
                </span>
              </div>
            </div>
            <div class="col-sm-10 col-xs-10 sideBar-main">
              <div class="row">
                <div class="col-sm-4 col-xs-4 sideBar-name">
                 
                <span class="name-meta">{{$chat_user['ticket_id']}}
                </span>
                </div>
                <div class="col-sm-5 col-xs-5 sideBar-name">
                  <span class="name-meta">{{$chat_user['issue']}}
                </span>
                </div>
               <div class="col-sm-3 col-xs-3 sideBar-name">
                  <span class="name-meta">{{$chat_user['time']}}
                </span>
                </div>
              </div>
            
            </div>
          </div>
          </a>
          @endforeach
       <!--    <div class="row sideBar-body">
            <div class="col-sm-3 col-xs-3 sideBar-avatar">
              <div class="avatar-icon">
                <img src="https://bootdey.com/img/Content/avatar/avatar2.png">
              </div>
            </div>
            <div class="col-sm-9 col-xs-9 sideBar-main">
              <div class="row">
                <div class="col-sm-8 col-xs-8 sideBar-name">
                  <span class="name-meta">John Doe
                </span>
                </div>
                <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                  <span class="time-meta pull-right">18:18
                </span>
                </div>
              </div>
            </div>
          </div>
          <div class="row sideBar-body">
            <div class="col-sm-3 col-xs-3 sideBar-avatar">
              <div class="avatar-icon">
                <img src="https://bootdey.com/img/Content/avatar/avatar3.png">
              </div>
            </div>
            <div class="col-sm-9 col-xs-9 sideBar-main">
              <div class="row">
                <div class="col-sm-8 col-xs-8 sideBar-name">
                  <span class="name-meta">John Doe
                </span>
                </div>
                <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                  <span class="time-meta pull-right">18:18
                </span>
                </div>
              </div>
            </div>
          </div> -->
     
        
        
        
     
          
      
     
        </div>
 </div>
 </div>


@endsection('content')

