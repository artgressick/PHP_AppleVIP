<?	
	$BF = '../';
	require($BF. '_lib.php');
	
	$title = 'Accounts';

	$row = fetch_database_query("SELECT * FROM Users WHERE ID=".$_REQUEST['id'],'getting user info');

	if(count($_POST)) {
		if(!$_SESSION['bMasq']) {
			$_SESSION['idOldUser'] = $_SESSION['idUser'];
			$_SESSION['chrOldFirst'] = $_SESSION['chrFirst'];
			$_SESSION['chrOldLast'] = $_SESSION['chrLast'];
			$_SESSION['bMasq'] = true;
		}
		$_SESSION['chrEmail'] = $row["chrEmail"];
		$_SESSION['idUser'] = $row["ID"];
		$_SESSION['chrFirst'] = $row["chrFirst"];
		$_SESSION['chrLast'] = $row["chrLast"];
		$_SESSION['idRight'] = $row["idRight"];
	
		header("Location: ". $BF. "index.php");
		die();
	}
	include($BF. 'includes/meta.php');
	$active = 'admin';
	$subactive = 'accounts';
	include($BF. 'includes/top.php');
	
?>
<form id="idForm" name="idForm" method="post" action=''>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
	<tr>
		<td class="left"></td>
		<td class="title">Masquerade</td>
		<td class="right"></form></td>
	</tr>
</table>

<div class='instructions'>To masquerade as this person, click on the "Masquerade" button.</div>
<div class='innerbody'>
	
	<p>You are about to Masquerade as <strong><?=$row['chrFirst']?> <?=$row['chrLast']?></strong>.</p>
	
	<input type='submit' value='Masquerade' />
	<input type='button' value='Cancel' onclick='history.go(-1);' />
	<input type='hidden' name='id' id='id' value='<?=$_REQUEST['id']?>' />

</div>
</form>

<?
	include($BF. 'includes/bottom.php');
?>
