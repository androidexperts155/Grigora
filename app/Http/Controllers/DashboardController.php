<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Order;
use App\Cuisine;

class DashboardController extends Controller
{
	 public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $data['restaurants'] = User::where('role', '4')->count();
        $data['drivers'] = User::where('role', '3')->count();
        $data['customers'] = User::where('role', '2')->count();
        $data['ongoingOrders'] = Order::whereIn('order_status', ['0','1','2','3','4','7'])->count();
        $data['deliveredOrders'] = Order::where('order_status', '5')->count();
        $data['cuisineRequest'] = Cuisine::where('status', '0')->count();
        return view('admin.dashboard')->with($data);
    }

    public function logout() {
        Auth::logout();
        return redirect('/login');
    }
    public function profile(){
        return view('admin.profile');
    }

    public function saveprofile(Request $request) {
      // return $request;

        $user = User::where('email',$request->email)->first();

        if( $request->file('new_image')!= ""){
            if (!file_exists( public_path('/images/profile'))) {
                mkdir(public_path('/images/profile'), 0777, true);
            }
            $path =public_path('/images/profile/');
            $image = $request->file('new_image');
            $profileImage = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images/profile');
            $image->move($destinationPath, $profileImage);
            $url = url('/images/profile/');
            $url = str_replace('/index.php', '', $url);
            $profileImage = $url.'/'.$profileImage;
        }else{
            $profileImage = $user->image;  
        }

       
            if($request->get('name') == ""){
              $name = $user->name;
            }else{
              $name = $request->get('name');
            }

            if($request->get('new_password') == ""){
              $password = $user->password;
            }else{
              $password = bcrypt($request->get('new_password'));
            }

            $provider  = User::where('email',$request->email)->update([
              'name' => $name,
              'password' => $password,
              'image' => $profileImage,

            ]);
        $data = User::where('email',$request->email)->first();
        
        return redirect('dashboard/edit-profile')->with('message', 'Profile Updated Successfully.');

    }
}
