var userName = "";
var firstName = "";
var lastName = "";
var profileImage = "";
var loggedIn = false;

function userLogin(){
	var data = $(this).serialize();
	$.ajax({
	  type: "POST",
	  dataType: "json",
	  url: "API-login",
	  data: data,
	  error: function(jqXHR, textStatus, errorThrown) {
			//reset all the form fields
			$(".loginEmail").removeClass("has-error");
			$(".loginPassword").removeClass("has-error");
			$(".loginEmail .help-block").text("");
			$(".loginPassword .help-block").text("");
			data = $.parseJSON(jqXHR.responseText);
			var errors = '';
			$.each(data.errors, function(index, element) {
				if(element.code == "L001" || element.code == "L007")
				{
					$(".container-fluid").append("<div class=\"modal fade\" id=\"myModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\"><div class=\"modal-dialog\" role=\"document\"><div class=\"modal-content\"><div class=\"modal-header\"><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button><h4 class=\"modal-title\" id=\"myModalLabel\">Error</h4></div><div class=\"modal-body\">An error occurred while trying to Login, Please try again.</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button></div></div></div> </div>");
					$('#myModal').modal('show');
					return false;
				}
				if(element.code == "L002")
				{
					$(".loginEmail").addClass("has-error");
					$(".loginEmail .help-block").text("Please enter your email address");				
				}
				
				if(element.code == "L003" || element.code == "L006")
				{
					$(".loginEmail").addClass("has-error");
					$(".loginEmail .help-block").text("Please enter a vaid email address");				
				}
				
				if(element.code == "L004")
				{
					$(".loginPassword").addClass("has-error");
					$(".loginPassword .help-block").text("Please enter your password");				
				}
				
				if(element.code == "L005")
				{
					$(".loginPassword").addClass("has-error");
					$(".loginPassword .help-block").text("Password is incorrect");				
				}
			});
		},				
	  success: function(data) {	
			location.reload();
		}
	});
	return false;
};

function userRegister(){
	var data = $(this).serialize();
	$.ajax({
	  type: "POST",
	  dataType: "json",
	  url: "API-register",
	  data: data,
	  error: function(jqXHR, textStatus, errorThrown) {
			//reset all the form fields
			$(".registerFirstName").removeClass("has-error");
			$(".registerFirstName .help-block").text("");
			$(".registerLastName").removeClass("has-error");
			$(".registerLastName .help-block").text("");
			$(".registerEmail").removeClass("has-error");
			$(".registerEmail .help-block").text("");
			$(".registerConfirmEmail").removeClass("has-error");
			$(".registerConfirmEmail .help-block").text("");
			$(".registerPassword").removeClass("has-error");
			$(".registerPassword .help-block").text("");
			$(".registerConfirmPassword").removeClass("has-error");
			$(".registerConfirmPassword .help-block").text("");
			data = $.parseJSON(jqXHR.responseText);
			$.each(data.errors, function(index, element) {
				if(element.code == "R001" || element.code == "R007")
				{
					$(".container-fluid").append("<div class=\"modal fade\" id=\"myModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\"><div class=\"modal-dialog\" role=\"document\"><div class=\"modal-content\"><div class=\"modal-header\"><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button><h4 class=\"modal-title\" id=\"myModalLabel\">Error</h4></div><div class=\"modal-body\">An error occurred while trying to Register, Please try again.</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button></div></div></div> </div>");
					$('#myModal').modal('show');
					return false;
				}
				if(element.code == "R002") {
					$(".registerEmail").addClass("has-error");
					$(".registerEmail .help-block").text("Please enter your email address");	
				}
				if(element.code == "R003") {
					$(".registerEmail").addClass("has-error");
					$(".registerEmail .help-block").text("Please enter a valid email address");	
				}
				if(element.code == "R004") {
					$(".registerConfirmEmail").addClass("has-error");
					$(".registerConfirmEmail .help-block").text("Please enter confirm email address");	
				}
				if(element.code == "R005") {
					$(".registerEmail").addClass("has-error");
					$(".registerConfirmEmail").addClass("has-error");
					$(".registerConfirmEmail .help-block").text("Confirm email doesnt match Email Address");	
				}
				if(element.code == "R006") {
					$(".registerEmail").addClass("has-error");
					$(".registerEmail .help-block").text("This email has already been used to register");	
				}
				if(element.code == "R008") {
					$(".registerFirstName").addClass("has-error");
					$(".registerFirstName .help-block").text("Please enter your First Name");	
				}
				if(element.code == "R009") {
					$(".registerLastName").addClass("has-error");
					$(".registerLastName .help-block").text("Please Enter your Last Name");	
				}
				if(element.code == "R010") {
					$(".registerPassword").addClass("has-error");
					$(".registerPassword .help-block").text("Your password does not meet the complexity requirements");	
				}
				if(element.code == "R011") {
					$(".registerConfirmPassword").addClass("has-error");
					$(".registerConfirmPassword .help-block").text("Confirm password does not match password");	
				}
				if(element.code == "R012") {
					$(".registerConfirmPassword").addClass("has-error");
					$(".registerConfirmPassword .help-block").text("Please confirm your password");	
				}
				if(element.code == "R013") {
					$(".registerPassword").addClass("has-error");
					$(".registerPassword .help-block").text("Please enter your password");	
				}
			});
		},
		success: function(data) {	
			
			}
		});
	return false;
}

