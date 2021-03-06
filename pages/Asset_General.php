<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);
	
$query = "SELECT online, ID, company_id, name, phone, email,hex, computer_type FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$result = mysqli_fetch_assoc($results);

$query = "SELECT name, phone, email,address,comments,date_added,hex,owner FROM companies WHERE ID='".$result['company_id']."' and active='1' LIMIT 1";
$companies = mysqli_query($db, $query);
$company = mysqli_fetch_assoc($companies);

$getWMI = array("general","logical_disk","bios","processor","agent","battery","windows_activation","agent_log","firewall","okla_speedtest");
$json = getComputerData($computerID, $getWMI);
//print_r($json['agent_log']);
$hostname = textOnNull($json['general']['Response'][0]['csname'],"Unavailable");

//Update Recents
if (in_array( $computerID, $_SESSION['recent'])){
	if (($key = array_search($computerID, $_SESSION['recent'])) !== false) {
		unset($_SESSION['recent'][$key]);
	}
	array_push($_SESSION['recent'], $result['ID']);
	$query = "UPDATE users SET recents='".implode(",", $_SESSION['recent'])."' WHERE ID=".$_SESSION['userid'].";";
	$results = mysqli_query($db, $query);
}else{
	if(end($_SESSION['recent']) != $computerID){
		array_push($_SESSION['recent'], $result['ID']);
		$query = "UPDATE users SET recents='".implode(",", $_SESSION['recent'])."' WHERE ID=".$_SESSION['userid'].";";
		$results = mysqli_query($db, $query);
	}
}
$online = $result['online'];
$date = strtotime($json['general_lastUpdate']);
if($date < strtotime('-1 days')) {
	$online="0";
}
//ex. 10-3=7
$used2 = $json['general']['Response'][0]['Totalphysicalmemory'] - $json['general']['Response'][0]['FreePhysicalMemory'];

//ex. 10-7=3
$free2 = $json['general']['Response'][0]['Totalphysicalmemory'] - $used2;
if($used2==0){
	$used2=100;
}
$total2 = $json['general']['Response'][0]['Totalphysicalmemory'];
$average2 = (int)round(($used2 / $total2) * 100,2);
if($average2==0){
	$average2=100;
}
//echo $json['general']['Response'][0]['Totalphysicalmemory']."....".$free2."....".$used2."....".$average2;

$cpuUsage= $json['processor']['Response'][0]['LoadPercentage'];
if($cpuUsage==""){
	$cpuUsage="100";
}
//log user activity
$activity = "Asset ".textOnNull($json['general']['Response'][0]['csname'],"Unavailable")." viewed";
userActivity($activity,$_SESSION['userid']);
//print_r(base64_encode($json['screenshot_1']));
if($online=="0"){
	$color="#696969";
}else{
	$color="green";
}
$icons = array("desktop","server","laptop","tablet","allinone","other");
	if(in_array(strtolower(str_replace("-","",$result['computer_type'])), $icons)){
		$icon = strtolower(str_replace("-","",$result['computer_type']));
		if($icon=="allinone")$icon="tv";
		if($icon=="tablet")$icon="tablet-alt";
		if($icon=="other")$icon="microchip";
	}else{
		$icon = "desktop";
	}  
?>
<style>
	.dataTables_info {margin-top:40px; }
