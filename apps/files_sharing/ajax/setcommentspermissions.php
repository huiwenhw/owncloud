<?php
require_once(OC::$APPSROOT . '/apps/files_sharing/lib_share.php');

file_put_contents("php://stderr", print_r('comment_permissions_called', TRUE));

OCP\JSON::checkAppEnabled('files_sharing');
OCP\JSON::checkLoggedIn();

$source = '/'.OCP\USER::getUser().'/files'.$_POST['source'];
$uid_shared_with = $_POST['uid_shared_with'];
$permissions = $_POST['permissions'];
OC_Share::setCommentsPermissions($source, $uid_shared_with, $permissions);

OCP\JSON::success();

?>