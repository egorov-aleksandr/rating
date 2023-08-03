function nextmonth(month, year){
	let m=month;
	m++;
	let y=year;
	if(m==13){m=1; y++;}
	location.href = "https://b24.opti.ooo/rating/index.php?year="+y+"&month="+ m;
}
function backmonth(month, year){
	let m=month;
	m--;
	let y=year;
	if(m==0){m=12; y--;}
	location.href = "https://b24.opti.ooo/rating/index.php?year="+y+"&month="+ m;
}

function rate(parent, rate,n){
	let page=document.getElementById("page"+n);
	let count=0;

	let rates = page.getElementsByClassName("rate-selected");
	if(rate.dataset.value < 3 || rate.dataset.value > 7){
		for(let i=0;i<rates.length;i++){
			if(rates[i].dataset.value == rate.dataset.value){count++;}
			if(count > 1){alert("Нельзя ставить больше двух одинаковых оценок, если оценка не в промежутке от 4 до 6"); return;}
		}
	}
    rates = parent.getElementsByClassName("rate");
    for(let i=0;i<rates.length;i++){
        rates[i].classList.remove("rate-selected");
        rates[i].style.background="white";
    }
    rate.classList.toggle("rate-selected");

    for(let i=0;i<rates.length;i++){
        if(rates[i].className.includes("rate-selected")){
            if(i<2){
                rates[i].style.background="#D74747";
            }else if(i<6){
                rates[i].style.background="#EAB223";
            }else if(i<8){
                rates[i].style.background="#528FA3";
            }else{
                rates[i].style.background="#2A824D";
            }
        }
    }
}
let  page = 1;
function nextpage(){
    let tags = document.getElementsByClassName("form-nav-elem");
    for(let i=0; i<tags.length;i++){
        tags[i].classList.remove("selected");
    }
    let pages = document.getElementsByClassName("page");
    for(let i=0; i<pages.length;i++){
        pages[i].classList.add("hide");
    }
    if(page==3){page=0;}
    page = page+1;

    document.getElementById("page"+page).classList.toggle("hide");
    document.getElementById("tag"+page).classList.toggle("selected");
    if(page==3){
        let buttonUpload=document.getElementById("upload-button");
        buttonUpload.classList.remove("hide");
        let nextButton=document.getElementById("next-button");
        nextButton.classList.add("hide");
    }else{
        let buttonUpload=document.getElementById("upload-button");
        buttonUpload.classList.add("hide");
        let nextButton=document.getElementById("next-button");
        nextButton.classList.remove("hide");
    }
}

function changepage(id){
    let number=parseInt(id.replace("tag",""));
    page=number-1;
    nextpage();
}

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
	//alert(xmlhttp.responseText); //здесь возвращаем значения без обработки
	return JSON.parse(xmlhttp.responseText); //здесь возвращаем значения в виде объекта
}

function send(){
	let author = document.getElementById('author');
	author = author.value;
	console.log("ID автора: " + author);

	let date = document.getElementById('year').value.split(".");
	let month = date[0];
	let year = date[1];

	console.log("Дата 1: " + year);
	console.log("Дата 2: " + month);

	let page = document.getElementById('page1');
	let selects = page.getElementsByClassName('rate-selected');
	var arrS = [];

	for(let i=0; i< selects.length; i++){
		arrS[i] = {
		id: selects[i].dataset.id,
		value: selects[i].dataset.value
		}
	}
	console.log(arrS);

	page = document.getElementById('page2');
	selects = page.getElementsByClassName('rate-selected');
	var arrO = [];

	for(let i=0; i< selects.length; i++){
		arrO[i] = {
		id: selects[i].dataset.id,
		value: selects[i].dataset.value
		}
	}
	console.log(arrO);

	page = document.getElementById('page3');
	selects = page.getElementsByClassName('rate-selected');
	var arrP = [];

	for(let i=0; i< selects.length; i++){
		arrP[i] = {
		id: selects[i].dataset.id,
		value: selects[i].dataset.value
		}
	}
	console.log(arrP);
	let result = syncRequest('send_rate.php', {"author": author, "month": month, "year": year, "S":arrS,"O":arrO, "P":arrP});
	console.log(result);
	location.reload();
}