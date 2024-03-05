// JavaScript Document
var labelShow = '<img src="System/VisualAssets/Images/Icons/Plus.gif"/>';
var labelHide = '<img src="System/VisualAssets/Images/Icons/Minus.gif"/>';

	function OnlyOpenCloseExternalFile(idTrObject,idLink,swapLinkLabel,url){
		var trObject = document.getElementById(idTrObject);
		if(trObject.style.display == 'none'){
			trObject.style.display = '';
			if(swapLinkLabel == true){
				trocaTexto(idLink,labelHide)
			}	
			//Spry.Utils.updateContent(idTrObject, urlTarget);
			ajaxpage(url, idTrObject); 
		}else{
			trObject.style.display = 'none';
			if(swapLinkLabel == true){
				trocaTexto(idLink,labelShow)
			}
			//alert(document.getElementById(idTrObject).innerHTML)
			document.getElementById(idTrObject).innerHTML = '';
			//alert(document.getElementById(idTrObject).innerHTML)
		}
		
	}
	
	function trocaTexto(elementID,label){
		var nodes = document.getElementById(elementID);
		nodes.innerHTML = label;
}

//onclick="Spry.Utils.updateContent('xa1333', 
//            'System/Data/XhtmlData/TableTest1THHoriz.html'); return false;" 


	function OnlyOpenClose(idTrObject,idLink,swapLinkLabel){
		var trObject = document.getElementById(idTrObject);
		if(trObject.style.display == 'none'){
			trObject.style.display = '';
			if(swapLinkLabel == true){
				trocaTexto(idLink,labelHide)
			}	 
		}else{
			trObject.style.display = 'none';
			if(swapLinkLabel == true){
				trocaTexto(idLink,labelShow)
			}

		}
		
	}