<?php

	require_once __DIR__.'/index_db.php';
	
	$db = getConnection('config/db.ini');
	
	$data['event'] = ( isset($_POST['event']) ) ? $_POST['event'] : null;
	
	$response = array(
		'data' => null,
		'success' => false
	);
	
	$event_data = getEventInfo($db, $data['event']);
	
	if(!$data['event']){
		$response['data'] = 'Please, type some new event at the beginning';
	}
	else{
		if(empty($event_data)){
			$response['success'] = true;
			$response['data'] = 'You have successfully added an event';
			addNewEvent($db, $data['event']);
		}
		else{
			$response['data'] = 'Such event already exists';
		}
	}
	
?>

<?php
	require_once __DIR__.'/header.php';
?>

<body>
	
	<form method="POST" action=<?= $_SERVER['SCRIPT_NAME'] ?> >
		<span>Add new event:</span><br />
		<input type="text" name="event" />
		<button name="event_button">Add</button><br />
	</form>
	
	<?php if(!empty($_POST)) : ?>
	
	<div id="event_log"><?= $response['data']; ?></div>
	
	<?php endif; ?>
	
       <?php
       require_once __DIR__.'/footer.php';
       ?>