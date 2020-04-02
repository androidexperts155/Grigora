<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/change-password/{id}', 'Admin\CustomerController@changePassword');
Route::get('/account-verification/{id}', 'Admin\CustomerController@accountVerification');
Route::post('/password/update', 'Admin\CustomerController@updatePassword');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', 'DashboardController@index')->name('main');
Route::get('/home', 'DashboardController@index')->name('home');
Route::get('/dashboard', 'DashboardController@index')->name('home');
Route::get('/dashboard/logout', 'DashboardController@logout')->name('logout');
Route::get('dashboard/edit-profile', 'DashboardController@profile')->name('profile');
Route::post('dashboard/save_edit-profile', 'DashboardController@saveprofile')->name('saveprofile');




Route::group(['middleware' => 'auth'], function () {
	Route::group(['namespace' => 'Admin'], function() {

		Route::get('/company_offline', 'SettingsController@viewcompanypage');
		Route::post('/company_offline_save', 'SettingsController@Company_Offline')->name('company_offline_save');
		

		Route::get('subadmin/add', 'SubadminController@add');
		Route::post('subadmin/save', 'SubadminController@save');
		Route::get('subadmin/list', 'SubadminController@list');
		Route::get('subadmin/view/{id}', 'SubadminController@view');
		Route::get('subadmin/edit/{id}', 'SubadminController@edit');
		Route::post('subadmin/update', 'SubadminController@update');
		Route::get('subadmin/delete/{id}', 'SubadminController@delete');


		Route::get('restaurant/add', 'RestaurantController@add');
		Route::post('restaurant/save', 'RestaurantController@save');
		Route::get('restaurant/list', 'RestaurantController@list');
		Route::get('restaurant/view/{id}', 'RestaurantController@view');
		Route::get('restaurant/edit/{id}', 'RestaurantController@edit');
		Route::post('restaurant/update', 'RestaurantController@update');
		Route::get('restaurant/delete/{id}', 'RestaurantController@delete');
		Route::get('restaurant/approve/{id}', 'RestaurantController@approve');
		Route::get('restaurant/items/list/{id}', 'RestaurantController@itemList');

		Route::get('driver/add', 'DriverController@add');
		Route::post('driver/save', 'DriverController@save');
		Route::get('driver/list', 'DriverController@list');
		Route::get('driver/view/{id}', 'DriverController@view');
		Route::get('driver/edit/{id}', 'DriverController@edit');
		Route::post('driver/update', 'DriverController@update');
		Route::get('driver/delete/{id}', 'DriverController@delete');
		Route::get('driver/approve/{id}', 'DriverController@approve');
		Route::get('driver/update/online-status/{user_id}/{status}/{attendance_id?}', 'DriverController@updateStatus');
		

		Route::get('customer/add', 'CustomerController@add');
		Route::post('customer/save', 'CustomerController@save');
		Route::get('customer/list', 'CustomerController@list');
		Route::get('customer/view/{id}', 'CustomerController@view');
		Route::get('customer/edit/{id}', 'CustomerController@edit');
		Route::post('customer/update', 'CustomerController@update');
		Route::get('customer/delete/{id}', 'CustomerController@delete');

		Route::get('item/list', 'ItemsController@list');
		Route::get('item/approve/{id}', 'ItemsController@approve');

		Route::get('cuisine/add', 'CuisineController@add');
		Route::post('cuisine/save', 'CuisineController@save');
		Route::get('cuisine/list', 'CuisineController@list');
		Route::get('cuisine/view/{id}', 'CuisineController@view');
		Route::get('cuisine/approve/{id}', 'CuisineController@approve');
		Route::get('cuisine/edit/{id}', 'CuisineController@edit');
		Route::post('cuisine/update', 'CuisineController@update');
		Route::get('cuisine/delete/{id}', 'CuisineController@delete');

		Route::get('brand/add', 'BrandsController@add');
		Route::post('brand/save', 'BrandsController@save');
		Route::get('brand/list', 'BrandsController@list');
		Route::get('brand/view/{id}', 'BrandsController@view');
		Route::get('brand/approve/{id}', 'BrandsController@approve');
		Route::get('brand/edit/{id}', 'BrandsController@edit');
		Route::post('brand/update', 'BrandsController@update');
		Route::get('brand/delete/{id}', 'BrandsController@delete');

		Route::get('categories/add', 'CategoriesController@add');
		Route::post('categories/save', 'CategoriesController@save');
		Route::get('categories/list', 'CategoriesController@list');
		Route::get('categories/view/{id}', 'CategoriesController@view');
		Route::get('categories/edit/{id}', 'CategoriesController@edit');
		Route::post('categories/update', 'CategoriesController@update');
		Route::get('categories/delete/{id}', 'CategoriesController@delete');

		Route::get('/promocode/list', 'SettingsController@promoCodes');
		Route::get('/promocode/add', 'SettingsController@addPromoCode');
		Route::get('/promocode/edit/{id}', 'SettingsController@editPromoCode');
		Route::post('/promocode/update', 'SettingsController@updatePromocode');
		Route::post('/promocode/save', 'SettingsController@savePromoCode');
		Route::get('/promocode/delete/{id}', 'SettingsController@deletePromoCode');

		Route::get('/location/list', 'LocationController@list');
		Route::get('/location/add', 'LocationController@add');
		Route::post('/location/save', 'LocationController@save');
		Route::get('/location/edit/{id}', 'LocationController@edit');
		Route::post('/location/update', 'LocationController@update');
		Route::get('/location/delete/{id}', 'LocationController@delete');

		Route::get('settings/list', 'SettingsController@list');
		Route::post('settings/save', 'SettingsController@save');

		Route::get('quiz/add', 'QuizController@list');
		Route::post('quiz/save', 'QuizController@save');
		Route::get('quiz/list', 'QuizController@listview');
		Route::get('quiz/view/{id}', 'QuizController@view');
		Route::get('quiz/delete/{id}', 'QuizController@delete');
		Route::get('quiz/edit/{id}', 'QuizController@edit');
		Route::post('quiz/editsave', 'QuizController@editsave');	

		Route::get('/voucher/list', 'VoucherController@list');
		Route::get('/voucher/redeem/{code}', 'VoucherController@RedeemeData');
		Route::post('/voucher/generate_code', 'VoucherController@add')->name('generate_code');	
		//Route::get('voucher_code/view/{id}', 'VoucherController@voucher_codes');
		Route::get('voucher_code/view/{id}', 'BarcodegeneratorController@barcode');
		
		Route::get('/voucher/viewback/{code}', 'VoucherController@viewback');
		Route::get('/voucher/viewback/generatepdf/{code}', 'GeneratepdfController@generatePDF');
		
	

		Route::get('orders/list', 'OrdersController@allOrders');
		Route::get('orders/details/{id}', 'OrdersController@orderDetails');
		Route::get('orders/earnings', 'OrdersController@dailyEarnings');

		Route::get('wallet/history/{id}', 'UsersController@walletHistory');
		Route::get('ratings/reviews/{id}', 'UsersController@ratingReviews');

		Route::get('/notifications', 'SettingsController@notifications');
		Route::post('/send-notification', 'SettingsController@sendNotification');

		Route::get('/promo_notifications', 'SettingsController@PromoNotifications'); 
		Route::post('send_promonotifications', 'SettingsController@SendPromoNotifications')->name('send_promonotifications');

		Route::get('/meal_notifications', 'SettingsController@MealNotifications'); 
		Route::post('send_mealnotifications', 'SettingsController@SendMealNotifications')->name('send_mealnotifications');
		
		Route::get('/paidpage_notifications', 'SettingsController@PaidpageNotifications'); 
		Route::post('send_paidpagenotifications', 'SettingsController@SendPaidpageNotifications')->name('send_paidpagenotifications');;

		Route::get('/addupdate_notifications', 'SettingsController@AddUpdateNotifications'); 
		Route::post('send_appupdatenotifications', 'SettingsController@SendAddUpdateNotifications')->name('send_appupdatenotifications'); 
		
		Route::get('contact-us/', 'SettingsController@contactUs');
		Route::get('about_us/', 'SettingsController@aboutUs');
		Route::get('about_us/french/', 'SettingsController@aboutUsFrench');
		Route::post('add_about_us/', 'SettingsController@addaboutUs')->name('add_about_us');

		Route::get('chat_list/', 'ChatController@chat_list')->name('chat_list');
		Route::get('chat/{id}/{ticket_id}', 'ChatController@chatview')->name('chat');
		Route::post('savemessage/', 'ChatController@chat')->name('savemessage');
		Route::post('getchat/', 'ChatController@getchat')->name('getchat');
		
		
		
	});

});
