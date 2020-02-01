<?	
	include('applevip-conf.php');
	$mysqli_connection = mysqli_connect($host, $user, $pass);
	mysqli_select_db($mysqli_connection, $db);
	unset($host, $user, $pass, $db);
	if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
		$q = "SELECT * FROM Users WHERE ID=". $_REQUEST['id'];
		
		$info = mysqli_fetch_assoc(mysqli_query($mysqli_connection,$q));
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Expires" content="Fri, 26 Mar 1999 23:59:59 GMT">
	<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
	<meta http-equiv="pragma" content="no-cache">

	<meta name="Author" content="techIT Solutions LLC">
	<meta name="Keywords" content="techIT Solutions">
	<title>Mini Popup</title>
	<style>
	body,html { border: 0; padding: 0; margin: 0; }
	body { background: white; padding: 5px; }
	</style>
</head>
<body>
	<div>First Name: <?=$info['chrFirst']?></div>
	<div>Last Name: <?=$info['chrLast']?></div>
	<div>Email Address: <?=$info['chrEmail']?></div>
</body>
</html>