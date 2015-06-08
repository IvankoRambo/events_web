<?php

	require_once __DIR__.'/index_db.php';
	$db = getConnection('config/db.ini');
	$email = $_GET['email'];
	$user_info = getUserInfo($db, $email);
	$count = getCountViaUserId($db, $user_info[0]['id']);
	$events = getAllEvents($db);
	
	
	$response = array(
		'data' => null
	);
	
	foreach($_POST as $key=>$value){
		if(strpos($key, 'event') !== false){
			$data[$key] = $value;
		}
	}
	
	if(!isset($data)){
		$response['data'] = 'You have to choose at least one event';
	}
	else{
		foreach($data as $key=>$value){
			$event_info = getEventInfo($db, $value);
			
			if(!isUserHasEvent($db, $user_info[0]['id'], $event_info[0]['id_event'])){
				$response['data'][] = 'You have been successfully added event '.$value;
				addEventForUser($db, $user_info[0]['id'], $event_info[0]['id_event']);
 			}
			else{
				$response['data'][] = 'You have already added event '.$value.' in your list';
			}
		}
	}
	
	$my_event_list = getUserEvents($db, $user_info[0]['id'], 'me');
	$others_event_list = getUserEvents($db, $user_info[0]['id'], 'others');
	
?>

<?php
	require_once __DIR__.'/header.php';
?>

<body>
	
	<div id="user_info">
		<span id="user_name">Your name: <?= $user_info[0]['name']; ?></span><br />
		<span id="user_email">Your email: <?= $email; ?></span><br />
		<span id="user_date">The date of your registration: <?= $user_info[0]['date_create'] ?></span><br />
		<span id="user_count">You have logged in <?= $count[0]['count'] ?> times</span>
	</div>
	
	<br />
	
	<div id="event_list">
		<span>Choose the events you'd like to visit.</span><br />
		
		<form method="POST">
			<?php for($i = 0; $i < count($events); $i++) : ?>
				<input type="checkbox" name="event<?= $i ?>" value="<?= $events[$i]['event'] ?>" /><?= $events[$i]['event']; ?><br /> 
			<?php endfor; ?>
			<button name="e_b">Add events to my list</button>
		</form>
		
	</div>
	
	<br />
	
	
	<?php if(!empty($_POST)) : ?>
		<div id="message_log">
			<?php if(!isset($data)) : ?>
				<span><?= $response['data']; ?></span><br />
			<?php else : ?>
				<?php for($i = 0; $i < count($response['data']); $i++) : ?>
					<span><?= $response['data'][$i]; ?></span><br />
				<?php endfor; ?>
			<?php endif; ?>
		</div>	
	<?php endif; ?>
	
	
	<br />
	
	<?php if(!empty($my_event_list)) : ?>
	<div id="my_events">
		<span>Events you subscribed to visit:</span><br />
		<ul>
			<?php for($i = 0; $i < count($my_event_list); $i++) : ?>
				<li><?= $my_event_list[$i]['event']; ?></li>
			<?php endfor; ?>
		</ul>
	</div>
	<?php endif; ?>
	
	<br />
	
	
	<?php if(!empty($others_event_list)) : ?>
		<div id="others_events">
			<span>Events other users subscribed to visit:</span><br />
			<ul>
				<?php for($i = 1; $i < count($others_event_list); $i++) : ?>
					<?php if($i == 1) : ?>
						<span style="color: red;"><?= $others_event_list[$i-1]['name']; ?></span><br />
						<li><?= $others_event_list[$i-1]['event']; ?></li>
					<?php endif; ?>
					<?php if($others_event_list[$i-1]['name'] != $others_event_list[$i]['name']) : ?>
						<span style="color: red;"><?= $others_event_list[$i]['name']; ?></span><br />
					<?php endif; ?>
					<li><?= $others_event_list[$i]['event']; ?></li>
				<?php endfor; ?>
			</ul>
		</div>
	<?php endif; ?>
	
	
	<form id="exit" method="POST" action="auth.php">
        <button name="exit">Exit</button>
    </form>

       <?php
       require_once __DIR__.'/footer.php';
       ?>