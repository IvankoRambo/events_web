<?php

require_once __DIR__.'/index_db.php';

$db = getConnection('config/db.ini');

$data['user_name'] = ( isset($_POST['user_name']) ) ? $_POST['user_name'] : null;
$data['email'] = ( isset($_POST['email']) ) ? $_POST['email'] : null;
$data['password'] = ( isset($_POST['password']) ) ? $_POST['password'] : null;
$data['is_active'] = ( isset($_POST['is_active']) ) ? $_POST['is_active'] : null;

$response = array(
	'data' => null,
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
	}
	else{
		$response['data'] = 'User with such email already exists in the system';
	}
}

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
	<?php endif; ?>
	
	<?php if($response['success']) : ?>
	<form method="POST">
		<button name="b_b">Back to the registation</button>
	</form>
	<?php endif; ?>
	
	<br />
	
	<form id="exit" method="POST" action="auth.php">
        <button name="exit">Exit</button>
    </form>
	
</body>