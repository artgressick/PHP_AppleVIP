<?php
	$BF = "";
	include($BF.'_lib.php');
	$title = 'Check-In Page';
	$active = '';
	//Make the original query
	$query = "SELECT Contacts.*, Invites.idInviteStatus, Invites.intGuests
	FROM Invites
	JOIN Contacts ON Contacts.ID=Invites.idContact
	WHERE !Invites.bDeleted AND Invites.ID = '".$_REQUEST['id']."'";
	
	$info = fetch_database_query($query,"Getting Attendee");
	
	// Grab Notes
	// Type 1 = Invites, Type 2 = Address Book (This is usually the table)
	// idRecord = ID of Table to link with this entry.
	$q = "SELECT DISTINCT DATE_FORMAT(Notes.dtStamp, '%M %D %Y') as dFormated, DATE_FORMAT(Notes.dtStamp, '%l:%i %p') as tFormated, Notes.txtNote, Users.chrFirst, Users.chrLast
	FROM Contacts as Source
	JOIN Notes ON Notes.idType=2 AND Notes.idRecord=Source.ID
	JOIN Users ON Users.ID = Notes.idUser
	WHERE Source.ID=".$info['ID'];

	$notes = database_query($q,"Getting Notes");


	if(isset($_POST['Submit'])) { 

		if(database_query("INSERT Checkin (idInvite, idUser, intGuests, idContact, idShow, idSponsor) VALUES('".$_POST['id']."','".$_SESSION['idUser']."','".$_POST['intGuests']."','".$_POST['idContact']."','".$_POST['idShow']."','".$_POST['idSponsor']."')","Checkin Contact")) {
			$tmp = database_query("UPDATE Invites SET idStatus=8 WHERE ID='".$_POST['id']."'");
			$tmp = database_query("INSERT INTO Notes SET idType=3, idRecord='".$_POST['id']."',	dtStamp=now(),	txtNote = '".encode('- Status set to Attended'.($_POST['intGuests']>0?', '.$_POST['intGuests'].' guest(s) checked in.':'').'<br /> from Checkin Desk')."'","Insert Note");
			$_SESSION['infoMessages'][] = "Checkin Successful";		
		} else {
			$_SESSION['errorMessages'][] = "An Error occurred while checking in the attendee!";
		}
	
		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		
		header("Location: checkin.php?idShow=".$_REQUEST['idShow']);
		die();
	}
	include('includes/meta.php');

?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="JavaScript" type='text/javascript' src="<?=$BF?>includes/overlays.js"></script>

<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('chrFirst', "You must enter a First Name.");
		total += ErrorCheck('chrLast', "You must enter a Last Name.");
//		total += ErrorCheck('idCategory', "You must choose a Category.");
//		total += ErrorCheck('chrEmail', "You must enter an Email Address.","email");

		if(total == 0) { document.getElementById('idForm').submit(); } else { window.scrollTo(0,0); }
	}
	function guestcheck(guests,max) {
		if(!IsNumeric(guests) || parseInt(guests) > max) {
			alert('Please enter a number between 0 and '+max+' for this person.');
			if(!IsNumeric(guests)) {
				document.getElementById('intGuests').value='0';
			} else if (parseInt(guests) > max) {
				document.getElementById('intGuests').value=max;
			}
		}
	}
</script>
<?	
	include('includes/topcheckin.php');
?>
<!-- This is the main body of the page.-->
<form name='idForm' id='idForm' method="post">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Checkin: <?=$info['chrFirst']?> <?=$info['chrLast']?></td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<div class='instructions'>Confirm Attendee and Click Check In Attendee</div>

	<div class='innerbody'>
<?
	if($info['intGuests']!=0) {
?>
		<div style="text-align:center; padding-bottom:5px;">Guests Checking In: <input name="intGuests" id='intGuests' type="text" maxlength="3" size="5" value='0' onchange='guestcheck(this.value,<?=$info['intGuests']?>);' /> (Max Allowed: <strong><?=$info['intGuests']?></strong>)</div>
<?
	} else {
?>
		<input type='hidden' name='intGuests' value='0' />			
<?		
	}
?>
		<div style="text-align:center;"><input name="Submit" type="submit" value="Check In Attendee"></div>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left' style="text-align:center;">
					<br />
					<div class='FormName' style="font-size:12px; font-weight:normal;"><strong>Name:</strong><?=($info['idCategory']==2?' <img src="'.$BF.'images/appleicon.png" width="15" height="15" />':'')?> <?=$info['chrFirst']?> <?=$info['chrLast']?></div>
					<br />
					<div class='FormName' style="font-size:12px; font-weight:normal;"><strong>Type:</strong> <img src="images/<?=($info['bType'] == 0 ? "circle_gold" : "circle_red")?>.png" style="margin-top:4px;" /></div>
					<br />	
					<div class='FormName' style="font-size:12px; font-weight:normal;"><strong>Category:</strong>
					<?
	$categories = database_query("SELECT ID,chrCategory FROM Categories WHERE !bDeleted", "getting contact categories");
	while($row = mysqli_fetch_assoc($categories)) { ?>
		<?=($info['idCategory'] == $row["ID"] ? $row['chrCategory'] : "")?>
<?  } ?>					
					</div><br />
					<div class='FormName' style="font-size:12px; font-weight:normal;"><strong>Company:</strong> <?=$info['chrCompany']?></div><br />
				</td>
			</tr>
		</table>
		<div style="text-align:center"><input name="Return" type="button" value="Cancel & Return to Check-in" onclick='location.href="checkin.php?idShow=<?=$_REQUEST['idShow']?>"'></div>		
		<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' />
		<input type='hidden' name='idContact' value='<?=$info['ID']?>' />
		<input type='hidden' name='idShow' value='<?=$_REQUEST['idShow']?>' />
		<input type='hidden' name='idSponsor' value='<?=$info['idUser']?>' />	
	</div>

</form>
<!-- This is the bottom of the body -->
<?php
	include('includes/bottom.php');
?>