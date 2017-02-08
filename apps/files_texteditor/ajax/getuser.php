<?php
	$stmt = OCP\DB::prepare('SELECT uid FROM *PREFIX*users');
	$result = $stmt->execute();

	// Setting current user first 
	$arr = array(OC_User::getUser());

	while($row = $result->fetchRow()) {
		$var = $row['uid'];
		array_push($arr, $var);
	}

	echo json_encode($arr);
?>