</style>
<div style="padding:20px;margin-bottom:-1px;" class="card col-md-12">
	<div class="tooltips" style="margin-top:13px;position:absolute;display:inline;float:left"><i style="font-size:30px;color:<?php echo $color; ?>" class="fas fa-<?php echo $icon; ?>"></i><span style="width:100px;margin-left:-50px" class="tooltiptext">Agent is Online</span></div>
	<h5 title="ID: <?php echo $computerID; ?>" style="margin-left:50px;color:#0c5460;display:inline;font-size:18px">Overview of <?php echo textOnNull($json['general']['Response'][0]['csname'],"Unavailable"); ?>	
		<center style="display:inline;margin-left:20px;">
			<?php $alertCount = count($json['Alerts']);?>
			<?php if($alertCount > 0){?>
				<button title="" onclick="computerAlertsModal('This PC','<?php echo $json['Alerts_raw'];?>');" data-bs-toggle="modal" data-bs-target="#computerAlerts" class="tooltips tooltipHelper btn btn-sm btn-danger">	
					<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
					<span class="tooltiptext" style="margin-top:2px;background:#eb3422;width:100px;margin-left:-50px"><?php echo $alertCount;?> Issues</span>
				</button>
			<?php } ?>
		</center>
		<div style="float:right;display:inline">
			<div class="btn-group">
				<button onclick="loadSection('Asset_General');" style="background:#0c5460;color:#fff" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button type="button" style="background:#0c5460;color:#fff" class="btn dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_General','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>&nbsp;
			<?php if(in_array("Asset_Agent_Settings", $allowed_pages)){  ?>
				<button title="Agent Configuration" onclick="loadSection('Asset_Agent_Settings');" class="btn btn-sm" style="margin:3px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
					<i class="fas fa-cogs"></i>
				</button>
			<?php } ?>
			<?php if(in_array("Asset_Edit", $allowed_pages)){  ?>
			<button title="Edit Asset Details" class="btn btn-sm" onclick="loadSection('Asset_Edit');" style="margin:3px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-pencil-alt"></i>
			</button>
			<?php } ?>
		</div>
		<br>
		<p>	
			<span style="font-size:12px;color:#333"> Last Updated: <?php echo ago($json['general_lastUpdate']);?></span>
		</p>
	</h5>
</div>
<?php
$agentVersion = preg_replace('/\D/', '', $json['agent']['Response'][0]['Version']);
if($agentVersion != preg_replace('/\D/', '', $siteSettings['general']['agent_latest_version']) and $online=="1"){ ?>
	<?php if($agentVersion==""){?>
		<div  style="border-radius: 0px 0px 4px 4px;padding:8px" class="alert alert-danger" role="alert">
			<div class="spinner-border spinner-border-sm" style="font-size:12px" role="status">
				<span class="sr-only">Loading...</span>
			</div>
			&nbsp;&nbsp;&nbsp;The agent is trying to get initial data for this asset.		
		</div>
	<?php }else{ ?>
		<div onclick="updateAgent('<?php echo $computerID; ?>')" style="border-radius: 0px 0px 4px 4px;cursor:pointer;padding:8px" class="alert alert-danger" role="alert">
			<i class="fas fa-cloud-upload-alt"></i>&nbsp;&nbsp;&nbsp;An update is available for this asset. <span style="color:#333;font-weigh:bold"><u>Update to v.<?php echo $siteSettings['general']['agent_latest_version']; ?></u></span>			
		</div>
	<?php } ?>
<?php }
if($online=="0"){ ?>
	<div  style="border-radius: 0px 0px 4px 4px;padding:8px" class="alert alert-danger" role="alert">
		<i class="fas fa-ban"></i>&nbsp;&nbsp;&nbsp;This agent is offline.		
	</div>
<?php }
if($alert!=""){ ?>
	<div class="row alert alert-<?php echo $alertType; ?>" role="alert">
		<b><?php echo $alert; ?></b>
	</div>
<?php } 
	if(base64_encode($json['screenshot_1'])!=""){
		$size="3";
		$height= "220px";
	}else{
		$size="4";
		$height= "250px";
	}
