<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\Transaction;
use App\RatingReview;
use Auth;

class UsersController extends Controller
{
    public function walletHistory($id){
    	//$user = Auth::user();
        $transactions = Transaction::where('user_id', $id)
                                    ->whereIn('type', ['3','4','5','6'])
                                    ->orderBy('created_at', 'Desc')
                                    ->get()->toArray();
        $usersImage = User::where('role', '<>', '1')->pluck('image', 'id')->toArray();
        $usersName = User::where('role', '<>', '1')->pluck('name', 'id')->toArray();
        $usersEmail = User::where('role', '<>', '1')->pluck('email', 'id')->toArray();
        //echo'<pre>';print_r($transactions);die;
        if($transactions){
            foreach ($transactions as $key => $transaction) {
                $transactions[$key]['user_image'] = $usersImage[$transaction['user_id']];
                $transactions[$key]['user_name'] = $usersName[$transaction['user_id']];
                $transactions[$key]['user_email'] = $usersEmail[$transaction['user_id']];
                if($transaction['type'] == '5' || $transaction['type'] == '6'){
                    if($transaction['transaction_data']){
                        $transactions[$key]['other_user_image'] = $usersImage[$transaction['transaction_data']];
                        $transactions[$key]['other_user_name'] = $usersName[$transaction['transaction_data']];
                        $transactions[$key]['other_user_email'] = $usersEmail[$transaction['transaction_data']];
                    }else{
                        $transactions[$key]['other_user_image'] = "";
                        $transactions[$key]['other_user_name'] = "";
                        $transactions[$key]['other_user_email'] = "";    
                    }
                }elseif($transaction['type'] == '4'){
                    $order = Order::where('id', $transaction['order_id'])->first();
                    if($order){
                        $transactions[$key]['other_user_image'] = $usersImage[$order['restaurant_id']];
                        $transactions[$key]['other_user_name'] = $usersName[$order['restaurant_id']];
                        $transactions[$key]['other_user_email'] = $usersEmail[$order['restaurant_id']];
                    }else{
                        $transactions[$key]['other_user_image'] = "";
                        $transactions[$key]['other_user_name'] = "";
                        $transactions[$key]['other_user_email'] = "";    
                    }
                }else{
                    $transactions[$key]['other_user_image'] = "";
                    $transactions[$key]['other_user_name'] = "";
                    $transactions[$key]['other_user_email'] = "";
                }
            }
        }
        echo'<pre>';print_r($transactions);die;
        return view('admin.wallet.history', ['transactions' => $transactions]);
    }

    public function ratingReviews($id){
    	$user = User::where('id', $id)->first();
    	if($user == '3'){
    		$receiverType = "3";
    	}elseif($user == '4'){
    		$receiverType = "2";
    	}else{
    		$receiverType = "";
    	}

    	$ratingReviews = RatingReview::where('receiver_id', $id)
										->where('receiver_type', $receiverType)
										->orderBy('id', 'Desc')
										->get()
										->toArray();
		//echo'<pre>';print_r($ratingReviews);die;
		return view('admin.ratings.reviews', ['ratingReviews' => $ratingReviews]);
    }
}
