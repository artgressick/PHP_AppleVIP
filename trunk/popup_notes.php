<?
	$BF = ""; //This is the BASE FOLDER.  This should be located at the top of every page with the proper set of '../'s to find the root folder 
	$title = "Add Note";
	require($BF. '_lib.php');
	include($BF. 'includes/meta.php');

	if(isset($_POST['txtNote'])) { // When doing isset, use a required field.  Faster than the php count funtion.
		$q = "INSERT INTO Notes SET 
			idType='". $_REQUEST['type'] ."',
			idUser='". $_SESSION['idUser'] ."',
			idRecord='". $_REQUEST['id'] ."',
			txtNote='". encode($_POST['txtNote'].($_REQUEST['type']==3?'<br /> by '.$_SESSION['chrFirst'].' '.$_SESSION['chrLast'] : ''))."',
			dtStamp=now()
		";
		database_query($q,"Insert into Notes");
		
		// This is the code for inserting the Audit Page
		// Type 1 means ADD NEW RECORD, change the TABLE NAME also
		global $mysqli_connection;  // This is needed for mysqli to be able to get the "last insert id"
		$newID = mysqli_insert_id($mysqli_connection);
				
		$q = "INSERT INTO Audit SET 
			idType=1, 
			idRecord='". $newID ."',
			txtNewValue='". encode($_POST['txtNote']) ."',
			dtDateTime=now(),
			chrTableName='Sessions',
			idUser='". $_SESSION['idUser'] ."'
		";
		$tCurrent = date('g:i A');
		$dCurrent = date('F jS Y');
		database_query($q,"Insert audit");
		//End the code for History Insert
		$_SESSION['infoMessages'][] = 'Note Added';
		?>

		<script language=JavaScript>
		opener.location.reload();
		window.close();
		</script>

		<?
		die();
		
	}
	

?>
<script language="JavaScript" src="<?=$BF?>includes/overlays.js"></script>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>

<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;
		total += ErrorCheck('txtNote', "You must enter a Note.");

		if(total == 0) { document.getElementById('idForm').submit(); } else { window.scrollTo(0,0); }
	}
</script>

<form name='idForm' id='idForm' method="post">


	<div class='title' style='font-size: 14px; font-weight: bold; margin-top: 10px;'>Add Note</div>
	<div class=''>
	
		<div id='errors'></div>
	
		<table cellspacing='0' cellpadding='0' class='twoCols'>
			<tr>
				<td class='left'>
					
					<div class='FormName'>Note: <span class='FormRequired'>(Required)</span></div>
					<div class='FormField'><textarea id="txtNote" name="txtNote" rows="10" cols="70" wrap="virtual"></textarea></div>
					
				</td>
			</tr>
		</table>
		<input class='FormButtons' type='button' value='Add Note' onclick="error_check();" />
	</div>
</form>
<?
	include($BF. 'includes/bottom.php');
?>
