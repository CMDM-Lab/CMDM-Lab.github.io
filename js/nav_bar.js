function goURL(){
	var url = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname;
	var pos = url.indexOf(".html");
	var flag = url.indexOf("_zh");
	var newURL = url;
	if(pos > -1){
		if(flag > -1)
			newURL = url.substr(0, flag)+url.substr(pos);		
		else
			newURL = url.substr(0, pos)+"_zh"+url.substr(pos);	
	}
	window.location.href = newURL;
	
}
