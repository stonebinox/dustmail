function messageBox(title,content,sizeFlag){
    title=$.trim(title);
	var modal=document.getElementById("myModal");
	$(modal).html('');
    if(sizeFlag==0){
        sizeFlag='modal-sm';
    }
    else{
        sizeFlag='modal-lg';
	}
	var dialog=document.createElement("div");
	$(dialog).addClass("modal-dialog "+sizeFlag);
		var modalContent=document.createElement("div");
		$(modalContent).addClass("modal-content");
			var modalHeader=document.createElement("div");
			$(modalHeader).addClass("modal-header");
				var close=document.createElement("a");
				//$(close).attr("type","button");
				$(close).attr("href","#");
				$(close).attr("data-dismiss","modal");
				$(close).html('&times;');
				$(close).addClass("close");
				$(modalHeader).append(close);
				var h4=document.createElement("h4");
				$(h4).addClass("modal-title");
				$(h4).html(title);
				$(modalHeader).append(h4);
			var modalBody=document.createElement("div");
			$(modalBody).addClass("modal-body");
				var p=document.createElement("p");
				$(p).append(content);
				$(modalBody).append(p);
			var modalFooter=document.createElement("div");
			$(modalFooter).addClass("modal-footer");
				var closebut=document.createElement("button");
				$(closebut).attr("type","button");
				$(closebut).attr("data-dismiss","modal");
				$(closebut).addClass("btn btn-default");
				$(closebut).html("Close");
				$(modalFooter).append(closebut);
			$(modalContent).append(modalHeader);
			$(modalContent).append(modalBody);
			$(modalContent).append(modalFooter);
		$(dialog).append(modalContent);
	$(modal).append(dialog);
	$("#myModal").modal("show");
}
function nl2br (str) {
    var breakTag = '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function dateFormat(date){ //to format dates from database
	var sp=date.split("-");
	var yr=sp[0];
	var month=sp[1];
	var day=sp[2];
	var monthArray=new Array("January","February","March","April","May","June","July","August","September","October","November","December");
	month=monthArray[parseInt(month)-1];
	date=day+" "+month+", "+yr;
	return date;
}
function stripslashes(str){ 
	return (str + '').replace(/\\(.?)/g, function (s, n1) {
	  switch (n1) {
	  case '\\':
		return '\\';
	  case '0':
		return '\u0000';
	  case '':
		return '';
	  default:
		return n1;
	  }
	});
}
function validate(str){
	if((str!="")&&(str!=null)&&(str!=undefined)){
		return true;
	}
	else
	{
		return false;
	}
}
function mover(layer){
	layer="#"+layer;
	$(".form").removeAttr("active");
	$(layer).attr("active","true");
}
function readParams(){
	var err=getUrlParameter('err');
	if(validate(err)){
		switch(err){
			case "INVALID_PARAMETERS":
			default:
			err='Something went wrong while processing your request.';
			break;
			case "INVALID_USER_CREDENTIALS":
			err='Invalid credentials. Please verify the details and try again.';
			break;
			case "INVALID_USER_NAME":
			err='Invalid user name. Please enter your full name and try again.';
			break;
			case "INVALID_USER_EMAIL":
			err='Invalid email ID. Please enter a valid email ID and try again.';
			break;
			case "INVALID_PASSWORD":
			err='Invalid password. Please ensure the password is at least 8 characters in length.';
			break;
			case "PASSWORD_MISMATCH":
			err='Password mismatch. Please ensure the passwords match each other.';
			break;
		}
		$("#message").html('<div class="alert alert-danger"><strong>Error</strong> '+err+'</div>');
	}
	var suc=getUrlParameter("suc");
	if(validate(suc)){
		switch(suc){
			case "ACCOUNT_CREATED":
			suc='Account created successfully. You may login to your account now.';
			break;
			case "ACCOUNT_VERIFIED":
			suc='Your account was verified successfully. Login to continue.';
			mover('login');
			break;
		}
		$("#message").html('<div class="alert alert-success"><strong>Success</strong> '+suc+'</div>');
	}
}