function addSay(){
	var data = $(this).serialize();
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "API-addsay",
		data: data,
		success: function(data) {	
			$(".sayBox").val("");
			$(".sayFeed").prepend($("<div class=\"row say\"> <div class=\"col-md-1 sayProfilePic\"> <img class=\"sayProfileImg img-circle pull-right\" src=\"" + data.say["profileImage"] + "\" /> </div> <div class=\"col-md-11 sayMessageDetails\"> <div class=\"row\"> <div class=\"col-md-12\">" + data.say["firstName"] + " " + data.say["lastName"] + " - (" + data.say["userName"] +")</div> </div> <div class=\"row\"> <div class=\"col-md-12 sayMessage\">" + data.say["message"] + " </div> </div> <div class=\"row\"> <div class=\"col-md-2\"></div> <div class=\"col-md-2\"></div> <div class=\"col-md-2\"></div> <div class=\"col-md-2\"></div> <div class=\"col-md-4 text-right\">" + data.say["timePosted"] + "</div> </div> </div> </div>").fadeIn(3000));
		}
	});
	return false;
}

function fetchSays(){
	$.ajax({
		dataType: "json",
		url: "API-fetchsays",
		success: function(data) {
			$.each(data.says, function(index, element) {	
				$(".sayFeed").prepend("<div class=\"row say\"> <div class=\"col-md-1 sayProfilePic\"> <img class=\"sayProfileImg img-circle pull-right\" src=\"" + element["profileImage"] + "\" /> </div> <div class=\"col-md-11 sayMessageDetails\"> <div class=\"row\"> <div class=\"col-md-12\">" + element["firstName"] + " " + element["lastName"] + " - (" + element["userName"] +")</div> </div> <div class=\"row\"> <div class=\"col-md-12 sayMessage\">" + element["message"] + " </div> </div> <div class=\"row\"> <div class=\"col-md-2\"></div> <div class=\"col-md-2\"></div> <div class=\"col-md-2\"></div> <div class=\"col-md-2\"></div> <div class=\"col-md-4 text-right\">" + element["timePosted"] + "</div> </div> </div> </div>");
			});
		}
	});
}

function getUserDetials() {
	return $.ajax({
		dataType: "json",
		url: "API-checklogin",
		success: function(data) {
			userName = data.userData["userName"];
			firstName = data.userData["firstName"];
			lastName = data.userData["lastName"];
			profileImage = data.userData["profileImage"];
			loggedIn = true;
		},
		error: function(jqXHR, textStatus, errorThrown) { loggedIn = false; }

	});
}


function getUserProfile() {
	return $.ajax({
		dataType: "json",
		url: "API-getProfile",
		success: function(data) {
			$(".acc-name").text(data.userProfile["firstName"] + " " + data.userProfile["lastName"]);
			$(".acc-username").text(data.userProfile["userName"]);
			$(".acc-email").text(data.userProfile["email"]);
			$(".acc-profileImage").attr("src", data.userProfile["profileImage"]);
			$(".acc-userbio").text(data.userProfile["userBio"]);
		}
	});
}


getUserDetials().done(function() {
if(loggedIn) {
$("#profileImage").attr("src", profileImage);
$("#userName").text(firstName);
fetchSays();
}
});