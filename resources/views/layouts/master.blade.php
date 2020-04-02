<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            {{ isset($data['page_title']) && $data['page_title'] != '' ? $data['page_title'] : 'Grigora' }}
        </title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">


       



        <link rel="stylesheet" href="{{url('bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{url('bower_components/font-awesome/css/font-awesome.min.css')}}">
        <link rel="stylesheet" href="{{url('bower_components/Ionicons/css/ionicons.min.css')}}">
        <link rel="stylesheet" href="{{url('bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
        <link rel="stylesheet" href="{{url('bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">

        <link rel="stylesheet" href="{{url('plugins/timepicker/bootstrap-timepicker.min.css')}}">
        

        <link rel="stylesheet" href="{{url('bower_components/jvectormap/jquery-jvectormap.css')}}">
        <link rel="stylesheet" href="{{url('dist/css/AdminLTE.min.css')}}">
        <link rel="stylesheet" href="{{url('dist/css/skins/_all-skins.min.css')}}">
        <link rel="stylesheet" href="{{url('bower_components/morris.js/morris.css')}}">
        <link rel="stylesheet" href="{{url('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}">
        <link rel="stylesheet" href="{{url('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
        <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic"> -->
        <link rel="stylesheet" href="{{url('css/bootstrap-confirm-delete.css')}}">
        <!-- <link rel="stylesheet" href="{{url('css/jquery.timepicker.min.css')}}"> -->
        <link rel="stylesheet" href="{{url('css/bootstrap-multiselect.css')}}">
        <link rel="stylesheet" href="{{url('css/admin_custom.css')}}">
        <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="{{url('css/jquery.multiselect.css')}}">
        <link rel="stylesheet" href="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/css/dataTables.checkboxes.css">
        



    </head>
    <body class="hold-transition skin-blue-light sidebar-mini">
        <div class="wrapper"> 

            <header class="main-header">
                <a href="{{asset('dashboard')}}" class="logo">
                    <span class="logo-mini"><b>Grigora</b></span>
                    <span class="logo-lg"><b>Grigora</b></span>
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="javascript:void(0)" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="dropdown user user-menu">
                                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <img src="{{asset(Auth::user()->image == NULL && Auth::user()->image == '' ? asset('images/default_profile.png') : asset(Auth::user()->image) )}}" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{ ucfirst(Auth::user()->name) }} - Grigora</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">
                                        <img src="{{asset(Auth::user()->image == NULL && Auth::user()->image == '' ? asset('images/default_profile.png') : asset(Auth::user()->image) )}}" class="img-circle" alt="User Image">
                                       <p>
                                            <a href="{{ url('dashboard/edit-profile') }}" style="color:#dbe7f3"> {{ ucfirst(Auth::user()->name) }} - Grigora</a>
                                            <small>{{Auth::user()->created_at}}</small>
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <!-- <div class="pull-left">
                                            <a href="{{ url('dashboard/edit-profile') }}" class="btn btn-default btn-flat">Profile</a>
                                        </div> -->
                                        <!-- <div class="pull-right"> -->
                                            <div style="    text-align: center;">
                                            <a href="{{ url('dashboard/logout') }}" class="btn btn-default btn-flat">Logout</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="{{asset(Auth::user()->profile_pic == NULL && Auth::user()->profile_pic == '' ? asset('images/default_profile.png') : asset(Auth::user()->profile_pic) )}}" class="img-circle" alt="User Image">
                        </div>
                        <div class="pull-left info">
                            <p>{{ucfirst(Auth::user()->name)}}</p>
                            <a href="javascript:void(0)"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    
                    <ul class="sidebar-menu" data-widget="tree">
                        <li class="header">MAIN NAVIGATION</li>
                        <?php //if(in_array("Dashboard", $permissions['modules'])){ ?>
                        <li><a href="{{ url('dashboard') }}">
                                <i class="fa fa-dashboard"></i> 
                                <span> Dashboard </span>
                            </a>
                        </li>
                        <?php //} ?>
                        @php
                        if(Auth::user()->role == 1){
                        @endphp 
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Sub Admins</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('subadmin/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('subadmin/add') }}"><i class="fa fa-circle-o"></i>Add</a></li>
                            </ul>
                        </li>
                        @php
                        }
                        @endphp

                           <li><a href="{{ url('company_offline') }}">
                                <i class="fa fa-dashboard"></i> 
                                <span> Company Offline </span>
                            </a>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Restaurants</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('restaurant/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('restaurant/add') }}"><i class="fa fa-circle-o"></i>Add</a></li> 
                            </ul>
                        </li>
                        
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Drivers</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('driver/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <!-- <li><a href="{{ url('driver/add') }}"><i class="fa fa-circle-o"></i>Add</a></li> -->
                            </ul>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Customers</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('customer/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <!-- <li><a href="{{ url('customer/add') }}"><i class="fa fa-circle-o"></i>Add</a></li> -->
                            </ul>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Cuisine</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('cuisine/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('cuisine/add') }}"><i class="fa fa-circle-o"></i>Add</a></li>
                            </ul>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Our Brands</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('brand/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('brand/add') }}"><i class="fa fa-circle-o"></i>Add</a></li>
                            </ul>
                        </li>

                           <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Items</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('item/list') }}"><i class="fa fa-circle-o"></i>List</a></li>                              
                            </ul>
                        </li>

                        <!-- <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Categories</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('categories/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('categories/add') }}"><i class="fa fa-circle-o"></i>Add</a></li>
                            </ul>
                        </li> -->
                        @php
                        if(Auth::user()->role == 1){
                        @endphp 
                        <li>
                            <a href="{{ url('orders/list') }}">
                                <i class="fa fa-circle"></i>
                                <span>Orders</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('orders/earnings') }}">
                                <i class="fa fa-circle"></i>
                                <span>Daily Earnings</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-circle"></i>
                                <span>Promocodes</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('promocode/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('promocode/add') }}"><i class="fa fa-circle-o"></i>Add</a></li>
                            </ul>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-circle"></i>
                                <span>Location Types</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('location/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('location/add') }}"><i class="fa fa-circle-o"></i>Add</a></li>
                            </ul>
                        </li>

                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-users"></i>
                                <span>Notifications</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{url('promo_notifications')}}"><i class="fa fa-circle-o"></i>Promo Notification</a></li>
                                <li><a href="{{url('meal_notifications')}}"><i class="fa fa-circle-o"></i>Meal Notification</a></li>
                                <li><a href="{{url('paidpage_notifications')}}"><i class="fa fa-circle-o"></i>Paid Page Notification</a></li>
                                <li><a href="{{url('addupdate_notifications')}}"><i class="fa fa-circle-o"></i>App Update Notification</a></li>
                                
                            </ul>
                        </li>

                   

                         <li class="treeview">
                            <a href="#">
                                <i class="fa fa-gift" aria-hidden="true"></i>
                                <span>Voucher</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{url('/voucher/list')}}"><i class="fa fa-circle-o"></i>List</a></li>
                                
                            </ul>
                        </li>


                        <!-- <li>
                            <a href="{{ url('notifications') }}">
                                <i class="fa fa-circle"></i>
                                <span>Send Notification</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>

                          <li>
                            <a href="{{url('promo_notifications')}}">
                                <i class="fa fa-circle"></i>
                                <span>Promo Notification</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>

                          <li>
                            <a href="{{url('meal_notifications')}}">
                                <i class="fa fa-circle"></i>
                                <span>Meal Notification</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>

                         <li>
                            <a href="{{url('paidpage_notifications')}}">
                                <i class="fa fa-circle"></i>
                                <span>Paid Page Notification</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>

                         <li>
                            <a href="{{url('addupdate_notifications')}}">
                                <i class="fa fa-circle"></i>
                                <span>App Update Notification</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li> -->
                            <li class="treeview">
                            <a href="#">
                                <i class="fa fa-question-circle" aria-hidden="true"></i>
                                <span>Quiz</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('quiz/list') }}"><i class="fa fa-circle-o"></i>List</a></li>
                                <li><a href="{{ url('quiz/add') }}"><i class="fa fa-circle-o"></i>Add</a></li>
                                                               
                            </ul>
                        </li>
                          <li>
                            <a href="{{ url('chat_list') }}">
                                <i class="fa fa-weixin" aria-hidden="true"></i>
                                <span>Chat</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>
                      
                        <li>
                            <a href="{{ url('settings/list') }}">
                                <i class="fa fa-cog" aria-hidden="true"></i>
                                <span>Settings</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/contact-us') }}">
                               <i class="fa fa-envelope" aria-hidden="true"></i>
                                <span>Contact Us</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                        </li>
                        @php
                        }
                        @endphp
                       <!--  <li>
                            <a href="{{ url('user/primum_user') }}">
                                <i class="fa fa-user"></i>
                                <span>Send Message</span>
                            </a>
                        </li> -->
                     
                       
                   
                        
                        
                        
                        <li><a href="{{ url('dashboard/logout') }}"><i class="fa fa-circle-o text-red"></i> <span> Logout</span></a></li>
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    <h1>
                        Dashboard
                        <small>Control panel</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Dashboard</li>
                    </ol>
                </section>
                <?php 
                
                if (session('success')) { ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-check"></i> Status!</h4>
                        <?php echo session('success'); ?>
                    </div>
                <?php } ?>
                <?php if (session('error')) { ?>
                    <div class="alert alert-error alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-check"></i> Error!</h4>
                        <?php echo session('error'); ?>
                    </div>
                <?php } ?>
                
                @yield('content')
            </div>
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 2.4.0
                </div>
                <strong>Copyright &copy; 2018-2019 <a href="javascript:void(0)">Admin</a>.</strong> All rights
                reserved.
            </footer>
            <div class="control-sidebar-bg"></div>
        </div>

        

         <script src="{{url('bower_components/jquery/dist/jquery.min.js')}}"></script>
        <script src="{{url('bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
        <script src="{{url('bower_components/fastclick/lib/fastclick.js')}}"></script>
        <script src="{{url('dist/js/adminlte.min.js')}}"></script>
        <script src="{{url('bower_components/morris.js/morris.js')}}"></script>
        <script src="{{url('bower_components/jquery-sparkline/dist/jquery.sparkline.min.js')}}"></script>

        <script src="{{url('plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>        

        <script src="{{url('plugins/jvectormap/jquery-jvectormap-1.2.2.min.js')}}"></script>
        <script src="{{url('plugins/jvectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
        <script src="{{url('bower_components/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
        <script src="{{url('bower_components/chart.js/Chart.js')}}"></script>
        <script src="{{url('bower_components/jquery-ui/jquery-ui.min.js')}}"></script>
        <script>
        //$.widget.bridge('uibutton', $.ui.button);
        </script>
        <script src="{{url('bower_components/jquery-knob/dist/jquery.knob.min.js')}}"></script>
        <script src="{{url('bower_components/moment/min/moment.min.js')}}"></script>
        <script src="{{url('bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
        <script src="{{url('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
        <script src="{{url('dist/js/pages/dashboard.js')}}"></script>
        <script src="{{url('dist/js/demo.js')}}"></script>
        <script src="{{ url('bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{ url('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
        <script src="{{ url('js/bootstrap-confirm-delete.js') }}"></script>
        <!-- <script src="{{ url('js/jquery.timepicker.min.js') }}"></script> -->
        <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBKc1EuLPpAAPk43btbtZT0_nwOF4-3R_A&libraries=places" async defer></script> -->
        <script src="{{ url('js/geocomplete.js') }}"></script>
        <script src="{{ url('js/bootstrap-notify.min.js') }}"></script>
        <script src="{{ url('js/jquery.validate.min.js') }}"></script>
        <script src="{{ url('js/jquery.shorten.js') }}"></script>
        <script src="{{ url('js/bootstrap-multiselect.js') }}"></script>
        <script src="{{ url('js/admin_custom.js') }}"></script>

         
        
        @yield('page_scripts')
        @yield('program_script')
    </body>
</html>
