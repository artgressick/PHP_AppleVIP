<?	
	$BF = '';
	$active = "home";
	$title = 'View Invites Page';
	require($BF. '_lib.php');
	
	$_SESSION['inviteRefer'] = $_SERVER['QUERY_STRING'];
	if(!isset($_REQUEST['idShow']) || !is_numeric($_REQUEST['idShow']) || $_REQUEST['idShow'] == "" || !isset($_REQUEST['d']) || $_REQUEST['d'] == '') {
		ErrorPage();
	}
	parse_str(base64_decode($_REQUEST['d']),$info);
	if ( $info['key'] != $_SESSION['idUser'] ) { ErrorPage(); }
	$_POST['idUser'] = $info['to'];

	$tmpStatus = database_query("SELECT ID, chrStatus FROM iStatus WHERE !bDeleted ORDER BY dOrder","Getting Status");
	$iStatus = array();
	while($row = mysqli_fetch_assoc($tmpStatus)) {
		$iStatus[$row['ID']] = $row['chrStatus'];
	}
		
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
	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast,chrFirst"; }
	
	if(isset($_REQUEST['idShow'])) {
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
			WHERE !Invites.bDeleted AND Invites.idStatus IN (2,5,6,8,9) AND !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."'
			ORDER BY ". $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
		$invite_result = database_query($q,"Getting Invited contacts");
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
			WHERE !Invites.bDeleted AND Invites.idStatus = 3 AND !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."'
			ORDER BY ". $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
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
			WHERE !Invites.bDeleted AND Invites.idStatus IN (4,7) AND !Contacts.bDeleted AND Contacts.idUser='".$_POST['idUser']."'
			ORDER BY ". $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
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
		<td class="title"></td>
		<td class="right"></td>
	</tr>
</table>
</form>
<?
	if(is_numeric($_REQUEST['idShow']) && $_REQUEST['idShow'] != 0 && is_numeric($_POST['idUser']) && $_POST['idUser'] != 0) {
?>
<div class='innerbody'>
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
				$link = 'location.href="viewinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
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
				<td><?=$row['intGuests']?></td>
				<td style='white-space:nowrap;'><?=$iStatus[$row['idStatus']]?></td>
			</tr>
<?
		}
?>
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
				$link = 'location.href="viewinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
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
				<td><?=$row['intGuests']?></td>
				<td style='white-space:nowrap;'><?=$iStatus[$row['idStatus']]?></td>
			</tr>
<?
		}
?>
	</table>
	<div style='font-size: 14px; font-weight: bold; padding-top:10px;'>Deleted or Regrets</div>
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
				$link = 'location.href="viewinvite.php?id='.$row['idInvite'].'";';
				$link_style = 'cursor:pointer;';
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
				<td><?=$row['intGuests']?></td>
				<td style='white-space:nowrap;'><?=$iStatus[$row['idStatus']]?></td>
			</tr>
<?
		}
?>
	</table>
	<div style="padding-top:10px;">List Status: <strong><?=$sbs['chrStatus']?></strong></div>
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
		</tr>
	</table>							

<?

	include($BF. 'includes/bottom.php');
?>