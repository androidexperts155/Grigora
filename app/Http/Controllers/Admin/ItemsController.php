<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Item;
use App\User;
use App\Mail\AccountVerification;
use Mail;
class ItemsController extends Controller
{
     public function list(){
    	   $items = Item::all(); 
         $users = array();
        foreach ($items as $key => $item) {
            $restaurant = User::where('id',$item->restaurant_id)->first();
            $users[$key]['id'] = $item->id;
            $users[$key]['name'] = $item->name;
            $users[$key]['image'] = $item->image;
            $users[$key]['approved'] = $item->approved;
            $users[$key]['restaurant'] = $restaurant->name;
            $users[$key]['restaurant_id'] = $item->restaurant_id;
                  # code...
              }      
    	return view('admin.items.index', ['users' => $users]);
    }
      public function approve($id){
        $user = Item::where('id', $id)->first();
        $user->approved = '1';
        if($user->save()){
            return redirect('item/list')->with('message', 'Item Successfully Approved.');
        }else{
            return redirect()->back()->with('message', 'Something went wrong.');   
        }
    }
}
