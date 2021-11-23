<?php
include("db.php");
$ID = (int)$_POST["id"];
$commands = $_POST['command'];
if(!isset($_SESSION['userid'])){
	http_response_code(404);
	die();
}
$expire_after = 30;
$exists = 0;

$query = "SELECT ID FROM computers WHERE ID='".$ID."'";
$results = mysqli_query($db, $query);
$computer = mysqli_fetch_assoc($results);
									
$query = "SELECT ID, expire_time, hex FROM commands WHERE computer_id='".$computer['ID']."' AND status='Sent' AND user_id='".$_SESSION['userid']."' ORDER BY ID DESC LIMIT 1";
$results = mysqli_query($db, $query);
$existing = mysqli_fetch_assoc($results);

if($existing['ID'] != ""){
	if(strtotime(date("Y-m-d h:i:s")) <= strtotime($existing['expire_time'])){
		$exists = 1;
	}
}

//generate expire time
$expire_time = date("Y-m-d h:i:s", strtotime('+'.$expire_after.' seconds', strtotime(date("Y-m-d h:i:s"))));

if($exists == 0){
	$salt = getSalt(40);
	$query = "INSERT INTO commands (hex, computer_id, user_id, command, expire_after, expire_time, status)
			  VALUES ('".$salt."','".$computer['ID']."', '".$_SESSION['userid']."', '".crypto('encrypt', $commands, $salt)."', '".$expire_after."', '".$expire_time."', 'Sent')";
	$results = mysqli_query($db, $query);
	$insertID = mysqli_insert_id($db);
	//echo mysqli_error($db);exit;
	MQTTpublish($ID."/Commands/CMD",'{"userID":'.$_SESSION['userid'].',"commandID": "'.$insertID.'","data":"'.$commands.'"}',$ID,false);

	$activity="Technician Sent ".$commands." Command To: ".$computer['hostname'];
	userActivity($activity,$_SESSION['userid']);
	
	//Get Response
	$count = 0;
	while($count <= 10){
		$query = "SELECT data_received FROM commands WHERE ID = '".$insertID."';";
		$results = mysqli_query($db, $query);
		$result = mysqli_fetch_assoc($results);
		if(trim(computerDecrypt($result["data_received"]))!=""){break;}
		sleep(1);
		$count++;
	}
	
	if(trim(computerDecrypt($result["data_received"]))!=""){
		$response = trim(computerDecrypt($result["data_received"]));
	}else{
		if($count >= 10){
			$response = "Response timed out";
		}else{
			$response = "No Response";
		}
	}
?>
	<pre style="color:#fff;"><?php echo $response;?></pre>
<?php }else{?>
	A command has already been sent and the asset has not proccessed your request. Please wait 30 seconds before retrying a command.
<?php }?>