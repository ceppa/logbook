function MsgOkCancel(messaggio,pagina)
{
	var fRet;
	fRet=confirm(messaggio);
	if(fRet)
		window.location=pagina;
}

function FormOkCancel(messaggio,form)
{
	var fRet;
	fRet=confirm(messaggio);
	if(fRet)
		form.submit();
}

function redirect(pagina)
{
	window.location=pagina;
}

function trim(stringa)
{
    while (stringa.substring(0,1) == ' ')
        stringa = stringa.substring(1, stringa.length);
    while (stringa.substring(stringa.length-1, stringa.length) == ' ')
        stringa = stringa.substring(0,stringa.length-1);
    return stringa;
}

function formatFloat(number)
{
	var out="";
	var stringa=String(number);
	
	if(stringa.length==1)
		return "0,0"+stringa;
	else if(number.length==2)
		return "0,"+stringa;
	else
	{
		out=stringa.substring((stringa.length-5)>=0 ? stringa.length-5 : 0,stringa.length-2)+","+stringa.substring(stringa.length-2,stringa.length);

		for(var i=0;i<stringa.length -5;i+=3)
			out=stringa.substring(((stringa.length-8-i)>=0 ? stringa.length-8-i : 0),(stringa.length-5-i))+"."+out;
		return out;
	}
}

function onlyNumbers(e)
{
	var keynum;
	var keychar;
	var numcheck;

	if(window.event) // IE
	{
		keynum = e.keyCode;
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which;
	}
	if((keynum==8)
			||(keynum==9)
			||(keynum==13)
			||(keynum==116)
			||(keynum==37)
			||(keynum==39)
			||(keynum==46)
			||(keynum==190))
		return true;
	keychar = String.fromCharCode(keynum);
	numcheck = /\d/;
	return numcheck.test(keychar);
}

function onlyTime(e,sender)
{
	var keynum;
	var keychar;
	var numcheck;

	if(window.event) // IE
	{
		keynum = e.keyCode;
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which;
	}
	
	if((keynum==8)
			||(keynum==9)
			||(keynum==13)
			||(keynum==17)
			||(keynum==37)
			||(keynum==39)
			||(keynum==46)
  			||(keynum==116))
  		return true;
	if((keynum==59)||(keynum==190))
	{
		if(sender.value.length
				&&(sender.value.indexOf(":")==-1)
				&&(sender.value.indexOf(".")==-1))
			sender.value=sender.value+":";
		return false;
  	}
  	else
		keychar = String.fromCharCode(keynum);
/*	if(((sender.value.length>1)
			&&(sender.value.indexOf(":")==-1))
		||((sender.value.indexOf(":")!=-1)
			&&(sender.value.length-sender.value.indexOf(":")>2)))
		return false;*/
	numcheck = /\d/;
	return numcheck.test(keychar);
}

function onlyNumbersFloat(e,sender)
{
	var keynum;
	var keychar;
	var numcheck;

	if(window.event) // IE
	{
		keynum = e.keyCode;
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which;
	}
	if((keynum==8)
			||(keynum==9)
			||(keynum==13)
			||(keynum==17)
			||(keynum==37)
			||(keynum==39)
			||(keynum==46)
  			||(keynum==116))
  		return true;
	if((keynum==188)||(keynum==190))
	{
		if(sender.value.length
				&&(sender.value.indexOf(",")==-1)
				&&(sender.value.indexOf(".")==-1))
			sender.value=sender.value+",";
		return false;
  	}
  	else
		keychar = String.fromCharCode(keynum);
	numcheck = /\d/;
	return numcheck.test(keychar);
}



function showMessage(testo)
{
	var element=document.getElementById("message");
	element.innerHTML=testo;
	element.style.display="";
	var tim = setTimeout('document.getElementById("message").style.display="none"', 3000);
}

function setSelection(txt, idx, length)
{
	if (txt.createTextRange)
	{
		var range = txt.createTextRange();

		range.collapse(true);
		range.moveStart('character', idx);
		range.moveEnd('character', idx + length);
		range.select();
	}
	else if (txt.selectionEnd)
	{
		txt.selectionStart = idx;
		txt.selectionEnd = idx + length;
	}
}

function check_all(sender,name)
{
	var obj = document.getElementsByTagName('input');
	if (obj) 
	{
		for (var i = 0; i < obj.length; i++) 
		{
			nome=obj[i].getAttribute("name");
			if(nome && nome.indexOf(name)!=-1)
				obj[i].checked=sender.checked;
		}
	}
}

function deleterow(id)
{
	var pars = 'id=' + id;
	var myAjax = new Ajax.Request(
			"./include/deleterow.php", 
			{
				method: 'post', 
				parameters: pars, 
				onComplete: function(response,callback)
				{
					if(response.responseText.length)
						alert(response.responseText);
					else
						location.reload();
				}
			});
}
