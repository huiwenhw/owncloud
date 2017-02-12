<?php
	require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');
	OCP\JSON::checkAppEnabled('files_sharing');
	OCP\JSON::checkLoggedIn();

	// Get source file 
	$file = $_POST['file'];
	$stmt = OCP\DB::prepare('SELECT source FROM *PREFIX*sharing WHERE target LIKE ?');
	$result = $stmt->execute(array("%".$file."%"));
	while($row = $result->fetchRow()) {
		$source = $row['source'];
	}

	$user = OC_User::getUser();
	$stmt = OCP\DB::prepare('SELECT comment_permissions FROM *PREFIX*sharing WHERE source LIKE ? AND (uid_owner = ? OR uid_shared_with = ?)');
	$result = $stmt->execute(array($source, $user, $user));
	while($row = $result->fetchRow()) {
		$perm = $row['comment_permissions'];
	}

	if($perm) {
		echo json_encode(array('cancomment' => 'success'));
	} else {
		echo json_encode(array('cancomment' => 'fail'));
	}
?>