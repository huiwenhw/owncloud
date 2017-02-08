<?php
	$data = json_decode(stripslashes($_POST['data']));

	foreach($data as $user){
		$stmt = OCP\DB::prepare('SELECT email FROM *PREFIX*users WHERE uid =?');
		$result = $stmt->execute(array($user));
	
		while($row = $result->fetchRow()) {
			$var = $row['email'];
			echo $var;
		}

		$to = $var;
		$subj = "User " . OC_User::getUser() . " invited you to comment on a file";
		$message = "Hello";
		mail($to, $subj, $message);

		echo $user;
	}
?>