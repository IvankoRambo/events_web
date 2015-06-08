<?php

//function to work with bots via redis

function redis_expire($user_email, $prefix = 'count_'){
	
	$bunpage = 'http://dev.school-server/events/events_web/logging_db2/auth.php';
	$get_params = '?email='.$user_email;
	
	$key = $prefix.$user_email;
	
	$bun_prefix = 'bun_';
	$bun_key = $bun_prefix.$user_email;
	
	$redis = new Redis();
	
	if(!($redis->pconnect('127.0.0.1', 6379)))	die('You cant connect to redis');		
	
	$redis->set($bun_key, 'no');
	$redis->incr($key);
	$redis->setTimeout($key, 10);
	
	if($redis->get($key) > 2){
		header("Location: ".$bunpage.$get_params);
		$redis->set($bun_key, 'yes');	
	}
	
}

function isUserBanned($user_email, $prefix = 'bun_'){
	
	$key = $prefix.$user_email;
	
	$redis = new Redis();
	
	if(!($redis->pconnect('127.0.0.1', 6379)))	die('You cant connect to redis');
	
	if(!($redis->get($key)))	return false;
	else return ( $redis->get($key) == 'yes' ) ? true : false; 
		
}

function showLastTenUsers($db, $last_user_id){
	
	$redis = new Redis();
	
	if(!($redis->pconnect('127.0.0.1', 6379)))	die('You cant connect to redis');
	
	if(!in_array($last_user_id, $redis->lGetRange('last_ten_users', 0, -1)))	$redis->rPush('last_ten_users', $last_user_id);
	
	$ids_length = count($redis->lGetRange('last_ten_users', 0, -1));
	
	if($ids_length > 10){
		$redis->listTrim('last_ten_users', $ids_length - 10, $ids_length-1);
	}
	$last_ids = $redis->lGetRange('last_ten_users', 0, -1);
	
	$last_names = array();
	for($i = 0; $i < count($last_ids); $i++){
		$current_user = getUserInfo($db, (int)$last_ids[$i]);
		$last_names[] = $current_user[0]['name'];
	}
	
	return $last_names;
	
}


