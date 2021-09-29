<?php
	include("../Includes/db.php");
	$computerID = $_GET['ID'];
	//show recents on sidebar
	$recent = array_slice($_SESSION['recent'], -8, 8, true);
?>
	<h6>Recently Viewed</h6>	
		<?php
		$count = 0;
		foreach(array_reverse($recent) as $item) {
			$query = "SELECT ID, hostname FROM computerdata where ID='".$item."'";
			$results = mysqli_query($db, $query);
			$data = mysqli_fetch_assoc($results);
			if($data['ID']==""){ continue; }
			$count++;
		?>
			<a href="#" onclick="loadSection('General', '<?php echo $data['ID']; ?>');">
				<li>
					<i class="fas fa-desktop"></i>&nbsp;&nbsp;&nbsp;
					<?php echo strtoupper($data['hostname']);?>
				</li>
			</a>
		<?php } ?>
		<?php if($count==0){ ?>
			<li>No Recent Computers</li> 
		<?php } ?>