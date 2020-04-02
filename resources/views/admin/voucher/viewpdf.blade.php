<html>
<head>

</head>
<body style="background-image: url(http://3.13.78.53/GriGora/public/GiftVoucher/images/bg.png); background-size:cover;background-position: center center;font-family: 'Open Sans', sans-serif;width: 100%;height: 500px;">
    <div>
<div class="main-gerigora" style="display: flex; flex-direction: row;width: 750px;margin: auto; padding: 80px 40px 30px 20px;margin-top: 30px;">
<div class="gerigora-left" style="width: 200px; display: inline-block;text-align: left; float: left;margin-right: 20px;">
<table>
<tr>
    <th style="text-align: left;padding-bottom: 30px;margin-top: 30px;"><img src="http://3.13.78.53/GriGora/public/GiftVoucher/images/logo.png" style="width: 100px;>
   margin-right: 40px;"></th>
  </tr>
  <tr>
    <th style="font-size: 18px;padding-bottom: 16px;color: #3c3939; text-align: left;">Steps to gift voucher card</th>
  </tr>
  <tr>
    <td style="font-size: 10px;padding-bottom: 6px;color: #2b2727;">1) Open Menu</td>
  </tr>
  <tr>
    <td style="font-size: 10px;padding-bottom: 6px;color: #2b2727;">2) Click on Grigora card</td>
  </tr>
  <tr>
    <td style="font-size: 10px;padding-bottom: 6px;color: #2b2727;">3) Scroll down to buy cards</td>
  </tr>
  <tr>
   <td style="font-size: 10px;padding-bottom: 6px;color: #2b2727;">4) Gift card as your wish to your friends and family</td>
  </tr>
</table>
</div>
<div class="gerigora-right" style="width: 450px; text-align: left; display: inline-block; padding-top: 75px; float: right; margin-right: 90px;">
<table>
  <tr>
    <th style="font-size: 24px; text-align: left; color: #3c3939;padding-bottom: 20px;">Terms and Conditions</th>
  </tr>
  <tr>
    <td style="font-size: 10px; padding-bottom: 30px;color: #2b2727;line-height: 20px;">Lorem Ipsum is simply dummy text of the printing and 
  typesetting industry. Lorem Ipsum has been the industry's 
  standard dummy text ever since the 1500s, when an unknown 
  printer took a galley of type and scrambled it to make a 
  type specimen book. It has survived not only five 
  centuries, but also the leap into electronic typesetting, 
  remaining essentially unchanged.</td>
  </tr>
  <tr>
    <td><input type="text" id="fname" name="fname" value="{{$CODE}}" style="width: 280px;height: 40px;line-height: 35px;margin-right: 100px;background-color: #fff;display: inline-block;border: 1px solid #7b7b7b;border-radius: 15px;float: right; font-size: 20px;padding:0px 10px;"></td>
  </tr>
  <tr>
    <td> <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($CODE, 'C128')}}" alt="barcode"  style="width: 120px;padding: 64px 0px 0px;display: inline-block;" /></td>
  </tr>
</table>
</div>
</div>
</div>
</body>