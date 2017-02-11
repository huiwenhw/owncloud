<?php
	require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');
	OCP\JSON::checkAppEnabled('files_sharing');
	OCP\JSON::checkLoggedIn();

	$user = OC_User::getUser();
	$file = $_POST['file'];

	$stmt = OCP\DB::prepare('SELECT source FROM *PREFIX*sharing WHERE target LIKE ?');
	$result = $stmt->execute(array("%B test file%"));
	while($row = $result->fetchRow()) {
		$source = $row['source'];
	}

	$data = array();
	$stmt = OCP\DB::prepare('SELECT uid,comment_text FROM oc_comments WHERE file = ?');
	$result = $stmt->execute(array($source));
	while($row = $result->fetchRow()) {
		$user = $row['uid'];
		$content = $row['comment_text'];
		array_push($data, array($user, $content));
	}

	echo json_encode($data);
?>