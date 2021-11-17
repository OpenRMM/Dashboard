    //Sidebar
$(document).ready(function () {
    $('.sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
});

//Terminal
$('#terminaltxt').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        $("#terminalResponse").html("Sending Command: "+$('#terminaltxt').val()+" <i class='fas fa-spinner fa-spin'></i>");
        $.post("includes/terminal.php", {
            id: computerID,
            command: $('#terminaltxt').val()
        },
        function(data, status){
            $("#terminalResponse").html(data);
        });
    }
});

//Alerts Modal
function computerAlertsModal(title, delimited='none', showHostname = false){
    $("#computerAlertsHostname").html("<b>Alerts for "+title+"</b>");
    if(delimited=="none"){
        $("#computerAlertsModalList").html("<div class='alert alert-success' style='font-size:12px' role='alert'><b><i class='fas fa-thumbs-up'></i> No Issues</b></div>");
        return;
    }
    $("#computerAlertsModalList").html("")
    var alerts = delimited.split(",");
    var hostname = "";
    for(alert in alerts){
        var alertData = alerts[alert].split("|");
        if(alertData[0].trim()==""){
            continue;
        }
        if(showHostname == true){
            hostname = alertData[3];
        }
        $("#computerAlertsModalList").html($("#computerAlertsModalList").html() + "<div class='calert alert alert-"+alertData[2]+"' role='alert'><b><i class='fas fa-exclamation-triangle text-"+alertData[2]+"'></i> "+ hostname + " " + alertData[0]+"</b> - " + alertData[1] + "</div>");
    }
}

//Random password
function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

//Set random passwords to inputs
function generate() {
    var pass = randomPassword(8);
    $('#editUserModal_password').prop('type', 'text').val(pass);
    $('#editUserModal_password2').prop('type', 'text').val(pass);
}

//Page Alerts, replaces alert()
function pageAlert(title, message, type="default"){
    if(title.trim() == ""){
        title = "Message From Webpage";
    }
    if(message.trim() != "") {
        type = type.toLowerCase();
        toastr.options.progressBar = true;
        toastr[type](message,title);
    }
}

//Send commands
function sendCommand(command, prompt, expire_after=5){
    if(confirm("Are you sure you would like to "+prompt+"?")){
        $.post("index.php", {
        type: "SendCommand",
        ID: computerID,
        command: command,
        expire_after: expire_after
        },
        function(data, status){
            toastr.options.progressBar = true;
            toastr.info('Your Command Has Been Sent.');
        });
    }
}
function deleteNote(delNote){  
    $.post("index.php", {
    delNote: delNote
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('Your Notes Have Been Deleted.');
        $(".noteList").hide();
        $(".no_noteList").show();
    });
}
function deleteCompany(ID,status2){  
    $.post("index.php", {
        ID: ID,
        type: "DeleteCompany",
        companyactive: status2
    },
    function(data, status){
        if(status2=="0"){
            toastr.options.progressBar = true;
            toastr.info('The selected <?php echo $msp; ?> has been deactivated.');
            $("#actCompany" + ID).show();
            $("#delCompany" + ID).hide();
        }
        if(status2=="1"){
            toastr.options.progressBar = true;
            toastr.info('The selected <?php echo $msp; ?> has been activated.');
            $("#actCompany" + ID).hide();
            $("#delCompany" + ID).show(); 
        }

    });
}
function deleteUser(ID,status2){  
    $.post("index.php", {
        ID: ID,
        type: "DeleteUser",
        useractive: status2
    },
    function(data, status){
        if(status2=="0"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been deactivated.');
            $("#actUser" + ID).show();
            $("#delUser" + ID).hide();
        }
        if(status2=="1"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been activated.');
            $("#actUser" + ID).hide();
            $("#delUser" + ID).show();    
        }

    });
}
function deleteAlert(ID){  
    $.post("index.php", {
        ID: ID,
        type: "delAlert"
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The selected alert has been deleted.');
        $("#alert" + ID).fadeOut();
    });
}
function deleteTask(ID){  
    $.post("index.php", {
        ID: ID,
        type: "delTask"
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The selected task has been deleted.');
        $("#task" + ID).fadeOut();
    });
}
function deleteUserProfile(ID,useractive){  
    $.post("index.php", {
        ID: ID,
        type: "DeleteUser",
        useractive: useractive
    },
    function(data, status){
        if(useractive=="0"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been disabled.');
            $("#userDel" + ID).hide();
            $("#userAct" + ID).show();  
        }
        if(useractive=="1"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been enabled.');
            $("#userAct" + ID).hide();
            $("#userDel" + ID).show();  
        }
    });
}
function newNote(){  
    var note = $("#note").val();
    var noteTitle = $("#noteTitle").val(); 
    $.post("index.php", {
        note: note,
        noteTitle: noteTitle
    },
    function(data, status){
        $(".no_noteList").hide();
        $("#note").val('');
        $("#noteTitle").val(''); 
        var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv' + counter);
		newTextBoxDiv.after().html('<a title="View Note" class="noteList" onclick="$(\'#notetitle\').text(\''+ noteTitle + '\');$(\'#notedesc\').text(\'' + note + '\');" data-toggle="modal" data-target="#viewNoteModal"><li style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item"><i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>' + noteTitle + '</li> </a>');
		newTextBoxDiv.prependTo("#TextBoxesGroup");  
        
    });
}
function deleteActivity(){
    var ID = $("#delActivity").val();  
    $.post("index.php", {
        delActivity: ID
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The Activity has been cleared for this user');
        $("#activity").slideUp("slow");
    });
}

