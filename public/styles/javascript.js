var loadedobjects=""
var loadstatustext="<p><img src='img/loader.gif'/> Requesting content...</p>"
var rootdomain="http://"+window.location.hostname


function ajaxpage(url, containerid){
var page_request = false
if (window.XMLHttpRequest) // if Mozilla, Safari etc
page_request = new XMLHttpRequest()
else if (window.ActiveXObject){ // if IE
try {
page_request = new ActiveXObject("Msxml2.XMLHTTP")
} 
catch (e){
try{
page_request = new ActiveXObject("Microsoft.XMLHTTP")
}
catch (e){}
}
}
else
return false
document.getElementById(containerid).innerHTML=loadstatustext
page_request.onreadystatechange=function(){
loadpage(page_request, containerid)
}
page_request.open('GET', url, true)
page_request.send(null)
}

function ajaxcombo(selectobjID, loadarea){
var selectobj=document.getElementById? document.getElementById(selectobjID) : ""
if (selectobj!="" && selectobj.options[selectobj.selectedIndex].value!="")
ajaxpage(selectobj.options[selectobj.selectedIndex].value, loadarea)
}

function loadpage(page_request, containerid){
if (page_request.readyState == 4 && (page_request.status==200 || window.location.href.indexOf("http")==-1))
document.getElementById(containerid).innerHTML=page_request.responseText
}

// position of the tooltip relative to the mouse in pixel //
var offsetx = 8;
var offsety = 8;

function newelement(newid)
{ 
    if(document.createElement)
    { 
        var el = document.createElement('div'); 
        el.id = newid;     
        with(el.style)
        { 
            display = 'none';
            position = 'absolute';
        } 
        el.innerHTML = '&nbsp;'; 
        document.body.appendChild(el); 
    } 
} 
var ie5 = (document.getElementById && document.all); 
var ns6 = (document.getElementById && !document.all); 
var ua = navigator.userAgent.toLowerCase();
var isapple = (ua.indexOf('applewebkit') != -1 ? 1 : 0);
function getmouseposition(e)
{
    if(document.getElementById)
    {
        var iebody=(document.compatMode && 
        	document.compatMode != 'BackCompat') ? 
        		document.documentElement : document.body;
        pagex = (isapple == 1 ? 0:(ie5)?iebody.scrollLeft:window.pageXOffset);
        pagey = (isapple == 1 ? 0:(ie5)?iebody.scrollTop:window.pageYOffset);
        mousex = (ie5)?event.x:(ns6)?clientX = e.clientX:false;
        mousey = (ie5)?event.y:(ns6)?clientY = e.clientY:false;

        var lixlpixel_tooltip = document.getElementById('tooltip');
        lixlpixel_tooltip.style.left = (mousex+pagex+offsetx) + 'px';
        lixlpixel_tooltip.style.top = (mousey+pagey+offsety) + 'px';
    }
}
function tooltip(tip)
{
    if(!document.getElementById('tooltip')) newelement('tooltip');
    var lixlpixel_tooltip = document.getElementById('tooltip');
    lixlpixel_tooltip.innerHTML = tip;
    lixlpixel_tooltip.style.display = 'block';
    document.onmousemove = getmouseposition;
}
function exit()
{
    document.getElementById('tooltip').style.display = 'none';
}






//funcao para manipular por class//

function filter(filterName, actionName){
	var htmlElements = document.getElementsByTagName("a");
	var contador = 0;

	for(var i =0; i < htmlElements.length; i++){
 		if(htmlElements[i].className == filterName){
	 		htmlElements[i].style.visibility = actionName;	
 		}	
	}

}

function filterVisible(filterName){
	filter(filterName, 'visible');
}

function filterInvisible(filterName){
	filter(filterName, 'hidden');
}

function filter(filterName, actionName){
 var htmlElements = document.getElementsByTagName("tr");
 var contador = 0;

 for(var i =0; i < htmlElements.length; i++){
   if(htmlElements[i].className == filterName){
    htmlElements[i].style.display = actionName; 
   } 
 }

}

function filterVisible(filterName){
 filter(filterName, '');
}

function filterInvisible(filterName){
 filter(filterName, 'none');
}

function getposOffset(overlay, offsettype){
var totaloffset=(offsettype=="left")? overlay.offsetLeft : overlay.offsetTop;
var parentEl=overlay.offsetParent;
while (parentEl!=null){
totaloffset=(offsettype=="left")? totaloffset+parentEl.offsetLeft :
totaloffset+parentEl.offsetTop;
parentEl=parentEl.offsetParent;
}
return totaloffset;
}

function overlay(curobj, subobjstr, opt_position){
if (document.getElementById){
var subobj=document.getElementById(subobjstr)
subobj.style.display=(subobj.style.display!="block")? "block" : "none"
var xpos=getposOffset(curobj, "left")+((typeof opt_position!="undefined" &&
opt_position.indexOf("right")!=-1)? -(subobj.offsetWidth-curobj.offsetWidth) : 0) 
var ypos=getposOffset(curobj, "top")+((typeof opt_position!="undefined" &&
opt_position.indexOf("bottom")!=-1)? curobj.offsetHeight : 0)
subobj.style.left=xpos+"px"
subobj.style.top=ypos+"px"
return false
}
else
return true
}

function overlayclose(subobj){
document.getElementById(subobj).style.display="none"
}


