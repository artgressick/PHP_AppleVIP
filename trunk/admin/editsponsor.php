<?	
	$BF = '../';  // Base folder for the root of the project.  This needs to be set on all pages.
	$title = 'Edit Sponsor';      // Title to display at the top of the browser window.
	$active = "admin";           // This needs to be set for the nav bar at the top to know which section to highlight..
	$subactive = "sponsors";		// Used to highlight the shows section
	
	require($BF. '_lib.php');
		(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']) ? ErrorPage() : "" );

	if(isset($_POST['intInvites'])) { 
	
		database_query("DELETE FROM SponsorsByShow WHERE idShow=". $_POST['id'] ." AND idUser=". $_POST['idUser'],"delete users from DB");
		$userList = array_unique($_POST['users']);

		// Set the basic values to be used.
		//   $table = the table that you will be connecting to to check / make the changes
		//   $mysqlStr = this is the "mysql string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		//   $sudit = this is the "audit string" that you are going to be using to update with.  This needs to be set to "" (empty string)
		$table = 'SponsorsbyShow';
		$mysqlStr = '';
		$audit = '';

		// "List" is a way for php to split up an array that is coming back.  
		// "set_strs" is a function (bottom of the _lib) that is set up to look at the old information in the DB, and compare it with
		//    the new information in the form fields.  If the information is DIFFERENT, only then add it to the mysql string to update.
		//    This will ensure that only information that NEEDS to be updated, is updated.  This means smaller and faster DB calls.
		//    ...  This also will ONLY add changes to the audit table if the values are different.
		list($mysqlStr,$audit) = set_strs($mysqlStr,'intInvites',$info['intInvites'],$audit,$table,$_POST['id']);
		
		// if nothing has changed, don't do anything.  Otherwise update / audit.
		if($mysqlStr != '') { list($str,$aud) = update_record($mysqlStr, $audit, $table, $_POST['id']);
			$_SESSION['infoMessages'][] = 'Sponsor updated successfully.';
		}

		// When the page is done updating, move them back to whatever the list page is for the section you are in.
		header("Location: sponsors.php?id=". $_POST['idShow']);
		die();
	}
	
	include($BF. 'includes/meta.php');	
	//Load drop down menus for the page
	$users = database_query("SELECT *
		FROM Users
		WHERE idRight=2 AND ID NOT IN (SELECT idUser FROM SponsorsByShow WHERE idShow='". $_REQUEST['id'] ."' AND !bDeleted)
		ORDER BY chrLast,chrFirst", "getting users");
	
	$show = fetch_database_query("SELECT chrName FROM Shows WHERE ID=". $_REQUEST['id'], "getting show");
	
	//This is needed for the nav_menu on top. We are setting the focus on the first text box of the page.
	$bodyParams = 'onload="document.getElementById(\'intInvites\').focus();"';
	
	//Load drop down menus for the page
	$info = fetch_database_query("SELECT idUser,chrFirst,chrLast,Shows.chrName as chrShow,intInvites,SponsorsByShow.idShow
		FROM SponsorsByShow
		JOIN Users ON Users.ID=SponsorsByShow.idUser
		JOIN Shows ON Shows.ID=SponsorsByShow.idShow
		WHERE SponsorsByShow.ID=". $_REQUEST['id'], "getting users");
?>
<script language="JavaScript" src="<?=$BF?>includes/forms.js"></script>
<script language="javascript">
	function error_check() {
		if(total != 0) { reset_errors(); }  

		var total=0;

		total += ErrorCheck('intInvites', "You must enter the maximum number of invites.");
		
		if(total == 0) { document.getElementById('idForm').submit(); } else { window.scrollTo(0,0); }
	}
	
</script>

<?
	include($BF. 'includes/top.php');
?>

<form name='idForm' id='idForm' method="post">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" class='title_fade'>
		<tr>
			<td class="left"></td>
			<td class="title">Edit Sponsor</td>
			<td class="title_right"></td>
			<td class="right"></td>
		</tr>
	</table>
	<!--<div class='instructions'>To edit a show, fill in all the information and click on the "Update Information" button.</div>-->

	<div class='innerbody'>
		<?=messages()?>
		<div id='errors'></div>
		<div id='showinfo'></div>
		
		<table class='twoCol'>
			<tr>
				<td class='left'>

					<div class='FormName'>Show</div>
					<div class='FormField'><?=$info['chrShow']?></div>
					
					<div class='FormName'>Sponsor</div>
					<div class='FormField'><?=$info['chrFirst']?> <?=$info['chrLast']?></div>
				</td>
			  
			  	<td class='gutter'>
				
			  </td>
				<td class='right'>
					
					<div class='FormName'>Invites <span class='Required'>(Required)</span></div>
					<div class='FormField'><input type='text' name='intInvites' id='intInvites' value='<?=$info['intInvites']?>' /></div>
							
				</td>
			</tr>
		</table>

		<input class='FormButtons' type='button' value='Update Information' onclick="error_check()" />
		<input type='hidden' name='id' value='<?=$_REQUEST['id']?>' >
		<input type='hidden' name='idUser' value='<?=$info['idUser']?>' >
		<input type='hidden' name='idShow' value='<?=$info['idShow']?>' >

	</div>

</form>
<?
	include($BF. 'includes/bottom.php');
?>
