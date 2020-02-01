<?php
	$BF = "../"; //This is the BASE FOLDER.  This should be located at the top of every page with the proper set of '../'s to find the root folder 
	require($BF. '_lib.php');
	
	parse_str(base64_decode($_REQUEST['d']),$info);
	
	if ( $info['key'] != $_SESSION['idUser'] ) { die(); }


	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 
	if(!isset($_REQUEST['sortCol'])) { $_REQUEST['sortCol'] = "chrLast, chrFirst"; }
		$q = "SELECT ID, chrFirst,chrLast
		FROM Users
		WHERE !bDeleted AND ID='". $info['to']."'";
	$userinfo = fetch_database_query($q,"Getting all users");
	
	if(isset($_POST['add']) && $_POST['add'] == 'Add Contacts and Close') {
		if(isset($_POST['userids'])) {
			foreach($_POST['userids'] as $id) {
	
				$test = database_query("SELECT ID FROM Invites WHERE idShow=".$_REQUEST['idShow']." AND idUser=".$info['to']." AND idContact=".$id,"Seeing if contact has already been added");
				
				if(mysqli_num_rows($test) == 0) {
					
					$tmp = database_query("INSERT INTO Invites SET 
							idShow='".$_REQUEST['idShow']."', 
							idUser='".$info['to']."', 
							idContact='".$id."',
							idInviteStatus='".$_POST['inviteStatus']."'"
					,"Inserting Entry");
				
				}
			}
		}
?>
<script type='text/javascript' language="javascript">
	function refresher() {
			window.opener.location.href='invites.php?id=<?=$info['idList']?>';
			
	}

	setTimeout('refresher();window.close();',500);
</script>
<?
	
	}
	
	
	$q = "SELECT Contacts.ID, chrFirst, chrLast, bType,chrCompany,Categories.chrCategory
	 FROM Contacts
	 JOIN Categories ON Contacts.idCategory=Categories.ID
	 WHERE !Contacts.bDeleted AND idUser='".$info['to']."'";
		

	if(@$_REQUEST['chrSearch'] != '') {  // if there is a search term 
		$q .= " AND ((chrFirst LIKE '%" . $_REQUEST['chrSearch'] . "%') OR (chrLast LIKE '%" . $_REQUEST['chrSearch'] . "%') OR (chrEmail LIKE '%" . $_REQUEST['chrSearch'] . "%'))";
	}
	$q .= " ORDER BY " . $_REQUEST['sortCol'] . " " . $_REQUEST['ordCol'];
	$result = database_query($q,"Getting all contacts");

	$statuses = database_query("SELECT ID,chrStatus FROM InviteStatus","getting statuses");
	
	$title = "Popup - Add Contact";
	include($BF. 'includes/meta.php');
?>
<script type='text/javascript' language="javascript">
function associate(id, fname, lname, btype, company, category) {
	var inviteStat = document.getElementById("inviteStatus").value;
	if(inviteStat == 1) {
		var tblName = "Listinvite";
	} else if(inviteStat == 2) {
		var tblName = "Listwaitlist";
	} else if(inviteStat == 3) {
		var tblName = "Listremoved";
	} 
	
	
	var tbl = window.opener.document.getElementById(tblName).innerHTML;

	var post = 0;
	if(!window.opener.document.getElementById("invitetr"+id) && !window.opener.document.getElementById("waitlisttr"+id) && !window.opener.document.getElementById("removedtr"+id)) {
		post = 1;
	} else {
		if(window.opener.document.getElementById(tblName +"tr"+id).style.display == "none") {
			window.opener.document.getElementById(tblName +"tr"+id).style.display = "";
			post = 1;
		}
	}

	if(post == 1) {
		repaintmini(tblName);

		var poststr = "idShow=<?=$_REQUEST['idShow']?>" +
			"&idContact=" + id + 
			"&idUser=<?=$info['to']?>" + 
			"&idInviteStatus=" + inviteStat + 
        	"&postType=" + encodeURI( "quickInsert" );

      	postInfo('ajax_contacts.php', poststr);
		
		setTimeout('refresher()',500);		
	}
}
function refresher() {
		window.opener.location.href='reviewinvites.php?id=<?=$info['idList']?>';
}
</script>

