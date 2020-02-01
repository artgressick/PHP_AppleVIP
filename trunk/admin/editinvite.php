<?	
	$BF = '../';
	$active = 'admin';
	$subactive = 'invites';
	$title = 'Edit Invite';
	require($BF. '_lib.php');
	
	if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
		$_SESSION['errorMessages'][] = "Invalid Contact";
		$moveTo = 'invites.php';
		header("Location: ".$moveTo);	
		die();
	}
	$info = fetch_database_query("SELECT I.ID, I.bType, I.idStatus, I.intGuests, I.idUser, I.idShow, C.ID AS idContact, C.chrFirst AS chrContactFirst, C.chrLast AS chrContactLast, C.chrCompany, 
									C.bType AS bCType, S.ID AS idShow, S.chrName AS chrShow, Cat.chrCategory, U.ID AS PostUser, U.chrFirst, U.chrLast, SBS.intInvites, RS.chrStatus, SBS.ID AS idSBS, SBS.idReviewStatus
									FROM Invites AS I
									JOIN Contacts AS C ON I.idContact=C.ID
									JOIN Shows AS S ON I.idShow=S.ID
									LEFT JOIN Categories AS Cat ON C.idCategory=Cat.ID
									JOIN Users AS U ON I.idUser=U.ID
									JOIN SponsorsbyShow AS SBS ON SBS.idUser=I.idUser AND SBS.idShow=I.idShow
									LEFT JOIN ReviewStatus AS RS ON RS.ID=SBS.idReviewStatus
									WHERE !I.bDeleted AND I.ID='".$_REQUEST['id']."'","Getting Invite Information");
	if($info['ID'] == '') {
		$_SESSION['errorMessages'][] = "Invalid Contact";
		$moveTo = 'invites.php';
		header("Location: ".$moveTo);	
		die();
	}
	
	$q = "SELECT Invites.ID, Invites.intGuests
			FROM Invites
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND Invites.idShow='". $info['idShow'] ."' AND Invites.idUser='". $info['idUser'] ."' AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted";

	$counttmp = database_query($q, "Get invites and Guests Allowed");	
	$invitecount = mysqli_num_rows($counttmp);
	$guestcount = 0;
	while ($row = mysqli_fetch_assoc($counttmp)) {
		$guestcount = $guestcount + $row['intGuests'];
	}
	$invitesused = $invitecount + $guestcount;
	
	$invitesleft = ($info['intInvites'] - $invitesused);
	
	$statusids = "3,4,7,";
	if(in_array($info['idStatus'],array(2,5,6,8,9)) || $invitesleft > 0) {
		$statusids .= "2,5,6,8,9,";
	}
	
	$tmpStatus = database_query("SELECT ID, chrStatus FROM iStatus WHERE !bDeleted AND ID IN (".substr($statusids,0,-1).") ORDER BY dOrder","Getting Status");
	$iStatus = array();
	while($row = mysqli_fetch_assoc($tmpStatus)) {
		$iStatus[$row['ID']] = $row['chrStatus'];
	}
	
	
	if(isset($_POST['Save'])) {
		$q = "SELECT Invites.ID, Invites.intGuests
				FROM Invites
				JOIN Contacts ON Invites.idContact=Contacts.ID
				WHERE !Invites.bDeleted AND Invites.idShow='". $info['idShow'] ."' AND Invites.idUser='". $info['idUser'] ."' AND Invites.ID != '".$info['ID']."' AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted";
	
		$counttmp = database_query($q, "Get invites and Guests Allowed");	
		$invitecount = mysqli_num_rows($counttmp);
		$guestcount = 0;
		while ($row = mysqli_fetch_assoc($counttmp)) {
			$guestcount = $guestcount + $row['intGuests'];
		}
		$invitesused = $invitecount + $guestcount;
		
		$invitesleft = $invitesused;
		$q = '';
		if($_POST['idStatus'] != $info['idStatus'] || $_POST['intGuests'] != $info['intGuests'] || $_POST['bType'] != $info['bType']) {
			// If status was changed to waitlist or deleted
			if(in_array($_POST['idStatus'],array(3,4,7)) && $_POST['idStatus'] != $info['idStatus']) {
				if($_POST['idStatus'] == 4) {
					$q = "UPDATE Invites SET bDeleted=1 WHERE ID='".$info['ID']."'";
					$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to Deleted<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
				} else {
					$q = "UPDATE Invites SET idStatus='".$_POST['idStatus']."', intGuests=0, bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
				}
				$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully";
				$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus']].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
			// If status was changed to a Invited Status
			} else if(in_array($_POST['idStatus'],array(2,5,6,8,9)) && $_POST['idStatus'] != $info['idStatus'] && !in_array($info['idStatus'],array(2,5,6,8,9))) {
				// check for room first
				if($invitesleft > 0) {
					$invitesleft--;
					//did we assign any guests?
					if(is_numeric($_POST['intGuests'])) {
						if($_POST['intGuests'] <= 0) {
							$q = "UPDATE Invites SET idStatus='".$_POST['idStatus']."', intGuests=0, bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
							$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully";
							$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus']].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
						} else {
							if($_POST['intGuests'] <= $invitesleft) {
								$q = "UPDATE Invites SET idStatus='".$_POST['idStatus']."', intGuests='".$_POST['intGuests']."', bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
								$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully";
								$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus']].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
							} else {
								$q = "UPDATE Invites SET idStatus='".$_POST['idStatus']."', intGuests='".$invitesleft."', bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
								$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully. However the number of guests exceeded allocations, Adjusted to ".$invitesleft;
								$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus']].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
							}
						}
					}
				} else {
					$q = "UPDATE Invites SET idStatus='3', intGuests=0, bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
					$_SESSION['errorMessages'][] = "There was not enough allocations avaliable for ".$info['chrContactFirst']." ".$info['chrContactLast'].", they have been placed in Wait List instead.";
					$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[3].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
				}
			} else if(in_array($_POST['idStatus'],array(2,5,6,8,9)) && $_POST['idStatus'] != $info['idStatus'] && in_array($info['idStatus'],array(2,5,6,8,9))) {
				if(is_numeric($_POST['intGuests'])) {
					if($_POST['intGuests'] <= 0) {
						$q = "UPDATE Invites SET idStatus='".$_POST['idStatus']."', intGuests=0, bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
						$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully";
						$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus']].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
					} else {
						if($_POST['intGuests'] <= $invitesleft) {
							$q = "UPDATE Invites SET idStatus='".$_POST['idStatus']."', intGuests='".$_POST['intGuests']."', bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
							$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully";
							$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus']].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
						} else {
							$q = "UPDATE Invites SET idStatus='".$_POST['idStatus']."', intGuests='".$invitesleft."', bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
							$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully. However the number of guests exceeded allocations, Adjusted to ".$invitesleft;
							$tmp = database_query("INSERT INTO Notes SET idType=3, idUser='".$_SESSION['idUser']."', idRecord='".$info['ID']."',	dtStamp=now(),	txtNote = '".encode('- Status set to '.$iStatus[$_POST['idStatus']].'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'","Insert Note");
						}
					}
				}
			} else if($info['bType'] != $_POST['bType']) {
				$q = "UPDATE Invites SET bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
				$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully.";
			} else if($_POST['intGuests'] != $info['intGuests']) {
				if(is_numeric($_POST['intGuests'])) {
					if($_POST['intGuests'] <= 0) {
						$q = "UPDATE Invites SET intGuests=0, bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
						$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully";
					} else {
						if($_POST['intGuests'] <= $invitesleft) {
							$q = "UPDATE Invites SET intGuests='".$_POST['intGuests']."', bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
							$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully";
						} else {
							$q = "UPDATE Invites SET intGuests='".$invitesleft."', bType='".$_POST['bType']."' WHERE ID='".$info['ID']."'";
							$_SESSION['infoMessages'][] = $info['chrContactFirst']." ".$info['chrContactLast']." has been updated successfully. However the number of guests exceeded allocations, Adjusted to ".$invitesleft;
						}
					}
				}
			}
		}
		
		if($q != '') { 
			database_query($q,"Run Query");
		} else {
			$_SESSION['infoMessages'][] = "No changes have been made to ".$info['chrContactFirst']." ".$info['chrContactLast'];
		}
		header("Location: reviewinvites.php?id=".$info['idSBS']);	
		die();
	}
	
	
	include($BF. 'includes/meta.php');
?>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" type='text/javascript'>
	function enableguests(idStatus) {
		var invites = new Array("2","5","6","8","9");
		if(invites.inArray(idStatus)) {
			document.getElementById('intGuests').disabled=false;
		} else {
			document.getElementById('intGuests').disabled=true;
			document.getElementById('intGuests').value='0';
		}
	}
	
	function checkguests(intGuests) {
		var invitesleft = parseInt('<?=$invitesleft?>');
		var origStatus = '<?=$info['idStatus']?>';
		var origGuests = parseInt('<?=$info['intGuests']?>');
		var invites = new Array("2","5","6","8","9");
		var maxGuests = (invitesleft + origGuests);
		if(invites.inArray(origStatus)) {
			if(!IsNumeric(intGuests) || parseInt(intGuests) > maxGuests) {
				if(maxGuests == 0) {
					alert('No number greater than 0 is allowed for Guests');
					document.getElementById('intGuests').value = 0;
					document.getElementById('SavePressed').value = 0;
				} else {
					alert('Please enter a number between 0 and '+maxGuests+' for Guests');
					document.getElementById('intGuests').value = maxGuests;
					document.getElementById('SavePressed').value = 0;
				}	
			}
		} else {
			maxGuests = (maxGuests - 1);
			if(maxGuests < 0) { maxGuests = 0; }
			if(!IsNumeric(intGuests) || parseInt(intGuests) > maxGuests) {
				if(maxGuests == 0) {
					alert('No number greater than 0 is allowed for Guests');
					document.getElementById('intGuests').value = 0;
					document.getElementById('SavePressed').value = 0;
				} else {
					alert('Please enter a number between 0 and '+maxGuests+' for Guests');
					document.getElementById('intGuests').value = maxGuests;
					document.getElementById('SavePressed').value = 0;
				}	
			}
		}
	}
	
	function error_check() {
		if(totalErrors != 0) { reset_errors(); }  
		
			totalErrors = 0;
			if(document.getElementById('SavePressed').value == 1) { totalErrors++; }
	
		return (totalErrors == 0 ? true : false);
	}
</script>

<?
	include($BF. 'includes/top.php');
?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Edit Invite: <?=$info['chrContactFirst']?> <?=$info['chrContactLast']?></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>
		Edit Invite Contact: <strong><?=$info['chrContactFirst']?> <?=$info['chrContactLast']?></strong> on <strong><?=$info['chrFirst']?> <?=$info['chrLast']?></strong> Invites for show: <strong><?=$info['chrShow']?></strong>
		<div style='padding-top:10px; font-weight:bold;'><?=$invitesused?> of <?=$info['intInvites']?> allocations filled</div>
	</div>
	<div class='innerbody'>
	<table cellpadding='5' cellspacing='0' style='width:100%;'>
		<tr>
			<td style='width:34%; vertical-align:top;'>
				<form action="" method="post" id="idForm" onsubmit="return error_check()">
				<table cellpadding='5' cellspacing='0' style='width:100%;'>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Contact Name:</td>
						<td style='text-align:left;'><?=$info['chrContactFirst']?> <?=$info['chrContactLast']?></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Company:</td>
						<td style='text-align:left;'><?=$info['chrCompany']?></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Category:</td>
						<td style='text-align:left;'><?=$info['chrCategory']?></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Contact Type:</td>
						<td style='text-align:left;'><img title='<?=($info['bCType']==1?'Special Guest':'Guest or VIP')?>' src="<?=$BF?>images/<?=($info['bCType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Invite Type:</td>
						<td style='text-align:left;'><input type='radio' value='0' name='bType'<?=($info['bType'] == 0?" checked='checked'":"")?> /><img src="<?=$BF?>images/circle_gold.png" width="11" height="11" />&nbsp;<input type='radio' value='1' name='bType'<?=($info['bType'] == 1?" checked='checked'":"")?> /><img src="<?=$BF?>images/circle_red.png" width="11" height="11" /></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Guests:</td>
						<td style='text-align:left;'><input type="text" size="2" name="intGuests" id="intGuests" value="<?=$info['intGuests']?>"<?=(in_array($info['idStatus'],array(3,4,7))?" disabled='disabled'" : '')?> onchange='checkguests(this.value);' /></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Status:</td>
						<td style='text-align:left;'><select style='width:115px;' name="idStatus" id="idStatus" onchange='enableguests(this.value);'>
			<?
						foreach($iStatus AS $k => $v) {
							if(in_array($k,array(2,3,4,5,6,7,$row['idStatus']))) {
			?>
								<option<?=($info['idStatus']==$k?' selected="selected"':'')?> value='<?=$k?>'><?=$v?></option>					
			<?
							}
						}
			?>
							</select></td>
					</tr>
				</table>
				<div style='padding-top:10px;'>
					<input type="submit" name="Save" value="Save" id='Save_button' onclick='document.getElementById("SavePressed").value=1;'/>
					<input type="hidden" name="id" value="<?=$_REQUEST['id']?>" />
					<input type="hidden" id="SavePressed" value="0" />
				</div>
				</form>
			</td>
			<td style='width:33%; vertical-align:top;'>
<?
				// Lets get the notes for this show.
				$notes = database_query("SELECT dtStamp, txtNote FROM Notes WHERE idType=3 AND idRecord='".$info['ID']."' ORDER BY dtStamp DESC","Getting Invite Notes");
?>
				<strong>Invite Notes</strong>&nbsp;&nbsp;&nbsp;<a style='cursor: pointer;' onclick='javascript:window.open("popup_notes.php?id=<?=$info['ID']?>&amp;type=3","new","width=600,height=400,resizable=1,scrollbars=1");'>[ADD NOTE]</a>
				<div style='border:1px solid #999; height:200px; padding:5px; overflow:auto; background:white;'>
<?
				$cnt = 0;
				while($row = mysqli_fetch_assoc($notes)) {
					$cnt++;
?>
					<p><?=date('F j, Y g:i a',strtotime($row['dtStamp']))?><br/><?=nl2br($row['txtNote'])?></p>
<?					
				}
				if($cnt == 0) {
?>
					<p>No notes to display.</p>
<?					
				}
?>				
				</div>
			</td>
			<td style='width:33%; vertical-align:top;'>
<?
				// Lets get the notes for this show.
				$history = database_query("SELECT S.chrName AS chrShow, I.dtStamp, Status.chrStatus
											FROM Shows AS S
											JOIN Invites AS I ON I.idShow=S.ID
											JOIN iStatus AS Status ON I.idStatus=Status.ID
											WHERE I.idContact='".$info['idContact']."' AND I.ID != '".$info['ID']."'
											ORDER BY dtStamp DESC","Getting Invite History");
?>
				<strong>Event History</strong>
				<div style='border:1px solid #999; height:200px; padding:5px; overflow:auto; background:white;'>
<?
				$cnt = 0;
				while($row = mysqli_fetch_assoc($history)) {
					$cnt++;
?>
					<p><?=date('F j, Y g:i a',strtotime($row['dtStamp']))?><br/>-<?=$row['chrShow'].'<br />&nbsp;&nbsp;Status: '.$row['chrStatus']?></p>
<?					
				}
				if($cnt == 0) {
?>
					<p>No History to display.</p>
<?					
				}
?>				
				</div>
			</td>

		</tr>
	</table>
	</div>
	<table cellpadding='0' cellspacing='0' style='padding-top:10px;'>
		<tr>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_gold.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Guest or VIP</td>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_red.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Special Guest</td>
		</tr>
	</table>	
<?	
	include($BF. 'includes/bottom.php');
?>