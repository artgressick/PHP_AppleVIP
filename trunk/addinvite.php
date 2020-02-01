<?	
	$BF = '';
	$active = "invites";
	$title = 'Add Invite';
	require($BF. '_lib.php');
	
	parse_str(base64_decode($_REQUEST['d']),$info);
	
	if ($info['key'] != $_SESSION['idUser'] ) { header("Location: index.php");	die(); }


	$q = "SELECT ID, chrFirst,chrLast
		FROM Users
		WHERE !bDeleted AND ID='". $info['to']."'";
	$userinfo = fetch_database_query($q,"Getting all users");
	$showinfo = fetch_database_query("SELECT chrName FROM Shows WHERE ID=".$_REQUEST['idShow'],"Get Show Info");
	
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
							if($ileft < 0) {
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
		if($_POST['moveTo'] == 'invites.php' && $_SESSION['inviteRefer'] != '') {
			$_POST['moveTo'] .= '?'.$_SESSION['inviteRefer'];
			unset($_SESSION['inviteRefer']);
		}
		$_SESSION['postUser'] = $info['to'];
		header("Location: ".$_POST['moveTo']);	
		die();
	}
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast, chrFirst"; }
	
	$q = "SELECT Contacts.ID, chrFirst, chrLast, bType,chrCompany,Categories.chrCategory,
					  (SELECT DupCheck.ID 
				  FROM Invites AS DupCheck 
				  JOIN Contacts AS DupContact ON DupCheck.idContact=DupContact.ID 
				  WHERE !DupCheck.bDeleted AND Contacts.chrFirst = DupContact.chrFirst AND Contacts.chrLast = DupContact.chrLast AND DupCheck.idShow='".$_REQUEST['idShow']."' AND DupCheck.idUser != Contacts.idUser AND DupCheck.idStatus IN (2,3,4,5,6,7,8,9)
				  LIMIT 1 ) as idDuplicate
	 FROM Contacts
	 LEFT JOIN Categories ON Contacts.idCategory=Categories.ID
	 WHERE !Contacts.bDeleted AND idUser='".$info['to']."' AND Contacts.ID NOT IN (SELECT idContact FROM Invites WHERE !Invites.bDeleted AND idShow='".$_REQUEST['idShow']."' AND idUser='".$info['to']."')";

	if(@$_REQUEST['chrSearch'] != '') {  // if there is a search term 
		$q .= " AND ((lower(chrFirst) LIKE '%" . strtolower($_REQUEST['chrSearch']) . "%') OR (lower(chrLast) LIKE '%" . strtolower($_REQUEST['chrSearch']) . "%') OR (lower(chrEmail) LIKE '%" . strtolower($_REQUEST['chrSearch']) . "%') OR (lower(concat(chrFirst,' ',chrLast)) LIKE '%" . strtolower($_REQUEST['chrSearch']) . "%'))";
	}
	$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	$result = database_query($q,"Getting all contacts");

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
	</script>
<?
	include($BF. 'includes/top.php');
?>

	<form id="idFilter" name="idFilter" method="get" style='padding:0;margin:0;'>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
			<tr>
				<td class="left"></td>
				<td class="title">Show Invites </td>
				<td class="title_right" style='text-align: right; padding-right: 10px;'><input type='text' name='chrSearch' value='<?=(isset($_REQUEST['chrSearch'])?$_REQUEST['chrSearch']:'')?>' /> <input type='submit' name='search' value='Search' /></td>
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
			<th style='width: 0.1cm;'></th>
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
				<td class='options'><? if(isset($row['idDuplicate'])) { ?><img src='<?=$BF?>images/caution.png' width='11' height='12' alt='Duplicate Email Address Found for Event' onmouseover="showExtra('user<?=$row['ID']?>',this)" onmouseout="hideExtra('user<?=$row['ID']?>')" /> <? DupInfo("'".$row['chrFirst']."'","'".$row['chrLast']."'",$info['to'], $row['ID']); } ?></td>
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
		<div style='text-align:right;padding-top:5px;'>
			<input type='submit' name='add' value='Add to Invite List' onclick="document.getElementById('moveTo').value='addinvite.php?<?=$_SERVER['QUERY_STRING']?>';" />&nbsp;&nbsp;<input type='submit' name='add' value='Add to List and return to Invites' onclick="document.getElementById('moveTo').value='invites.php';" /> 
			<input type='hidden' id='ids' name='ids' value='<?=substr($allids,0,-1)?>' />
			<input type='hidden' id='moveTo' name='moveTo' value='' />
		</div>
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