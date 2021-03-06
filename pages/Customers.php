<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page']);

$query = "SELECT hex,ID,name,phone,email,address,comments,active,owner FROM companies where ID<>'1' ORDER BY active,name ASC";
$results = mysqli_query($db, $query);
$companyCount = mysqli_num_rows($results);
?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h5 style="color:#0c5460">All  <?php echo $msp; ?>s (<?php echo $companyCount;?>)	
		<button title="Refresh" onclick="loadSection('Customers');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</button>
		<button type="button" style="float:right;margin:5px;background:#0ac282;;color:#fff" data-bs-toggle="modal" data-bs-target="#companyModal" class="btn-sm btn btn-light" title="Add User" onclick="editCompany('','','','','','')">
			<i class="fas fa-plus"></i> Add  <?php echo $msp; ?> 
		</button>	
	</h5>
</div>	
<div class="card table-card">
	<div class="card-header">
		<h5>Listing All Current <?php echo $msp; ?>s</h5>
		<div class="card-header-right">
			<ul class="list-unstyled card-option">
				<li>
					<i class="feather icon-maximize full-card"></i>
				</li>
				<li>
					<i class="feather icon-minus minimize-card"></i>
				</li>
				<li>
					<i class="feather icon-trash-2 close-card"></i>
				</li>
			</ul>
		</div>
	</div>
	<div style="padding:10px;overflow-x:auto">
		<table id="<?php echo $_SESSION['userid']; ?>Customers" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table-striped table table-hover table-borderless">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
					<th scope="col">ID</th>
					<th scope="col">Name</th>
					<th scope="col">Alerts</th>
					<th scope="col">Phone</th>
					<th scope="col">Email</th>
					<th scope="col">Comments</th>
					<th scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
					//Fetch Results
					$count=0;
					while($company = mysqli_fetch_assoc($results)){
						$computersWithAlerts = 0;
						$aggrigateAlerts = "";
						$count++;
						$query = "SELECT ID FROM computers WHERE company_id='".$company['ID']."'";
						$computerResults = mysqli_query($db, $query);
						$computerCount = mysqli_num_rows($computerResults);
						
						while($computerData = mysqli_fetch_assoc($computerResults)){
							$getWMI = array("logical_disk", "general","agent");
							$data = getComputerData($computerData['ID'], $getWMI);
							if(count($data['Alerts'])>0){
								$computersWithAlerts++;
								$aggrigateAlerts .= $data['Alerts_raw'].",";
							}
						}
				?>
					<tr id="companyList<?php echo $company['ID']; ?>">
						<td>
							<?php echo $company['ID'];?>
						</td>
						<td style="cursor:pointer" onclick="loadSection('Assets', '','latest','<?php echo crypto('decrypt', $company['name'], $company['hex']); ?>');">
							<b><?php echo crypto('decrypt', $company['name'], $company['hex']);?></b>
							&nbsp;(<?php echo $computerCount;?>)
						</td>
						<td>
							<?php if($computersWithAlerts > 0){?>
								<button style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#computerAlerts" onclick="computerAlertsModal('<?php echo crypto('decrypt', $company['name'], $company['hex']); ?>','<?php echo $aggrigateAlerts;?>', true);">
									<i title="Priority" class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
									<?php echo $computersWithAlerts;?>
							</button>
							<?php }else{?>
								<button data-bs-toggle="modal" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" data-bs-target="#computerAlerts" class="btn btn-success btn-sm" onclick="computerAlertsModal('<?php echo crypto('decrypt', $company['name'], $company['hex']); ?>');">
									<i class="fas fa-thumbs-up"></i>
							</button>
							<?php }?>
						</td>
						<td>
							<?php echo textOnNull(phone(crypto('decrypt', $company['phone'], $company['hex'])),"No Phone");?>
						</td>
						<td>
							<a href="mailto:<?php echo $company['email'];?>">
								<?php echo textOnNull(ucfirst(crypto('decrypt', $company['email'], $company['hex'])),"No Email");?>
							</a>
						</td>
						<td>
							<?php echo textOnNull(ucfirst(crypto('decrypt', $company['comments'], $company['hex'])), "No Comments");?>
						</td>
						<td>
							<form style="display:inline" >
								<input type="hidden" name="type" value="DeleteCompany"/>
								<input type="hidden" name="ID" value="<?php echo $company['ID'];?>"/>
								<?php if($company['active']=="1"){ ?>
									<button type="button" id="delCompany<?php echo $company['ID']; ?>" onclick="deleteCompany(<?php echo $company['ID']; ?>,'0')" title="Remove <?php echo $msp; ?>" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i>
									</button>
									<button type="button" id="actCompany<?php echo $company['ID']; ?>" onclick="deleteCompany(<?php echo $company['ID']; ?>,'1')" title="Reactivate <?php echo $msp; ?>" style="display:none;margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-success btn-sm">
										<i class="fas fa-plus"></i>&nbsp;
									</button>
								<?php }else{ ?>
									<button type="button" id="actCompany<?php echo $company['ID']; ?>" onclick="deleteCompany(<?php echo $company['ID']; ?>,'1')" title="Reactivate <?php echo $msp; ?>" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-success btn-sm">
										<i class="fas fa-plus"></i>
									</button>
									<button type="button" id="delCompany<?php echo $company['ID']; ?>" onclick="deleteCompany(<?php echo $company['ID']; ?>,'0')" title="Remove <?php echo $msp; ?>" style="display:none;margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i>&nbsp;				
									</button>
								<?php }?>								
								<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#companyModal" onclick="editCompany('<?php echo $company['ID'];?>','<?php echo crypto('decrypt',$company['name'],$company['hex']);?>','<?php echo crypto('decrypt',$company['address'],$company['hex']);?>','<?php echo (crypto('decrypt',$company['phone'],$company['hex']));?>','<?php echo ucfirst(crypto('decrypt',$company['email'],$company['hex']));?>','<?php echo ucfirst(crypto('decrypt',$company['comments'],$company['hex']));?>','<?php echo ucfirst(crypto('decrypt',$company['owner'],$company['hex']));?>')" title="Edit <?php echo $msp; ?>" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-dark btn-sm">
									<i class="fas fa-pencil-alt"></i>
								</a>
							</form>
							<form action="/" method="post" style="display:inline;">
								<input type="hidden" value="<?php echo $company['ID'];?>" name="companyAgent">
								<button type="submit" title="Download <?php echo $msp; ?> Agent" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-dark btn-sm">
									<i class="fas fa-download"></i>
								</button>
							</form>
							<?php if($computerCount>0){ ?>
							<button type="button" onclick="updateCompanyAgent(<?php echo $company['ID'];?>);" title="Update All Agents Assigned To This <?php echo $msp; ?>" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-dark btn-sm">
								<i class="fas fa-cloud-upload-alt"></i>
							</button>	
							<?php } ?>			
						</td>
					</tr>
				<?php }?>
			</tbody>
		</table>
	</div>
</div>
<script>
	//Edit Company
	function editCompany(ID, name, address, phone, email, comments, owner){
		$("#editCompanyModal_ID").val(ID);
		$("#editCompanyModal_name").val(name);
		$("#editCompanyModal_address").val(address);
		$("#editCompanyModal_phone").val(phone);
		$("#editCompanyModal_email").val(email);
		$("#editCompanyModal_comments").val(comments);
		$("#editCompanyModal_owner").val(owner);
	}
	function searchItem(text, page="Dashboard", ID=0, filters="", limit=25){
		$(".loadSection").html("<center><h3 style='margin-top:40px;'><i class='fas fa-spinner fa-spin'></i> Loading "+text+"</h3></center>");
		$(".loadSection").load("ajax/"+page+".php?limit="+limit+"&search="+encodeURI(text)+"&ID="+ID+"&filters="+encodeURI(filters));
	}
</script>
	<script>
    $(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Customers').dataTable( {
			colReorder: true,
			stateSave: true
		} );
    });
</script>
