<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;

class SubadminController extends Controller
{
    public function list(){
    	$users = User::where('role', '5')->orderBy('id', 'Desc')->get()->toArray();
    	return view('admin.subadmin.index', ['users' => $users]);
    	//return view('admin.subadmin.index', ['users' => $users]);
    }

    public function edit($id){
    	$users = User::where('id', $id)->first();
    	return view('admin.subadmin.edit', ['users' => $users]);
    }

    public function update(Request $request){
    	$user = User::where('id', $request->id)->first();
    	$user->name = $request->name;
    	$user->email = $request->email;
    	$user->password = $request->password;
    	if($user->save()){
    		return redirect('subadmin/list')->with('message', 'Sub Admin Successfully Updated.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }

    public function add(){
    	return view('admin.subadmin.add');
    }

    public function save(Request $request){
    	//echo'<pre>';print_r($request->all());die;
    	$user = new User;
    	$user->name = $request->name;
    	$user->email = $request->email;
    	$user->password = bcrypt($request->password);
    	$user->role = '5';
    	if($user->save()){
    		return redirect('subadmin/list')->with('message', 'Sub Admin Successfully Added.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }

    public function delete($id){
    	$delete = User::where('id', $id)->delete();
    	if($delete){
    		return redirect('subadmin/list')->with('message', 'Sub Admin Successfully Deleted.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');       
        }
    }
}
