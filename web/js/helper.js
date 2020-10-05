
var showElementStatus;
function showElement(type){
    param=document.getElementById(type);
    if(param.style.display == "none") {
        if(showElementStatus) showElementStatus.style.display = "none";
        param.style.display = "block";
        showElementStatus = param;
    }else param.style.display = "none"
}