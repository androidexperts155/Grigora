<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['namespace' => 'Api'], function() {
	Route::post('/signup', 'UsersController@signup');
	Route::post('/login', 'UsersController@login');
	Route::post('/phone-login', 'UsersController@phoneLogin');
	Route::post('/social-login', 'UsersController@socialLogin');
	Route::post('/forgot-password', 'UsersController@forgotPassword');
	Route::post('/search-brand', 'BrandsController@searchBrand');
	Route::get('/get-brands', 'BrandsController@getAllBrands');
	//added by dilpreet(changed by yamini)
	
	//added by dilpreet(changed by yamini)
	
	Route::get('/get-cuisine', 'CategoriesController@getCuisine');
	
	
	
	Route::post('/get-items-by-category', 'ItemsController@getItemByCategory');
	Route::get('/get-item-detail/{id}', 'ItemsController@getItemDetail');
	Route::post('/get-restaurant-by-cuisine', 'RestaurantController@getRestaurantByCuisine');
	Route::get('/location-types', 'LocationController@locationTypes');
	Route::get('/get-parent-categories', 'CategoriesController@listParentCategories');
	Route::get('/get-all-categories', 'CategoriesController@listAllCategories');
	Route::post('/restaurant-list', 'RestaurantController@list');
	Route::post('/restaurant-items-list', 'RestaurantController@itemlist');
	Route::get('/promocode-list/{id}', 'OrderController@promoCodeList');
	Route::get('/restaurant-promo/{id}', 'RestaurantController@restaurantPromo');
	Route::get('/restaurant-reviews/{id}', 'RestaurantController@restaurantReviews');

	Route::get('about_us/{language}', 'UsersController@about_us');
	Route::post('/customer-home-screen', 'UsersController@customerHomeScreen');
	Route::post('/trending-items', 'RestaurantController@trendingItems');
	Route::post('/restaurant-details', 'RestaurantController@restaurantDetails');
	Route::post('/show-all-filter-data', 'UsersController@showAllFilterData');
	Route::post('/pickup-restaurants', 'RestaurantController@pickupRestaurants');
	Route::post('/search', 'RestaurantController@search');
	
	Route::post('/add-cart', 'OrderController@addCart');
	Route::post('/view-cart', 'OrderController@viewCart');
	Route::post('/remove-cart', 'OrderController@removeCart');
	Route::post('/update-cart-item-quantity', 'OrderController@updateCartItemQty');
	Route::post('/cart-item-list', 'OrderController@cartItemList');

	Route::post('/promo-restaurants', 'UsersController@promoRestaurants');
	Route::post('/change-ordertype', 'RestaurantController@ChangeOrderType');

	Route::group(['middleware'=>['auth:api']], function(){
		
		Route::post('/update/time', 'RestaurantController@updatePreparingTime');
		
		Route::post('/popular-restaurants', 'RestaurantController@popularRestaurants');
		
		
		
		
		Route::post('/notification-setting', 'UsersController@notificationSetting');
		Route::get('/get-userinfo', 'UsersController@userInfo');
		Route::post('/change-password', 'UsersController@changePassword');	
		Route::get('/change-language/{id}', 'UsersController@changeLanguage');
		Route::post('/add-category', 'CategoriesController@add');
		Route::post('/edit-category', 'CategoriesController@edit');
		Route::get('/delete-category/{id}', 'CategoriesController@delete');
		Route::post('/join-quiz', 'RestaurantController@joinQuiz');

		Route::post('/add-cuisine', 'CategoriesController@addCuisine');
		Route::post('/get-items-by-cuisine', 'RestaurantController@getItemByCuisine');
		
		Route::post('/add-item', 'ItemsController@add');
		Route::post('/edit-item', 'ItemsController@edit');
		Route::get('/delete-item/{id}', 'ItemsController@delete');
		Route::post('/restaurant-offer', 'RestaurantController@restaurantOffer');
		Route::get('/quiz-question', 'RestaurantController@quizQuestion');
		Route::post('/check-quiz-answer', 'RestaurantController@checkQuizAnswer');
		
		//Route::get('/get-restaurant-by-cuisine/{id}', 'RestaurantController@getRestaurantByCuisine');
		Route::post('/edit-profile', 'UsersController@editProfile');
		Route::get('/get-user-info/{id}', 'RestaurantController@userInfo');
		Route::post('/complete-profile', 'UsersController@completeProfile');
		
		
		Route::get('/category-item-list/{id}', 'RestaurantController@categoryItemList');
		Route::get('/get-settings', 'OrderController@settings');
		
		
		
		Route::post('/save-cart-link', 'OrderController@saveCartShareLink');
		Route::post('/create-group-cart', 'OrderController@createGroupCart');
		Route::post('/add-in-group-cart', 'OrderController@addInGroupCart');
		
		Route::post('/view-group-cart', 'OrderController@viewGroupcart');
		Route::get('/get-group-carts', 'OrderController@getGroupCarts');
		
		Route::post('/group-cart-item-list', 'OrderController@groupCartItemList');
		Route::post('/remove-item', 'OrderController@removeItem');
		
		Route::post('/place-order', 'OrderController@placeOrder');
		Route::post('/re-order', 'OrderController@reOrder');
		Route::post('/book-table', 'OrderController@bookTable');
		Route::get('/table-booking-list', 'UsersController@tableBookingList');

		Route::post('/booking-available', 'OrderController@bookingAvailable');
		Route::get('/new-bookings', 'RestaurantController@newBookings');
		Route::post('/accept-booking', 'RestaurantController@acceptBooking');
		Route::post('/assign-promo-to-restaurant', 'RestaurantController@assignPromoToRestaurant');
		Route::post('/update-user-token', 'UsersController@userToken');
		Route::post('/payment', 'OrderController@payment');
		Route::get('/create-token', 'OrderController@createToken');

		Route::post('/change-anonymous-to-login', 'OrderController@changeUserInCart');
		

		Route::get('/restaurant-new-orders', 'RestaurantController@newOrders');
		Route::get('/restaurant-ongoing-orders', 'RestaurantController@onGoingOrders');
		Route::get('/restaurant-past-orders', 'RestaurantController@pastOrders');
		Route::get('/current-orders-location', 'RestaurantController@currentOrdersLocation');

		Route::post('/restaurant-accept-order', 'RestaurantController@acceptOrder');
		Route::get('/complete-pickup-order/{id}', 'RestaurantController@completePickupOrder');
		Route::post('/order-almost-prepared', 'RestaurantController@almostPrepared');
		Route::post('/logout', 'UsersController@logOut');
		Route::post('/driver-accept-order', 'DriverController@acceptOrder');
		Route::post('/checkDrive', 'DriverController@checkDrive');
		Route::post('/update-lat-long', 'DriverController@updateLatLong');
		Route::get('/get-driver-lat-long/{id}', 'UsersController@getDriverLatLong');
		Route::post('/schedule-order', 'OrderController@scheduleOrder');
		Route::get('/driver-orders', 'DriverController@ongoingOrders');
		Route::get('/driver-order-history', 'DriverController@allOrders');
		Route::post('/available-orders', 'DriverController@availableOrders');
		Route::get('/order-delivered/{id}', 'DriverController@orderDelivered');
		Route::get('/order-picked/{id}', 'DriverController@orderPicked');
		Route::get('/driver-status/{id}', 'DriverController@driverStatus');
		Route::post('/favourite', 'UsersController@markFavourite');
		//Route::get('/mark-unfavourite/{id}', 'UsersController@markUnFavourite');
		Route::post('/add-rating-review', 'UsersController@addRatingReview');
		Route::post('/add-items-rating', 'UsersController@addItemsRating');
		Route::get('/customer-ongoing-order', 'UsersController@customerOngoingOrders');
		Route::get('/customer-upcoming-order', 'UsersController@customerUpcomingOrders');

		Route::get('/customer-past-order', 'UsersController@customerPastOrders');
		Route::get('/order-details/{id}', 'UsersController@orderDetails');
		Route::get('/get-wallet', 'UsersController@getWallet');
		
		Route::post('/add-user-location', 'LocationController@addUserLocation');
		Route::get('/get-user-addresses', 'LocationController@getUserLocations');
		Route::post('/available-status', 'UsersController@availableStatus');
		Route::post('/contact-us', 'UsersController@contactUs');
		Route::post('/save-account-details', 'UsersController@saveAccountDetails');
		Route::get('/get-account-details', 'UsersController@getAccountDetails');

		Route::post('/payout', 'RestaurantController@payout');
		Route::post('/wallet-in', 'RestaurantController@walletIn');
		Route::post('/search-customer', 'UsersController@searchCustomer');
		Route::post('/search-user', 'UsersController@searchUser');
		Route::get('/get-customers', 'UsersController@getCustomer');
		Route::get('/wallet-history', 'UsersController@walletHistory');
		Route::post('/send-wallet-money', 'UsersController@sendWalletMoney');
		Route::get('/cancel-order/{id}', 'OrderController@cancelOrder');
		Route::post('/check-under-location', 'RestaurantController@checkUnderLocation');
		//Route::post('/update-account-details', 'UsersController@updateAccountDetails');
		//Route::get('/order-delivered/{id}', 'DriverController@orderDeliveredByDriver');

		Route::get('/delete-user-location/{id}', 'LocationController@deleteUserLocation');
		Route::post('/change-item-availabilty-status', 'ItemsController@changeItemStatus');
		Route::get('/cancel-reasons', 'RestaurantController@cancelReasons');
		Route::post('/near-by-drivers', 'RestaurantController@nearbyDrivers');

		Route::get('/notification-list', 'UsersController@getNotificationList');
		Route::post('/read-notification', 'UsersController@readNotification');
		Route::post('/change-delievry-to-pickup', 'OrderController@changeDeliveryToPickup');
		Route::get('/contact-us', 'UsersController@getContactUs');
		Route::post('/get-sub-issues', 'UsersController@subIssues');
		Route::get('/faqs', 'UsersController@faqs');
		Route::post('/chat', 'ChatController@chat');
		Route::post('/getchat', 'ChatController@getchat');
		Route::get('/chatheads', 'ChatController@chat_heads');
		Route::post('/delete-notification', 'UsersController@deleteNotification');
		Route::post('/start-preparing', 'RestaurantController@startPreparing');
		Route::get('/get-voucher-codes', 'UsersController@getVoucherCodes');
		Route::get('/voucher-card/{id}', 'UsersController@voucherCard');
		Route::post('/buy-card', 'UsersController@buyCard');
		Route::post('/send-gift-card', 'UsersController@sendGiftCard');
		Route::get('/my-gift-card', 'UsersController@mygiftCards');
		Route::get('/my-purchased-card', 'UsersController@myPurchsedcards');
		Route::post('/redeme-card', 'UsersController@redemeCard');
		Route::post('/add-tip-to-driver', 'DriverController@addTip');
		Route::post('/change-restaurant-booking-status', 'RestaurantController@changeRestaurantBookingStatus');
		Route::post('/check-login-logout-cart', 'OrderController@checkLoginLogoutCart');
		Route::post('/add-parent-cuisine', 'RestaurantController@addParentCuisine');
		Route::post('/edit-parent-cuisine', 'RestaurantController@editParentCuisine');
		Route::post('/delete-parent-cuisine', 'RestaurantController@deleteParentCuisine');
	});

	
	Route::post('/pay-stack', 'OrderController@payStack');
	Route::post('/check', 'RestaurantController@check');

	Route::get('/test', 'RestaurantController@getRecipit');	

	
});
