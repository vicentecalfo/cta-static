/*  
* Fun��o: showDiv
* Objetivo: Exibe uma div escondida
*/ 
function showDiv(idName){
	htmlObject = document.getElementById(idName);

	// Testa se a tabela est� escondida
	if(htmlObject.style.display == 'none'){
		// Exibe div
		htmlObject.style.display = '';
	}
}

/*  
* Fun��o: hideDiv
* Objetivo: Esconde uma div
*/ 
function hideDiv(idName){
	htmlObject = document.getElementById(idName);
	
	// Testa se a div est� escondida
	if(htmlObject.style.display == ''){
		// Esconde div
		htmlObject.style.display = 'none';
	}
}



/*  
* Fun��o: multipleDivAction
* Objetivo: Exibe ou esconde v�rios divs
*/ 
function multipleDivAction(divIds,actionName){
	var arrayofDivs = divIds.split(",");

	for(i = 0; i < arrayofDivs.length; i++){
		if(actionName == 'show'){
			showDiv(arrayofDivs[i]);	
		}else if(actionName == 'hide'){
			hideDiv(arrayofDivs[i]);
		}
	}	
}

/*  
* Fun��o: multipleDivShow
* Objetivo: Exibe v�rios divs
*/
function multipleDivShow(divIds){
	var actionName = 'show'; 
	multipleDivAction(divIds,actionName);
}

/*  
* Fun��o: multipleDivHide
* Objetivo: Esconde v�rios divs
*/
function multipleDivHide(divIds){
	var actionName = 'hide'; 
	multipleDivAction(divIds,actionName);
}

/*  
* Fun��o: showHideElement
* Objetivo: Exibe ou um esconde um elementos. Se o elemento estiver vis�vel
*/
function showHideElement(elementID){
	
	if( getDisplayStatus(elementID)== 'none'){
		showDiv(elementID);
	}else{
		hideDiv(elementID);
	}
}

/*  
* Fun��o: getDisplayStatus
* Objetivo: Retorna o status do elemento
*/
function getDisplayStatus(elementID){
	return document.getElementById(elementID).style.display;
}

//codigo de aumentar fotos

var min=11;
var max=14;
function increaseFontSize() {
   var p = document.getElementsByTagName('body');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 12;
      }
      if(s!=max) {
         s += 1;
      }
      p[i].style.fontSize = s+"px"
   }
}
function decreaseFontSize() {
   var p = document.getElementsByTagName('body');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 12;
      }
      if(s!=min) {
         s -= 1;
      }
      p[i].style.fontSize = s+"px"
   }   
}
function defaultFontSize() {
   var p = document.getElementsByTagName('body');
   for(i=0;i<p.length;i++) {
      p[i].style.fontSize = min+"px"
   }   
}