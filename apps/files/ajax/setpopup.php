<?php
	$stmt = OCP\DB::prepare('UPDATE *PREFIX*users SET popup_show = ? WHERE uid =?');
	$stmt->execute(array(0, OC_User::getUser()));
	echo 'done';
?>