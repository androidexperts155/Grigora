var app = require('express')();
var http = require('http').createServer(app);
var io = require('socket.io')(http);
var mysql  = require('mysql');
var distance = require('google-distance');
var moment = require('moment');

distance.apiKey = 'AIzaSyC2aWp0P-YzltErSlsHn7-AJcQV7iTzN5E';





var con = mysql.createConnection({
									host: "localhost",
									user: "user",
									password: "40OC7515%c4930x",
									database: "user_grigora"
								});
con.connect();
var allUser = {};
io.on('connection', function(socket){
	//console.log("a user found",socket);
	var initial_user_id = socket.request._query.id;
	//console.log("data",socket.request);
	//console.log("a user found",initial_user_id);
	if(typeof initial_user_id !='undefined' && initial_user_id !="" && initial_user_id !=null){
       	allUser[socket.request._query.id] = socket.id;
       	var getuserobj = allUser[socket.request._query.id];
       	io.to(getuserobj).emit('getconnectresponse', {user_id:initial_user_id, message:"success", status:1});
   	}
   	//var selectSql = "SELECT orders.preparing_end_time,orders.order_status,users.latitude,users.longitude,orders.end_lat,orders.end_long,orders.preparing_time FROM `orders` inner join users on orders.driver_id = users.id Where orders.id = "+orderId;

   // 	var selectOrder = "SELECT orders.*,users.latitude,users.longitude FROM `orders` inner join users on orders.driver_id = users.id WHERE `orders`.`user_id` = "+initial_user_id+" AND `orders`.`order_status` IN(2,3,4,7)";
   // 	console.log("------y",selectOrder);
   // 	con.query(selectOrder, function(err, orderData){
   // 		if (typeof orderData !== 'undefined' && orderData.length > 0) {
   			
   // 			orderData.forEach(function(entry) {
   // 				console.log("entry",entry.id);
			//     console.log(entry);

			//     var origin = entry.latitude+","+entry.longitude;
			// 		   	var destination = entry.end_lat+","+entry.end_long;
			// 		   //console.log("origin",origin);
			// 		   //console.log("destination",destination);
			// 		   	distance.get(
			// 			{
			// 			  index: 1,
			// 			  origin: origin,
			// 			  destination: destination
			// 			},
			// 			function(err, data1) {
			// 			  	if (err) return console.log(err);
						  
			// 			  	var driver_time = data1.durationValue;
			// 			  	var driver_distance = data1.distanceValue;
			// 			  	//console.log("result1",result1);
						  	
			// 			  	var currentTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
			// 			  	var endTime = new Date(entry.preparing_end_time).toISOString().slice(0, 19).replace('T', ' ');
			// 			  	var startDate = moment(currentTime, 'YYYY-M-DD HH:mm:ss')
						  	
			// 				var endDate = moment(endTime, 'YYYY-M-DD HH:mm:ss')
			// 				console.log("endDate",endDate);
			// 				console.log("startDate",startDate);
			// 				var dishTimeRemaing = endDate.diff(startDate, 'minutes')
							
			// 			  	//console.log("endTime",endTime);
			// 			  	entry.dishRemainingTime = dishTimeRemaing;
			// 			  	entry.driverTime = driver_time;
			// 			  	entry.distance = driver_distance;

			// 			  	console.log("output",entry);
			// 			  	console.log("output to",initial_user_id);
			// 			  	console.log("output to orderId",entry.id);

			// 			  	io.to(allUser[initial_user_id]).emit(entry.id, entry);
			//     			console.log('success'); 	

			// 			});

			   
			// });
   // 		}else{
   // 			return false;
   // 		}
   // 	});

	allUser[socket.request._query.id] = socket.id;
	//console.log(allUser);
	socket.on('updateLatLong', function(data){
		//console.log("send by id",socket.request._query.id)
		var a = IsJsonString(data);
		if(a){
			var data = JSON.parse(data);
	  		//console.log("customerId",data);
		}else{
			//console.log("customerId-----");
			return false;
		}
		console.log("updateLatLong---------",data);
	  	if(typeof data.customerId !='undefined' && data.customerId != ''){
	  		console.log("updateLatLong2222---------",);
	  		//provide lat long to customer for tracking
	  		var orderId = data.orderId;
	  		//console.log("orderId",orderId);
	  		if(socket.request._query.id == "" || socket.request._query.id == undefined){
	  			return false;
	  		}

	  		var updateSql = "Update `users` Set `address` = "+"'"+data.address+"'"+", `latitude` = "+data.latitude+",`longitude` = "+data.longitude+" Where `id` = "+socket.request._query.id;
			 	//console.log("sql",updateSql);
			 	con.query(updateSql, function (err, result) {
				   	if (err){
				   		return console.log(err);
				   	} //throw err
				   	orderIdArr = orderId.split(',');
				   	console.log("orderIdArr",orderIdArr);
				   var selectSql = "SELECT orders.request_time,orders.preparing_end_time,orders.restaurant_id,orders.order_status,users.latitude ,users.longitude,users.id as driver_id,users.name as driver_name,users.email as driver_email,users.phone as driver_phone, users.image as driver_image, orders.end_lat,orders.end_long,orders.preparing_time FROM `orders` inner join users on orders.driver_id = users.id  Where orders.id = "+orderId;
			  		//console.log("sql--",selectSql);

					result1 =  [];	  	
						  	console.log("here");
			  		con.query(selectSql, function (err1, result1) {
			  			//console.log("result1",result1);
					   	if (err1){
					   		//console.log(err1);
					   		return false;
					   	} //throw err
					   	if(result1.length == 0){
					   		return false;
					   	}
					   	var origin = result1[0].latitude+","+result1[0].longitude;
					   	var destination = result1[0].end_lat+","+result1[0].end_long;
					   console.log("here1111");
					   	distance.get(
						{
						  index: 1,
						  origin: origin,
						  destination: destination
						},
						function(err, data1) {
						  	if (err) return console.log(err);
						  	//console.log("data1",data1);
						  	var driver_time = data1.durationValue;//seconds
						  	var driver_distance = data1.distanceValue;//kms
						  	//console.log("result1",result1);
						  	
						  	//var currentTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
						  	var requestTime = result1[0].request_time;
						  	var currentTime = new Date(requestTime).toISOString().slice(0, 19).replace('T', ' ');
						  	console.log('---currentTime---',currentTime);
						  	var endTime = new Date(result1[0].preparing_end_time).toISOString().slice(0, 19).replace('T', ' ');
						  	var startDate = moment(currentTime, 'YYYY-M-DD HH:mm:ss')
						  	
							var endDate = moment(endTime, 'YYYY-M-DD HH:mm:ss')
							//console.log("endDate",endDate);
							//console.log("startDate",startDate);
							var dishTimeRemaing = endDate.diff(startDate, 'minutes')
							
						  	//console.log("endTime",endTime);
						  	result1[0].dishRemainingTime = dishTimeRemaing;
						  	result1[0].driverTime = driver_time;
						  	result1[0].distance = driver_distance;

						  	//console.log("output",result1[0]);
						  	//console.log("output to",orderId);
						  	var resturentSql = "Select * FROM users Where id = "+result1[0].restaurant_id
						  	con.query(resturentSql, function (err2, result2) {	
						  		if (err2){
							   		console.log(err2);
							   		return false;
							   	} //throw err
							   	if(result2.length == 0){
							   		return false;
							   	}
								result1[0].resturent_name = result2[0].name;				   	
								result1[0].resturent_email = result2[0].email;				   	
								result1[0].resturent_phone = result2[0].phone;				   	
								result1[0].resturent_image = result2[0].image;				   	
								result1[0].resturent_lat = result2[0].latitude;				   	
								result1[0].resturent_long = result2[0].longitude;				   	

								//console.log("output",result1[0]);
							  	//console.log("output to",orderId);
							  	io.to(allUser[socket.request._query.id]).emit(orderId, result1[0]); 
						  	});	   	

						});
					    
						
					   //con.destroy();
				 	});	
				   //con.destroy();
			 	});


	  		

	  		
	  	}else{
	  		console.log("updateLatLong1111---------",);
	  		//update drivers lat long
	  		//con.connect(function(err) {
    			//if (err) //throw err
			 	//console.log(err)
			 	if(socket.request._query.id == "" || socket.request._query.id == undefined){
	  				return false;
	  			}

			 	var sql = "Update `users` Set `address` = "+"'"+data.address+"'"+", `latitude` = "+data.latitude+",`longitude` = "+data.longitude+" Where `id` = "+socket.request._query.id;
			 	//console.log("sql",sql);
			 	con.query(sql, function (err, result) {
				   if (err) //throw err
				   	return console.log(err);
				   //con.destroy();
			 	});
			//});
	  	}
	});

	socket.on('getOrderData', function(data){
		//console.log(data);
		var a = IsJsonString(data);
		//console.log("------YAMINI---------------");
		if(a){
			var data = JSON.parse(data);
	  		//console.log("customerId",data);
		}else{
			//console.log("customerId-----");
			return false;
		}
		if(socket.request._query.id == "" || socket.request._query.id == undefined){
  			return false;
  		}
		var orderId = data.orderId;
		var selectSql = "SELECT orders.preparing_end_time,orders.restaurant_id,orders.order_status,users.latitude ,users.longitude,users.id as driver_id,users.name as driver_name,users.email as driver_email,users.phone as driver_phone, users.image as driver_image, orders.end_lat,orders.end_long,orders.preparing_time,orders.cancel_type FROM `orders` inner join users on orders.driver_id = users.id  Where orders.id = "+orderId;
			  		//console.log("sql--",selectSql);

		result1 =  [];	  	
	  	//console.log("result1",result1);
  		con.query(selectSql, function (err1, result1) {	
		   	if (err1){
		   		console.log(err1);
		   		return false;
		   	} //throw err
		   	if(result1.length == 0){
		   		return false;
		   	}

		   	
		   	
		   	var origin = result1[0].latitude+","+result1[0].longitude;
		   	var destination = result1[0].end_lat+","+result1[0].end_long;
		   
		   	distance.get(
			{
			  index: 1,
			  origin: origin,
			  destination: destination
			},
			function(err, data1) {
			  	if (err) return console.log(err);
			  		//console.log("data1--------------------",data1);
			  	var driver_time = data1.durationValue;
			  	var driver_distance = data1.distanceValue;
			  	//console.log("result1",result1);
			  	
			  	var currentTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
			  	var endTime = new Date(result1[0].preparing_end_time).toISOString().slice(0, 19).replace('T', ' ');
			  	var startDate = moment(currentTime, 'YYYY-M-DD HH:mm:ss')
			  	
				var endDate = moment(endTime, 'YYYY-M-DD HH:mm:ss')
				//console.log("endDate",endDate);
				//console.log("startDate",startDate);
				var dishTimeRemaing = endDate.diff(startDate, 'minutes')
				
			  	//console.log("endTime",endTime);
			  	result1[0].dishRemainingTime = dishTimeRemaing;
			  	result1[0].driverTime = driver_time;
			  	result1[0].distance = driver_distance;

			  	var resturentSql = "Select * FROM users Where id = "+result1[0].restaurant_id
			  	con.query(resturentSql, function (err2, result2) {	
			  		if (err2){
				   		console.log(err2);
				   		return false;
				   	} //throw err
				   	if(result2.length == 0){
				   		return false;
				   	}
					result1[0].resturent_name = result2[0].name;				   	
					result1[0].resturent_email = result2[0].email;				   	
					result1[0].resturent_phone = result2[0].phone;				   	
					result1[0].resturent_image = result2[0].image;				   	
					result1[0].resturent_lat = result2[0].latitude;				   	
					result1[0].resturent_long = result2[0].longitude;				   	

					//console.log("output",result1[0]);
				  	//console.log("output to",orderId);
				  	io.to(allUser[socket.request._query.id]).emit(orderId, result1[0]); 
			  	});	
			});
		    
			
		   //con.destroy();
	 	});	
	});

	socket.on('getNearByCustomer', function(data){
		//param: latitude, longitude
		var params = IsJsonString(data);
		if(params){
			var data = JSON.parse(data);
		}else{
			return false;
		}

		var latitude = data.latitude;
		var longitude = data.longitude;

		var settings = "SELECT * FROM `settings` WHERE `id` = '1'";
		con.query(settings, function (err, setting) {
	   		if (err){
	   			return console.log(err);
	   		}
	   		//console.log(setting);
	   		var distance = setting[0].distance;
	   		//console.log(distance);
	   		var nearByDrivers = "SELECT id,latitude, longitude, ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( "+latitude+" ) ) + COS( RADIANS( latitude ) ) * COS( RADIANS( "+latitude+" )) * COS( RADIANS( longitude ) - RADIANS( "+longitude+" )) ) * 6371 AS distance FROM users WHERE ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( "+latitude+" ) ) + COS( RADIANS( latitude ) ) * COS( RADIANS( "+latitude+" )) * COS( RADIANS( longitude ) - RADIANS( "+longitude+" )) ) * 6371  < "+distance+" And `role` IN(3) AND busy_status = '0' ORDER BY `distance`";
	   		con.query(nearByDrivers, function (err, nearByDrivers){
	   			if (err){
	   				return console.log(err);
	   			}
	   			console.log(nearByDrivers);
	   			io.to(allUser[socket.request._query.id]).emit('nearByDrivers', nearByDrivers); 

	   		});
	   	});

	});

	/*socket.on('fetchLatLongCustomer', function(data){
		var data = JSON.parse(data);
		// data.orderID
		// data.myId
		//console.log("my-data",data);
		var sql = "SELECT * FROM `orders` LEFT JOIN users ON orders.driver_id = users.id  WHERE orders.id = "+data.orderID+ "";
		 	//console.log("sql",sql);
		 	con.query(sql, function (err, orderResult) {
		 		if(err){
		 			//console.log("err",err)
		 		}
		 		//console.log("orderResult",orderResult)
			   	if(orderResult){
			   		var orderData = orderResult;
			   		//console.log('worked');
			  		io.to(allUser[data.myId]).emit('provideLatLongToCustomer', orderData); 		

			   	}else{
			   		//console.log('error');
			   	}
		 	});
	  	
	});*/

	socket.on('disconnect', function () {
	   	//console.log('A user disconnected');
		delete allUser[socket.request._query.id];
	            // console.log('after logourt', allusers)
	});



});

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

http.listen(3000, function(){
  console.log('listening on *:3000');
});