?>
<div style="margin-top:10px" id="sortableChart" class="row">
	<?php if($size=="3"){ ?>
		<div data-id="1" data-bs-toggle="modal" data-bs-target="#screenshotModal" style="overflow:hidden" id="chart-1" class="col-md-3">
			<div style="height:<?php echo $height; ?>;cursor:zoom-in;overflow:hidden;padding:0" class="card-body card">			
				<img class="img-fluid" style="height:<?php echo $height; ?>" src="data:image/jpeg;base64,<?php echo base64_encode($json['screenshot_1']); ?>"/>              
				<h6></h6>
			</div>		
		</div>
	<?php } ?>
    <div data-id="2" style="z-index:1"  id="chart-2" class=" col-md-<?php echo $size; ?>">
        <div style="height:<?php echo $height; ?>;" data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','processor','0.LoadPercentage');" id="processor_LoadPercentage" class="card-body card">
			<canvas style="height:<?php echo $height; ?>;" data-centerval="<?php echo $cpuUsage; ?>%" id="chDonut2"></canvas>
			<h6 style="text-align:center">CPU Usage</h6>
        </div>
    </div>
    <div data-id="3" id="chart-3" class="col-md-<?php echo $size; ?>">
        <div style="height:<?php echo $height; ?>;" data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.FreePhysicalMemory');" id="general_FreePhysicalMemory" class="card-body card">
			<canvas style="height:<?php echo $height; ?>;" data-centerval="<?php echo (int)$average2; ?>%" id="chDonut1"></canvas>
			<h6 style="text-align:center">RAM Usage</h6>
        </div>
    </div>
    <div data-id="4" id="chart-4" class="col-md-<?php echo $size; ?>">
        <div data-bs-toggle="modal" style="height:<?php echo $height; ?>;" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','logical_disk','0.FreeSpace');" id="logical_disk_FreeSpace" class="card-body card">
			<?php
			//Determine Warning Level
				$freeSpace = $json['logical_disk']['Response']['C:']['FreeSpace'];
				$size2 = $json['logical_disk']['Response']['C:']['Size'];
				$used = $size2 - $freeSpace;
				$usedPct = round(($used/$size2) * 100);
				if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
					$pbColor = "red";
				}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
					$pbColor = "#ffa500";
				}else{ $pbColor = "#03925e"; }
				$left = 100 - $usedPct;
				if((int)$usedPct=="0"){$usedPct=100;}
				
			?>
			<canvas style="height:<?php echo $height; ?>;" data-centerval="<?php echo (int)$usedPct;?>%" id="chDonut3"></canvas>
			<h6 style="text-align:center">Disk Usage</h6>
        </div>
    </div>
