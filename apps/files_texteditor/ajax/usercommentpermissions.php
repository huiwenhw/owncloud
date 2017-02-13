<?php
	require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');
	OCP\JSON::checkAppEnabled('files_sharing');
	OCP\JSON::checkLoggedIn();

	$user = OC_User::getUser();
	$file = $_POST['file'];
	$path = '/'. $user . '/files/' . $file;
	$fileuser = '';
	$ownfileperm = '';
	$perm = '';
	$source = '';

	// If file is not shared 
	$stmt = OCP\DB::prepare('SELECT user FROM oc_fscache WHERE path = ? AND name = ? LIMIT 1');
	$result = $stmt->execute(array($path, $file));
	while($row = $result->fetchRow()) {
		$fileuser = $row['user'];
	}

	if($fileuser == $user) {
		$ownfileperm = 1;
	} else {
		// Get source file 
		$stmt = OCP\DB::prepare('SELECT source FROM *PREFIX*sharing WHERE target LIKE ?');
		$result = $stmt->execute(array("%".$file."%"));
		while($row = $result->fetchRow()) {
			$source = $row['source'];
		}

		// If file is shared 
		$stmt = OCP\DB::prepare('SELECT comment_permissions FROM *PREFIX*sharing WHERE source LIKE ? AND (uid_owner = ? OR uid_shared_with = ?)');
		$result = $stmt->execute(array($source, $user, $user));
		while($row = $result->fetchRow()) {
			$perm = $row['comment_permissions'];
		}	
	}

	if($perm || $ownfileperm) {
		echo json_encode(array('cancomment' => 'success', 'perm' => $perm, 'ownfileperm' => $ownfileperm));
	} else {
		echo json_encode(array('cancomment' => 'fail', 'perm' => $perm, 'ownfileperm' => $ownfileperm));
	}
?>