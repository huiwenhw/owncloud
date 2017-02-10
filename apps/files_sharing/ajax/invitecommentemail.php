<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('files_sharing');
$user = OCP\USER::getUser();
// TODO translations
//$type = (strpos($_POST['file'], '.') === false) ? 'folder' : 'file';
$subject = $user .' invited you to comment on a file.';
$text = 'Hello! '. $user . ' invited you to comment on the file: "' . $_POST['file'] . '". It will be in your shared folder when you log in!';
$fromaddress = OCP\Config::getUserValue($user, 'settings', 'email', 'sharing-noreply@'.OCP\Util::getServerHost());
echo $fromaddress . " ";

$user = $_POST['touser'];
$stmt = OCP\DB::prepare('SELECT email FROM *PREFIX*users WHERE uid =?');
$result = $stmt->execute(array($user));

while($row = $result->fetchRow()) {
	$var = $row['email'];
}
echo $user;
echo $var;

$toaddress = $var;
OCP\Util::sendMail($toaddress, $_POST['touser'], $subject, $text, $fromaddress, $user);

?>
