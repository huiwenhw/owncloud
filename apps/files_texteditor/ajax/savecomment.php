<?php
	require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');
	OCP\JSON::checkAppEnabled('files_sharing');
	OCP\JSON::checkLoggedIn();

	$user = OC_User::getUser();
	//echo $user;

	$file = $_POST['file'];
	//$filepath = '/' . $user . '/files/Shared/' . $file;

	// Get file owner 
	$stmt = OCP\DB::prepare('SELECT uid_owner, source FROM *PREFIX*sharing WHERE target LIKE ?');
	$result = $stmt->execute(array("%".$file."%"));
	while($row = $result->fetchRow()) {
		$owner = $row['uid_owner'];
		$source = $row['source'];
	}

	// TODO: timestamp T.T 
	//$date = new DateTime();
	//echo $date->getTimestamp();

	// Save comment into table 
	$stmt = OCP\DB::prepare("INSERT into oc_comments (uid, uid_file_owner, comment_text, file) values (?,?,?,?)");
	$stmt->execute(array($user, $owner, $_POST['content'], $source));

	$last_id_query = OCP\DB::prepare('SELECT LAST_INSERT_ID() as commentid');
	$result = $last_id_query->execute();				
	while($row = $result->fetchRow()) {
		$commentid = $row['commentid'];
	}

	echo json_encode(array('user' => $user, 'commentid' => $commentid));
?>