<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/popup.js"></script>
<?
//	include($BF. 'includes/top_popup.php');
?>

	<form action="" method="post">
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
			<tr>
				<td class="left"></td>
				<td class="title">Show Invites </td>
				<td class="title" style='text-align: right; padding-right: 10px;'><input type='text' name='chrSearch' /> <input type='submit' name='search' value='Search' /></td>
				<td class="title_right">
					<span style='white-space: nowrap; color: white;'>Choose Status: 
						<select id='inviteStatus' name='inviteStatus'>
<?	while($row = mysqli_fetch_assoc($statuses)) { ?>
							<option value='<?=$row["ID"]?>'><?=$row["chrStatus"]?></option>
<?	} ?>
						</select>
					</span>
				</td>
				<td class="right"></td>
			</tr>
		</table>
		
	<div class='instructions'>Click on the Person to add him/her to <strong><?=$userinfo['chrFirst']?> <?=$userinfo['chrLast']?></strong> Invites.</div>
	
	<div class='innerbody'>	

	<table id='List' class='List' style='width: 100%;' cellpadding="0" cellspacing="0">
		<tr>
			<th>&nbsp;</th>
			<th>Type</th>
			<? sortList('First Name', 'chrFirst', '','idShow='.$_REQUEST["idShow"].'&d='.$_REQUEST['d']); ?>
			<? sortList('Last Name', 'chrLast', '','idShow='.$_REQUEST["idShow"].'&d='.$_REQUEST['d']); ?>
			<? sortList('Company', 'chrCompany', '','idShow='.$_REQUEST["idShow"].'&d='.$_REQUEST['d']); ?>
			<? sortList('Category', 'chrCategory', '','idShow='.$_REQUEST["idShow"].'&d='.$_REQUEST['d']); ?>
		</tr>
<?  $count=0;	
	while ($row = mysqli_fetch_assoc($result)) {
	$link = $row['ID'].", '".jsencode($row['chrFirst'])."', '".jsencode($row['chrLast'])."','".$row['bType']."','".jsencode($row['chrCompany'])."','".$row['chrCategory']."'";
	 ?>
			<tr id='tr<?=$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' 
			onmouseover='RowHighlight("tr<?=$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['ID']?>");'>
				<td style='cursor: pointer;'><input type='checkbox' name='userids[]' value='<?=$row['ID']?>' /></td>
				<td style='cursor: pointer;' onclick="javascript:associate(<?=$link?>)"
					><a class='listlink' href="javascript:associate(<?=$link?>)"><img src="<?=$BF?>images/<?=($row['bType'] == 1 ? "circle_red" : "circle_gold" )?>.png" width="11" height="11" /></a></td>
				<td style='cursor: pointer;' onclick="javascript:associate(<?=$link?>)"
					><a class='listlink' href="javascript:associate(<?=$link?>)"><?=$row['chrFirst']?></a></td>
				<td style='cursor: pointer;' onclick="javascript:associate(<?=$link?>)"
					><a class='listlink' href="javascript:associate(<?=$link?>)"><?=$row['chrLast']?></a></td>
				<td style='cursor: pointer;' onclick="javascript:associate(<?=$link?>)"
					><a class='listlink' href="javascript:associate(<?=$link?>)"><?=$row['chrCompany']?></a></td>
				<td style='cursor: pointer;' onclick="javascript:associate(<?=$link?>)"
					><a class='listlink' href="javascript:associate(<?=$link?>)"><?=$row['chrCategory']?></a></td>
				</tr>
<?	} 
if($count == 0) { ?>
			<tr>
				<td align="center" colspan="5">No Contacts to display</td>
			</tr>
<?	} ?>
		</table>
	
	
	</div>
	<div align='center'>
		<input type='submit' name='add' value='Add Contacts and Close' /> &nbsp;&nbsp;&nbsp; <input type='button' onclick='window.close();' value='Close this Window' />
	</div>
</form>
</body>
</html>