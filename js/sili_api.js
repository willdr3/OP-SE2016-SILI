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
	  url: "API/user/login",
	  data: data,
	  error: function(jqXHR, textStatus, errorThrown) {
			//reset all the form fields
			$(".loginEmail").removeClass("has-error");
			$(".loginPassword").removeClass("has-error");
			$(".loginEmail .help-block").text("");
			$(".loginPassword .help-block").text("");
			data = $.parseJSON(jqXHR.responseText);
			$.each(data.errors, function(index, element) {
				if(element.code == "L001" || element.code == "L007")
				{
					$(".container-fluid").append("<div class=\"modal fade\" id=\"myModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\"><div class=\"modal-dialog\" role=\"document\"><div class=\"modal-content\"><div class=\"modal-header\"><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button><h4 class=\"modal-title\" id=\"myModalLabel\">Error</h4></div><div class=\"modal-body\">An error occurred while trying to Login, Please try again.</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button></div></div></div> </div>");
					$('#myModal').modal('show');
					return false;
				}
				else 
				{
					$("." + errors[element.code].field).addClass("has-error");
					$("." + errors[element.code].field +" .help-block").text(errors[element.code].errorMessage);	
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
	  url: "API/user/register",
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
				else 
				{
					$("." + errors[element.code].field).addClass("has-error");
					$("." + errors[element.code].field +" .help-block").text(errors[element.code].errorMessage);	
				}
			});
		},
		success: function(data) {	
			location.reload();
			}
		});
	return false;
}

function addSay(){
	var data = $(this).serialize();
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "API/say/",
		data: data,
		success: function(data) {	
			$(".sayBox").val("");
			$(".sayFeed").loadTemplate("content/templates/say.html",
				{
				        firstName: data.say["firstName"],
				        lastName: data.say["lastName"],
				        userName: data.say["userName"],
					message: data.say["message"],
					profilePicture: data.say["profileImage"],
					timePosted: data.say["timePosted"]
				}, { prepend: true });
;
		}
	});
	return false;
}

function fetchSays(){
	$.ajax({
		dataType: "json",
		url: "API/say/",
		success: function(data) {
			$.each(data.says, function(index, element) {	
				$(".sayFeed").loadTemplate("content/templates/say.html",
				{
				        firstName: element["firstName"],
				        lastName: element["lastName"],
				        userName: element["userName"],
					message: element["message"],
					profilePicture: element["profileImage"],
					timePosted: element["timePosted"]
				}, { append: true });
			});
		}
	});
}

function addComment(){
	var data = $(this).serialize();
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "API-addComment",
		data: data,
		success: function(data) {	
			$(".commentBox").val("");
			$(".commentFeed").loadTemplate("content/templates/comment.html", //<-- front end page yet to be made <--<-- commentFeed????? ask Lewey
				{
				        firstName: data.say["firstName"],
				        lastName: data.say["lastName"],
				        userName: data.say["userName"],
					message: data.say["message"],
					profilePicture: data.say["profileImage"],
					timePosted: data.say["timePosted"]
				}, { prepend: true }); // <--- Maybe change this
;
		}
	});
	return false;
}

function getUserDetials() {
	return $.ajax({
		dataType: "json",
		url: "API/user/",
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
		url: "API/profile/settings",
		success: function(data) {
			$(".acc-name").text(data.userProfile["firstName"] + " " + data.userProfile["lastName"]);
			$(".acc-username").text(data.userProfile["userName"]);
			$(".acc-email").text(data.userProfile["email"]);
			$(".acc-profileImage").attr("src", data.userProfile["profileImage"]);
			$(".acc-userbio").text(data.userProfile["userBio"]);
			
			$("body").loadTemplate("content/templates/changeEmail.html", "", { append: true });
				
			$("body").loadTemplate("content/templates/changePassword.html", "", { append: true });
				
			$("body").loadTemplate("content/templates/personalForm.html",
				{
				    firstName: data.userProfile["firstName"],
				    lastName: data.userProfile["lastName"],
				    userName: data.userProfile["userName"],
					dob: data.userProfile["dob"],
					gender: data.userProfile["gender"]
				}, { append: true });
				
			$("body").loadTemplate("content/templates/userBio.html",
				{
				        bio: data.userProfile["userBio"]
				}, { append: true });
			
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

var myAppModule = angular.module('MyApp', ['ngImgCrop']);

var options = {
  url: function(phrase) {
		return "API/profile/search/" + phrase;
	},
  
  placeholder: "Search SILI for friends!",
  
  getValue: "name",
  
  cssClasses: "userSearch",
  
  adjustWidth: false,
  
  requestDelay: 500,

  template: {
    type: "iconRight",
    fields: {
      iconSrc: "profileImage"
    }
  },

  list: {
		maxNumberOfElements: 10,
		match: {
				enabled: true
		},
		showAnimation: {
		  type: "slide"
		},
		hideAnimation: {
		  type: "slide"
		}
  }

};

$("document").ready(function() {
	$("#userSearch").easyAutocomplete(options);
});