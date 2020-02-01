<?	
	$BF = '';
	$active = "invites";
	$title = 'Invites Page';
	require($BF. '_lib.php');
	
	if($_SESSION['idRight'] == 4) {
		header('Location: '. $BF .'checkin.php');
		die();
	}
	
	$_SESSION['inviteRefer'] = $_SERVER['QUERY_STRING'];
	if (!isset($_REQUEST['idShow']) && !isset($_SESSION['idShow'])) { $_REQUEST['idShow'] = ""; }
	else if (isset($_SESSION['idShow']) && !isset($_REQUEST['idShow'])) { 
	$tmp = fetch_database_query("SELECT DISTINCT Shows.ID,Shows.chrName 
		FROM Shows
		JOIN SponsorsbyShow ON SponsorsbyShow.idShow=Shows.ID AND !SponsorsbyShow.bDeleted 
		LEFT JOIN SponsorsByUser ON SponsorsbyShow.idUser=SponsorsByUser.idSponsor
		WHERE !Shows.bDeleted AND Shows.idStatus IN (2,3) AND (SponsorsbyShow.idUser = ".$_SESSION['idUser']." OR SponsorsByUser.idUser = ".$_SESSION['idUser'].") AND Shows.ID='".$_SESSION['idShow']."'
		ORDER BY Shows.dBegin DESC, Shows.chrName","Does this user have access to this show?");
		if($tmp['ID'] != '' && $tmp['ID'] == $_SESSION['idShow']) {	$_REQUEST['idShow'] = $_SESSION['idShow']; }
	}
	

	if(!isset($_REQUEST['idShow']) || !is_numeric($_REQUEST['idShow'])) { 
		$firstShow = fetch_database_query("
		SELECT DISTINCT Shows.ID,Shows.chrName 
		FROM Shows
		JOIN SponsorsbyShow ON SponsorsbyShow.idShow=Shows.ID AND !SponsorsbyShow.bDeleted 
		LEFT JOIN SponsorsByUser ON SponsorsbyShow.idUser=SponsorsByUser.idSponsor
		WHERE !Shows.bDeleted AND Shows.idStatus IN (2,3) AND (SponsorsbyShow.idUser = ".$_SESSION['idUser']." OR SponsorsByUser.idUser = ".$_SESSION['idUser'].")
		ORDER BY Shows.dBegin DESC, Shows.chrName","Getting First Show");
		if ($firstShow != "") {
			header('Location: invites.php?idShow='. $firstShow['ID']);
			die();
		} else {
			header('Location: invites.php?idShow=0');
			die();
		}
		
	}
	
	if(!isset($_REQUEST['idShow']) || !is_numeric($_REQUEST['idShow']) || $_REQUEST['idShow'] == "") {
		$_REQUEST['idShow'] = 0;
	}
	if(isset($_SESSION['postUser']) && is_numeric($_SESSION['postUser'])) {
		$_POST['idUser'] = $_SESSION['postUser'];
		unset($_SESSION['postUser']);
	} else if (isset($_REQUEST['d'])) {
		parse_str(base64_decode($_REQUEST['d']),$info);
		if ( $info['key'] != $_SESSION['idUser'] ) { ErrorPage(); }
		$_POST['idUser'] = $info['to'];
	}
	$tmpStatus = database_query("SELECT ID, chrStatus FROM iStatus WHERE !bDeleted ORDER BY dOrder","Getting Status");
	$iStatus = array();
	while($row = mysqli_fetch_assoc($tmpStatus)) {
		$iStatus[$row['ID']] = $row['chrStatus'];
	}
	
	if(isset($_POST['Save']) && $_POST['Save'] == 'Save') {
		$q = "SELECT Invites.ID as ID, Invites.intGuests, Contacts.chrFirst, Contacts.chrLast, Invites.idStatus
			FROM Invites 
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."' AND idShow='". $_REQUEST['idShow'] ."' ORDER BY idStatus, chrLast, chrFirst";
			
		$results = database_query($q,"Getting all contacts");

		$q = "SELECT SponsorsbyShow.ID, chrStatus, idReviewStatus, intInvites
			 FROM SponsorsbyShow
			 LEFT JOIN ReviewStatus ON ReviewStatus.ID=idReviewStatus
			 WHERE idShow=". $_REQUEST['idShow'] ." AND idUser='".$_POST['idUser'] ."'";

		$sbs = fetch_database_query($q, "Getting Invite Page Status");
		$maxinvites = $sbs['intInvites'];
		$invites = 0;
		while($row = mysqli_fetch_assoc($results)) {
			$q = '';
			if(isset($_POST['idStatus'.$row['ID']])) {
				if(in_array($_POST['idStatus'.$row['ID']],array(2,5,6,8,9))) {  // is of Invite status
					if($invites < $maxinvites) {  // have we exceeded the max invites (No)
						$invites++;
						if(($_POST['intGuests'.$row['ID']] + $invites) <= $maxinvites) { // do we have enough room for the guests?
							 $invites = ($invites + $_POST['intGuests'.$row['ID']]);
						} else { // Not enough room for guests
							$guests = $maxinvites - $invites;
							$invites = ($invites + $guests);
							$_SESSION['errorMessages'][] = "You do not have enough allocations to allow ".$row['chrFirst']." ".$row['chrLast']." to have ".$_POST['intGuests'.$row['ID']].". Amount changed to ".$guests.".";
							$_POST['intGuests'.$row['ID']] = $guests;
						}
						$q = "UPDATE Invites SET idStatus='".$_POST['idStatus'.$row['ID']]."', intGuests='".$_POST['intGuests'.$row['ID']]."' WHERE ID=".$row['ID'];
						if($_POST['idStatus'.$row['ID']] != $row['idStatus']) {
							$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$row['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus'.$row['ID']]].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
						}
					} else {  // have we exceeded the max invites (Yes)
						$_SESSION['errorMessages'][] = "You do not have enough allocations to allow ".$row['chrFirst']." ".$row['chrLast']." to be invited. Status changed to Wait List";
						$q = "UPDATE Invites SET idStatus='3', intGuests='0' WHERE ID=".$row['ID'];
						if($row['idStatus'] != 3) {
							$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$row['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[3].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
						}
					}
				} else { //is waitlist, deleted, or regrets
					if($_POST['idStatus'.$row['ID']] == 4) {
						$q = "UPDATE Invites SET bDeleted=1 WHERE ID=".$row['ID'];
						$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$row['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to Deleted<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
					} else {
						$q = "UPDATE Invites SET idStatus='".$_POST['idStatus'.$row['ID']]."', intGuests='0' WHERE ID=".$row['ID'];
					}
					if($_POST['idStatus'.$row['ID']] != $row['idStatus']) {
						$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$row['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus'.$row['ID']]].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
					}
				}
			} else { //Has not changed Status
//				$q = "DELETE FROM Invites WHERE ID=".$row['ID'];
			}
			if($q != '') { database_query($q,'Running Query'); }
			if(isset($_POST['bReview']) && $_POST['bReview'] == 1) {
				$q = "UPDATE SponsorsbyShow SET idReviewStatus='2'
				WHERE idUser=".$_POST['idUser']." AND idShow=".$_REQUEST['idShow']." AND idReviewStatus IN (1,4)";
				database_query($q,'Running Sent for Review');
			}
			
		} // end while
		$_SESSION['postUser'] = $_POST['idUser'];
		$moveTo = 'invites.php?'.$_SESSION['inviteRefer'];
		unset($_SESSION['inviteRefer']);
		header("Location: ".$moveTo);	
		die();
	} // end of $_POST['Save']
		

	if ($_SESSION['idRight'] != 3) {
		$q = "SELECT DISTINCT Users.ID, Users.chrFirst, Users.chrLast
			FROM Users 
			JOIN SponsorsbyShow ON SponsorsbyShow.idUser=Users.ID
			LEFT JOIN SponsorsByUser ON SponsorsByUser.idSponsor=Users.ID 
			WHERE !Users.bDeleted AND !SponsorsbyShow.bDeleted AND SponsorsbyShow.idShow='".$_REQUEST['idShow']."' AND (SponsorsByUser.idUser='". $_SESSION['idUser'] ."' OR SponsorsbyShow.idUser='". $_SESSION['idUser'] ."')
			ORDER BY chrFirst, chrLast";
	} else { 
		$q = "SELECT DISTINCT Users.ID, Users.chrFirst, Users.chrLast
			FROM Users 
			JOIN SponsorsbyShow ON SponsorsbyShow.idUser=Users.ID			
			LEFT JOIN SponsorsByUser ON SponsorsByUser.idSponsor=Users.ID
			WHERE !Users.bDeleted AND !SponsorsbyShow.bDeleted AND SponsorsbyShow.idShow='".$_REQUEST['idShow']."' AND SponsorsByUser.idUser='". $_SESSION['idUser'] ."'
			ORDER BY chrFirst, chrLast";	
	}			
	$invites = database_query($q,"Getting all Books");
	$num_results = mysqli_num_rows($invites);
	
	function DupInfo($chrFirst, $chrLast, $idUser, $id) { //Function to retrieve Duplicate E-mail Information
		
		$q = "SELECT Users.chrFirst AS chrUserFirst, Users.chrLast AS chrUserLast, Contacts.chrFirst AS chrContactFirst, Contacts.chrLast AS chrContactLast, iStatus.chrStatus
				FROM Invites
				JOIN Users ON Users.ID = Invites.idUser
				JOIN Contacts ON Contacts.ID = Invites.idContact
				JOIN iStatus ON Invites.idStatus=iStatus.ID
				WHERE !Invites.bDeleted AND Contacts.chrFirst=".$chrFirst." AND Contacts.chrLast=".$chrLast." AND Invites.idShow=".$_REQUEST['idShow']." AND Invites.idUser != ".$idUser;

		$confs = database_query($q,"getting conflicts");
		
		?>	<div style='display: none; position: absolute; padding: 3px; background: #EFE0E0; border: 1px solid black;' id='user<?=$id?>'>
			<div style='text-decoration: underline;'><strong>User Conflicts with the following</strong></div>
			<table cellspacing="0" cellpadding="0" class='conflicts'>
		 <? $count = 0;
			while($row = mysqli_fetch_assoc($confs)) { ?>
			<?=($count++ > 0 ? '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>' : '') // this is to add a space if there is more than one entry?>
				<tr><td><strong>User Name</strong>:</td><td><?=$row['chrUserFirst'] ." ". $row['chrUserLast']?></td></tr>
				<tr><td><strong>Contact Name</strong>:</td><td><?=$row['chrContactFirst'] ." ". $row['chrContactLast']?></td></tr>
				<tr><td><strong>Status</strong>:</td><td><?=$row['chrStatus']?></td></tr>
		<? }
		echo "</table></div>";
	}
	
	
	// Set Default User
	$DefaultUser = fetch_database_query($q,"Getting First User Listed");
	$MeCheck = fetch_database_query("SELECT ID,idUser FROM SponsorsbyShow WHERE !bDeleted AND idUser=". $_SESSION['idUser'] ." AND idShow=".$_REQUEST['idShow'],"Checks to see if you are a Sponsor");
	if (isset($MeCheck['ID']) && !isset($_POST['idUser']) ) { $_POST['idUser'] = $_SESSION['idUser']; } else if ( !isset($MeCheck['ID']) && !isset($_POST['idUser'])) { $_POST['idUser'] = $DefaultUser['ID'] ; }

	if(@$_REQUEST['idContact'] != "" && @$_REQUEST['idStatus'] != "") {
		database_query("UPDATE Invites SET idInviteStatus=". $_REQUEST['idStatus'] ." WHERE idContact=". $_REQUEST['idContact'] ." AND idShow=". $_REQUEST['idShow'] ." AND idUser=". $_POST['idUser'],"update status");
		header("Location: invites.php?idShow=". $_REQUEST['idShow']);
		die();
	}
	
	if(@$_REQUEST['idContact'] != "" && @$_REQUEST['intGuests'] != "") {
		database_query("UPDATE Invites SET intGuests=". $_REQUEST['intGuests'] ." WHERE idContact=". $_REQUEST['idContact'] ." AND idShow=". $_REQUEST['idShow'] ." AND idUser=". $_POST['idUser'],"update guests");
		header("Location: invites.php?idShow=". $_REQUEST['idShow']);
		die();
	}
	include($BF. 'includes/meta.php');
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast,chrFirst"; }
	
	if(isset($_REQUEST['idShow'])) {

		(isset($_POST['chrSearch']) ? $chrSearch = encode($_POST['chrSearch']) : $chrSearch = "" );
		$possiblebadge = ltrim(substr($chrSearch, 5, -3), '0');
	
		$q = "SELECT Invites.ID as idInvite, Invites.intGuests, Contacts.ID, Contacts.chrFirst, contacts.chrLast, Contacts.chrEmail, idStatus, Contacts.idUser, Contacts.chrCompany, Categories.chrCategory, 
				IF(Invites.bType IS NULL, Contacts.bType, Invites.bType) AS bType, Invites.idStatus,
				  (SELECT DupCheck.ID 
				  FROM Invites AS DupCheck 
				   JOIN Contacts AS DupContact ON DupCheck.idContact=DupContact.ID 
				  WHERE !DupCheck.bDeleted AND Contacts.chrFirst = DupContact.chrFirst AND Contacts.chrLast = DupContact.chrLast AND DupCheck.idShow=Invites.idShow AND DupCheck.idUser != Invites.idUser AND DupCheck.idStatus IN (2,3,4,5,6,7,8,9)
				  LIMIT 1 ) as idDuplicate
			FROM Contacts 
			LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
			JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $_REQUEST['idShow'] ."'
			JOIN iStatus AS i ON Invites.idStatus=i.ID
			WHERE !Invites.bDeleted AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."'".($chrSearch != '' ? " AND 
			((lower(Contacts.chrFirst) LIKE '%" . $chrSearch . "%' 
			OR lower(Contacts.chrLast) LIKE '%" . $chrSearch . "%' 
			OR lower(chrCompany) LIKE '%" . $chrSearch . "%')
			OR lower(concat(Contacts.chrFirst,' ',Contacts.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'
			OR (Contacts.ID = '" . $possiblebadge ."'))" : "")."
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		$invite_result = database_query($q,"Getting all contacts");
		$q = "SELECT Invites.ID as idInvite, Invites.intGuests, Contacts.ID, Contacts.chrFirst, contacts.chrLast, Contacts.chrEmail, idStatus, Contacts.idUser, Contacts.chrCompany, Categories.chrCategory, 
				IF(Invites.bType IS NULL, Contacts.bType, Invites.bType) AS bType, Invites.idStatus,
				  (SELECT DupCheck.ID 
				  FROM Invites AS DupCheck 
				   JOIN Contacts AS DupContact ON DupCheck.idContact=DupContact.ID 
				  WHERE !DupCheck.bDeleted AND Contacts.chrFirst = DupContact.chrFirst AND Contacts.chrLast = DupContact.chrLast AND DupCheck.idShow=Invites.idShow AND DupCheck.idUser != Invites.idUser AND DupCheck.idStatus IN (2,3,4,5,6,7,8,9)
				  LIMIT 1 ) as idDuplicate
			FROM Contacts 
			LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
			JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $_REQUEST['idShow'] ."'
			JOIN iStatus AS i ON Invites.idStatus=i.ID
			WHERE !Invites.bDeleted AND Invites.idStatus = 3 AND !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."'".($chrSearch != '' ? " AND 
			((lower(Contacts.chrFirst) LIKE '%" . $chrSearch . "%' 
			OR lower(Contacts.chrLast) LIKE '%" . $chrSearch . "%' 
			OR lower(concat(Contacts.chrFirst,' ',Contacts.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'
			OR lower(chrCompany) LIKE '%" . $chrSearch . "%')
			OR (Contacts.ID = '" . $possiblebadge ."'))" : "")."
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		$waitlist_result = database_query($q,"Getting all contacts");
		$q = "SELECT Invites.ID as idInvite, Invites.intGuests, Contacts.ID, Contacts.chrFirst, contacts.chrLast, Contacts.chrEmail, idStatus, Contacts.idUser, Contacts.chrCompany, Categories.chrCategory, 
				IF(Invites.bType IS NULL, Contacts.bType, Invites.bType) AS bType, Invites.idStatus,
				  (SELECT DupCheck.ID 
				  FROM Invites AS DupCheck 
				   JOIN Contacts AS DupContact ON DupCheck.idContact=DupContact.ID 
				  WHERE !DupCheck.bDeleted AND Contacts.chrFirst = DupContact.chrFirst AND Contacts.chrLast = DupContact.chrLast AND DupCheck.idShow=Invites.idShow AND DupCheck.idUser != Invites.idUser AND DupCheck.idStatus IN (2,3,4,5,6,7,8,9)
				  LIMIT 1 ) as idDuplicate
			FROM Contacts 
			LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
			JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $_REQUEST['idShow'] ."'
			JOIN iStatus AS i ON Invites.idStatus=i.ID
			WHERE !Invites.bDeleted AND Invites.idStatus = 7 AND !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."'".($chrSearch != '' ? " AND 
			((lower(Contacts.chrFirst) LIKE '%" . $chrSearch . "%' 
			OR lower(Contacts.chrLast) LIKE '%" . $chrSearch . "%' 
			OR lower(concat(Contacts.chrFirst,' ',Contacts.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'
			OR lower(chrCompany) LIKE '%" . $chrSearch . "%')
			OR (Contacts.ID = '" . $possiblebadge ."'))" : "")."
			ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		$regret_result = database_query($q,"Getting all contacts");
		
		$q = "SELECT Invites.ID, Invites.intGuests
				FROM Invites
				JOIN Contacts ON Invites.idContact=Contacts.ID
				WHERE !Invites.bDeleted AND Invites.idShow='". $_REQUEST['idShow'] ."' AND Invites.idUser='". $_POST['idUser'] ."' AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted";
	
		$counttmp = database_query($q, "Get invites and Guests Allowed");	
		$invitecount = mysqli_num_rows($counttmp);
		$guestcount = 0;
		while ($row = mysqli_fetch_assoc($counttmp)) {
			$guestcount = $guestcount + $row['intGuests'];
		}
		$invitesused = $invitecount + $guestcount;
				
		$q = "SELECT SponsorsbyShow.ID, chrStatus, idReviewStatus, intInvites
			 FROM SponsorsbyShow
			 LEFT JOIN ReviewStatus ON ReviewStatus.ID=idReviewStatus
			 WHERE idShow=". $_REQUEST['idShow'] ." AND idUser='".$_POST['idUser'] ."'";

		$sbs = fetch_database_query($q, "Getting Invite Page Status");
		
		// Grab Notes
		// Type 1 = Invites, Type 2 = Address Book (This is usually the table)
		// idRecord = ID of Table to link with this entry.
		
		$q = "SELECT DISTINCT DATE_FORMAT(Notes.dtStamp, '%M %D %Y') as dFormated, DATE_FORMAT(Notes.dtStamp, '%l:%i %p') as tFormated, Notes.txtNote, Users.chrFirst, Users.chrLast
			FROM SponsorsbyShow as SS 
			JOIN Notes ON Notes.idType=1 AND Notes.idRecord=SS.ID
			JOIN Users ON Users.ID = Notes.idUser
			WHERE SS.ID='".$sbs['ID']."' 
			ORDER BY Notes.dtStamp DESC";
	
		$notes = database_query($q,"Getting Notes");

		// Permissions: 1 = Add Invite, 2 = Edit Invite, 3 = Guests AND Status Updates, 4 = Save Button
		if ($sbs['idReviewStatus'] == 1 ) {
			$permissions = array('1'=>true,'2'=>true,'3'=>true,'4'=>true);
			$review_msg = "Submit this invite list for review.";
			$review_status = array('1'=>' checked="checked"','2'=>'');
		} else if ($sbs['idReviewStatus'] == 2) {
			$permissions = array('1'=>false,'2'=>false,'3'=>false,'4'=>false);
			$added_inst = $review_msg = "This invite request has been submitted for review. No changes can be made until it has been reviewed.";
			$review_status = array('1'=>' disabled="disabled"','2'=>' checked="checked" disabled="disabled"');
		} else if ($sbs['idReviewStatus'] == 3) {
			$permissions = array('1'=>true,'2'=>true,'3'=>true,'4'=>true);
			$added_inst = $review_msg = "This Invite Request has been Approved.";
			$review_status = array('1'=>' checked="checked" disabled="disabled"','2'=>' disabled="disabled"');
		} else if ($sbs['idReviewStatus'] == 4) {
			$permissions = array('1'=>true,'2'=>true,'3'=>true,'4'=>true);
			$added_inst = $review_msg = "This Invite Request has been Declined, Please make any necessary changes and re-check to resubmit.";
			$review_status = array('1'=>' checked="checked"','2'=>'');
		} else if ($_REQUEST['idShow'] == "" || $_REQUEST['idShow'] == 0) {
			$permissions = array('1'=>false,'2'=>false,'3'=>false,'4'=>false);
			$added_inst = $review_msg = "You must select a show to proceed.";
			$review_status = array('1'=>' disabled="disabled"','2'=>' disabled="disabled"');
		} else {
			$permissions = array('1'=>true,'2'=>true,'3'=>true,'4'=>true);
			$review_msg = "Submit this invite list for review.";
			$review_status = array('1'=>' checked="checked"','2'=>'');
		}			 
		
		$showstatus = fetch_database_query("SELECT idStatus FROM Shows WHERE ID='".$_REQUEST['idShow']."'","Getting Show Status");
		if ($showstatus['idStatus'] == 3) {
			$permissions = array('1'=>false,'2'=>false,'3'=>false,'4'=>false);
			$added_inst = $review_msg = "The show has been locked by the Administrator, No more edits can be made. Contact an Administrator if you need changes made.";
			$review_status[1] .= ' disabled="disabled"';
			$review_status[2] .= ' disabled="disabled"';
		}
	}
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" type='text/javascript'>
	function showExtra(ID,obj) {
		var spt = findPos(obj);
		
		document.getElementById(ID).style.top = (parseInt(spt[0]) + 13) +"px";
		document.getElementById(ID).style.left = parseInt(spt[1]) +"px";
		document.getElementById(ID).style.display = '';
	}

	function hideExtra(ID) {
		document.getElementById(ID).style.display = 'none';	
	}

	function findPos(obj) {
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			curleft = obj.offsetLeft
			curtop = obj.offsetTop
			while (obj = obj.offsetParent) {
				curleft += obj.offsetLeft
				curtop += obj.offsetTop
			}
		}
		return [curtop,curleft];
	}
	
	function guest_update(id) {
		var maxguests = <?=($sbs['intInvites'] - $invitesused)?>;
		var invites = new Array("2","5","6","8","9");
		var idStatus = document.getElementById('idStatus'+id).value;
		if(invites.inArray(idStatus)) {
			var guests = document.getElementById('intGuests'+id).value; 
			var max = document.getElementById('max'+id).value;
			if (maxguests > 0 && max < maxguests) {
				max = maxguests;
			} else if (maxguests > 0 && max >= maxguests) {
				max = (max+maxguests);
			}
			if(!IsNumeric(guests) || parseInt(guests) > max) {
				var chrName = document.getElementById('chrFirst'+id).innerHTML+' '+document.getElementById('chrLast'+id).innerHTML;
				if(max == 0) {
					alert('No number greater than 0 is allowed for '+chrName);
					document.getElementById('intGuests'+id).value = 0;
				} else {
					alert('Please enter a number between 0 and '+max+' for '+chrName);
					document.getElementById('intGuests'+id).value = max;
				}	
			}
		}
	}
</script>
<?

	include($BF. 'includes/top.php');
	$TableName = "Invites";
	include($BF . 'includes/overlay2.php');	
	//Load drop down menus for the page
?>
<form id="idFilter" name="idFilter" method="POST">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">
				<strong>Show:</strong> <select name='idShow' id='idShow' onchange='location.href="invites.php?idShow="+this.value'>
				<option value=''>-Select Show-</option>
<?
		//Load drop down menus for the page
		$shows = database_query("SELECT DISTINCT Shows.ID,Shows.chrName 
		FROM Shows
		JOIN SponsorsbyShow ON SponsorsbyShow.idShow=Shows.ID AND !SponsorsbyShow.bDeleted 
		LEFT JOIN SponsorsByUser ON SponsorsbyShow.idUser=SponsorsByUser.idSponsor
		WHERE !Shows.bDeleted AND Shows.idStatus IN (2,3) AND (SponsorsbyShow.idUser = ".$_SESSION['idUser']." OR SponsorsByUser.idUser = ".$_SESSION['idUser'].")
		ORDER BY Shows.dBegin DESC, chrName
		", "getting show status");
					while($row = mysqli_fetch_assoc($shows)) { ?>
							<option <?=(isset($_REQUEST['idShow'])) ? ($_REQUEST['idShow'] == $row["ID"] ? ' selected ' : '') : '' ?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
				<?	} ?>
			</select>
		
		Manage show invites for 
		<!--Drop Down for List of Sponsers this user has access to-->
	
						<select id='idUser' name='idUser' onchange='document.getElementById("idFilter").submit()'>
						<?=(mysqli_num_rows($invites) > 1 ? "<option value=''>-Select User-</option>" : '')?>
<?	while($row = mysqli_fetch_assoc($invites)) { ?>
							<option<?=($_POST['idUser'] == $row["ID"] ? ' selected="selected" ' : '')?> value='<?=$row["ID"]?>'><?=$row['chrFirst']?> <?=$row['chrLast']?></option>
<?	}
	if($num_results == 0) {
?>
							<option	value=''>N/A</option>	
<?
	}						
?>
						</select> </td>
<?
	if(is_numeric($_REQUEST['idShow']) && $_REQUEST['idShow'] != 0 && is_numeric($_POST['idUser']) && $_POST['idUser'] != 0) {
?>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idFilter").submit()' name="Filter" value="Filter" /></td>						
		<td class="title_right">
			<input type="button" name="export" id="name" onclick='javascript:location.href="_excel_invites.php?idShow=<?=$_REQUEST['idShow']?>&d=<?=base64_encode("to=".$_POST['idUser']."&key=".$_SESSION['idUser'])?>";' value="Export to Excel" />
<?
/*
	<input type="button" name="print" id="name" onclick='javascript:window.open("popup_printinvites.php?idShow=<?=$_REQUEST['idShow']?>&d=<?=base64_encode("to=".$_POST['idUser']."&key=".$_SESSION['idUser'])?>","new","width=700,height=500,resizable=1,scrollbars=1");' value="Print List" />
*/
?>
		</td>
		<td class="title_right" style='padding-right: 10px;'>
		</td>
		<td class="title_right">
			<? if ($permissions[1]) { ?>
			<a href='addinvite.php?idShow=<?=$_REQUEST['idShow']?>&d=<?=base64_encode("to=".$_POST['idUser']."&key=".$_SESSION['idUser'])?>'><img src="<?=$BF?>images/plus_add.gif"border="0" /></a>
			<? } else { ?>&nbsp;<? } ?>
		</td>
<?
	}						
?>
		<td class="right"></td>
	</tr>
</table>
</form>
<?
	if(is_numeric($_REQUEST['idShow']) && $_REQUEST['idShow'] != 0 && is_numeric($_POST['idUser']) && $_POST['idUser'] != 0) {
?>
<div class='instructions'>
	Please add users to the invite list.
<?
if(isset($added_inst) && $added_inst != '') {
?>
<p style='color:red; font-weight:bold;'>NOTE: <?=$added_inst?></p>
<?	
}
?>
</div>

<div class='innerbody'>
	<form action="" method="post" id="idForm" onsubmit='<?=(!$permissions[4]?'return false':'')?>'>
	<?=messages()?>
	<div style='font-size: 12px; font-weight: bold;'>Invites Allowed: <?=$sbs['intInvites']?>, Invites Used: <?=$invitesused?> <span style='color:#FF0000;'></span></div>
<?
if(@$_REQUEST['idShow'] != "") { 
?>
	<div style='font-size: 14px; font-weight: bold;'>Invites</div>
	<table id='Listinvite' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>			
			<? sortList('Last Name', 'chrLast', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('First Name', 'chrFirst', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Company', 'chrCompany', '','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
			<th>Status</th>					
		</tr>
<? 		$count=0;
		while($row = mysqli_fetch_assoc($invite_result)) {
			if($permissions[2]) {
				$link = 'location.href="editinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			} else {
				$link = 'location.href="viewinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			}
?>
			<tr id='invitetr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("invitetr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("invitetr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img title='Download VCF Card' src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>'><img title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$_POST['idUser'], $row['idInvite']); } ?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrLast<?=$row['idInvite']?>'><?=$row['chrLast']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrFirst<?=$row['idInvite']?>'><?=$row['chrFirst']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCategory']?></td>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="<?=$row['intGuests']?>" onchange='guest_update(<?=$row['idInvite']?>);' <?=(!$permissions[3]?' disabled="disabled"':'')?> /><input type='hidden' id='max<?=$row['idInvite']?>' value='<?=$row['intGuests']?>' /></td>
				<td class='options'><select style='width:115px;' name="idStatus<?=$row['idInvite']?>" id="idStatus<?=$row['idInvite']?>" onchange='guest_update(<?=$row['idInvite']?>);'<?=(!$permissions[3]?' disabled="disabled"':'')?>>
<?
			foreach($iStatus AS $k => $v) {
				if(in_array($k,array(2,3,4,5,6,7,$row['idStatus']))) {
?>
					<option<?=($row['idStatus']==$k?' selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
				}
			}
?>
				</select></td>
			</tr>
<?
		}?>
	</table>
	<div style='font-size: 14px; font-weight: bold; padding-top:10px;'>Wait List</div>
	<table id='Listinvite' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>			
			<? sortList('Last Name', 'chrLast', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('First Name', 'chrFirst', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Company', 'chrCompany', '','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
			<th>Status</th>					
		</tr>
<? 		$count=0;
		while($row = mysqli_fetch_assoc($waitlist_result)) {
			if($permissions[2]) {
				$link = 'location.href="editinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			} else {
				$link = 'location.href="viewinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			}
?>
			<tr id='invitetr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("invitetr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("invitetr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img title='Download VCF Card' src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>'><img title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$_POST['idUser'], $row['idInvite']); } ?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrLast<?=$row['idInvite']?>'><?=$row['chrLast']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrFirst<?=$row['idInvite']?>'><?=$row['chrFirst']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCategory']?></td>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="0" disabled='disabled' /></td>
				<td class='options'><select style='width:115px;' name="idStatus<?=$row['idInvite']?>" id="idStatus<?=$row['idInvite']?>" <?=(!$permissions[3]?' disabled="disabled"':'')?>>
<?
			foreach($iStatus AS $k => $v) {
				if(in_array($k,array(2,3,$row['idStatus']))) {
?>
					<option<?=($row['idStatus']==$k?' selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
				}
			}
?>
				</select></td>
			</tr>
<?
		}?>
	</table>	
	<div style='font-size: 14px; font-weight: bold; padding-top:10px;'>Regrets</div>
	<table id='Listinvite' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>			
			<? sortList('Last Name', 'chrLast', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('First Name', 'chrFirst', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Company', 'chrCompany', '','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
			<th>Status</th>					
		</tr>
<? 		$count=0;
		while($row = mysqli_fetch_assoc($regret_result)) {
			if($permissions[2]) {
				$link = 'location.href="editinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			} else {
				$link = 'location.href="viewinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			}
?>
			<tr id='invitetr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("invitetr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("invitetr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img title='Download VCF Card' src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>'><img title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$_POST['idUser'], $row['idInvite']); } ?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrLast<?=$row['idInvite']?>'><?=$row['chrLast']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrFirst<?=$row['idInvite']?>'><?=$row['chrFirst']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCategory']?></td>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="0"  disabled='disabled' /></td>
				<td class='options'><select style='width:115px;' name="idStatus<?=$row['idInvite']?>" id="idStatus<?=$row['idInvite']?>" <?=(!$permissions[3]?' disabled="disabled"':'')?>>
<?
			foreach($iStatus AS $k => $v) {
				if(in_array($k,array(2,3,$row['idStatus']))) {
?>
					<option<?=($row['idStatus']==$k?' selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
				}
			}
?>
				</select></td>
			</tr>
<?
		}?>
	</table>
  		

<div style="padding-top:10px;">Current Status: <strong><?=$sbs['chrStatus']?></strong></div>
<div style='padding-bottom:5px;'><input type="radio" id="bReview" name="bReview" value="0"<?=$review_status[1]?> /> Save to drafts <input type="radio" id="bReview" name="bReview" value="1"<?=$review_status[2]?> /> <?=$review_msg?></div>
<input type="submit" name="Save" value="Save" id='Save_button' <?=(!$permissions[4]?' disabled="disabled"':'')?>/>
<input type="hidden" name="idUser" value="<?=$_POST['idUser']?>" />
<input type="hidden" id="save_pressed" name="save_pressed" value='0' />
</form>

		<div style="margin-top:15px;">
			<table class='title' style='width: 100%; border: 0; padding: 0; margin: 0;' cellpadding="0" cellspacing="0">
				<tr>
					<td style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Notes:</td>
					<td style="text-align:right;"><a style='cursor: pointer;' onclick='javascript:window.open("popup_notes.php?id=<?=$sbs['ID']?>&amp;type=1","new","width=600,height=400,resizable=1,scrollbars=1");'>[ADD NOTE]</a></td>
				</tr>
			</table>
		</div>
		<div id="NotesArea" class='List' style='border: 1px solid #666;'>
<?
	if ( mysqli_num_rows($notes) > 0 ) {
		while($row = mysqli_fetch_assoc($notes)) { 
?>
			<div style="background-color:#c0c0c0; padding:3px;">At <?=$row['tFormated']?> on <?=$row['dFormated']?> <?=$row['chrFirst']?> <?=$row['chrLast']?> wrote:</div>
			<div style="background-color:#FFFFFF; padding:3px;"><?=nl2br($row['txtNote'])?></div>
<?
		}
	} else { 
?>
			<div id="noNotes" style='padding: 3px; text-align:center'>No notes to display.</div>
<?
	}
?>	
		</div>
<?
	} // this is for the "is show set" check ?>



<?
	} else {
?>
	<div class='innerbody'>
		You must select a Show and User to choose invites.
<?
	}
?>
	</div>
	<table cellpadding='0' cellspacing='0' style='padding-top:10px;'>
		<tr>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_gold.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Guest or VIP</td>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_red.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Special Guest</td>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/plus_add2.gif" border="0" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Add Contact to Invite List</td>
		</tr>
	</table>							

<?

	include($BF. 'includes/bottom.php');
?>