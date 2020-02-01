<?	
	$BF = '../';
	$active = 'admin';
	$subactive = 'invites';
	$title = 'Manage Invites';
	require($BF. '_lib.php');
	if($_SESSION['idRight'] != 1) {
		header('Location: '. $BF);
		die();
	}
	(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );
/*
	if (isset($_POST['submit']) ) {
	
		$q = "UPDATE SponsorsbyShow SET idReviewStatus='".$_POST['idStatus']."'
			WHERE idUser=".$_POST['idUser']." AND idShow=".$_POST['idShow'];

		if(database_query($q,"Update Lock Field")) {
			$_SESSION['infoMessages'][] = 'Invite list updated successfully.';
		} else {
			ErrorPage('An Error has occurred while trying to save this Invite List. Please contact Support.');
		}
					
	}
*/
	// Grab SponserbyShow Information
	$q = "SELECT SS.ID, SS.idShow, SS.idUser, SS.idReviewStatus, Users.chrFirst, Users.chrLast, RS.chrStatus, idReviewStatus, Shows.chrName, SS.intInvites, Shows.idStatus
			FROM SponsorsbyShow AS SS
			JOIN Users ON Users.ID=SS.idUser
			JOIN Shows ON Shows.ID=SS.idShow
			JOIN ReviewStatus AS RS ON RS.ID=SS.idReviewStatus
			WHERE SS.ID=".$_REQUEST['id'];
	
	$userinfo = fetch_database_query($q,"Getting Sponsor by User information for id");

	if ($userinfo['idReviewStatus'] == 3) {
		$tmp = database_query("SELECT * FROM ContactInviteStatus ORDER BY ID","Getting Contact Invite Status");
		$contact_status = array();
		while ($row = mysqli_fetch_assoc($tmp)) {
			$contact_status[$row['ID']] = $row['chrStatus'];
		}
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
			WHERE !Invites.bDeleted AND !Contacts.bDeleted AND Contacts.idUser='".$userinfo['idUser']."' AND idShow='". $userinfo['idShow'] ."' ORDER BY idStatus, chrLast, chrFirst";
			
		$results = database_query($q,"Getting all contacts");

		$maxinvites = $userinfo['intInvites'];
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
							$_SESSION['errorMessages'][] = "There is not enough allocations to allow ".$row['chrFirst']." ".$row['chrLast']." to have ".$_POST['intGuests'.$row['ID']].". Amount changed to ".$guests.".";
							$_POST['intGuests'.$row['ID']] = $guests;
						}
						$q = "UPDATE Invites SET idStatus='".$_POST['idStatus'.$row['ID']]."', intGuests='".$_POST['intGuests'.$row['ID']]."' WHERE ID=".$row['ID'];
						if($_POST['idStatus'.$row['ID']] != $row['idStatus']) {
							$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$row['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus'.$row['ID']]].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
						}
					} else {  // have we exceeded the max invites (Yes)
						$_SESSION['errorMessages'][] = "There is not have enough allocations to allow ".$row['chrFirst']." ".$row['chrLast']." to be invited. Status changed to Wait List";
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
		} // end while
		if(isset($_POST['idReviewStatus']) && $_POST['idReviewStatus'] != $userinfo['idReviewStatus']) {
			$q = "UPDATE SponsorsbyShow SET idReviewStatus='".$_POST['idReviewStatus']."'
			WHERE idUser=".$userinfo['idUser']." AND idShow=".$userinfo['idShow']."";
			database_query($q,'Updating Status');
			$_SESSION['infoMessages'][] = "Status Updated";
		}
		header("Location: reviewinvites.php?id=".$userinfo['ID']);	
		die();
	} // end of $_POST['Save']


	include($BF. 'includes/meta.php');
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast,chrFirst"; }
	
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
		JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $userinfo['idShow'] ."'
		JOIN iStatus AS i ON Invites.idStatus=i.ID
		WHERE !Invites.bDeleted AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted AND Contacts.idUser='".$userinfo['idUser']."'".($chrSearch != '' ? " AND 
		((lower(Contacts.chrFirst) LIKE '%" . strtolower($chrSearch) . "%' 
		OR lower(Contacts.chrLast) LIKE '%" . strtolower($chrSearch) . "%' 
		OR lower(concat(Contacts.chrFirst,' ',Contacts.chrLast)) LIKE '%" . strtolower($chrSearch) . "%' 
		OR lower(chrCompany) LIKE '%" . strtolower($chrSearch) . "%')
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
		JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $userinfo['idShow'] ."'
		JOIN iStatus AS i ON Invites.idStatus=i.ID
		WHERE !Invites.bDeleted AND Invites.idStatus = 3 AND !Contacts.bDeleted AND Contacts.idUser='".$userinfo['idUser']."'".($chrSearch != '' ? " AND 
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
		JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $userinfo['idShow'] ."'
		JOIN iStatus AS i ON Invites.idStatus=i.ID
		WHERE !Invites.bDeleted AND Invites.idStatus = 7 AND !Contacts.bDeleted AND Contacts.idUser='".$userinfo['idUser']."'".($chrSearch != '' ? " AND 
		((lower(Contacts.chrFirst) LIKE '%" . $chrSearch . "%' 
		OR lower(Contacts.chrLast) LIKE '%" . $chrSearch . "%' 
		OR lower(concat(Contacts.chrFirst,' ',Contacts.chrLast)) LIKE '%" . strtolower($chrSearch) . "%'
		OR lower(chrCompany) LIKE '%" . $chrSearch . "%')
		OR (Contacts.ID = '" . $possiblebadge ."'))" : "")."
		ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	$regret_result = database_query($q,"Getting all contacts");
	
	// Getting Notes
	
	$q = "SELECT DISTINCT DATE_FORMAT(Notes.dtStamp, '%M %D %Y') as dFormated, DATE_FORMAT(Notes.dtStamp, '%l:%i %p') as tFormated, Notes.txtNote, Users.chrFirst, Users.chrLast
		FROM SponsorsbyShow as SS 
		JOIN Notes ON Notes.idType=1 AND Notes.idRecord=SS.ID
		JOIN Users ON Users.ID = Notes.idUser
		WHERE SS.ID=".$userinfo['ID']."
		ORDER BY Notes.dtStamp DESC";

	$notes = database_query($q,"Getting Notes");

	$q = "SELECT Invites.ID, Invites.intGuests
			FROM Invites
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND Invites.idShow='". $userinfo['idShow'] ."' AND Invites.idUser='". $userinfo['idUser'] ."' AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted";

	$counttmp = database_query($q, "Get invites and Guests Allowed");	
	$invitecount = mysqli_num_rows($counttmp);
	$guestcount = 0;
	while ($row = mysqli_fetch_assoc($counttmp)) {
		$guestcount = $guestcount + $row['intGuests'];
	}
	$invitesused = $invitecount + $guestcount;

	function DupInfo($chrFirst, $chrLast, $idUser, $id) { //Function to retrieve Duplicate E-mail Information
		global $userinfo;
		$q = "SELECT Users.chrFirst AS chrUserFirst, Users.chrLast AS chrUserLast, Contacts.chrFirst AS chrContactFirst, Contacts.chrLast AS chrContactLast, iStatus.chrStatus
				FROM Invites
				JOIN Users ON Users.ID = Invites.idUser
				JOIN Contacts ON Contacts.ID = Invites.idContact
				JOIN iStatus ON Invites.idStatus=iStatus.ID
				WHERE !Invites.bDeleted AND Contacts.chrFirst=".$chrFirst." AND Contacts.chrLast=".$chrLast." AND Invites.idShow=".$userinfo['idShow']." AND Invites.idUser != ".$idUser;

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
		var maxguests = <?=($userinfo['intInvites'] - $invitesused)?>;
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
?>

<form id="idForm" name="idForm" method="POST"<?=($userinfo['idStatus']==4?' onsubmit="return false"':'')?>>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Review Invites for <strong><?=$userinfo['chrFirst']?> <?=$userinfo['chrLast']?></strong> for show <strong><?=$userinfo['chrName']?></strong></td>
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>	
		<td class="title_right"><? if($userinfo['idStatus'] != 4) { ?><a href='addinvite.php?idShow=<?=$userinfo['idShow']?>&d=<?=base64_encode("to=".$userinfo['idUser']."&key=".$_SESSION['idUser'])?>'><img src="<?=$BF?>images/plus_add.gif"border="0" /></a> <? } else { echo '&nbsp;'; } ?></td>
		</td>
		<td class="right"></td>
	</tr>
</table>
<div class='instructions'>This is a list of Invites that this sponser has requested, please review and set status at bottom.</div>

<div class='innerbody'>
<?=messages()?>
<div style='font-size: 12px; font-weight: bold;'>Invites Allowed: <?=$userinfo['intInvites']?>, Invites Used: <?=$invitesused?></div>
	<div style='font-size: 14px; font-weight: bold;'>Invites</div>
	<table id='Listinvite' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>			
			<? sortList('Last Name', 'chrLast', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<? sortList('First Name', 'chrFirst', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<? sortList('Company', 'chrCompany', '','id='.$_REQUEST["id"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
			<th>Status</th>					
		</tr>
<? 		$count=0;
		while($row = mysqli_fetch_assoc($invite_result)) {
			if($userinfo['idStatus'] != 4) {
				$link = 'location.href="editinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			} else {
				$link = '';
				$link_style='';
			}
?>
			<tr id='invitetr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("invitetr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("invitetr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img title='Download VCF Card' src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>'><img title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$userinfo['idUser'], $row['idInvite']); } ?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrLast<?=$row['idInvite']?>'><?=$row['chrLast']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrFirst<?=$row['idInvite']?>'><?=$row['chrFirst']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCategory']?></td>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="<?=$row['intGuests']?>"<?=($userinfo['idStatus']==4?' disabled="disabled"':'')?> onchange='guest_update(<?=$row['idInvite']?>);' /><input type='hidden' id='max<?=$row['idInvite']?>' value='<?=$row['intGuests']?>' /></td>
				<td class='options'><select style='width:115px;' name="idStatus<?=$row['idInvite']?>" id="idStatus<?=$row['idInvite']?>"<?=($userinfo['idStatus']==4?' disabled="disabled"':'')?> onchange='guest_update(<?=$row['idInvite']?>);'>
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
			<? sortList('Last Name', 'chrLast', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<? sortList('First Name', 'chrFirst', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<? sortList('Company', 'chrCompany', '','id='.$_REQUEST["id"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
			<th>Status</th>					
		</tr>
<? 		$count=0;
		while($row = mysqli_fetch_assoc($waitlist_result)) {
			if($userinfo['idStatus'] != 4) {
				$link = 'location.href="editinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			} else {
				$link = '';
				$link_style='';
			}
?>
			<tr id='invitetr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("invitetr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("invitetr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img title='Download VCF Card' src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>'><img title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$userinfo['idUser'], $row['idInvite']); } ?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrLast<?=$row['idInvite']?>'><?=$row['chrLast']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrFirst<?=$row['idInvite']?>'><?=$row['chrFirst']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCategory']?></td>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="0" disabled='disabled' /></td>
				<td class='options'><select style='width:115px;' name="idStatus<?=$row['idInvite']?>"<?=($userinfo['idStatus']==4?' disabled="disabled"':'')?> id="idStatus<?=$row['idInvite']?>">
<?
			foreach($iStatus AS $k => $v) {
				if(in_array($k,array(2,3,4))) {
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
			<? sortList('Last Name', 'chrLast', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<? sortList('First Name', 'chrFirst', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<? sortList('Company', 'chrCompany', '','id='.$_REQUEST["id"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','id='.$_REQUEST["id"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
			<th>Status</th>					
		</tr>
<? 		$count=0;
		while($row = mysqli_fetch_assoc($regret_result)) {
			if($userinfo['idStatus'] != 4) {
				$link = 'location.href="editinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
			} else {
				$link = '';
				$link_style='';
			}
?>
			<tr id='invitetr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("invitetr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("invitetr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img title='Download VCF Card' src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>'><img title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$userinfo['idUser'], $row['idInvite']); } ?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrLast<?=$row['idInvite']?>'><?=$row['chrLast']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><span id='chrFirst<?=$row['idInvite']?>'><?=$row['chrFirst']?></span></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='<?=$link_style?>' onclick='<?=$link?>' style='width:25%;'><?=$row['chrCategory']?></td>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="0"  disabled='disabled' /></td>
				<td class='options'><select style='width:115px;' name="idStatus<?=$row['idInvite']?>"<?=($userinfo['idStatus']==4?' disabled="disabled"':'')?> id="idStatus<?=$row['idInvite']?>">
<?
			foreach($iStatus AS $k => $v) {
				if(in_array($k,array(2,3,4,7))) {
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
	

<div style="padding-top:10px;padding-bottom:5px;">Current Status: <strong><?=$userinfo['chrStatus']?></strong> <br /><br />
Change To: <select id="idReviewStatus" name="idReviewStatus"<?=($userinfo['idStatus']==4?' disabled="disabled"':'')?>>
<?
	$q = "SELECT *
		FROM ReviewStatus
		ORDER BY ID";
	$reviewstatus = database_query($q,"Getting Review Status");
while ($row = mysqli_fetch_assoc($reviewstatus)) { ?>
	<option value="<?=$row['ID']?>" <?=($row['ID'] == $userinfo['idReviewStatus'] ? 'selected="selected"' : "")?>><?=$row['chrStatus']?></option>
<?
}
?>
</select></div>
<input type="submit" name="Save" value="Save"<?=($userinfo['idStatus']==4?' disabled="disabled"':'')?> />
<input type="hidden" name="idUser" value="<?=$userinfo['idUser']?>" />
<input type="hidden" name="idShow" value="<?=$userinfo['idShow']?>" />
</form>

		<div style="margin-top:15px;">
			<table class='title' style='width: 100%; border: 0; padding: 0; margin: 0;' cellpadding="0" cellspacing="0">
				<tr>
					<td style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Notes:</td>
					<td style="text-align:right;"><a style='cursor: pointer;' onclick='javascript:window.open("<?=$BF?>popup_notes.php?id=<?=$userinfo['ID']?>&amp;type=1","new","width=600,height=400,resizable=1,scrollbars=1");'>[ADD NOTE]</a>
</td>
				</tr>
			</table>
		</div>
		<div id="NotesArea" class='List' style='border: 1px solid #666;'>
<?
	if ( mysqli_num_rows($notes) > 0 ) {
		while ($row = mysqli_fetch_assoc($notes)) { 
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
	</div>
<?
	include($BF. 'includes/bottom.php');
?>
