<?php
	$stmt = OCP\DB::prepare('SELECT popup_show FROM *PREFIX*users WHERE uid = ?');
	$result = $stmt->execute(array(OC_User::getUser()));
	
	while($row = $result->fetchRow()) {
		$var = $row['popup_show'];
		echo 'show modal var: ' . $var;
	}
?>