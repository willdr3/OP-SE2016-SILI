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
					sayID: data.say["sayID"],
					firstName: data.say["firstName"],
					lastName: data.say["lastName"],
					userName: data.say["userName"],
					message: data.say["message"],
					profilePicture: data.say["profileImage"],
					timeStamp: data.say["timePosted"],
					timePosted: moment(data.say["timePosted"]).fromNow(),
					boos: data.say["boos"],
					applauds: data.say["applauds"],
					resays: data.say["resays"],
					applaudImg: getActionImage("applaud", data.say["applaudStatus"]),
					resayImg: getActionImage("resay", data.say["resayStatus"]),
					booImg: getActionImage("boo", data.say["booStatus"]),
				}, { prepend: true});
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
				    sayID: element["sayID"],
					firstName: element["firstName"],
				    lastName: element["lastName"],
				    userName: element["userName"],
					message: element["message"],
					profilePicture: element["profileImage"],
					timeStamp: element["timePosted"],
					timePosted: moment(element["timePosted"]).fromNow(),
					boos: element["boos"],
					applauds: element["applauds"],
					resays: element["resays"],
					applaudImg: getActionImage("applaud", element["applaudStatus"]),
					resayImg: getActionImage("resay", element["resayStatus"]),
					booImg: getActionImage("boo", element["booStatus"]),
				}, { append: true });	
			});
		}
	});
}

function fetchSayDetails(sayID){
	return $.ajax({
		dataType: "json",
		url: "API/say/say/" + sayID,
		success: function(data) {
			$(".sayDetailsModal").loadTemplate("content/templates/sayDetails.html",
			{
				sayID: data.say["sayID"],
				firstName: data.say["firstName"],
			    lastName: data.say["lastName"],
			    userName: data.say["userName"],
				message: data.say["message"],
				profilePicture: data.say["profileImage"],
				timePosted: moment(data.say["timePosted"]).fromNow(),
				boos: data.say["boos"],
				applauds: data.say["applauds"],
				resays: data.say["resays"],
				applaudImg: getActionImage("applaud", data.say["applaudStatus"]),
				resayImg: getActionImage("resay", data.say["resayStatus"]),
				booImg: getActionImage("boo", data.say["booStatus"])
			});
		}
	});
}

