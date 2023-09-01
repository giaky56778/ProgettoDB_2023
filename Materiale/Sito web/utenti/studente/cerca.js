var page=-1;
function mostra_esame(bool){
    str="";
	if(bool){
		page++;
	}else{
		page--;
	}
	
	if (window.XMLHttpRequest)
		ajax = new XMLHttpRequest();  // browser IE7+, Firefox, Chrome, Opera, Safari
	else                        
		ajax = new ActiveXObject("Microsoft.XMLHTTP");// browser IE6, IE5
		
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4 && ajax.status == 200)
			document.getElementById("risposta").innerHTML = ajax.responseText;
	}
	ajax.open("GET", "ricerca.php?page=" + page, true);
	ajax.send();
	if (str.length == 0){
		document.getElementById("risposta").innerHTML = "";
		return;
	}
}

function mostra_carriera(tipo,p){
    str="";
	
	if (window.XMLHttpRequest)
		ajax = new XMLHttpRequest();  // browser IE7+, Firefox, Chrome, Opera, Safari
	else                        
		ajax = new ActiveXObject("Microsoft.XMLHTTP");// browser IE6, IE5
		
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4 && ajax.status == 200)
			document.getElementById("risposta").innerHTML = ajax.responseText;
	}
	ajax.open("GET", "ricerca.php?carriera="+tipo+"&page=" + p, true);
	ajax.send();
	if (str.length == 0){
		document.getElementById("risposta").innerHTML = "";
		return;
	}
}

function mostra_esami_iscrizioni(bool){
    str="";
	if(bool){
		page++;
	}else{
		page--;
	}
	
	if (window.XMLHttpRequest)
		ajax = new XMLHttpRequest();  // browser IE7+, Firefox, Chrome, Opera, Safari
	else                        
		ajax = new ActiveXObject("Microsoft.XMLHTTP");// browser IE6, IE5
		
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4 && ajax.status == 200)
			document.getElementById("risposta").innerHTML = ajax.responseText;
	}
	ajax.open("GET", "ricerca.php?iscrizione_esami=1&page=" + page, true);
	ajax.send();
	if (str.length == 0){
		document.getElementById("risposta").innerHTML = "";
		return;
	}
}

function esame(str,bool){
	if(bool){
		page++;
	}else{
		page--;
	}
	
	if (window.XMLHttpRequest)
		ajax = new XMLHttpRequest();  // browser IE7+, Firefox, Chrome, Opera, Safari
	else                        
		ajax = new ActiveXObject("Microsoft.XMLHTTP");// browser IE6, IE5
		
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4 && ajax.status == 200)
			document.getElementById("risposta").innerHTML = ajax.responseText;
	}
	ajax.open("GET", "ricerca.php?esame=" + str + "&page=" + page, true);
	ajax.send();
	if (str.length == 0){
		document.getElementById("risposta").innerHTML = "";
		return;
	}
}



function mostra_corsi(str,bool){
	if(bool){
		page++;
	}else{
		page--;
	}
	
	if (window.XMLHttpRequest)
		ajax = new XMLHttpRequest();  // browser IE7+, Firefox, Chrome, Opera, Safari
	else                        
		ajax = new ActiveXObject("Microsoft.XMLHTTP");// browser IE6, IE5
		
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4 && ajax.status == 200)
			document.getElementById("risposta").innerHTML = ajax.responseText;
	}
	ajax.open("GET", "ricerca.php?laurea_id=" + str + "&page=" + page, true);
	ajax.send();
	if (str.length == 0){
		document.getElementById("risposta").innerHTML = "";
		return;
	}
}

function mostra_insegnamento(str,anno,p){
	page=p;
	
	
	if (window.XMLHttpRequest)
		ajax = new XMLHttpRequest();  // browser IE7+, Firefox, Chrome, Opera, Safari
	else                        
		ajax = new ActiveXObject("Microsoft.XMLHTTP");// browser IE6, IE5
		
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4 && ajax.status == 200)
			document.getElementById("risposta").innerHTML = ajax.responseText;
	}
	ajax.open("GET", "ricerca.php?corso_laurea=" + str + "&anno="+anno+"&page=" + page, true);
	ajax.send();
	if (str.length == 0){
		document.getElementById(id).innerHTML = "";
		return;
	}
}

function mostra_lauree(bool){
	if(bool){
		page++;
	}else{
		page--;
	}
	str="";
	if (window.XMLHttpRequest)
		ajax = new XMLHttpRequest();  // browser IE7+, Firefox, Chrome, Opera, Safari
	else                        
		ajax = new ActiveXObject("Microsoft.XMLHTTP");// browser IE6, IE5
		
	ajax.onreadystatechange = function(){
		if (ajax.readyState == 4 && ajax.status == 200)
			document.getElementById("risposta").innerHTML = ajax.responseText;
	}
	ajax.open("GET", "ricerca.php?laurea=1&page=" + page, true);
	ajax.send();
	if (str.length == 0){
		document.getElementById("risposta").innerHTML = "";
		return;
	}
}