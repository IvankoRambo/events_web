<?php
	require_once __DIR__.'/index_db.php';
	require_once __DIR__.'/redis_work.php';
	session_start();
	$db = getConnection('config/db.ini');
	$count = 1;
	
	$data['email'] = ( isset($_POST['email']) ) ? $_POST['email'] : null;	
	$data['password'] = ( isset($_POST['password']) ) ? $_POST['password'] : null;
	
	$response = array(
	'data' => null,
	'success' => false
	);
	
	if(!($data['email']) || !($data['password'])){
		$response['data'] = 'You ought to fill all fields';
	}
	else{
		if(isEmailExists($db, $data['email'])){
			if(isRightPassword($db, $data['email'], $data['password'])){
				if(isUserBanned($data['email'])){
					
						$response['data'] = 'You are banned user';
				
				}
				else{
					
				$response['data'] = 'You\'ve been successfully logged in,';
				$_SESSION['email'] = $data['email'];
				$_SESSION['password'] = $data['password'];
				if(!isset($_SESSION['count_'.$data['email']]))	$_SESSION['count_'.$data['email']] = $count;
				else $_SESSION['count_'.$data['email']]++;
				$user_array = getUserInfo($db, $data['email']);
				addUserCount($db, $user_array[0]['id'], $_SESSION['count_'.$data['email']]);
				$response['success'] = true;
					
					
				}
			}
			else{
				$response['data'] = 'You typed the wrong password. Wait 10 seconds and try again';
				redis_expire($data['email']);
			}
		}
		else{
			$response['data'] = 'You have to register at the beggining.';
		}
	}

?>

<?php
	require_once __DIR__.'/header.php';
?>

    <body>
		
		<?php if(!empty($_GET)) : ?>
			<span>Sorry, but you are banned, guy. Your email is <?= $_GET['email']; ?></span><br />
		<?php endif; ?>
		
		<?php if(empty($_GET)) : ?>
			
        <form id="auth" method="POST" action=<?= $_SERVER["SCRIPT_NAME"] ?> >
            <span>Your email:</span><br />
            <input type="text" id="email" name="email" /><br>
            <span>Your password:</span><br />
            <input type="password" id="password" name="password" /><br />
            <button name="button">Log in</button>
        </form><br />
        
        <?php endif; ?>
        
        
		<?php if(!empty($_POST) && empty($_GET)) : ?>
			<div id="message_log"><?= $response['data']; ?></div>
			<?php if($response['success']) : ?>
				<?php $user_data = getUserInfo($db, $data['email']); ?>
				<a href="user_info.php?email=<?= $data['email']; ?>" ><?= $user_data[0]['name']; ?></a>
			<?php endif; ?>
		<?php endif; ?>
		
        <br />
        
        <?php if(empty($_GET)) : ?>
        <form method="POST" action="register.php" >
        	<button name="register">Register</button>
        </form>
        <?php endif; ?>
        
        <br />
       
        <form id="exit" method="POST" action=<?= $_SERVER['SCRIPT_NAME']; ?>>
                <button name="exit">Exit</button>
        </form>
  
       <?php
       require_once __DIR__.'/footer.php';
       ?>