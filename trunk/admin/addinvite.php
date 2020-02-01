<?	
	$BF = '../';
	$active = 'admin';
	$subactive = 'invites';
	$title = 'Add Invite';
	require($BF. '_lib.php');
	
	parse_str(base64_decode($_REQUEST['d']),$info);
	
	if ( $info['key'] != $_SESSION['idUser'] ) { header("Location: index.php");	die(); }


	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast, chrFirst"; }
		$q = "SELECT ID, chrFirst,chrLast
		FROM Users
		WHERE !bDeleted AND ID='". $info['to']."'";
	$userinfo = fetch_database_query($q,"Getting all users");
	$showinfo = fetch_database_query("SELECT chrName FROM Shows WHERE ID='".$_REQUEST['idShow']."'","Get Show Info");
	
	$q = "SELECT SponsorsbyShow.ID, chrStatus, idReviewStatus, intInvites
		 FROM SponsorsbyShow
		 LEFT JOIN ReviewStatus ON ReviewStatus.ID=idReviewStatus
		 WHERE idShow=". $_REQUEST['idShow'] ." AND idUser='".$info['to'] ."'";

	$sbs = fetch_database_query($q, "Getting Invite Page Status");
	
	$q = "SELECT Invites.ID, Invites.intGuests
			FROM Invites
			JOIN Contacts ON Invites.idContact=Contacts.ID
			WHERE !Invites.bDeleted AND Invites.idShow='". $_REQUEST['idShow'] ."' AND Invites.idUser='". $info['to'] ."' AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted";

	$counttmp = database_query($q, "Get invites and Guests Allowed");	
	$invitecount = mysqli_num_rows($counttmp);
	$guestcount = 0;
	while ($row = mysqli_fetch_assoc($counttmp)) {
		$guestcount = $guestcount + $row['intGuests'];
	}
	$invitesused = $invitecount + $guestcount;

	$status = database_query("SELECT ID,chrStatus FROM iStatus WHERE !bDeleted AND ID IN (2,3)","getting status");
	while($row = mysqli_fetch_assoc($status)) {
		$invitestatus[$row['ID']] = $row['chrStatus'];
	}	
	
	if(isset($_POST['moveTo']) && $_POST['moveTo'] != '') {
		if(isset($_POST['ids']) && $_POST['ids'] != '') {
			$ids = explode(',',$_POST['ids']);
			$ileft = ($sbs['intInvites'] - $invitesused);
			foreach($ids as $k => $id) {
				if(isset($_POST['inviteStatus'.$id]) && $_POST['inviteStatus'.$id] != '') {
					$test = database_query("SELECT ID FROM Invites WHERE !bDeleted AND idShow=".$_REQUEST['idShow']." AND idUser=".$info['to']." AND idContact=".$id,"Seeing if contact has already been added");
					if(mysqli_num_rows($test) == 0) {
						if($_POST['inviteStatus'.$id] == '2') {
							$ileft--;
							if($ileft <= 0) {
								$_POST['inviteStatus'.$id] = '3';
							}
						}
						$tmp = database_query("INSERT INTO Invites SET 
								idShow='".$_REQUEST['idShow']."', 
								idUser='".$info['to']."', 
								bType='".$_POST['bType'.$id]."',
								idContact='".$id."',
								idStatus='".$_POST['inviteStatus'.$id]."',
								dtStamp=now()"
								
						,"Inserting Entry");
						
						global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
						$newID = mysqli_insert_id($mysqli_connection);
						
						$tmp = database_query("INSERT INTO Notes SET
								idType=3,
								idUser='".$_SESSION['idUser']."',
								idRecord='".$newID."',
								dtStamp=now(),
								txtNote = '".encode('- Invitation created
- Status set to '.$invitestatus[$_POST['inviteStatus'.$id]].'
 by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'])."'
						","Insert Note");
					}
				}
			}
		}
		$q = "SELECT ID
			FROM SponsorsbyShow
			WHERE idShow='".$_REQUEST['idShow']."' AND idUser='".$info['to']."'";
		$tmp = fetch_database_query($q,"Getting ID");
		
		header("Location: reviewinvites.php?id=".$tmp['ID']);	
		die();
	}

	
	$q = "SELECT Contacts.ID, chrFirst, chrLast, bType,chrCompany,Categories.chrCategory
	 FROM Contacts
	 LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
	 WHERE !Contacts.bDeleted AND idUser='".$info['to']."' AND Contacts.ID NOT IN (SELECT idContact FROM Invites WHERE !Invites.bDeleted AND idShow='".$_REQUEST['idShow']."' AND idUser='".$info['to']."')";

	if(@$_REQUEST['chrSearch'] != '') {  // if there is a search term 
		$q .= " AND ((chrFirst LIKE '%" . $_REQUEST['chrSearch'] . "%') OR (chrLast LIKE '%" . $_REQUEST['chrSearch'] . "%') OR (chrEmail LIKE '%" . $_REQUEST['chrSearch'] . "%') OR (lower(concat(chrFirst,' ',chrLast)) LIKE '%" . strtolower($_REQUEST['chrSearch']) . "%'))";
	}
	$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	$result = database_query($q,"Getting all contacts");

	include($BF. 'includes/meta.php');
?>
	<script language="JavaScript" type='text/javascript'>
		function massstatus(val) {
			var ids = document.getElementById('ids').value;
			if(ids != '') {
				var allids = ids.split(',');
				for(i=0;i<allids.length;i++) {
					document.getElementById('inviteStatus'+allids[i]).value=val;
				}
			}
		}
	</script>
<?
	include($BF. 'includes/top.php');
	
//	include($BF. 'includes/top_popup.php');
?>

	<form id="idFilter" name="idFilter" method="get" style='padding:0;margin:0;'>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
			<tr>
				<td class="left"></td>
				<td class="title">Show Invites </td>
				<td class="title_right" style='text-align: right; padding-right: 10px;'><input type='text' name='chrSearch' /> <input type='submit' name='search' value='Search' /></td>
				<td class="right"></td>
			</tr>
		</table>
		<input type='hidden' name='d' value='<?=$_REQUEST['d']?>' />
		<input type='hidden' name='idShow' value='<?=$_REQUEST['idShow']?>' />
	</form>
	<div class='instructions'>
		Click on the check box to add contact to <strong><?=$userinfo['chrFirst']?> <?=$userinfo['chrLast']?></strong> Invites for show: <strong><?=$showinfo['chrName']?></strong>
		<div style='padding-top:10px; font-weight:bold;'><?=$invitesused?> of <?=$sbs['intInvites']?> allocations filled</div>
	</div>
<form id="idForm" name="idForm" method="POST">	
	<div class='innerbody'>	
	<div style='text-align:right; padding-bottom:5px;'>
		<input type='submit' name='add' value='Add to Invite List' onclick="document.getElementById('moveTo').value='addinvite.php?<?=$_SERVER['QUERY_STRING']?>';" />&nbsp;&nbsp;<input type='submit' name='add' value='Add to List and return to Invites' onclick="document.getElementById('moveTo').value='invites.php';" /> 
	</div>
	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th style='width:50px; padding-top:0; padding-bottom:0;'>
				<select id='inviteStatus' name='inviteStatus' onchange='massstatus(this.value);'>
					<option value=''></option>
<?	foreach($invitestatus as $k => $v) { ?>
					<option value='<?=$k?>'><?=$v?></option>
<?	} ?>
				</select>
			</th>
			<? $url = 'idShow='.$_REQUEST['idShow'].'&d='.$_REQUEST['d']; ?>
			<th colspan='2'>Type</th>
			<? sortList('First Name', 'chrFirst','',$url); ?>
			<? sortList('Last Name', 'chrLast','',$url); ?>
			<? sortList('Company', 'chrCompany', '',$url); ?>
			<? sortList('Category', 'chrCategory', '',$url); ?>
		</tr>
<?  $count=0;	
	$allids = '';
	while ($row = mysqli_fetch_assoc($result)) {
		$allids .= $row['ID'].',';
?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>

				<td style=''>
					<select id='inviteStatus<?=$row['ID']?>' name='inviteStatus<?=$row['ID']?>'>
						<option value=''></option>
<?	foreach($invitestatus as $k => $v) { ?>
						<option value='<?=$k?>'><?=$v?></option>
<?	} ?>
					</select>
				</td>
				<td style='white-space:nowrap; width:20px;'><a class='listlink'><input type='radio' value='0' name='bType<?=$row['ID']?>'<?=($row['bType'] == 0?" checked='checked'":"")?> /><img src="<?=$BF?>images/circle_gold.png" width="11" height="11" /></a></td>
				<td style='white-space:nowrap; width:20px;'><a class='listlink'><input type='radio' value='1' name='bType<?=$row['ID']?>'<?=($row['bType'] == 1?" checked='checked'":"")?> /><img src="<?=$BF?>images/circle_red.png" width="11" height="11" /></a></td>
				<td style=''><a class='listlink'><?=$row['chrFirst']?></a></td>
				<td style=''><a class='listlink'><?=$row['chrLast']?></a></td>
				<td style=''><a class='listlink'><?=$row['chrCompany']?></a></td>
				<td style=''><a class='listlink'><?=$row['chrCategory']?></a></td>
				</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan="9" style='height:20px;'>No Contacts avaliable to add</td>
			</tr>
<?	} ?>

		</table>
	</div>
	<div style='text-align:right;'>
		<input type='submit' name='add' value='Add to Invite List' onclick="document.getElementById('moveTo').value='addinvite.php?<?=$_SERVER['QUERY_STRING']?>';" />&nbsp;&nbsp;<input type='submit' name='add' value='Add to List and return to Invites' onclick="document.getElementById('moveTo').value='invites.php';" /> 
		<input type='hidden' id='ids' name='ids' value='<?=substr($allids,0,-1)?>' />
		<input type='hidden' id='moveTo' name='moveTo' value='' />
	</div>
	<table cellpadding='0' cellspacing='0' style='padding-top:10px;'>
		<tr>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_gold.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Guest or VIP</td>
			<td style='width:10px; vertical-align:middle; text-align:center;'><img src="<?=$BF?>images/circle_red.png" /></td>
			<td style='vertical-align:left; text-align:center; padding:0 20px 0 5px;'>Special Guest</td>
		</tr>
	</table>							
</form>
<?
	include($BF. 'includes/bottom.php');
?>