</div>
<div style="margin-top:-15px;background:#D3D3D3;padding:10px;border-radius:10px;margin:20px;margin-top:0px" id="sortableCard" class="row shadow p-3 mb-5 rounded">
	<div id="card-1" class="col-xs-6 col-sm-6 col-md-3 col-lg-3" style="padding:5px;">
		<div class="panel panel-default" style="z-index:999;border-radius:10px;">
			<div class="panel-body" style="z-index:999;border-radius:10px;height:285px;">
				<div style="margin-top:5px">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div style="margin-top:-10px">
							<a href="javascript:void(0)" style="color:#696969;text-decoration:none;" data-bs-toggle="modal" data-bs-target="#companyMoreInfo">
							<?php echo $msp; ?> Details:
								<br>
								<ul class="list-group" style="margin-left:20px">
									<li class="list-group-item secbtn" style="padding:4px">	 
										<b>Name:</b> <?php echo textOnNull((crypto('decrypt',$company['name'],$company['hex'])!="N/A" ? crypto('decrypt',$company['name'],$company['hex']) : ""), "not assigned"); ?>
										<i style="float:right;margin-right:10px;margin-top:4px" class="fas fa-ellipsis-h"></i>
									</li>
								</ul>
							</a>
							<div style="margin-top:5px">
								<span style="color:#696969">Client Details</span><br>
								<ul class="list-group" style="margin-left:20px">
									<li class="list-group-item" style="padding:4px"><b>Name:</b> <?php echo textOnNull(crypto('decrypt',$result['name'],$result['hex']),"not set"); ?></li>
									<li class="list-group-item" style="padding:4px"><b>Email:</b> <a style="text-decoration:none" href="mailto:<?php echo crypto('decrypt',$result['email'],$result['hex']); ?>"><?php echo textOnNull(crypto('decrypt',$result['email'],$result['hex']),"not set"); ?></a></li>
									<li class="list-group-item" style="padding:4px"><b>Phone:</b> <?php echo textOnNull(crypto('decrypt',$result['phone'],$result['hex']),"not set"); ?></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 mx-auto" style="text-align:center;bottom:0;position:absolute;left:0;padding-bottom:10px">
						<button class="btn btn-danger btn-sm" onclick='sendCommand("shutdown -s -t 0", "Shutdown Computer");' style="text-align:center;width:22%;height:60px;border-radius:5px">
							<i class="fas fa-power-off"></i><br>Shutdown
						</button>&nbsp;
						<button class="btn btn-warning btn-sm" onclick='sendCommand("shutdown -r -t 0", "Reboot Computer");' style="text-align:center;width:22%;border:#ffa500;;height:60px;border-radius:5px">
							<i class="fas fa-redo"></i><br>Reboot
						</button>&nbsp;
						<button class="btn btn-sm btn-warning" data-bs-dismiss="modal" type="button" style="text-align:center;background:#0ac282;border:#0ac282;width:22%;;height:60px;border-radius:5px" data-bs-toggle="modal" data-bs-target="#agentMessageModal">
							<i class="fas fa-comment"></i><br>Show Alert
						</button>&nbsp;
						<button class="btn btn-sm" onclick='$("#terminaltxt").delay(3000).focus();' type="button" style="text-align:center;width:22%;color:#fff;background:#333;;height:60px;border-radius:5px" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#terminalModal">
							<i class="fas fa-terminal"></i><br>Terminal
						</button>
					</div>
				</div>
		  </div>
		</div>
	</div>
	<div id="card-2" class="col-xs-6 col-sm-6 col-md-4 col-lg-3" style="padding:3px;">
		<div class="panel panel-default" style="z-index:999;border-radius:10px;">		
			<div class="panel-body" style="z-index:999;border-radius:10px;height:285px;">	
				<div style="margin-top:5px">
					<ul class="list-group" style="margin-left:10px;font-size:12px">
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','processor','0.Name');" id="processor_0Name" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Processor: </b><?php echo textOnNull(str_replace(" 0 ", " ",str_replace("CPU", "",str_replace("(R)","",str_replace("(TM)","",$json['processor']['Response'][0]['Name'])))), "N/A");?></li>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.Caption');" id="general_0Caption" class="list-group-item secbtn olderdata" style="padding:6px"><b>Operating System: </b><?php echo textOnNull(str_replace("Microsoft", "", $json['general']['Response'][0]['Caption']), "N/A")." ".$json['general']['Response'][0]['Version'];?></li>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.SystemType');" id="general_0SystemType" class="list-group-item secbtn olderdata" style="padding:6px"><b>Architecture: </b><?php echo textOnNull(str_replace("PC", "",$json['general']['Response'][0]['SystemType']), "N/A");?></li>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','bios','0.Version');" id="bios_0Version" class="list-group-item secbtn olderdata" style="padding:6px"><b>BIOS Version: </b><?php echo textOnNull($json['bios']['Response'][0]['Version'], "N/A");?></li>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','ExternalIP.ip');" id="general_ExternalIPip" class="list-group-item secbtn olderdata" style="padding:6px"><b>Public IP Address: </b><?php echo textOnNull($json['general']['Response'][0]['ExternalIP']["ip"], "N/A");?></li>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.PrimaryLocalIP');" id="general_0PrimaryLocalIP" class="list-group-item secbtn olderdata" style="padding:6px"><span style="margin-left:0px"><b>Local IP Address: </b><?php echo textOnNull($json['general']['Response'][0]['PrimaryLocalIP'], "N/A");?></span></li>
						<?php if(count($json['windows_activation']['Response']) > 0) {
							$status = $json['windows_activation']['Response'][0]['LicenseStatus'];
							if($status!="Licensed")$status="Not activated";
							$color = ($status == "Licensed" ? "text-success" : "text-danger");
						?>
							<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','windows_activation','0.LicenseStatus');" id="windows_activation_0LicenseStatus" class="list-group-item secbtn olderdata" style="padding:6px"><b>Windows Activation: </b><span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
						<?php } 
						 if((int)$json['battery']['Response'][0]['BatteryStatus']>0){ ?>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','battery','0.BatteryStatus');" id="battery_0BatteryStatus" class="list-group-item secbtn olderdata" style="padding:6px"><b>Battery Status: </b><?php 								
							$statusArray = [
							"1" => ["Text" => "Discharging", "Color" => "red"],
							"2" => ["Text" => "Unknown", "Color" => "red"],
							"3" => ["Text" => "Fully Charged", "Color" => "green"],
							"4" => ["Text" => "Low", "Color" => "red"],
							"5" => ["Text" => "Critical", "Color" => "red"],
							"6" => ["Text" => "Charging", "Color" => "green"],
							"7" => ["Text" => "Charging And High", "Color" => "green"],
							"8" => ["Text" => "Charging And Low", "Color" => "green"],
							"9" => ["Text" => "Charging And Critical", "Color" => "yellow"],
							"10" =>["Text" => "Undefined", "Color" => "red"],
							"11" =>["Text" => "Partially Charged", "Color"=>"yellow"]];
							$statusInt = $json['battery']['Response'][0]['BatteryStatus'];						
						?>
						<?php echo textOnNull($json['battery']['Response'][0]['EstimatedChargeRemaining'], "Unknown");?>%
						(<span style="color:<?php echo $statusArray[$statusInt]['Color']; ?>"><?php echo $statusArray[$statusInt]['Text']; ?></span>)	
						</li>
						<?php } ?>
					</ul>
				</div>
		  </div>
		</div>
	</div>
	<div id="card-3" class="col-xs-6 col-sm-6 col-md-4 col-lg-3" style="padding:3px;">
		<div class="panel panel-default" style="z-index:999;border-radius:10px;">		
			<div class="panel-body" style="z-index:999;border-radius:10px;height:285px;">				
				<div style="margin-top:5px">
					<ul class="list-group" style="margin-left:20px;font-size:12px">
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.UserName');" id="general_0UserName" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Current User: </b><?php echo textOnNull(basename($json['general']['Response'][0]['UserName']), "Unknown");?></li>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.Domain');" id="general_0Domain" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Domain: </b><?php echo textOnNull($json['general']['Response'][0]['Domain'], "N/A");?></li>
						<!--<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.BuildNumber');" id="general_0BuildNumber" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Build: </b><?php echo textOnNull($json['general']['Response'][0]['Version'], "N/A");?></li>-->
						<?php
							$lastBoot = explode(".", $json['general']['Response'][0]['LastBootUpTime'])[0];
							$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
						?>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.LastBootUpTime');" id="general_0LastBootUpTime" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Uptime: </b><?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
						<?php if(count($json['firewall']) > 0) {

							$public = $json['firewall']['Response'][0]['publicProfile'];
							//if($public=="OFF"){ $public="Disabled"; }else{ $public="Enabled"; }
							$color1 = (($public == "Enabled") ? "text-success" : "text-danger");

							$private = $json['firewall']['Response'][0]['privateProfile'];
							//if($private=="OFF"){ $private="Disabled"; }else{ $private="Enabled"; }
							$color2 = (($private == "Enabled") ? "text-success" : "text-danger");

							$domain = $json['firewall']['Response'][0]['domainProfile'];
							//if($domain=="OFF"){ $domain="Disabled"; }else{ $domain="Enabled"; }
							$color3 = (($domain == "Enabled") ? "text-success" : "text-danger");
						?>
							<li id="Firewall" class="list-group-item olderdata" style="z-index:2;padding:6px;width:100%"><b>Firewall Status: </b><br>
								<center>
									<span data-bs-toggle="modal" data-bs-target="#olderDataModal"  onclick="olderData('<?php echo $computerID; ?>','firewall','0.publicProfile');"  style="margin-left:20px">
										Public: <span style="padding-right:20px" class="<?php echo $color1; ?>"><?php echo $public; ?></span>
									</span>
									<span data-bs-toggle="modal" data-bs-target="#olderDataModal"  onclick="olderData('<?php echo $computerID; ?>','firewall','0.privateProfile');">
										Private: <span style="padding-right:20px" class="<?php echo $color2; ?>"><?php echo $private; ?></span>
									</span>
									<span data-bs-toggle="modal" data-bs-target="#olderDataModal"  onclick="olderData('<?php echo $computerID; ?>','firewall','0.domainProfile');">
										Domain: <span class="<?php echo $color3; ?>"><?php echo $domain; ?></span>
								</span>
								</center>
							
							</li>
						<?php } 
						
						if(count($json['general']['Response'][0]['Antivirus']) > 0) {
							$status = $json['general']['Response'][0]['Antivirus'];
							$color = ($status == "No Antivirus" ? "text-danger" : "text-success");
						?>
							<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.Antivirus');" id="general_0Antivirus" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Antivirus: </b><span title="<?php echo textOnNull($status, "N/A"); ?>" class="<?php echo $color; ?>"><?php echo mb_strimwidth(textOnNull($status, "N/A"), 0, 30, "...");?></span></li>
						<?php } ?>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','agent','0.Version');" id="agent_0Version" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%" title="Path: <?php echo $json['agent']['Response'][0]['Path']; ?>"><b>Agent Version: </b><?php echo textOnNull($json['agent']['Response'][0]['Version'],"N/A"); ?></li>
						<li data-bs-toggle="modal" data-bs-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','agent','0.Python_Version');" id="agent_0Python_Version" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Python Version: </b><?php echo textOnNull($json['agent']['Response'][0]['Python_Version'],"N/A"); ?></li>
					</ul>
				</div>
		  </div>
		</div>
	</div>
	<div id="card-4" class="col-xs-6 col-sm-6 col-md-4 col-lg-3" style="padding:3px;">
		<div class="panel panel-default" style="z-index:999;border-radius:10px;padding:5px">
			<div class="panel-body" style="z-index:999;border-radius:10px;height:275px;overflow-x:auto;overflow-y:hidden">
				<div class="rows">
					<table id="<?php echo $_SESSION['userid']; ?>General" style="width:125%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover table-borderless">
						<thead>
							<tr style="border-bottom:2px solid #d3d3d3;">
							<th style="width:20px;">Title</th>
							<th scope="col">Details</th>
							</tr>
						</thead>
						<tbody>		
						<?php
							$Logs = array_reverse($json['agent_log']['Response']);
							$error = $json['agent_log_error'];
							//Sort The array by Name ASC
							usort($Logs, function($a, $b) {
								return $a['Name'] <=> $b['Name'];
							});
							$count=0;
							foreach($Logs as $key=>$log){
								if (strpos($log['Type'], 'Warn') !== false) {
									$logColor="background:#fff3cd;color:#856404";
									$type="Warning";
								}
								if (strpos($log['Type'], 'Info') !== false) {
									//$logColor="background:#e2e3e5;color:#383d41";
									$logColor="";
									$type="Information";
								}
								if (strpos($log['Type'], 'Error') !== false ) {
									$logColor="background:#f8d7da;color:#721c24";
									$type="Error";
								}
								$time = $log['Time'];
								$count++;
								if($count==400){
									break;
								}
								if (strlen($log['Message']) >= 45) {
									$message= substr($log['Message'], 0, 45)."...";
								}
								else {
									$message= $log['Message'];
								}
								$count++;
						?>	
							<tr style="<?php echo $logColor; ?>">
								<td width="5%" ><?php echo $log['Title']; ?></td>
								<td title="<?php echo $log['Message']." @ ".$time; ?>" ><?php echo $message; ?></td>
							</tr>	
							<?php }
							 if($count== 0){ ?>
								<tr>
									<td colspan=2><center><h6>No error logs.</h6></center></td>
								</tr>
							<?php }?>				
						</tbody>
					</table>
				</div>
		  	</div>
		</div>
	</div>
	<div id="card-5" class="col-xs-6 col-sm-6 col-md-4 col-lg-3"style="padding:3px;">
		<div class="panel panel-default" style="z-index:999;border-radius:10px;">			
			<div class="panel-body" style="z-index:999;border-radius:10px;height:285px;padding:0px">
				<div class="row">
					<?php $loc = $json['general']['Response'][0]['ExternalIP']["loc"]; ?>
					<div style="width: 100%">
						<iframe style="border-radius:10px;" width="100%" height="285" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=<?php echo $loc; ?>&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
						</iframe>
					</div>
				</div>
		  	</div>
		</div>
	</div>
	<div id="card-6" class="col-xs-6 col-sm-6 col-md-4 col-lg-3" style="padding:3px;">
		<div class="panel panel-default" style="z-index:999;border-radius:10px;border:#343a40">

			<div class="panel-body" style="z-index:999;border-radius:10px;overflow:hidden;background:#32344a;height:285px;border:#343a40">
				<div class="rsow">
					<a target="_blank" href="<?php echo str_replace(".png","",$json['okla_speedtest']['Response'][0]['result']['url']); ?>">
						<form method="post" action="/">
							<?php if($json['okla_speedtest']['Response'][0]['result']['url']!=""){ ?>
								<center><img width="100%" style="margin-top:0px" height="80%" src="<?php echo $json['okla_speedtest']['Response'][0]['result']['url']; ?>.png"/></center>
							<?php }else{ ?>
								<center><h6 style='text-align:center;width:100%;bottom:0;padding:30px;color:#fff'>Refresh the results to get the latest Internet Speedtest from this asset.</h6></center><br><br>
							<?php } ?>
							<input type="hidden" value="refreshSpeedtest" name="type">
							<input type="hidden" value="<?php echo $computerID; ?>" name="computer_id">
							<center>
								<button class="btn btn-md btn-secondary" style="left:0;width:100%;bottom:0;position:absolute" type="submit">Refresh Results</button>
							</center>
						</form>
					</a>
				</div>
		 	 </div>
		</div>
	</div>