function fetchComments(sayID){
	return $.ajax({
		dataType: "json",
		url: "API/say/comment/" + sayID,
		success: function(data) {
			$.each(data.says, function(index, element) {
				$(".commentFeed").loadTemplate("content/templates/comment.html",
				{
					sayID: element["sayID"],
					firstName: element["firstName"],
				    lastName: element["lastName"],
				    userName: element["userName"],
					message: element["message"],
					profilePicture: element["profileImage"],
					timePosted: moment(element["timePosted"]).fromNow()
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
		url: "API/say/comment/",
		data: data,
		success: function(data) {	
			$(".commentBox").val("");
			$(".commentFeed").loadTemplate("content/templates/comment.html",
				{
					firstName: data.say["firstName"],
				    lastName: data.say["lastName"],
				    userName: data.say["userName"],
					message: data.say["message"],
					profilePicture: data.say["profileImage"],
					timePosted: moment(data.say["timePosted"]).fromNow()
				}, { append: true });
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

function getUserProfile(reqUserName = '') {

	reqUserName = window.btoa(reqUserName).replace("=",""); //Remove equals from base64 string	
	requestUserProfile(reqUserName).done(function(data) {
		//Button Styling
	});
}

function requestUserProfile(reqUserName) {
	return $.ajax({
		dataType: "json",
		url: "API/profile/user/" + reqUserName,
		success: function(data) {
			$(".profile-name").text(data.userProfile["firstName"] + " " + data.userProfile["lastName"]);
			$(".profile-username").text(data.userProfile["userName"]);
			$(".profile-profileImage").attr("src", data.userProfile["profileImage"]);
			$(".profile-userbio").text(data.userProfile["userBio"]);
			$(".profile-listens").text(numeral(data.userProfile["listensTo"]).format('0 a'));
			$(".profile-audience").text(numeral(data.userProfile["audience"]).format('0 a')); 
		}
	});
}

function getUserSettings() {
	requestUserSettings().done(function(data) {
			$('body').find('select[name="gender"]').val(data.userProfile["gender"]);
			$('[data-toggle="datepicker"]').datepicker({
				autohide: true,
				format: 'dd/mm/yyyy',
				zIndex: 10000,
				startView: 2,
				date: data.userProfile["dob"]
			});
	});
}

function requestUserSettings() {
	return $.ajax({
		dataType: "json",
		url: "API/profile/",
		success: function(data) {
			$(".acc-name").text(data.userProfile["firstName"] + " " + data.userProfile["lastName"]);
			$(".acc-username").text(data.userProfile["userName"]);
			$(".acc-email").text(data.userProfile["email"]);
			$(".acc-profileImage").attr("src", data.userProfile["profileImage"]);
			$(".acc-userbio").text(data.userProfile["userBio"]);
			$(".acc-gender").text(data.userProfile["gender"]);
			$(".acc-location").text(data.userProfile["location"]);

			$("#profileModals").html("");

			$("#profileModals").loadTemplate("content/templates/changeEmail.html", "", { append: true });
				
			$("#profileModals").loadTemplate("content/templates/changePassword.html", "", { append: true });
				
			$("#profileModals").loadTemplate("content/templates/personalForm.html",
			{
			    firstName: data.userProfile["firstName"],
			    lastName: data.userProfile["lastName"],
			    userName: data.userProfile["userName"],
				dob: data.userProfile["dob"],
				gender: data.userProfile["gender"]
			}, { append: true, async: false });
				
			$("#profileModals").loadTemplate("content/templates/userBio.html",
				{
				        bio: data.userProfile["userBio"]
				}, { append: true });
			
			
		}
	});
}


function SayAction(sayID, action) {
	var count, image;
	$.ajax({
		dataType: "json",
		async: false,
		url: "API/say/" + action + "/" + sayID,
		success: function(data) {
			count = data["count"];			
			image = getActionImage(action, data["status"]);
		}
	});
	
	return [count, image];
}

function ProfileEdit(data)
{
	$.ajax({
	  type: "POST",
	  dataType: "json",
	  url: "API/profile/",
	  data: data,
	  error: function(jqXHR, textStatus, errorThrown) {
			
		},				
	  success: function(data) {	
			$('#personal-form').modal('hide');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			getUserSettings();
		}
	});
	return false;
}

function ProfilePasswordChange(data)
{
	$.ajax({
	  type: "POST",
	  dataType: "json",
	  url: "API/profile/password",
	  data: data,
	  error: function(jqXHR, textStatus, errorThrown) {
			
		},				
	  success: function(data) {	
			$('#changePassword-form').modal('toggle');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			getUserSettings();
		}
	});
	return false;
}

function ProfileEmailChange(data)
{
	$.ajax({
	  type: "POST",
	  dataType: "json",
	  url: "API/profile/email",
	  data: data,
	  error: function(jqXHR, textStatus, errorThrown) {
			
		},				
	  success: function(data) {	
			$('#changeEmail-form').modal('toggle');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			getUserSettings();
		}
	});
	return false;
}

function ProfileBioEdit(data)
{
	$.ajax({
	  type: "POST",
	  dataType: "json",
	  url: "API/profile/bio",
	  data: data,
	  error: function(jqXHR, textStatus, errorThrown) {
			
		},				
	  success: function(data) {	
			$('#userBio-form').modal('toggle');
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			getUserSettings();
		}
	});
	return false;
}

function getActionImage(action, status)
{
	if (action == "applaud")
	{
		image = "images/applaud.png";
		if(status == true)
		{
			image = "images/applaudActive.png";
		}
	}	
	else if (action == "resay")
	{
		image = "images/resay.png";
		if(status == true)
		{
			image = "images/resayActive.png";
		}
	}		
	else if (action == "boo")
	{
		image = "images/boo.png";
		if(status == true)
		{
			image = "images/booActive.png";
		}
	}		

	return image;
}

getUserDetials().done(function() {
	if(loggedIn) {
		$("#profileImage").attr("src", profileImage);
		$("#userName").text(firstName);
		fetchSays();
	}
});

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

angular.module('app', ['ngImgCrop'])
  .controller('Ctrl', function($scope) {
    $scope.myImage = '';
    $scope.myCroppedImage = '';

    var handleFileSelect=function(evt) {
      var file = evt.currentTarget.files[0];
      var reader = new FileReader();
      reader.onload = function (evt) {
        $scope.$apply(function($scope){
          $scope.myImage = evt.target.result;
        });
      };

    $scope.onLoadError=function() {
      console.log('onLoadError fired');
    };
    $scope.onLoadDone=function() {
      console.log('onLoadDone fired');
      $('#profileImage-form').modal('show');
    };
      reader.readAsDataURL(file);
    };
    angular.element(document.querySelector('#profileImageUpload')).on('change',handleFileSelect);
  });

$("document").ready(function() {
	setInterval(function()
		{
			$(".say").each(function() {
				var timeCode = $(this).data("timestamp");
				$(this).find(".timeStamp").text(moment(timeCode).fromNow());
			});
		},60000);
	$("#userSearch").easyAutocomplete(options);
	$(document).on('click', '.applaud', function(){		
		var $el = $(this).parent().parent().parent().parent();
		var sayID = $el.attr('id');
		var action = SayAction(sayID, "applaud")
		var count = action[0];
		var image = action[1];
		$(this).parent().find("span").html(count);			
		$(this).parent().find("img").attr('src', image);
			
	});
	
	$(document).on('click', '.reSay', function(){
		var $el = $(this).parent().parent().parent().parent();
		var sayID = $el.attr('id');
		var action = SayAction(sayID, "resay");
		var count = action[0];
		var image = action[1];
		$(this).parent().find("span").html(count);			
		$(this).parent().find("img").attr('src', image);
	});
	
	$(document).on('click', '.boo', function(){
		var $el = $(this).parent().parent().parent().parent();
		var sayID = $el.attr('id');
		var action = SayAction(sayID, "boo");
		var count = action[0];
		var image = action[1];
		$(this).parent().find("span").html(count);			
		$(this).parent().find("img").attr('src', image);	
		});

	$(document).on('click', '.commentPen', function(){
		var $el = $(this).parent().parent().parent().parent();
		var sayID = $el.attr('id');
		fetchSayDetails(sayID).done(function() {
			fetchComments(sayID).done(function()
			{
				$('#sayDetailsModal').modal('show');
			});

		});
		
		console.log(sayID);
		});
	
	
	$("body").tooltip({
		selector: '[data-toggle="tooltip"]'
	});
});