function serverStatus(action){ 
    $.post("index.php", {
        type: "serverStatus",
        action: action
    },
    function(data, status){
        toastr.options.progressBar = true;
        if(action=="stop"){
            toastr.error('The request to stop the server has been sent.');
        }
        if(action=="restart"){
            toastr.warning('The request to restart the server has been sent.');
        }
    });
}

function updateAgent(ID2){ 
    $.post("index.php", {
       type: "updateAgent",
       ID: ID2
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The update request has been sent. Please allow up to 5 minutes for the update to complete.');
    });
}

function deleteAssets(){ 
    var array = []
    var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')

    for (var i = 0; i < checkboxes.length; i++) {
    array.push(checkboxes[i].value)
    }
    $.post("index.php", {
        computers: array,
        type: "deleteAssets"
    },
    function(data, status){
        array.forEach(function (item) {
            $("#row"+item).hide();
        });
        toastr.options.progressBar = true;
        toastr.error('The Selected Assets Have Been Deleted');
        
    });
}
function assignAssets(){ 
    var array = []
    var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')
    var companyID = $('input[name="companies"]:checked').attr('company')
    var company = $('input[name="companies"]:checked').val();
    for (var i = 0; i < checkboxes.length; i++) {
    array.push(checkboxes[i].value)
    }
    $.post("index.php", {
        computers: array,
        companies: company,
        companyID: companyID,
        type: "CompanyComputers"
    },
    function(data, status){
        array.forEach(function (item) {
            $("#col"+item).text(company);
        });
        toastr.options.progressBar = true;
        toastr.info('The Selected Assets Have Been Assigned To ' + company);
        
    });
}

function updateTicket(type,data2,ticket,id=0){  
	if(type=="category"){
		$('#category').html(data2);
	}
	if(type=="priority"){
		$('#priority').html(data2);
	}
	if(type=="status"){
		$('#status').html(data2);
        $('#status' + ticket).html(data2);
	}
	if(type=="assignee"){
		$('#assignee').html(data2);
		data2 = id;
	}
	
	$.post("/", {
	type: "updateTicket",
	ID: ticket,
	tkttype: type,
	tktdata: data2
	},
	function(data, status){
		toastr.options.progressBar = true;
		toastr.success("Your Changes Have Been Saved");
	});  
}
function olderData(ID, name, key){
    $("#olderData_content").load("includes/olderData.php?ID="+btoa(ID)+"&name="+btoa(name)+"&key="+btoa(key));
    $(".olderdata").css({"z-index": "2"});
    key = key.replace(".", "");

    if(key=="null"){
        $("#olderDataModalDialog").removeClass("modal-md");
        $("#olderDataModalDialog").addClass("modal-lg");
    }else{
        $("#olderDataModalDialog").addClass("modal-md");
        $("#olderDataModalDialog").removeClass("modal-lg");
         
    }
    if(name=="Firewall"){
        $("#" + name).css({"z-index": "99999"});
    }else{
        $("#" + name + "_" + key).css({"z-index": "99999"});
    }
}