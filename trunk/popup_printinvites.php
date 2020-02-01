<?	
	$BF = '';
	require($BF. '_lib.php');
	
	parse_str(base64_decode($_REQUEST['d']),$info);
	
	if ( $info['key'] != $_SESSION['idUser'] ) { die(); }
	
	$q = "SELECT Invites.ID as idInvite, Invites.intGuests, Contacts.chrFirst, contacts.chrLast, Contacts.chrCompany, Categories.chrCategory, chrStatus
			FROM Contacts 
			JOIN Categories ON Contacts.idCategory=Categories.ID
			JOIN Invites ON Invites.idContact=Contacts.ID AND idShow='". $_REQUEST['idShow'] ."'
			JOIN iStatus ON Invites.idStatus=iStatus.ID
			WHERE !Invites.bDeleted AND !Contacts.bDeleted AND Contacts.idUser='".$info['to']."'
			ORDER BY chrCategory,iStatus.dInviteOrder,chrLast,chrFirst";
	
		  
	$result = database_query($q,"Getting all Invites");
	$Show = fetch_database_query("SELECT chrName FROM Shows where !bDeleted AND ID='".$_REQUEST['idShow']."'", "getting show");
	$User = fetch_database_query("SELECT chrFirst, chrLast FROM Users WHERE ID='".$info['to']."'","Getting User");
	if (!isset($_REQUEST['idStatus'])) { $_REQUEST['idStatus'] = '%'; }
	?>
	<html>
	<head>
		<title>Print List</title>
	    <style type="text/css">
<!--
body {
	font-family: Arial, Helvetica, sans-serif;
	font-size:12px
}
-->
        </style>
    </head>		
		<body>
		<span style="font-size:24px; color:#000; font-weight:bold;"><?=$User['chrFirst']?> <?=$User['chrLast']?> Invite List for <?=$Show['chrName']?></span>
		<br />
		<br />
		
	<?
	$cat="";
	$count=0;
	while ($row = mysqli_fetch_assoc($result))  {
		
		if ($cat != $row['chrCategory']) {
			$cat = $row['chrCategory'];
			?>
			<?=($count > 0 ? "</table><br />" : "")?>
			<table cellpadding="3" cellspacing="0" border="0" style="width:600;">
				<tr>
					<td colspan="4" style="font-weight:bold; font-size:18px;"><?=$row['chrCategory']?></td>
				</tr>
				<tr>
					<td style="font-weight:bold; font-size:14px; border:solid #999999 1px; width:220px;">Name</td>
					<td style="font-weight:bold; font-size:14px; border:solid #999999 1px;">Company</td>
					<td style="font-weight:bold; font-size:14px; border:solid #999999 1px; width:40px;">Guests</td>
					<td style="font-weight:bold; font-size:14px; border:solid #999999 1px; width:50px;">Status</td>
				</tr>
		<?
		}
		?>
				<tr>
					<td style="font-weight:normal; font-size:12px; border:solid #999999 1px;"><?=$row['chrFirst']?> <?=$row['chrLast']?></td>
					<td style="font-weight:normal; font-size:12px; border:solid #999999 1px;"><?=$row['chrCompany']?>&nbsp;</td>
					<td style="font-weight:normal; font-size:12px; border:solid #999999 1px; text-align:center;"><?=$row['intGuests']?></td>
					<td style="font-weight:normal; font-size:12px; border:solid #999999 1px;"><i><?=$row['chrStatus']?></i></td>
				</tr>
		<?
		$count++;
	
	}
	?>
	</table>
	</body>
	</html>
	<script language="javascript">
	window.print();
	window.close();
	</script>
	
