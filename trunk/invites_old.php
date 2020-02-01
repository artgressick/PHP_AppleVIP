<?	
	$BF = '';
	$active = "invites";
	$title = 'Invites Page';
	require($BF. '_lib.php');

	if (!isset($_REQUEST['idShow']) && !isset($_SESSION['idShow'])) { $_REQUEST['idShow'] = ""; }
	else if (isset($_SESSION['idShow']) && !isset($_REQUEST['idShow'])) { 
	$tmp = fetch_database_query("SELECT DISTINCT Shows.ID,Shows.chrName 
		FROM Shows
		JOIN SponsorsbyShow ON SponsorsbyShow.idShow=Shows.ID AND !SponsorsbyShow.bDeleted 
		LEFT JOIN SponsorsByUser ON SponsorsbyShow.idUser=SponsorsByUser.idSponsor
		WHERE !Shows.bDeleted AND Shows.idStatus IN (2,3) AND (SponsorsbyShow.idUser = ".$_SESSION['idUser']." OR SponsorsByUser.idUser = ".$_SESSION['idUser'].") AND Shows.ID='".$_SESSION['idShow']."'
		","Does this user have access to this show?");
		if($tmp['ID'] == $_SESSION['idShow']) {	$_REQUEST['idShow'] = $_SESSION['idShow']; }
	 }
	

	if(!isset($_REQUEST['idShow']) || !is_numeric($_REQUEST['idShow'])) { 
		$firstShow = fetch_database_query("
		SELECT DISTINCT Shows.ID,Shows.chrName 
		FROM Shows
		JOIN SponsorsbyShow ON SponsorsbyShow.idShow=Shows.ID AND !SponsorsbyShow.bDeleted 
		LEFT JOIN SponsorsByUser ON SponsorsbyShow.idUser=SponsorsByUser.idSponsor
		WHERE !Shows.bDeleted AND Shows.idStatus IN (2,3) AND (SponsorsbyShow.idUser = ".$_SESSION['idUser']." OR SponsorsByUser.idUser = ".$_SESSION['idUser'].")
		","Getting First Show");
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
	
	if (isset($_REQUEST['d'])) {
		parse_str(base64_decode($_REQUEST['d']),$info);
		if ( $info['key'] != $_SESSION['idUser'] ) { ErrorPage(); }
		$_POST['idUser'] = $info['to'];
		}

	if (count($_POST)) { 
		if (isset($_POST['bReview']) && $_POST['bReview'] == 1 && $_POST['totalinvites'] >= 0) {
			$q = "UPDATE SponsorsbyShow SET idReviewStatus='2'
				WHERE idUser=".$_POST['idUser']." AND idShow=".$_REQUEST['idShow']." AND idReviewStatus IN (1,4)";
	
			if (database_query($q,"Update Lock Field")) {
				$_SESSION['infoMessages'][] = 'Invite list saved and submitted for review';
			}
		} else {
			$_SESSION['infoMessages'][] = 'Invite list saved.';
		}
	}
		
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
		
		$q = "SELECT Users.chrFirst AS chrUserFirst, Users.chrLast AS chrUserLast, InviteStatus.chrStatus, Contacts.chrFirst AS chrContactFirst, Contacts.chrLast AS chrContactLast 
				FROM Invites
				JOIN InviteStatus ON InviteStatus.ID = Invites.idInviteStatus
				JOIN Users ON Users.ID = Invites.idUser
				JOIN Contacts ON Contacts.ID = Invites.idContact
				WHERE Contacts.chrFirst=".$chrFirst." AND Contacts.chrLast=".$chrLast." AND Invites.idShow=".$_REQUEST['idShow']." AND Invites.idUser != ".$idUser;

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
	
		$q = "SELECT Invites.ID as idInvite, Invites.intGuests, Contacts.ID, Contacts.chrFirst, contacts.chrLast, Contacts.chrEmail, idInviteStatus, Contacts.idUser, Contacts.chrCompany, Categories.chrCategory, 
				IF(Invites.bType IS NULL, Contacts.bType, Invites.bType) AS bType, Invites.idStatus,
				  (SELECT DupCheck.ID 
				  FROM Invites AS DupCheck 
				   JOIN Contacts AS DupContact ON DupCheck.idContact=DupContact.ID 
				  WHERE Contacts.chrFirst = DupContact.chrFirst AND Contacts.chrLast = DupContact.chrLast AND DupCheck.idShow=Invites.idShow AND DupCheck.idUser != Invites.idUser
				  LIMIT 1 ) as idDuplicate
			FROM Contacts 
			JOIN Categories ON Contacts.idCategory=Categories.ID
			JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $_REQUEST['idShow'] ."'
			WHERE !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."' AND 
			((lower(Contacts.chrFirst) LIKE '%" . $chrSearch . "%' 
			OR lower(Contacts.chrLast) LIKE '%" . $chrSearch . "%' 
			OR lower(chrCompany) LIKE '%" . $chrSearch . "%')
			OR (Contacts.ID = '" . $possiblebadge ."'))
			ORDER BY idInviteStatus,chrLast,chrFirst"; 
		$result = database_query($q,"Getting all contacts");
		
		$q = "SELECT Invites.ID, Invites.intGuests
				FROM Invites
				JOIN Contacts ON Invites.idContact=Contacts.ID
				WHERE Invites.idShow='". $_REQUEST['idShow'] ."' AND Invites.idUser='". $_POST['idUser'] ."' AND Invites.idInviteStatus=1 AND !Contacts.bDeleted";
	
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

		if ($sbs['idReviewStatus'] == 1 ) {
			$review_status = "";
			$save_option = "";
			$review_msg = "Submit This Invite List for Review (CAUTION! this will lock this Invite List)";
			$button_disabled=0;
		} else if ($sbs['idReviewStatus'] == 2) {
			$review_status = "checked='checked' disabled='disabled'";
			$save_option = "disabled='disabled'";
			$review_msg = "This Invite Request has been submitted for Review. No Changes Can Be Made until it has been Reviewed.";
			$button_disabled=1;
		} else if ($sbs['idReviewStatus'] == 3) {
			$review_status = "checked='checked' disabled='disabled'";
			$save_option = "disabled='disabled'";
			$review_msg = "This Invite Request has been Approved.";
			$button_disabled=0;
			$tmp = database_query("SELECT * FROM ContactInviteStatus ORDER BY ID","Getting Contact Invite Status");
			$contact_status = array();
			while ($row = mysqli_fetch_assoc($tmp)) {
				$contact_status[$row['ID']] = $row['chrStatus'];
			}
		} else if ($sbs['idReviewStatus'] == 4) {
			$review_status = "";
			$save_option = "";
			$review_msg = "This Invite Request has been Declined, Please make any necessary changes and re-check to resubmit.";
			$button_disabled=0;
		} else if ($_REQUEST['idShow'] == "" || $_REQUEST['idShow'] == 0) {
			$review_status = "disabled='disabled'";
			$save_option = "disabled='disabled'";
			$review_msg = "You must select a show to proceed.";
			$button_disabled=1;
		} else {
			$review_status = "";
			$save_option = "";
			$review_msg = "Submit This Invite List for Review (CAUTION! this will lock this Invite List)";
			$button_disabled=0;
		}			 
		
		$showstatus = fetch_database_query("SELECT idStatus FROM Shows WHERE ID='".$_REQUEST['idShow']."'","Getting Show Status");
		if ($showstatus['idStatus'] == 3) {
			$review_status .= "disabled='disabled'";
			$save_option = "disabled='disabled'";
			$review_msg = "The show has been locked by the Administrator, No more edits can be made. Contact an Administrator if you need changes made.";
			$button_disabled=1;
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
	function change_type(id) {
		if(document.getElementById('bType'+id)) {
			if(document.getElementById('bType'+id).title == 'Special Guest') {
				var bType = 0;
				invite_type('<?=$BF?>',id,bType);
				document.getElementById('bType'+id).title = 'Guest or VIP';
				document.getElementById('bType'+id).src = '<?=$BF?>images/circle_gold.png';
			} else {
				invite_type('<?=$BF?>',id,bType);
				var bType = 1;
				document.getElementById('bType'+id).title = 'Special Guest';
				document.getElementById('bType'+id).src = '<?=$BF?>images/circle_red.png';
			}
		}
	}

</script>
<?

	include($BF. 'includes/top.php');
	$TableName = "Invites";
	include($BF . 'includes/overlay2.php');	
	//Load drop down menus for the page

	$statuses = database_query("SELECT ID,chrStatus FROM InviteStatus","getting statuses");
	$status = array();
	while($row = mysqli_fetch_assoc($statuses)) {
		$status[$row['ID']] = $row['chrStatus'];
	}
?>
<form id="idForm" name="idForm" method="POST">
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
		ORDER BY chrName
		", "getting show status");
					while($row = mysqli_fetch_assoc($shows)) { ?>
							<option <?=(isset($_REQUEST['idShow'])) ? ($_REQUEST['idShow'] == $row["ID"] ? ' selected ' : '') : '' ?> value='<?=$row["ID"]?>'><?=$row['chrName']?></option>
				<?	} ?>
			</select>
		
		Manage Show Invites for 
		<!--Drop Down for List of Sponsers this user has access to-->
	
						<select id='idUser' name='idUser' onchange='document.getElementById("idForm").submit()'>
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
		<td class="title_right">Search <input type="text" id="chrSearch" name="chrSearch" size="10" value="<?=$chrSearch?>" /><input type="button" onclick='document.getElementById("idForm").submit()' name="Filter" value="Filter" /></td>						
		<td class="title_right"><input type="button" name="print" id="name" onclick='javascript:window.open("popup_printinvites.php?idShow=<?=$_REQUEST['idShow']?>&d=<?=base64_encode("to=".$_POST['idUser']."&key=".$_SESSION['idUser'])?>","new","width=700,height=500,resizable=1,scrollbars=1");' value="Print List" />
		<td class="title_right" style='padding-right: 10px;'>
		<td class="title_right">
			<? if ($button_disabled != 1 && $sbs['idReviewStatus']!=3) { ?>
			<a onclick='javascript:window.open("popup_contacts.php?idShow=<?=$_REQUEST['idShow']?>&d=<?=base64_encode("to=".$_POST['idUser']."&key=".$_SESSION['idUser'])?>","new","width=800,height=400,resizable=1,scrollbars=1");'><img src="<?=$BF?>images/plus_add.gif"border="0" /></a>
			<? } else { ?> <? } ?></td>
		</td>
<?
	}						
?>

		<td class="right"></form></td>
	</tr>
</table>
<?
	if(is_numeric($_REQUEST['idShow']) && $_REQUEST['idShow'] != 0 && is_numeric($_POST['idUser']) && $_POST['idUser'] != 0) {
?>
<div class='instructions'>Please add users to the invite list.</div>

<div class='innerbody'>
<?=messages()?>
<div style='font-size: 12px; font-weight: bold;'>Invites Allowed: <?=$sbs['intInvites']?>, Invites Used: <?=$invitesused?> <?=($sbs['intInvites']-$invitesused == 0 ? " <span style='color:#FF0000;'>MAX INVITES REACHED</span>" : ($sbs['intInvites']-$invitesused < 0 ? " <span style='color:#FF0000;'>MAX INVITES EXCEEDED PLEASE ADJUST</span>" : "") )?> </div>

<?	if(@$_REQUEST['idShow'] != "") { 
	$row = mysqli_fetch_assoc($result);
?>
	<div style='font-size: 14px; font-weight: bold;'>Invites</div>
	<table id='Listinvite' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>			
			<? sortList('Last Name', 'chrFirst', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('First Name', 'chrLast', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Company', 'chrCompany', '','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
<?
		if($sbs['idReviewStatus'] == 3) {
?>
			<th>Contact Status</th>					
<?
		} 
		if($showstatus['idStatus'] != 3) {
?>
			<th>Invite Status</th>
			<th class='options'><img src="<?=$BF?>images/options.gif"></th>
<?
		}
?>
		</tr>
<? 		$count=0;
		if($row['idInviteStatus'] == 1) { 
		do { ?>
			<tr id='invitetr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("invitetr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("invitetr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td><img style='cursor:pointer;' title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" onclick='change_type(<?=$row['idInvite']?>);' /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$_POST['idUser'], $row['idInvite']); } ?></td>
				<td style='width:25%;'><?=$row['chrLast']?></td>
				<td style='width:25%;'><?=$row['chrFirst']?></td>
				<td style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='width:25%;'><?=$row['chrCategory']?></td>
<? 
			if ($button_disabled != 1) { 
?>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="<?=$row['intGuests']?>" onkeyup="javascript:invite_guests('<?=$BF?>',<?=$row['idInvite']?>, this.value);" /></td>
<?
				if($sbs['idReviewStatus'] == 3) {
?>
				<td class='options'><select onchange="javascript:contact_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);">
					<option value='0'>-Select Status-</option>
<?
					foreach($contact_status AS $k => $v) {
?>
						<option <?=($row['idStatus']==$k?'selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
					}
?>
				</select></td>
<?
				}
?>
				<td class='options'><select onchange="javascript:invite_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);"><option value=''>-Select Status-</option><option selected value='1'>Invite</option><option value='2'>Waitlist</option><option value='3'>Delete</option></select></td>
				<td class='options'><?=deleteButton($row['idInvite'],$row['chrFirst']." ".$row['chrLast'],'invite')?></td>
<?
			 } else { 
?>
				<td><?=$row['intGuests']?></td>
<?
				if($sbs['idReviewStatus'] == 3) {
					if($showstatus['idStatus'] != 3) {
?>
				<td class='options'><select onchange="javascript:contact_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);">
					<option value='0'>-Select Status-</option>
<?
					foreach($contact_status AS $k => $v) {
?>
						<option <?=($row['idStatus']==$k?'selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
					}
?>
				</select></td>
<?
					} else {
?>
				<td class='options' style='white-space:nowrap;'><?=($row['idStatus'] != '' ? $contact_status[$row['idStatus']] : '')?></td>
<?						
					}
				} else { 
?>
				<td colspan="3"><i>Options Disabled</i></td>
<?
				}
			}
?>
			</tr>
<?			$row = mysqli_fetch_assoc($result);
		} while($row['idInviteStatus'] == 1); 
		}?>
	</table>




	<div style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Waitlist</div>
	<table id='Listwaitlist' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<? sortList('Last Name', 'chrFirst', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('First Name', 'chrLast', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Company', 'chrCompany', '','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
<?
		if($sbs['idReviewStatus'] == 3) {
?>
			<th>Contact Status</th>					
<?
		} 
		if($showstatus['idStatus'] != 3) {
?>
			<th>Invite Status</th>
			<th class='options'><img src="<?=$BF?>images/options.gif"></th>
<?
		}
?>
		</tr>
<? 		$count=0;	
		if($row['idInviteStatus'] == 2) {
		do { ?>
			<tr id='waitlisttr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("waitlisttr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("waitlisttr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td><img style='cursor:pointer;' title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" onclick='change_type(<?=$row['idInvite']?>);' /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$_POST['idUser'], $row['idInvite']); } ?></td>
				<td style='width:25%;'><?=$row['chrLast']?></td>
				<td style='width:25%;'><?=$row['chrFirst']?></td>
				<td style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='width:25%;'><?=$row['chrCategory']?></td>
<? 
			if ($button_disabled != 1) { 
?>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="<?=$row['intGuests']?>" onkeyup="javascript:invite_guests('<?=$BF?>',<?=$row['idInvite']?>, this.value);" /></td>
<?
				if($sbs['idReviewStatus'] == 3) {
?>
				<td class='options'><select onchange="javascript:contact_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);">
					<option value='0'>-Select Status-</option>
<?
					foreach($contact_status AS $k => $v) {
?>
						<option <?=($row['idStatus']==$k?'selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
					}
?>
				</select></td>
<?
				}
?>
				<td class='options'><select onchange="javascript:invite_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);"><option value=''>-Select Status-</option><option value='1'>Invite</option><option selected value='2'>Waitlist</option><option value='3'>Delete</option></select></td>
				<td class='options'><?=deleteButton($row['idInvite'],$row['chrFirst']." ".$row['chrLast'],'waitlist')?></td>
<?
			 } else {
?>
				<td><?=$row['intGuests']?></td>
<?
				if($sbs['idReviewStatus'] == 3) {
					if($showstatus['idStatus'] != 3) {
?>
				<td class='options'><select onchange="javascript:contact_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);">
					<option value='0'>-Select Status-</option>
<?
					foreach($contact_status AS $k => $v) {
?>
						<option <?=($row['idStatus']==$k?'selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
					}
?>
				</select></td>
<?
					} else {
?>
				<td class='options'><?=($row['idStatus'] != '' ? $contact_status[$row['idStatus']] : '')?></td>
<?						
					}
				} else { 
?>
				<td colspan="2"><i>Options Disabled</i></td>
<?
				}
			}
?>
			</tr>
<?			$row = mysqli_fetch_assoc($result);
		} while($row['idInviteStatus'] == 2); 
		}?>
  </table>




	<div style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Removed</div>
	<table id='Listremoved' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<th class='headImg' style='width: 0.1cm;'></th>
			<? sortList('Last Name', 'chrFirst', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('First Name', 'chrLast', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Company', 'chrCompany', '','idShow='.$_REQUEST["idShow"]); ?>
			<? sortList('Category', 'chrCategory', 'width:150px;','idShow='.$_REQUEST["idShow"]); ?>
			<th class='headImg' style="width:40px;">Guests</th>
<?
		if($sbs['idReviewStatus'] == 3) {
?>
			<th>Contact Status</th>					
<?
		} 
		if($showstatus['idStatus'] != 3) {
?>
			<th>Invite Status</th>
			<th class='options'><img src="<?=$BF?>images/options.gif"></th>
<?
		}
?>
		</tr>
<? 		$count=0;	
		if($row['idInviteStatus'] == 3) {
		do { ?>
			<tr id='removedtr<?=$row['idInvite']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("removedtr<?=$row['idInvite']?>");' onmouseout='UnRowHighlight("removedtr<?=$row['idInvite']?>");'>
				<td><a href='<?=$BF?>getvcf.php?id=<?=$row['ID']?>'><img src="<?=$BF?>images/icon_contact.gif" width="14" height="12" /></a></td>
				<td><img style='cursor:pointer;' title='<?=($row['bType']==1?'Special Guest':'Guest or VIP')?>' id='bType<?=$row['idInvite']?>' src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" onclick='change_type(<?=$row['idInvite']?>);' /></td>						
				<td><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['idInvite']?>',this)" onmouseout="hideExtra('user<?=$row['idInvite']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$_POST['idUser'], $row['idInvite']); } ?></td>
				<td style='width:25%;'><?=$row['chrLast']?></td>
				<td style='width:25%;'><?=$row['chrFirst']?></td>
				<td style='width:25%;'><?=$row['chrCompany']?></td>
				<td style='width:25%;'><?=$row['chrCategory']?></td>
<?
			if ($button_disabled != 1) { 
?>
				<td><input type="text" size="2"	name="intGuests<?=$row['idInvite']?>" id="intGuests<?=$row['idInvite']?>" value="<?=$row['intGuests']?>" onkeyup="javascript:invite_guests('<?=$BF?>',<?=$row['idInvite']?>, this.value);" /></td>				
<?
				if($sbs['idReviewStatus'] == 3) {
?>
				<td class='options'><select onchange="javascript:contact_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);">
					<option value='0'>-Select Status-</option>
<?
					foreach($contact_status AS $k => $v) {
?>
						<option <?=($row['idStatus']==$k?'selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
					}
?>
				</select></td>
<?
				}
?>
				<td class='options'><select onchange="javascript:invite_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);"><option value=''>-Select Status-</option><option value='1'>Invite</option><option  value='2'>Waitlist</option><option selected value='3'>Delete</option></select></td>
				<td class='options'><?=deleteButton($row['idInvite'],$row['chrFirst']." ".$row['chrLast'],'removed')?></td>
<?
			} else { 
?>
				<td><?=$row['intGuests']?></td>
<?
				if($sbs['idReviewStatus'] == 3) {
					if($showstatus['idStatus'] != 3) {
				
?>
				<td class='options'><select onchange="javascript:contact_status('<?=$BF?>',<?=$row['idInvite']?>, this.value);">
					<option value='0'>-Select Status-</option>
<?
					foreach($contact_status AS $k => $v) {
?>
						<option <?=($row['idStatus']==$k?'selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
<?
					}
?>
				</select></td>
<?
					} else {
?>
				<td class='options'><?=($row['idStatus'] != '' ? $contact_status[$row['idStatus']] : '')?></td>
<?						
					}
				} else { 
?>
				<td colspan="2" style='white-space:nowrap;'><i>Options Disabled</i></td>
<?
				}
			}
?>
			</tr>
<?			$row = mysqli_fetch_assoc($result);
		} while($row['idInviteStatus'] == 3);
		} ?>
  </table>
  		
<form id="formlock" name="formlock" method="POST">
<div style="padding-top:10px;">Current Status: <strong><?=$sbs['chrStatus']?></strong></div>
<div style='padding-bottom:5px;'><input type="radio" id="bReview" name="bReview" value="0" <?=$save_option?> <?=($review_status == ''?'checked="checked"':'')?> <?=($sbs['intInvites']-$invitesused < 0 ? "disabled='disabled'" : "")?> /> Save to Drafts <input type="radio" id="bReview" name="bReview" value="1" <?=$review_status?> <?=($sbs['intInvites']-$invitesused < 0 ? "disabled='disabled'" : "")?> /> <?=$review_msg?></div>
<input type="submit" name="submit" value="Save" <?=($button_disabled == 1 && $sbs['idReviewStatus'] != 3 ? "disabled='disabled'" : "" )?> />
<input type="hidden" name="idUser" value="<?=$_POST['idUser']?>" />
<input type="hidden" name="totalinvites" value="<?=$sbs['intInvites']-$invitesused?>" />
</form>

		<div style="margin-top:15px;">
			<table class='title' style='width: 100%; border: 0; padding: 0; margin: 0;' cellpadding="0" cellspacing="0">
				<tr>
					<td style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Notes:</td>
					<td style="text-align:right;"><a style='cursor: pointer;' onclick='javascript:window.open("popup_notes.php?id=<?=$sbs['ID']?>&amp;type=1","new","width=600,height=400,resizable=1,scrollbars=1");'>[ADD NOTE]
						</a></td>
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
