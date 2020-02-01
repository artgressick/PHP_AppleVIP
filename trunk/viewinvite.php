<?	
	$BF = '';
	$active = "invites";
	$title = 'View Invite';
	require($BF. '_lib.php');
	
	if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
		$_SESSION['errorMessages'][] = "Invalid Contact";
		$moveTo = 'listinvites.php?'.$_SESSION['inviteRefer'];
		unset($_SESSION['inviteRefer']);
		header("Location: ".$moveTo);	
		die();
	}
	$info = fetch_database_query("SELECT I.ID, I.bType, I.idStatus, I.intGuests, I.idUser, I.idShow, C.ID AS idContact, C.chrFirst AS chrContactFirst, C.chrLast AS chrContactLast, C.chrCompany, 
									C.bType AS bCType, S.ID AS idShow, S.chrName AS chrShow, Cat.chrCategory, U.ID AS PostUser, U.chrFirst, U.chrLast, SBS.intInvites, RS.chrStatus, SBS.idReviewStatus
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
		$moveTo = 'viewinvites.php?'.$_SESSION['inviteRefer'];
		unset($_SESSION['inviteRefer']);
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
	include($BF. 'includes/meta.php');
	include($BF. 'includes/top.php');
?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">View Invite: <?=$info['chrContactFirst']?> <?=$info['chrContactLast']?></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>
		View Invite Contact: <strong><?=$info['chrContactFirst']?> <?=$info['chrContactLast']?></strong> on <strong><?=$info['chrFirst']?> <?=$info['chrLast']?></strong> Invites for show: <strong><?=$info['chrShow']?></strong>
		<div style='padding-top:10px; font-weight:bold;'><?=$invitesused?> of <?=$info['intInvites']?> allocations filled</div>
	</div>
	<div class='innerbody'>
	<table cellpadding='5' cellspacing='0' style='width:100%;'>
		<tr>
			<td style='width:34%; vertical-align:top;'>
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
						<td style='text-align:left;'><img title='<?=($info['bType']==1?'Special Guest':'Guest or VIP')?>' src="<?=$BF?>images/<?=($info['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Guests:</td>
						<td style='text-align:left;'><?=$info['intGuests']?></td>
					</tr>
					<tr>
						<td style='width:10%; white-space:nowrap; text-align:right; font-weight:bold;'>Status:</td>
						<td style='text-align:left;'><?=$iStatus[$info['idStatus']]?></td>
					</tr>
				</table>
				<input type='button' value='Back' onclick='javascript: history.go(-1)' />
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
											WHERE !I.bDeleted AND I.idContact='".$info['idContact']."' AND I.ID != '".$info['ID']."'
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