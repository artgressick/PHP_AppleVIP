<?
	require('applevip-conf.php');

	$connection = @mysql_connect($host, $user, $pass);
	mysql_select_db($db, $connection);
	unset($host, $user, $pass, $db);
	
	if($_REQUEST['postType'] == "delete") {
		$total = 0;
		$q = "UPDATE ". $_REQUEST['tbl'] ." SET bDeleted=1 WHERE ID=".$_REQUEST['id'];
		if(mysql_query($q)) { $total++; }

		$q = "INSERT INTO Audit SET idUser=".$_REQUEST['idUser'].", idRecord=".$_REQUEST['id'].", chrTableName='". $_REQUEST['tbl'] ."', chrColumnName='bDeleted', dtDatetime=now(), txtOldValue='0', txtNewValue='1', idType=3"; 
		if(mysql_query($q)) { $total += 2; }
  		echo $total;
	}
	if($_REQUEST['postType'] == "permdelete") {
		$total = 0;
		if(is_numeric($_REQUEST['id'])) {
			if($_REQUEST['tbl'] == 'SponsorsByUser') {
				$q = "DELETE FROM SponsorsByUser WHERE idSponsor=".$_REQUEST['id']; 
				if(mysql_query($q)) { $total++; }
			} else {
				$q = "DELETE FROM ". $_REQUEST['tbl'] ." WHERE ID=".$_REQUEST['id']; 
				if(mysql_query($q)) { $total++; }
			}
		}

	}
	if($_REQUEST['postType'] == "updateguests") {
		$total = 0;
		$q = "UPDATE Invites SET intGuests=" . $_REQUEST['intGuests'] ." WHERE ID=" . $_REQUEST['id'];
		if(mysql_query($q)) { $total++; }
		echo $total;
	}

	if($_REQUEST['postType'] == "updateinvitetype") {
		$total = 0;
		$q = "UPDATE Invites SET bType=" . $_REQUEST['bType'] ." WHERE ID=" . $_REQUEST['id'];
		if(mysql_query($q)) { $total++; }
		echo $total;
	}
	
	
	if($_REQUEST['postType'] == "updatestatus") {
		$total = 0;
		$q = "UPDATE Invites SET idInviteStatus=".$_REQUEST['idStatus']." WHERE ID=". $_REQUEST['id'];
		if(mysql_query($q)) { $total++; }
		echo $total;		
	}

	if($_REQUEST['postType'] == "updatecontactstatus") {
		$total = 0;
		$q = "UPDATE Invites SET idStatus=".$_REQUEST['idStatus']." WHERE ID=". $_REQUEST['id'];
		if(mysql_query($q)) { $total++; }
		echo $total;		
	}
	
	
?>
