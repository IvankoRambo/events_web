<?php

function getConnection($config_path){
	$config = parse_ini_file($config_path);
	
	try{
		$db = new PDO("mysql:host={$config['host']};dbname={$config['db_name']}", $config['user'], $config['password']);
	}
	catch(PDOException $e){
		echo "Connection is failed:".$e->getMessage();
		return;
	}
	
	return $db;
		
}


function createUser($db, $user_name, $email, $password, $date, $is_active = 0){
	$query = $db->prepare("INSERT INTO user (name, email, password, date_create, is_active) VALUE (:name, :email, PASSWORD(:password), :date, :status)");
	$query->bindParam(":name", $user_name, PDO::PARAM_STR);
	$query->bindParam(":email", $email, PDO::PARAM_STR);
	$query->bindParam(":password", $password, PDO::PARAM_STR);
	$query->bindParam(":date", $date, PDO::PARAM_STR);
	$query->bindParam(":status", $is_active, PDO::PARAM_INT);

	return $query->execute();
}

 
function isEmailExists($db, $email){
	
	$query = $db->prepare("SELECT id FROM user WHERE email = :email");
	$array_check = array(':email' => $email);
	$query->execute($array_check);
	$check = $query->fetchAll(PDO::FETCH_NUM);
	
	return ( empty($check) ) ? false : true;
}

function isRightPassword($db, $mail, $password){
	$query = $db->prepare("SELECT id FROM user WHERE email = :email AND password = PASSWORD(:password)");
	$query->bindParam(":email", $mail, PDO::PARAM_STR);
	$query->bindParam(":password", $password, PDO::PARAM_STR);
	$query->execute();
	$check = $query->fetchAll(PDO::FETCH_NUM);
	
	return ( empty($check) ) ? false : true;
}

function getUserInfo($db, $pointer)
{

    if (preg_match('/.+@.+\..+/', $pointer)) {
        $query = $db->prepare("SELECT id, name, date_create, is_active FROM user WHERE email = :email");
        $query->bindParam(":email", $pointer, PDO::PARAM_STR);
    } else {
        $query = $db->prepare("SELECT name, email, date_create, is_active FROM user WHERE id = :id");
        $query->bindParam(":id", $pointer, PDO::PARAM_INT);
    }

    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);

}

function addNewEvent($db, $event_name){
	$query = $db->prepare("INSERT INTO event (event) VALUE (:event)");
	$query->bindParam(":event", $event_name, PDO::PARAM_STR);
	
	return $query->execute();
}

function getEventInfo($db, $pointer){
	if(preg_match('/([A-Za-z]+)/', $pointer)){
		$query = $db->prepare("SELECT id_event FROM event WHERE event = :event");
		$query->bindParam(":event", $pointer, PDO::PARAM_STR); 
	}
	else{
		$query = $db->prepare("SELECT event FROM event WHERE id_event = :id_event");
		$query->bindParam(":id_event", $pointer, PDO::PARAM_INT); 
	}
	
	$query->execute();
	return $query->fetchAll(PDO::FETCH_ASSOC);
	
}


function getAllEvents($db){
	$query = $db->prepare("SELECT event FROM event");
	$query->execute();
	return $query->fetchAll(PDO::FETCH_ASSOC);
}


function getCountViaUserId($db, $user_id){
	$query = $db->prepare("SELECT id_count, count FROM count WHERE id = :user_id");
	$query->bindParam(":user_id", $user_id, PDO::PARAM_INT);
	
	$query->execute();
	return $query->fetchAll(PDO::FETCH_ASSOC);
}

function addUserCount($db, $user_id, $count){
	
	$count_array = getCountViaUserId($db, $user_id);
	
	if(empty($count_array)){
		$query = $db->prepare("INSERT INTO count (id, count) VALUE (:user_id, :count)");
	}
	else{
		$query = $db->prepare("UPDATE count SET count = :count WHERE id = :user_id");
	}
	$query->bindParam(":user_id", $user_id, PDO::PARAM_INT);
	$query->bindParam(":count", $count, PDO::PARAM_INT);
	
	return $query->execute();
	
}

function isUserHasEvent($db, $user_id, $event_id){
	$query = $db->prepare("SELECT id_user_event FROM user_event WHERE id = :user_id AND id_event = :event_id");
	$query->bindParam(":user_id", $user_id, PDO::PARAM_INT);
	$query->bindParam(":event_id", $event_id, PDO::PARAM_INT);
	
	$query->execute();
	$check = $query->fetchAll(PDO::FETCH_ASSOC);
	return ( empty($check) ) ? false : true;
}


function addEventForUser($db, $user_id, $event_id){
	$query = $db->prepare("INSERT INTO user_event (id, id_event) VALUE (:user_id, :event_id)");
	$query->bindParam(":user_id", $user_id, PDO::PARAM_INT);
	$query->bindParam(":event_id", $event_id, PDO::PARAM_INT);
	
	return $query->execute();
	
}

/*
 * mode can be 'me' or 'others'
 */
function getUserEvents($db, $user_id, $mode){
	
	switch ($mode){
	case 'me':
		$query = $db->prepare("SELECT id, id_event FROM user_event WHERE id= :user_id");
		break;
	case 'others':
		$query = $db->prepare("SELECT id, id_event FROM user_event WHERE NOT id= :user_id");
		break;
	}
	
	$query->bindParam(":user_id", $user_id, PDO::PARAM_INT);
	$query->execute();
	
	$output_array = array();
	
	$data = $query->fetchAll(PDO::FETCH_ASSOC);
	foreach($data as $key=>$row){
		$id[$key] = $row['id'];
	}
	
	if(empty($id))	return $output_array;
	
	array_multisort($id, SORT_ASC, $data);
	
	for($i = 0; $i < count($data); $i++){
		$user_info = getUserInfo($db, $data[$i]['id']);
		$event_info = getEventInfo($db, $data[$i]['id_event']);
		$array_push = ['name' => $user_info[0]['name'], 'event' => $event_info[0]['event']];
		$output_array[] = $array_push;
	}
	
	
	return $output_array;
	
}

//get the id of last registered user

function getLastUserInfo($db){
	
	$query = $db->prepare("SELECT * FROM user WHERE id = (SELECT MAX(id) FROM user)");
	$query->execute();
	
	return ($query->fetchAll(PDO::FETCH_ASSOC));
}

function registration_difference($db, $n1, $n2, $diff){
	$last_user_info = getLastUserInfo($db);
	$query = $db->prepare("SELECT t1.name AS :n1, t2.name AS :n2, TIMESTAMPDIFF(MINUTE, t1.date_create, t2.date_create) AS :diff FROM user t1 INNER JOIN user t2 ON t1.id+1 = t2.id WHERE t1.id = :last_id-1");
	$query->bindParam(":n1", $n1, PDO::PARAM_STR);
	$query->bindParam(":n2", $n2, PDO::PARAM_STR);
	$query->bindParam(":diff", $diff, PDO::PARAM_STR);
	$query->bindParam(":last_id", $last_user_info[0]['id'], PDO::PARAM_INT);
	
	$query->execute();
	return ($query->fetchAll(PDO::FETCH_ASSOC));
	
}