</div>
<!-------------------------------MODALS------------------------------------>
<div id="companyMoreInfo" class="modal fade" role="dialog">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<h4 class="modal-title"><?php if(crypto('decrypt', $company['name'],$company['hex'])!="N/A"){ echo textOnNull(crypto('decrypt',$company['name'],$company['hex']), "No ".$msp." Info"); }else{ echo $msp." Information";} ?></h4>
	  </div>
	  <div class="modal-body">
		<ul class="list-group">
			<?php if($msp=="Customer"){ ?>
				<li class="list-group-item">
					<b>Owner:</b>
					<?php echo textOnNull(phone(crypto('decrypt',$company['owner'],$company['hex'])), "No ".$msp." Owner"); ?>
				</li>
			<?php } ?>
			<li class="list-group-item">
				<b>Phone:</b>
				<?php echo textOnNull(phone(crypto('decrypt',$company['phone'],$company['hex'])), "No ".$msp." Phone"); ?>
			</li>
			<li class="list-group-item">
				<b>Email:</b>
				<a href="mailto:<?php echo crypto('decrypt',$company['email'],$company['hex']); ?>">
					<?php echo textOnNull(crypto('decrypt',$company['email'],$company['hex']), "No ".$msp." Email"); ?>
				</a>
			</li>
			<li class="list-group-item">
				<b>Address:</b>
				<?php echo textOnNull(crypto('decrypt',$company['address'],$company['hex']), "No ".$msp." Address"); ?>
			</li>
			<li class="list-group-item">
				<b>Comments:</b><br>
				<span style="margin-left:10px;">
					<?php echo textOnNull(crypto('decrypt',$company['comments'],$company['hex']), "No Comments"); ?>
				</span>
			</li>
		</ul>
		<span style="color:#696969;float:right;font-size:10px;">
		<?php if($company['date_added']!=""){ ?>
			Added <?php echo date("m/d/Y\ h:i:s", strtotime($company['date_added'])); 
		} ?>
		</span>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;color:#0c5460;" data-bs-dismiss="modal">Close</button>
	  </div>
	</div>
  </div>
