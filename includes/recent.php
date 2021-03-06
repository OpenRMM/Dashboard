<?php
include("db.php");
$computerID = (int)base64_decode($_GET['ID']);

if(!isset($_SESSION['userid'])){
	http_response_code(404);
	die();
}
//show recents on sidebar
$recent = array_slice($_SESSION['recent'], -5, 5, true);
echo "<h6>Recently Viewed Assets</h6>";	
$count = 0;
foreach(array_reverse($recent) as $item) {
	$query = "SELECT ID, computer_type, online FROM computers where ID='".$item."' and active='1'";
	$results = mysqli_query($db, $query);
	$data = mysqli_fetch_assoc($results);
	if($data['ID']==""){ continue; }
	$json = getComputerData($data['ID'], array("general"));
	$date = strtotime($json['general_lastUpdate']);
	if($date < strtotime('-1 days')) {
		$data['online']="0";
	}
	$hostname =  $json['general']['Response'][0]['csname'];
	$count++;
	
	$icons = array("desktop","server","laptop","tablet","allinone","other");
	if(in_array(strtolower(str_replace("-","",$data['computer_type'])), $icons)){
		$icon = strtolower(str_replace("-","",$data['computer_type']));
		if($icon=="allinone")$icon="tv";
		if($icon=="tablet")$icon="tablet-alt";
		if($icon=="other")$icon="microchip";
	}else{
		$icon = "desktop";
	} 
	if($data['online']=="1"){
		$color="color:green";
		$title="Online";
	}else{
		$color="color:#DCDCDC";
		$title="Offline";
	}
	if($data['ID']==$_SESSION['computerID'] and $_SESSION['computerID']!=""){
		$style="secActive";
		if($data['online']=="0"){
			$color="color:#333";
		}
	}else{
		$style="secbtn";
	}
?>
	<li style="cursor:pointer" onclick="loadSection('Asset_General', '<?php echo $data['ID']; ?>');$('.sidebarComputerName').text('<?php echo textOnNull(strtoupper($hostname),'Unavailable');?>');" class="<?php echo $style; ?>">
		<i title="<?php echo $title; ?>" style="<?php echo $color; ?>" class="fas fa-<?php echo $icon; ?>"></i>&nbsp;&nbsp;&nbsp;
		<?php echo textOnNull(strtoupper($hostname),"Unavailable");?>
	</li>
<?php } 
 if($count==0){ ?>
	<li>No recent computers</li> 
<?php }?>
<br>

<?php if($siteSettings['Service_Desk']=="Enabled"){ 
	if(in_array("Service_Desk_Ticket", $allowed_pages)){ 
		//show recents on sidebar
		$recent2 = array_slice($_SESSION['recentTickets'], -5, 5, true);
		echo "<h6>Recently Viewed Tickets</h6>";	
		$count2 = 0;
		foreach(array_reverse($recent2) as $item2) {
			$query2 = "SELECT * FROM tickets where active='1' and ID='".$item2."'";
			$results2 = mysqli_query($db, $query2);
			$data2 = mysqli_fetch_assoc($results2);
			if($data2['ID']==""){ continue; }
			$count2++;
		?>
			<li style="cursor:pointer" onclick="loadSection('Service_Desk_Ticket', '<?php echo $data2['ID']; ?>');" class="secbtn">
				<i title="<?php echo $data2['title']; ?>" style="color:#fff" class="fas fa-ticket-alt"></i>&nbsp;&nbsp;&nbsp;
				<?php echo textOnNull($data2['title'],"Unavailable");?>
			</li>
		<?php } 
		if($count2==0){ ?>
			<li>No recent tickets</li> 
		<?php 
			} 
	}
}
?>
