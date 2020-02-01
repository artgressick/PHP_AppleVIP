<?	
	$BF = '';
	require($BF. '_lib.php');
	// Checking request variables
	
	if($_SESSION['idRight'] == 4) {
		header('Location: '. $BF .'checkin.php');
		die();
	}
	
	$title = 'Home Page';
	include($BF. 'includes/meta.php');

	//Lets first grab any shows
	
	$q = "SELECT Shows.ID, Shows.chrName, Users.chrFirst, Users.chrLast, Users.ID as idUser,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus IN (2,5,6,8,9)) AS intInvites,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus IN (6,8)) AS intAccepted,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus = 7) AS intRegrets,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus IN (2,5,9)) AS Unconfirmed
	FROM Shows
	JOIN SponsorsbyShow AS SBS ON SBS.idShow=Shows.ID AND !SBS.bDeleted
	JOIN Users ON SBS.idUser=Users.ID
	LEFT JOIN SponsorsByUser AS SBU ON SBS.idUser=SBU.idSponsor
	WHERE !Shows.bDeleted AND Shows.idStatus IN (2,3) AND (SBS.idUser='".$_SESSION['idUser']."' OR SBU.idUser='".$_SESSION['idUser']."')
	ORDER BY Shows.dBegin DESC, Shows.chrName
	";
	$shows = database_query($q,"Getting Shows");

	$q = "SELECT Shows.ID, Shows.chrName, Users.chrFirst, Users.chrLast, Users.ID as idUser,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus IN (2,5,6,8,9)) AS intInvites,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus IN (6,8)) AS intAccepted,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus = 7) AS intRegrets,
		(SELECT COUNT(I.ID) FROM Invites AS I WHERE !I.bDeleted AND I.idShow=Shows.ID AND I.idUser=Users.ID AND idStatus IN (2,5,9)) AS Unconfirmed
	FROM Shows
	JOIN SponsorsbyShow AS SBS ON SBS.idShow=Shows.ID AND !SBS.bDeleted
	JOIN Users ON SBS.idUser=Users.ID
	LEFT JOIN SponsorsByUser AS SBU ON SBS.idUser=SBU.idSponsor
	WHERE !Shows.bDeleted AND Shows.idStatus=4 AND (SBS.idUser='".$_SESSION['idUser']."' OR SBU.idUser='".$_SESSION['idUser']."')
	ORDER BY Shows.dBegin DESC, Shows.chrName
	";
	$archivedshows = database_query($q,"Getting Shows");

	// This is for the sorting of the rows and columns.  We must set the default order and name
	include($BF. 'components/list/sortList.php'); 

	$active = 'home';
	include($BF. 'includes/top.php');
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title"></td>
		<td class="right"></td>
	</tr>
</table>
<div class='innerbody'>
<?=messages()?>
<div style='font-size:14px; font-weight:bold;'>Active Events</div>
<?
	$count=0;
	$idShow = 0;	
	while ($row = mysqli_fetch_assoc($shows)) {
		if($row['ID'] != $idShow) {
			$idShow = $row['ID'];
			if($count > 0) {
?>
	</table>
<?				
			}
?>
	
	<table id='List' class='List' style='width: 100%; padding-top:5px;' cellpadding="0" cellspacing="0">
		<tr>
			<th style='width:50%;'><?=$row['chrName']?></th>
			<th>Total Invites</th>
			<th>Total Accepted</th>
			<th>Total Regrets</th>
			<th>Total Unconfirmed</th>
		</tr>
<?
		}
		$link = 'location.href="invites.php?idShow='.$row['ID'].'&d='.base64_encode("to=".$row['idUser']."&key=".$_SESSION['idUser']).'";';
?>
		<tr id='tr<?=$row['idUser'].$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' onmouseover='RowHighlight("tr<?=$row['idUser'].$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['idUser'].$row['ID']?>");'>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=($row['idUser'] == $_SESSION['idUser']?'My':$row['chrFirst'].' '.$row['chrLast'])?> List</td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['intInvites']?></td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['intAccepted']?></td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['intRegrets']?></td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['Unconfirmed']?></td>
		</tr>
<?	
	}
	if($count == 0) { ?>
	<div style='padding-left:10px; padding-top:5px; font-style:italic;'>No current Events Found</div>
<?
	} else {
?>
	</table>
<?
	}
?>
	
<div style='font-size:14px; font-weight:bold; padding-top:10px;'>Archived Events</div>
<?
	$count=0;
	$idShow = 0;	
	while ($row = mysqli_fetch_assoc($archivedshows)) {
		if($row['ID'] != $idShow) {
			$idShow = $row['ID'];
			if($count > 0) {
?>
	</table>
<?				
			}
?>
	
	<table id='List' class='List' style='width: 100%; padding-top:5px;' cellpadding="0" cellspacing="0">
		<tr>
			<th style='width:50%;'><?=$row['chrName']?></th>
			<th>Total Invites</th>
			<th>Total Accepted/Attended</th>
			<th>Total Regrets</th>
			<th>Total Unconfirmed</th>
		</tr>
<?
		}
		$link = 'location.href="listinvites.php?idShow='.$row['ID'].'&d='.base64_encode("to=".$row['idUser']."&key=".$_SESSION['idUser']).'";';
?>
		<tr id='tr<?=$row['idUser'].$row['ID']?>' class='<?=($count++%2?'ListLineOdd':'ListLineEven')?>' onmouseover='RowHighlight("tr<?=$row['idUser'].$row['ID']?>");' onmouseout='UnRowHighlight("tr<?=$row['idUser'].$row['ID']?>");'>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=($row['idUser'] == $_SESSION['idUser']?'My':$row['chrFirst'].' '.$row['chrLast'])?> List</td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['intInvites']?></td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['intAccepted']?></td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['intRegrets']?></td>
			<td style='cursor: pointer;' onclick='<?=$link?>'><?=$row['Unconfirmed']?></td>
		</tr>
<?	
	}
	if($count == 0) { ?>
	<div style='padding-left:10px; padding-top:5px; font-style:italic;'>No archived Events Found</div>
<?
	} else {
?>
	</table>
<?
	}
?>
</div>
<?
	include($BF. 'includes/bottom.php');
?>
