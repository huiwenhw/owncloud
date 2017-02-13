<?php
	require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');
	OCP\JSON::checkAppEnabled('files_sharing');
	OCP\JSON::checkLoggedIn();

	$user = OC_User::getUser();
	$file = $_POST['file'];
	$path = '/'. $user . '/files/' . $file;
	$fileuser = '';
	//echo $file;

	// If file is not shared 
	$stmt = OCP\DB::prepare('SELECT user FROM oc_fscache WHERE path = ? AND name = ? LIMIT 1');
	$result = $stmt->execute(array($path, $file));
	while($row = $result->fetchRow()) {
		$fileuser = $row['user'];
	}

	if($fileuser == $user) {	// user's own file & not shared 
		$source = $path;
	} else {
		$stmt = OCP\DB::prepare('SELECT source FROM *PREFIX*sharing WHERE target LIKE ?');
		$result = $stmt->execute(array("%".$file."%"));
		while($row = $result->fetchRow()) {
			$source = $row['source'];
		}
	}

	$data = array();
	$stmt = OCP\DB::prepare('SELECT uid,comment_text,commentid FROM oc_comments WHERE file = ? ORDER BY commentid DESC');
	$result = $stmt->execute(array($source));
	while($row = $result->fetchRow()) {
		$user = $row['uid'];
		$content = $row['comment_text'];
		$commentid = $row['commentid'];
		array_push($data, array('user' => $user, 'commentid' => $commentid, 'content' => $content));
	}

	echo json_encode($data);
?>