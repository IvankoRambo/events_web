<?php

require_once __DIR__.'/index_db.php';
require_once __DIR__.'/redis_work.php';

$db = getConnection('config/db.ini');

$data['user_name'] = ( isset($_POST['user_name']) ) ? $_POST['user_name'] : null;
$data['email'] = ( isset($_POST['email']) ) ? $_POST['email'] : null;
$data['password'] = ( isset($_POST['password']) ) ? $_POST['password'] : null;
$data['is_active'] = ( isset($_POST['is_active']) ) ? $_POST['is_active'] : null;

$response = array(
	'data' => null,
	'additional' => null,
	'success' => false
);


if(!$data['user_name'] || !$data['email'] || !$data['password'] || !$data['is_active']){
	$response['data'] = 'You ought to fill all fields!';	
}
else{
	$user_data = getUserInfo($db, $data['email']);
	if(empty($user_data)){
		$response['success'] = true;
		createUser($db, $data['user_name'], $data['email'], $data['password'], date('Y-m-d H:i:s'), (int)$data['is_active']);
		$response['data'] = 'You have been successfully registered';
		$diff_info = registration_difference($db, 'name1', 'name2', 'minute_diff');
		$response['additional'] = ( $diff_info['0']['minute_diff'] == 0 ) ? 'The previous user registered less than minute ago. We a pretty popular now.' : "The previous user registered {$diff_info['0']['minute_diff']} minutes ago";
	}
	else{
		$response['data'] = 'User with such email already exists in the system';
	}
}

	$last_user_info = getLastUserInfo($db);
	$last_names = showLastTenUsers($db, $last_user_info[0]['id']);

?>

<?php
	require_once __DIR__.'/header.php';
?>

<body>
	
	<?php if(!$response['success']) : ?>
	<form method="POST" action=<?= $_SERVER['SCRIPT_NAME'] ?> >
		<span>Your name:</span><br />
		<input type="text" name="user_name"  /><br />
		<span>Your mail:</span><br />
		<input type="text" name="email" /><br />
		<span>Your password:</span><br />
		<input type="text" name="password" /><br />
		<span>Are you active user? (1 or 0)</span><br />
		<input type="text" name="is_active" /><br />
		<button name="r_b">Register</button>
	</form>
	<?php endif; ?>
	
	<?php if(!empty($_POST)) : ?>
		<div id="reg_info"><?= $response['data']; ?></div>
		<?php if(!is_null($response['additional'])) : ?>
			<span><?= $response['additional']; ?></span><br />
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if($response['success']) : ?>
	<form method="POST">
		<button name="b_b">Back to the registation</button>
	</form>
	<?php endif; ?>
	
	<br />
	
	<?php if(!empty($last_names)) : ?>
		
		<span>Last ten registered users: </span><br />
		<ul>
			<?php for($i = 0; $i < count($last_names); $i++) : ?>
				<li><?= $last_names[$i]; ?></li>
			<?php endfor; ?>
		</ul>
		
		<br />
	<?php endif; ?>
	
	<form id="exit" method="POST" action="auth.php">
        <button name="exit">Exit</button>
    </form>
	
       <?php
       require_once __DIR__.'/footer.php';
       ?>