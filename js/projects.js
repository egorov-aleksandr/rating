function showTasks(img,task){
    let tasks=document.getElementById(task);
    tasks.classList.toggle("hide");

    let btns=document.getElementsByClassName(img);
    btns[0].classList.toggle("hide");
    btns[1].classList.toggle("hide");
}
function filter(id,name){
	let filter="filter-"+id;
	let projects=document.getElementsByClassName("project-row");
	for(let i=0;i<projects.length;i++){
		projects[i].classList.remove("hide");
	}
	for(let i=0;i<projects.length;i++){
		if(projects[i].classList.contains(filter)){
			continue;
		}else{
			projects[i].classList.add("hide");
		}
	}
	let con=document.getElementById("filter");
	con.classList.remove("hide");
	let text=document.getElementById("filter-text");
	text.innerHTML=name;
}
function removeFilter(){
	let projects=document.getElementsByClassName("project-row");
	for(let i=0;i<projects.length;i++){
		projects[i].classList.remove("hide");
	}
	let con=document.getElementById("filter");
	con.classList.add("hide");
	let text=document.getElementById("filter-text");
	text.innerHTML="";
}