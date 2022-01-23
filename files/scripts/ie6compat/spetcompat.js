fixClears();
emptyElems();

function fixClears(){
	var ua = window.navigator.userAgent;
    var msie = ua.indexOf ( "MSIE " );
	if(ua.substring (msie+5, ua.indexOf (".", msie ))==6)
		return;
	var divs = document.getElementsByTagName("div");
	
	for(var i in divs)
		if(divs[i].className == "clear")
			divs[i].outerHTML="";
	
}

function emptyElems(){
	var elems = document.all;
	for(var i=0;i<elems.length;i++){
		if(elems[i].innerHTML.trim()=="&nbsp;" || elems[i].innerHTML.trim()=="&#160;")
			elems[i].innerHTML="";
	}
}