</div>
<!--------------- View screenshot ------------->
<div id="screenshotModal" class="modal fade" role="dialog">
	<div style="cursor:zoom-out"class="modal-dialog modal-lg">
		<div class="modal-content">
			<?php 
				$query3 = "SELECT * FROM computer_data WHERE computer_id='".$computerID."' and name LIKE 'screenshot_%' ORDER BY ID DESC";
				$results3 = mysqli_query($db, $query3);
				$count3=0;
				while($data3 = mysqli_fetch_assoc($results3)){ 
					$count3++;	if($count3>10){break;}
					if($count3>1){ $style= "display:none;"; $class=""; }else{$class="secActive disabled";}
					$buttons .= "<button id='screenshotbtn".$count3."' type='button' onclick=\"$('.btn-sm').removeClass('secActive disabled');$('#screenshotbtn".$count3."').addClass('secActive disabled');$('.screenshots').slideUp('fast');$('#screenshot".$count3."').slideDown('fast');\" style='margin-right:5px;' class='btn btn-sm btn-primary ".$class."'>Display #".$count3."</button>"; 
			?>
						<img  data-bs-dismiss="modal" class="screenshots" id="screenshot<?php echo $count3;  ?>" style="height:500px;width:auto;<?php echo $style; ?>" src="data:image/jpeg;base64,<?php echo base64_encode($json['screenshot_'.$count3]); ?>"/> 
			<?php } ?>
			<div class="modal-footer">
				<?php echo $buttons; ?>
			</div>
		</div>
	</div>
