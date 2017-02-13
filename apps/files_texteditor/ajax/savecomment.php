<?php
	require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');
	OCP\JSON::checkAppEnabled('files_sharing');
	OCP\JSON::checkLoggedIn();

	$user = OC_User::getUser();
	//echo $user;

	$file = $_POST['file'];
	$path = '/'. $user . '/files/' . $file;
	$fileuser = '';

	// If file is not shared 
	$stmt = OCP\DB::prepare('SELECT user FROM oc_fscache WHERE path = ? AND name = ? LIMIT 1');
	$result = $stmt->execute(array($path, $file));
	while($row = $result->fetchRow()) {
		$fileuser = $row['user'];
	}

	if($fileuser == $user) {	// user's own file & not shared 
		$source = $path;
		$owner = $user;
	} else {
		// Get file owner 
		$stmt = OCP\DB::prepare('SELECT uid_owner, source FROM *PREFIX*sharing WHERE target LIKE ?');
		$result = $stmt->execute(array("%".$file."%"));
		while($row = $result->fetchRow()) {
			$owner = $row['uid_owner'];
			$source = $row['source'];
		}
	}

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