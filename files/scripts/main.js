if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/gm, '');
  };
}

(function($){
    $.fn.disableSelection = function() {
        return this
                 .attr('unselectable', 'on')
                 .css('user-select', 'none')
                 .on('selectstart', false);
    };   
})(jQuery);

$(function(){

	/*if(document.referrer.indexOf("chrome.google.com") >= 0){
		$("#dvChromeExt").show();
	}
	$("#dvChromeExt button.close").click(function(){$("#dvChromeExt").hide();});*/
	
	$('#dvSlides').disableSelection();
	$('form.contact').submit(function(e){e.preventDefault();submitContact.apply(this);});

	if($(document.body).hasClass("company-contact") && location.pathname.split("/")[2] == "quote"){
		$('textarea[name=comments]')[0].focus();
		$('textarea[name=comments]').val("I am interested in a quote... ");
	}

	$('#dvSlides .arrow').click(function(){

		if($(this).hasClass("left"))
			doSlide(-1);
		else
			doSlide(1);

	});

	var slideInterval = null;
	var slideDirection = 1;

	var doSlide = function(direction){
		window.clearInterval(slideInterval);
		if(direction != 0){
			slideDirection = direction;
			var slideWidth = 1002;
			var newLeft;
			if(direction < 0)
				newLeft = 0;
			else
				newLeft = slideWidth * -2;

			$('#dvSlides div.container').animate({"margin-left": newLeft + "px"}, function(){
				$('#dvSlides div.container').css("margin-left", (slideWidth * -1) + "px");
				if(newLeft == 0){
					$('#dvSlides .slide:last-child').insertBefore($('#dvSlides .slide:first-child'));
				}else{
					$('#dvSlides .slide:first-child').insertAfter($('#dvSlides .slide:last-child'));
				}
			});
		}

		slideInterval = window.setInterval(function(){
			doSlide(slideDirection);
		}, 5000);

	};
	doSlide(0);


	$('#dvHamburgerBar button.hamburger').click(function(){
		if($('body').hasClass("menuopen")){
			$('body').addClass("menuclosed");
			$('body').removeClass("menuopen");
		}else{
			$('body').removeClass("menuclosed");
			$('body').addClass("menuopen");
		}

	});

});

var submitContact = function(){
	$form = $(this);

	$('#dvMessage').hide().empty().removeClass("red green");
	var $missingFields = $([]), $messages = $([]);

	$form.find(".missing").removeClass("missing");

	if(
		$form.find("input.required,select.required,textarea.required").filter(function(){
			if($(this).val().trim() == ""){
				$missingFields.push(this);
				$(this).addClass("missing");
				return true;
			}
			return false;
		}).length > 0
	)
		$messages.push("Please fill out all required fields.");

	if($form.find("input[name=phone]").val().trim() + $form.find("input[name=email]").val().trim() == "")
		$messages.push("Please provide either your email address or telephone number.");

	if($messages.length > 0){
		$('#dvMessage').append($("<b />").text("Your message was not sent. Please correct the following issues:")).append($("<br/>"));
		$messages.each(function(i){
			$('#dvMessage').append("- " + this).append(i == $messages.length - 1 ? null : $("<br />"));
		});
		$('#dvMessage').addClass("red").show();
		return;
	}

	$form.find('button').prop("disabled", true);

	$.post(
		"/" + location.pathname.split("/")[1] + "/sendmail",
		$form.serialize()
	).done(function(xml){
		var $xml = $(xml);
		var $result = $xml.find("> sendEmail");

		if($result.attr("fail") == "0"){
			$form.hide();
			$('#dvMessage').addClass("green").append($("<b />").text("Your message has been received. A representative will contact you shortly.")).show();
		}else{
			$('#dvMessage').addClass("red").append($("<b />").text("An error has occurred. Please try again later.")).show();
			$form.find('button').prop("disabled", false);
		}
	}).fail(function(){
		$('#dvMessage').addClass("red").append($("<b />").text("An error has occurred. Please try again later.")).show();
		$form.find('button').prop("disabled", false);
	});
}

var setCookie = function(name, value, days){
	if(expires){
		var date = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		var expires = "; expires=" + date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
};

var getCookie = function(name){
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
};

var deleteCookie = function(name) {
    createCookie(name,"",-1);
};
