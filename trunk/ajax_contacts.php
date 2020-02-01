<?
	$BF = "";
	$NON_HTML_PAGE = true;
	include('_lib.php');

	if(@$_POST['postType'] == "quickInsert") {
	
		//$q lets first see if this is already added
		$insertcheck = fetch_database_query("SELECT ID FROM Invites WHERE idShow='".$_POST['idShow']."' AND idContact='".$_POST['idContact']."' AND idUser='".$_POST['idUser']."'","Checking for exsiting");
	
		if ($insertcheck['ID'] == "") { 
			$q = "INSERT INTO Invites SET 
				bType='". $_POST['bType'] ."',
				idShow='". $_POST['idShow'] ."',
				idContact='". $_POST['idContact'] ."',
				idUser='". $_POST['idUser'] ."',
				idInviteStatus='". $_POST['idInviteStatus'] ."'
				";
			database_query($q,"insert");
		}
	}

	if(@$_REQUEST['postType'] == "quickDelete") {
	
		$q = "DELETE FROM Invites WHERE
			idShow='". $_REQUEST['idShow'] ."' AND idContact='". $_REQUEST['idContact'] ."' AND idUser='". $_REQUEST['idUser'] ."' AND idInviteStatus='". $_REQUEST['idInviteStatus'] ."'
			";
		database_query($q,"delete");
	}
	
	echo "1";
?>