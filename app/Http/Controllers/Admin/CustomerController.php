<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\UserOrderType;

class CustomerController extends Controller
{
    public function add(){
    	return view('admin.customer.add');
    }

	public function save(Request $request){
        //echo'<pre>';print_r($request->all());die;
        $request->validate([
                            'email' => 'required|email|unique:users',
                            'password' => 'required',
                            'phone' => 'required|unique:users',
                            //'image' => 'required',
                            //'id_proof' => 'required'
                        ]);
        // if( $request->file('id_proof')!= ""){
        //     if (!file_exists( public_path('/images/id_proof'))) {
        //         mkdir(public_path('/images/id_proof'), 0777, true);
        //     }
        //     $path =public_path('/images/id_proof/');
        //     $image = $request->file('id_proof');
        //     $idProofImage = time().'.'.$image->getClientOriginalExtension();
        //     $destinationPath = public_path('/images/id_proof');
        //     $image->move($destinationPath, $idProofImage);
        //     $url = url('/images/id_proof/');
        //     $url = str_replace('/index.php', '', $url);
        //     $idProofImage = $url.'/'.$idProofImage;
        // }else{
        //     $idProofImage = "";  
        // }

        // if( $request->file('image')!= ""){
        //     if (!file_exists( public_path('/images/profile'))) {
        //         mkdir(public_path('/images/profile'), 0777, true);
        //     }
        //     $path =public_path('/images/profile/');
        //     $image = $request->file('image');
        //     $profileImage = time().'.'.$image->getClientOriginalExtension();
        //     $destinationPath = public_path('/images/profile');
        //     $image->move($destinationPath, $profileImage);
        //     $url = url('/images/profile/');
        //     $url = str_replace('/index.php', '', $url);
        //     $profileImage = $url.'/'.$profileImage;
        // }else{
        //     $profileImage = "";  
        // }
    	$password = $request->get('password');
        $user = new User;
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->phone = $request->get('phone');
        //$user->image = $profileImage;
        //$user->id_proof = $idProofImage;
        $user->password = bcrypt($password);
        $user->role = '2';
        if($user->save()){
            return redirect('customer/list')->with('message', 'Customer Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }    

    public function list(){
    	$users = User::where('role', '2')->orderBy('id', 'Desc')->get()->toArray();
    	return view('admin.customer.index', ['users' => $users]);
    }

    public function edit($id){
    	$users = User::where('id', $id)->first();
        //echo'<pre>';print_r($users);die;
    	return view('admin.customer.edit', ['users' => $users]);
    }

    public function view($id){
        // $users = DB::table('users')
        //                         ->leftJoin('user_rewards', 'users.id', '=', 'user_rewards.user_id')
        //                         ->where('users.id', $id)
        //                         ->first();
        $users = User::where('id', $id)->first();
        return view('admin.customer.view', ['users' => $users]);
    }

    public function delete($id){
    	$user = User::where('id', $id)->delete();
    	if($user){
            return redirect('customer/list')->with('message', 'Customer Successfully Deleted.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function update(Request $request){
        // if( $request->file('id_proof')!= ""){
        //     if (!file_exists( public_path('/images/id_proof'))) {
        //         mkdir(public_path('/images/id_proof'), 0777, true);
        //     }
        //     $path =public_path('/images/id_proof/');
        //     $image = $request->file('id_proof');
        //     $idProofImage = time().'.'.$image->getClientOriginalExtension();
        //     $destinationPath = public_path('/images/id_proof');
        //     $image->move($destinationPath, $idProofImage);
        //     $url = url('/images/id_proof/');
        //     $url = str_replace('/index.php', '', $url);
        //     $idProofImage = $url.'/'.$idProofImage;
        // }else{
        //     $user = User::where('id', $request->get('id'))->first();  
        //     $idProofImage = $user->id_proof;
        // }

        // if( $request->file('image')!= ""){
        //     if (!file_exists( public_path('/images/profile'))) {
        //         mkdir(public_path('/images/profile'), 0777, true);
        //     }
        //     $path =public_path('/images/profile/');
        //     $image = $request->file('image');
        //     $profileImage = time().'.'.$image->getClientOriginalExtension();
        //     $destinationPath = public_path('/images/profile');
        //     $image->move($destinationPath, $profileImage);
        //     $url = url('/images/profile/');
        //     $url = str_replace('/index.php', '', $url);
        //     $profileImage = $url.'/'.$profileImage;
        // }else{
        //     $user = User::where('id', $request->get('id'))->first();  
        //     $profileImage = $user->image;
        // }
        $update = User::where('id', $request->id)->update([
                                                            'name' => $request->name,
                                                            'email' => $request->email,
                                                            'phone' => $request->phone,
                                                            // 'image' => $profileImage,
                                                            // 'id_proof' => $idProofImage
                                                            ]);
        if($update){
            return redirect('customer/list')->with('message', 'Customer Successfully Updated.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }

    public function changePassword($id){
        return view('change_password', ['id' => $id]);
    }

    public function accountVerification($id){
        $user = User::where('id', $id)->first();
        $restaurants = User::where('role', '4')->where('approved', '1')->get()->toArray();
        if($restaurants){
            foreach ($restaurants as $key => $restaurant) {
                $check = UserOrderType::where('user_id', $user->id)
                                        ->where('restaurant_id', $restaurant['id'])
                                        ->first();
                if($check){
                }else{
                    $userOrderType = new UserOrderType;
                    $userOrderType->user_id = $user->id;
                    $userOrderType->restaurant_id = $restaurant['id'];
                    $userOrderType->save();
                }
            }
        }
        $user->approved = '1';
        $user->save();
        return view('account_verification');
    }

    public function updatePassword(Request $request){
        //echo'<pre>';print_r($request->all());die;
        $userId = base64_decode(urldecode($request->id));
        //echo $userId;die;
        $confirmPassword = $request->password_confirmation;
        if($confirmPassword == $request->password){
            $password = bcrypt($request->password);
            $update = User::where('id', $userId)->update(['password' => $password]);
            $return =  'Password Successfully Changed';
        }else{
            $return = 'Password and Confirm Password is not mathced';
        }
        return $return;
    }
}
