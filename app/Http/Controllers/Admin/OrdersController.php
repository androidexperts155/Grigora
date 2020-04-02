<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Order;
use App\User;
use App\OrderDetail;
use App\Item;
use Carbon\Carbon;
use DB;

class OrdersController extends Controller
{
    public function allOrders(){
    	//$userNames = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
        $userNames = User::pluck('name', 'id')->toArray();
    	$orders = Order::orderBy('id', 'Desc')->get()->toArray();
        //echo'<pre>';print_r($orders);die;
    	if($orders){
    		foreach ($orders as $key => $order) {
              // return $userNames[$order['user_id']];
    			$orders[$key]['customer_name'] = $userNames[$order['user_id']];
    			$orders[$key]['restaurant_name'] = $userNames[$order['restaurant_id']];
    		}
    	}
    	//echo'<pre>';print_r($orders);die;
    	return view('admin.orders.index', ['orders' => $orders]);
    }

    public function orderDetails($orderId){
        //$userName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
        $userName = User::pluck('name', 'id')->toArray();
        $order = Order::where('id', $orderId)->first();
        //echo'<pre>';print_r($order);die;
        $order['details'] = OrderDetail::where('order_id', $orderId)->get()->toArray();
        $order['customer_name'] = $userName[$order->user_id];
        $order['restaurant_name'] = $userName[$order->restaurant_id];
        if(!empty($order->driver_id)){
            $order['driver_name'] = $userName[$order->driver_id];
        }else{
            $order['driver_name'] = "";
        }


        //$order['details'][0]["test"] = "test";
        //echo'<pre>';print_r($order['details']);die;
        $items = Item::where('status', '1')->pluck('name', 'id')->toArray();
        //return $items;
        //echo'<pre>';print_r($items);
        //foreach ($order['details'] as $key => $orderData) {
            //echo $orderData['item_id'];die;
            //$itemDetail = Item::where('id', $orderData['item_id'])->first();
            //echo'<pre>';print_r($order['details'][$key]);die;
            //$order['details'][$key]['item_name'] = $items[$orderData['item_id']];
            //$order['details'][$key]['item_name'] = "test";
        //}
    	// $order = DB::table('orders')
     //                            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
     //                            ->where('orders.id', $orderId)
     //                            ->get()
     //                            ->toArray();
        //echo'<pre>';print_r($order);die;
    	return view('admin.orders.details', ['order' => $order, 'items' => $items]);
    }

    public function dailyEarnings(){
        $date = Carbon::today();
        //echo'<pre>';print_r($date);die;
        $orders = Order::get()->toArray();
        $users = User::where('role', '4')->pluck('name', 'id')->toArray();
        //echo'<pre>';print_r($orders);die;
        return view('admin.orders.dailyEarnings', ['orders' => $orders, 'users' => $users]);
    }
}
