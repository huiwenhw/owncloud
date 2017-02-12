<?php
	OCP\JSON::checkLoggedIn();
	// Get file owner & comment owner 
	$commentid = $_POST['commentid'];
	$stmt = OCP\DB::prepare('SELECT uid, uid_file_owner FROM oc_comments WHERE commentid = ?');
	$result = $stmt->execute(array($commentid));
	while($row = $result->fetchRow()) {
		$uid = $row['uid'];
		$fileowner = $row['uid_file_owner'];
	}

	// remove from db only if its user || owner 
	$user = OC_User::getUser();
	if($user == $uid || $user == $fileowner) {
		$stmt = OCP\DB::prepare('DELETE FROM oc_comments WHERE commentid = ? LIMIT 1');
		$result = $stmt->execute(array($commentid));
		echo json_encode(array('uid' => $uid, 'file_owner' => $fileowner, 'result' => "success" ));
	} else {
		echo json_encode(array('uid' => $uid, 'file_owner' => $fileowner, 'result' => "failed"));
	}
?>