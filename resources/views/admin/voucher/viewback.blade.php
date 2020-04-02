@extends('layouts.master')
@section('content')

   <style>
      .DepositApp{
      background: url("http://3.13.78.53/GriGora/public/GiftVoucher/images/bg.png");
      background-size: cover;
      width: 100%;
      margin: auto;
      /* max-width: 800px; */
      }
      .logo img {
      width: 100%;
      max-width: 120px;
      }
      .logo h3 {
      font-weight: 600;
      font-size: 20px;
      }
      
      .links {
      list-style: none;
      font-size: 18px;
      margin-bottom: 10px;
      }
      .main-box {
      margin-top: 30px;
      }
      ul.links {
      padding: 0;
      }
      h1.heading {
      text-align: left;
      font-weight: 600;
      margin-bottom: 40px;
      margin-top: 50px;
      }
      p.content {
      font-size: 18px;
      line-height: 30px;
      width: 95%;
      }
      .code-fl input {
      width: 100%;
      max-width: 500px;
      height: 95px;
      border-radius: 25px;
      border: 1px solid #a5a5a5;
      float: right;
      margin-top: 25px;
      font-size: 30px;
      margin-right: 55px;
      }
      .barcode img {
      width: 100%;
      max-width: 500px;
      margin-bottom: 20px;
      }
      .barcode {
      margin-top: 40px;
      text-align: center;
      }
   </style>
<section class="content"> 


    <div class="row">


      <div class="box-body"  >

        <div style="margin: 30px;">
          
          <a style="padding: 14px;
    border: 1px solid;
    background: #777;
    color: #fff;" href='generatepdf/{{$code}}'>Generate pdf</a>
        </div>
       <div class="col-lg-12 col-md-12">
                 <div class="DepositApp">
                    <div class="container">
                 <div class="row main-box">
                    <div class="col-md-4 col-sm-4 col-xs-12">
                       <div class="logo">
                          <img src="http://3.13.78.53/GriGora/public/GiftVoucher/images/logo.png">
                          <h3>Steps to gift Voucher Card</h3>
                          <ul class="links">
                             <li>1. Open Menu</li>
                             <li>2. Click on Grigora Card</li>
                             <li>3. Scroll down to buy cards</li>
                             <li>4. Gift card as your wish to your friends and family.</li>
                          </ul>
                       </div>
                    </div>
                    <div class="col-md-8 col-sm-8 col-xs-12">
                       <h1 class="heading">Terms and Conditions</h1>
                       <p class="content">It is a long established fact that a reader will be distracted by the 
                          readable content of a page when looking at its layout. The point 
                          of using Lorem Ipsum is that it has a more-or-less normal 
                          distribution of letters, as opposed to using 'Content here, 
                          content here', making it look like readable English. Many 
                          desktop publishing packages and web page editors now use 
                          Lorem Ipsum as their default model text.
                       </p>
                       <div class="code-fl"><input type="text" id="fname" name="fname" value="{{$code}}" readonly></div>
                    </div> 
                 </div>
                 <div class="barcode">
                    <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($code, 'C128')}}" alt="barcode" />
                 </div>
              </div>
</div>

        </div>
<!-- /.row -->
</section>


@endsection
@section('page_scripts')
<script>
    var token = "{{ csrf_token() }}";
</script>
<script>
function goBack() {
  window.history.back();
}
</script>
<script>
    $(function () {
        $('#example1').DataTable();
        $('#example2').DataTable({
            'paging': true,
            'lengthChange': true,
            'searching': true,
            'ordering': false,
            'info': true,
            'autoWidth': false
        });
        
        $(".lp-change-password-btn").on("click", function () {
            var id = $(this).attr("data-id");
            var url = "{{url('admin/change-user-password')}}" + "/" + id;
            $("#change-password-modal form").attr('action', url);
            $("#change-password-modal").modal('show');
        });
    </script>
    @endsection