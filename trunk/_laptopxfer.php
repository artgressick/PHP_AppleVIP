<?php
	require('applevip-conf.php');

	$checkinID = '27860';
	$idShow = '39';
	$notesID = '31769';
	
	function encode($val,$extra="") {
		if($val == 'N' || $val == '\N') {
			$val = "";
		}
		$val = str_replace("'",'&#39;',stripslashes($val));
		$val = str_replace('"',"&quot;",$val);
		if($extra == "tags") { 
			$val = str_replace("<",'&lt;',stripslashes($val));
			$val = str_replace('>',"&gt;",$val);
		}
		if($extra == "amp") { 
			$val = str_replace("&",'&amp;',stripslashes($val));
		}
		return $val;
	}
	
	
	echo "Conecting to DB<br/>";
	if($source = mysqli_connect($host, 'jsummers', 'rtv250')) {
		if(mysqli_select_db($source, 'applevip_restore')) {
			if($destination = mysqli_connect($host, $user, $pass)) {
				if(mysqli_select_db($destination, $db)) {
					unset($host, $user, $pass, $db); // Clean out variables
					echo "DB Connections Success<br />";
					
					//Lets first do the Checkin table
					echo "Importing Checkin Table....<br />";
					$checkin = mysqli_query($source, "SELECT * FROM Checkin WHERE ID > ".$checkinID);
					while($row = mysqli_fetch_assoc($checkin)) {
						if(mysqli_query($destination, "INSERT INTO Checkin SET 
													ID='".$row['ID']."',
													idShow='".$row['idShow']."',
													idContact='".$row['idContact']."',
													idInvite='".$row['idInvite']."',
													idSponsor='".$row['idSponsor']."',
													idUser='".$row['idUser']."',
													idStatus='".$row['idStatus']."',
													intGuests='".$row['intGuests']."',
													dtStamp='".$row['dtStamp']."'
													")) { echo $row['ID'].' Imported Successfully.<br />'; } else { echo 'Error Importing '.$row['ID'].'.<br />'; } 
					}
					echo "Checkin Table Imported Successfully<br />";
					
					//Now to do the Invites Table
					echo "Updating Invites Table....<br />";
					$invites = mysqli_query($source, "SELECT * FROM Invites WHERE idShow = '".$idShow."'");
					/*
					while($row = mysqli_fetch_assoc($invites)) {
						if(mysqli_query($destination, "UPDATE Invites SET 
													idStatus='".$row['idStatus']."',
													dtStamp='".$row['dtStamp']."',
													intGuests='".$row['intGuests']."'
													WHERE ID = '".$row['ID']."';
													")) { echo $row['ID'].' Update Successfully.<br />'; } else { echo 'Error Updateing '.$row['ID'].'.<br />'; }
					}
					*/
					echo "Invites Table Updated Successfully<br />";				
					
					//Now to do the Notes Table
					echo "Updating Notes Table....<br />";
					$invites = mysqli_query($source, "SELECT * FROM Notes WHERE ID > '".$notesID."'");
					/*
					while($row = mysqli_fetch_assoc($invites)) {
						if(mysqli_query($destination, "INSERT INTO Notes SET 
													idType='".$row['idType']."',
													idUser='".$row['idUser']."',
													idRecord='".$row['idRecord']."',
													idShow='".$row['idShow']."',
													txtNote='".encode($row['txtNote'])."',
													dtStamp='".$row['dtStamp']."'
													")) { echo $row['ID'].' Inserted Successfully.<br />'; } else { echo 'Error Inserting '.$row['ID'].'.<br />'; }
					}
					*/
					echo "Notes Table Updated Successfully<br />";				
					
				
				
				} else { echo "Destination DB Error: ".mysqli_error($destination)."<br />"; }
			} else { echo "Destination DB Connect Error: ".mysqli_connect_error($destination)."<br />"; }
		}  else { echo "Source DB Error: ".mysqli_error($source)."<br />"; }
	} else { echo "Source DB Connect Error: ".mysqli_connect_error($source)."<br />"; }
	



	
	
	
	
	

	
?>