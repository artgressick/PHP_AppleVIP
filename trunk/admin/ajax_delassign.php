<?
	require('applevip-conf.php');

	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	if($_REQUEST['postType'] == "delete") {
//		$q = "DELETE FROM ". $_REQUEST['tbl'] ." WHERE idUser=".$_REQUEST['id'];
//		mysql_query($q);
	}
?>