</div>
<script>
	function sendMessage(){  
		var alertType = $("input[name='alertType']:checked").val();
		var alertTitle = $("#inputTitle").val();
		var alertMessage = $("#inputMessage").val();
		$.post("/", {
		type: "assetOneWayMessage",
		ID: computerID,
		alertType: alertType,
		alertTitle: alertTitle,
		alertMessage: alertMessage,
		},
		function(data, status){
			toastr.options.progressBar = true;
			toastr.success("Your Message Has Been Sent");
		});  
	}
	var data = {
	  labels: [
	    "Used (bytes)","Unused (bytes)"
	  ],
	  datasets: [
	    {
	      data: [<?php echo (int)$used2 .",".(int)$free2; ?>],
	      backgroundColor: [
	        "#0c5460"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
	      ]
	    }]
	};
	var data2 = {
	  labels: [
	    "CPU Usage","Unused"
	  ],
	  datasets: [
	    {
	      data: [<?php echo $cpuUsage.",".(100 - $cpuUsage); ?>],
	      backgroundColor: [
	        "<?php echo $siteSettings['theme']['Color 3']; ?>"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
	      ]
	    }]
	};
	var data3 = {
	  labels: [
	    "Disk Usage","Unused"
	  ],
	  datasets: [
	    {
	      data: [<?php echo (int)$usedPct.",".(int)$left; ?>],
	      backgroundColor: [
	        "<?php echo $siteSettings['theme']['Color 5']; ?>"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
	      ]
	    }]
	};
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut1'), {
	  type: 'doughnut',
	  data: data,
	  options: {
	  	responsive: true,
	    legend: {
	      display: false
	    }
	  }
	});
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut2'), {
	  type: 'doughnut',
	  data: data2,
	  options: {
	  	responsive: true,
	    legend: {
	      display: false
	    }
	  }
	});
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut3'), {
	  type: 'doughnut',
	  data: data3,
	  options: {
		
	  	responsive: true,
	    legend: {
	      display: false
	    }
		
	  }
		
	});	
	Chart.pluginService.register({
	  beforeDraw: function(chart) {
	    var width = chart.chart.width,
	        height = chart.chart.height,
	        ctx = chart.chart.ctx;	
	    ctx.restore();
	    var fontSize = (height / 114).toFixed(2);
	    ctx.font = fontSize + "em sans-serif";
	    ctx.textBaseline = "middle";
			var text = $('#'+chart.canvas.id).attr('data-centerval');        
			textX = Math.round((width - ctx.measureText(text).width) / 2),
	        textY = height / 2;
	    ctx.fillText(text, textX, textY);
	    ctx.save();
	  }
	});
