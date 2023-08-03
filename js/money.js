function syncRequest(url, data) {
	let xmlhttp;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
	try {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (E) {
		xmlhttp = false;
	}
	}

	if (!xmlhttp && typeof XMLHttpRequest !== 'undefined') {
		xmlhttp = new XMLHttpRequest();
	}

	xmlhttp.open( "POST", url, false  );//переключатель синхронный/асинхронный
	xmlhttp.setRequestHeader("Content-type", "application/json");
	xmlhttp.setRequestHeader("Accept", "application/json");
	let test = false;
	try {
		xmlhttp.send(JSON.stringify(data));
	} catch (e) {
		test = true;
	}
	if(test) return {'status': 'no'};
	alert(xmlhttp.responseText); //здесь возвращаем значения без обработки
	return JSON.parse(xmlhttp.responseText); //здесь возвращаем значения в виде объекта
}


function nextmonth(month, year){
	let m=month;
	m++;
	let y=year;
	if(m==13){m=1; y++;}
	location.href = "https://b24.opti.ooo/rating/money.php?year="+y+"&month="+ m;
}
function backmonth(month, year){
	let m=month;
	m--;
	let y=year;
	if(m==0){m=12; y--;}
	location.href = "https://b24.opti.ooo/rating/money.php?year="+y+"&month="+ m;
}

function changed(id){
	let arr = id.split('-');
	let emp = arr[1];

	let okladof = parseFloat(document.getElementById("okladof-"+emp).value);
	let tax = parseFloat(document.getElementById("tax-"+emp).value);
	let avans = parseFloat(document.getElementById("avans-"+emp).value);
	let sum = parseFloat(document.getElementById("sum-"+emp).value);
	
	let payoffvalue = okladof-tax-avans;
	let awardvalue = sum-okladof;

	let payoff = document.getElementById("payoff-"+emp);
	payoff.value = payoffvalue.toFixed(2);

	let award = document.getElementById("award-"+emp);
	award.value = awardvalue.toFixed(2);

}

function save(id,m,y){
	let month = m;
	let year = y;

	console.log("Дата 1: " + year);
	console.log("Дата 2: " + month);

	let okladof = document.getElementById("okladof-"+id).value;
	let tax = document.getElementById("tax-"+id).value;
	let avans = document.getElementById("avans-"+id).value;
	let award = document.getElementById("award-"+id).value;
	let payoff = document.getElementById("payoff-"+id).value;
	let hos = document.getElementById("hos-"+id).value;
	let hol = document.getElementById("hol-"+id).value;
	let result = syncRequest('server.php', {"command":"SAVE", "employee": id, "month": month, "year": year, "oklad":okladof,
											"tax":tax, "avans":avans, "award":award, "payoff":payoff, "hospital":hos,"holiday":hol});
	console.log(result);
	let button = document.getElementById(id);
	button.innerHTML = 'Изменить';
	button.style.background = "#2A824D";
}

function addtasks(month,year){

	console.log("Дата 1: " + year);
	console.log("Дата 2: " + month);

	let result = syncRequest('server.php', {"command":"ADDTASKS", "month": month, "year": year});

	console.log(result);
}

function auto(id){
	let okladcomp = document.getElementById("okladcomphide-"+id).value;
	let okladof = document.getElementById("okladofhide-"+id).value;
	let tax = document.getElementById("taxhide-"+id).value;
	let avans = document.getElementById("avanshide-"+id).value;
	let award = document.getElementById("awardhide-"+id).value;
	let payoff = document.getElementById("payoffhide-"+id).value;
	let sum = document.getElementById("sumhide-"+id).value;
	let hos = document.getElementById("hoshide-"+id).value;
	let hol = document.getElementById("holhide-"+id).value;

	document.getElementById("okladcomp-"+id).value = okladcomp;
	document.getElementById("okladof-"+id).value = okladof;
	document.getElementById("tax-"+id).value = tax;
	document.getElementById("avans-"+id).value = avans;
	document.getElementById("award-"+id).value = award;
	document.getElementById("payoff-"+id).value = payoff;
	document.getElementById("sum-"+id).value = sum;
	document.getElementById("hos-"+id).value = hos;
	document.getElementById("hol-"+id).value = hol;

}
