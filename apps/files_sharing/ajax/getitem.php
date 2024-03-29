<?php
require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');
 
OCP\JSON::checkAppEnabled('files_sharing');
OCP\JSON::checkLoggedIn();

$item = array();
$userDirectory = '/'.OCP\USER::getUser().'/files';
$source = $userDirectory.$_GET['item'];
$path = $source;
// Search for item and shared parent folders
while ($path != $userDirectory) {
	if ($rows = OC_Share::getMySharedItem($path)) {
		for ($i = 0; $i < count($rows); $i++) {
			$uid_shared_with = $rows[$i]['uid_shared_with'];
			if ($uid_shared_with == OC_Share::PUBLICLINK && !isset($item['privateLink'])) {
				$token = OC_Share::getTokenFromSource($path);
				if ($path == $source) {
					$item['privateLink'] = $token;
				} else {
					// If in parent folder, include a path parameter to get direct access to file
					$item['privateLink'] = $token.'&path='.str_replace('%2F', '/', str_replace('+', '%20', urlencode(substr($source, strlen($path)))));;
				}
			} else {
				// Check if uid_shared_with is a group
				if (($pos = strpos($uid_shared_with, '@')) !== false) {
					$gid = substr($uid_shared_with, $pos + 1);
					// Include users in the group so the users can be removed from the list of people to share with
					if ($path == $source) {
						$group = array(array('gid' => $gid, 'permissions' => $rows[$i]['permissions'], 'commentspermissions' => $rows[$i]['comment_permissions'], 'users' => OC_Group::usersInGroup($gid), 'parentFolder' => false));
					} else {
						$group = array(array('gid' => $gid, 'permissions' => $rows[$i]['permissions'], 'commentspermissions' => $rows[$i]['comment_permissions'], 'users' => OC_Group::usersInGroup($gid), 'parentFolder' => basename($path)));
					}
					if (!isset($item['groups'])) {
						$item['groups'] = $group;
					} else if (is_array($item['groups'])) {
						$gidExists = false;
						$currentGroups = $item['groups'];
						// Check if the group is already included
						foreach ($currentGroups as $g) {
							if ($g['gid'] == $gid) {
								$gidExists = true;
							}
						}
						if (!$gidExists) {
							$item['groups'] = array_merge($item['groups'], $group);
						}
					}
				} else {
					// TODO: change permissions to comment_permissions
					if ($path == $source) {
						$user = array(array('uid' => $uid_shared_with, 'permissions' => $rows[$i]['permissions'], 'commentspermissions' => $rows[$i]['comment_permissions'], 'parentFolder' => false));
					} else {
						$user = array(array('uid' => $uid_shared_with, 'permissions' => $rows[$i]['permissions'], 'commentspermissions' => $rows[$i]['comment_permissions'], 'parentFolder' => basename($path)));
					}
					if (!isset($item['users'])) {
						$item['users'] = $user;
					} else if (is_array($item['users'])) {
						$item['users'] = array_merge($item['users'], $user);
					}
				}
			}
		}
	}
	$path = dirname($path);
}

OCP\JSON::success(array('data' => $item));

?>
