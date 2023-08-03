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

function upload(m,y,pt,a,o,ct,c){
	let month = m;
	let year = y;

	let projectTime = pt;
	let all = a;
	let overdue = o;
	let clientTime = ct;
	let completed = c;

	let result = syncRequest('server.php', {"command":"UPLOAD", "month": month, "year": year, "projectTime":projectTime,
											"all":all, "overdue":overdue, "clientTime":clientTime, "completed":completed});

	let button = document.getElementById("upload");
	button.style.display = "none";
}

function nextmonth(month, year){
	let m=month;
	m++;
	let y=year;
	if(m==13){m=1; y++;}
	location.href = "https://b24.opti.ooo/rating/results.php?year="+y+"&month="+ m;
}
function backmonth(month, year){
	let m=month;
	m--;
	let y=year;
	if(m==0){m=12; y--;}
	location.href = "https://b24.opti.ooo/rating/results.php?year="+y+"&month="+ m;
}