</script>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>General').DataTable( {
			"lengthMenu": [[6], [6]],
			colReorder: true,
			"searching": false,
			"lengthChange": false,
			"pagingType": "simple",
			"info": false,
			"order": [],
			colReorder: true
		} );
	} );


	var test = atob(getCookie("sortableChart<?php echo $_SESSION['userid']; ?>"));
	if(test==""){
		var custumOrder = ["chart-1", "chart-3", "chart-4", "chart-2"];
	}else{
		var custumOrder = test.split(",");
	}
	var ul = $("#sortableChart");
	var items = $("#sortableChart div");
	for (var item of custumOrder) {
		ul.append($('#' + item + ''));
	}
	$("#sortableChart").disableSelection();
	$('#sortableChart').sortable({
		update: function (event, ui) {
			var data2 = $(this).sortable('serialize');
			data2 = data2.replaceAll("[]=", "-");
			data2 = data2.replaceAll("&", ",");

			setCookie("sortableChart<?php echo $_SESSION['userid']; ?>", btoa(data2), 365);
		}
	});

	var test = atob(getCookie("sortableCard<?php echo $_SESSION['userid']; ?>"));
	if(test==""){
		var custumOrder = ["card-1", "card-2", "card-3", "card-4", "card-5", "card-6"];
	}else{
		var custumOrder = test.split(",");
	}
	var ul = $("#sortableCard");
	var items = $("#sortableCard div");
	for (var item of custumOrder) {
		ul.append($('#' + item + ''));
	}
	$("#sortableCard").disableSelection();
	$('#sortableCard').sortable({
		update: function (event, ui) {
			var data2 = $(this).sortable('serialize');
			data2 = data2.replaceAll("[]=", "-");
			data2 = data2.replaceAll("&", ",");

			setCookie("sortableCard<?php echo $_SESSION['userid']; ?>", btoa(data2), 365);
		}
	});
</script>
<script>
//$('#dataTable4').DataTable();
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove()
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>
