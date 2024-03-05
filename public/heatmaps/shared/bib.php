<?
# numero de genes ate' mostrar os tecidos de novo
  $block=250;

function vertical($string){//esta função gera as figuras com o nome dos genes que aparecem no topo da tabela de heatmaps na aba de mRna expression
 $vert="";
 $figaux="../../imgLetras/$string.gif";
 if(!file_exists($figaux)){
   //echo "criei o arquivo $figaux <br/>";
   $imagem=ImageCreate(15,80);
   $branco=ImageColorAllocate($imagem,255,255,255);
   $preto=ImageColorAllocate($imagem,102,102,102);
   ImageStringUp($imagem,3,1,78,$string,$preto);
   ImageGif($imagem,$figaux);
   ImageDestroy($imagem);
 }
 $vert="<img src=\"$figaux\" alt=\"\">";
 
 return $vert;
}


if($config == "est"){
    # normalizacao
    $norm_factor=1000000;
    $norm_factor_str="1 000 000";

    # config da legenda e cores
    $parcel=10;
    $step=10;
    $max=90;
    $title="Number of ESTs/".$norm_factor;

    # selecao de tecidos a serem mostrados
    $nlibs_min=9;
    $libsize_min=60000;

    # caso se queira cortar acima de um certo valor de expressao
    $filter=10000000;

    
}
if($config == "sage"){
    $norm_factor=200000;
    $norm_factor_str="200 000" ;
    $parcel=1;
    $step=1;
    $max=9;
    $title="Number of tags/".$norm_factor;
    $nlibs_min=2;
    $libsize_min=100000;
    $filter=10000000;
    
}
if($config == "mpss"){
    $norm_factor=1000000;
    $norm_factor_str="1 000 000";
    $parcel=5;
    $step=5;
    $max=45;
    $title="Number of tags/".$norm_factor;
    $nlibs_min=1; 
    $libsize_min=100000;
    $filter=10000000;
   
}


#
# LAYOUT
#
function cell($value1, $parcel, $step, $highlight, $text, $gene, $type){
    if($value1=="")
       $class="tdtissue";
    else{
       $class="names";
       $SQL="SELECT idGene FROM Gene WHERE gene='$value1'";
       $query=mysql_query($SQL);
       $vet=mysql_fetch_row($query);
       if($vet[0]!="")
         $value=vertical($value1);
      }
    if($type=="est"){$type="ESTs";}
    if($type=="mpss" || $type == "sage"){$type="tags";}
    
    if($parcel){
#	if($value1 < 20){$value1=0;}
	$bgcolor=color_pervalue($value1, $parcel, $step);
	return "<td  width=\"15px\" bgcolor=\"$bgcolor\" title=\"".round($value1,1)." $type in $text\">&nbsp;</td>\n";
    }
    else if($highlight!=0){
	if($type == "ESTs"){
           return "<td class=\"$class\"><a href=\"#\" title=\"total ESTs: $text\">$value</a></td>\n";
	}
	else{
              return "<td >$value</td><!--td class=\"$class\">$value</td-->\n";
          
	}
    }
    else if($gene!=0){
	if($type == "ESTs"){
          if ($text >= 10){
           return "<td class=\"$class\"><a href=\"#\" title=\"total ESTs: $text\">$value</a></td>\n";
          } else {
           return "<td class=\"$class\"><img src=\"img/csize.png\" width=\"11\" height=\"11\"  alt=\"\"><a href=\"#\" title=\"total ESTs: $text\">$value</a></td>\n"; 
          }
          
           
	   
	}
        else if($type == "status"){
	  if($value1 == 2 || $value1 == 3 || $value1 == 4 || $value1 == 5){$value1=1;}
          #return "<td class=\"names\">$value1</td>\n";
          if ($value1 == 1){
	   $status_img = "<img src=\"heatmaps/img/1.png\" alt=\"\">\n";
	  } else if ($value1 == 6){
	   $status_img = "<img src=\"heatmaps/img/6.png\" alt=\"\">\n";
	  } else if ($value1 == 7){
	   $status_img = "<img src=\"heatmaps/img/7.png\" alt=\"\">\n";
	  }
	   
	  return "<td class=\"names\" align=\"center\">$status_img</td>\n";
	} else {
          return "<td class=\"$class\">$value</td>\n";
	}
    }      
    else{
         //primeira coluna da tabela na linha de valores
	return "<td width=\"250px\">$value1</td>\n";
    }
}
function imprime_tabela($tissue_list, $gene_first, $genes, $tissues, $libs_tissue, $echo_first, $echo, $show_gene, $col, $width, $type, $reason, $gene, $tags2show, $lib_ids,$len,$status_row,$idGene,$gene_count){
    # nao achou o gene, mostra AVISO explicando porque
    if(!$gene_first && !$libs_tissue){
	$comment=$comment2="";
	if($reason){$comment = "reliable"; $comment2="The expression level cannot be correctly inferred.";}
	if($type == "est"){$comment="EST";}

	## WARNING
	echo "<table bgcolor='yellow'><tr><td>WARNING: There is no $comment data for $gene. $comment2<br />\n";
	if($reason){
	    echo "Reason:<br />$reason<br />\n";
	}
	echo "</td></tr></table>\n";
	##

	echo "<br />Displaying data for all other CT genes.\n";
	echo "<br />\n";
    }

    # se achou o gene e esta' mostrando o painel geral por tecido, mostra a tag
    if(!$libs_tissue && $type != "est" && $gene_first){
	######
	$tag=strtoupper($type);
	echo "<b>$gene's $tag tag(s):</b><br />\n";
	echo "$tags2show<br />\n";
	######
    }

    # o gene de interesse sempre e' movido para a primeira coluna
    # o primeiro grupo de colunas pode ter 30 (se o gene de interesse esta' no grupo) ou 29 colunas (se o gene esta' no 2 grupo em diante)
    # para evitar problemas com o deslocamento de colunas, a solucao e' colocar as duas primeiras colunas sempre manualmente aqui 
    //$width+=20+200;
     //echo "<table id=\"est\" class=\"heatmap\">\n";
    //echo "<col>";
   // echo "<col>";

    # imprime as outras colunas
   // echo "$col";
    $tam=$gene_count*15+250;
    if(!$libs_tissue){
        echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" id=\"alex\" width=\"$tam px\"><!--table id=\"est\" class=\"heatmap\"-->\n";
	echo "<tr>$tissue_list$gene_first$genes</tr>\n";
        echo "<tr><td class=\"tdtissue\" >$status_row</td></tr>\n";
    }
    else{
     //echo "<tbody>";
     echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"$tam px\">\n";
    }
    $tam=substr_count($genes,'</td>')+2;
    for( $i=0; $i<sizeof($tissues); $i++ ){
	# se esta' mostrando o heatmap por biblioteca SAGE, muda a cor da lista de tecidos para indicar que e' biblioteca e nao tecido
	# so' SAGE
	if($libs_tissue){
	    $tissue=strip_name($tissues[$i]);
            if(strlen($tissue)>34){
               $aux=substr($tissue,0,34);
               $tissue=$aux."<br/>".substr($tissue,35,strlen($tissue));
            }
            $id=$lib_ids["$tissues[$i]"];
            echo "<tr><td width=250px><a href=\"#\" title=\"see library details\" onclick=\"show_libs_details('$type','$id','$len'); return false;\">$tissue</a></td>".$echo_first[$i].$echo[$i]."</tr>\n";
            
	}
	# se for MPSS, nao mostra o link para ver as bibliotecas
	# so' MPSS
	else if($type == "mpss"){
	    echo '<tr><td>'.$tissues[$i].'</td>'.$echo_first[$i].$echo[$i].'</tr>'."\n";
	}
	# se esta'mostrando o painel geral por TECIDO, preparar o link para abrir por BIBLIOTECA
	# SAGE, MPSS e EST
	else{
	    $_tissue=$tissue=$tissues[$i];
	    if($type == "est"){
		if($tissue == "glioma (brain)"){ $_tissue="brain";}
		if($tissue == "melanoma (skin)"){ $_tissue="skin";}
	    }
	    $idnovo=$i."tr";           
	    #echo "<tr><td class=\"tdtissue\"><a href=\"#\" title=\"see breakdown per library\" onclick=\"show_libs('$show_gene','$_tissue','$type','$len','cancer')\">$tissue</a></td>".$echo_first[$i].$echo[$i]."</tr>\n";
           //echo "<tr><td class=\"tdtissue\"><a href=\"#\" title=\"see on link per library\" onclick=\"show_libs('$show_gene','$_tissue','$type','$len','cancer')\">$tissue</a></td>".$echo_first[$i].$echo[$i]."</tr>\n";
	   if($type == "sage" || $type == "mpss" ){
              $idnovo.=$type.$len;
	      echo "<tr><td width=250px><a href=\"javascript:OnlyOpenCloseExternalFile('$idnovo','$tissue',false,'heatmaps/heatmap_tag.php?state=cancer&amp;type=$type&amp;len=$len&amp;gene=$show_gene&amp;libs_tissue=$_tissue&amp;id=$idGene')\"
id=\"$tissue\">$tissue</a></td>".$echo_first[$i].$echo[$i]."</tr>\n";
              echo"<tr><td colspan=\"$tam\" align=\"center\" style=\"display:none;\" id=\"$idnovo\" class=\"RowTableMoreContent\">&nbsp;</td></tr>";
              
	   }
	   else{
              echo "<tr><td class=\"tdtissue\"><a href=\"#\" title=\"see on link per library\" onclick=\"show_libs('$show_gene','$_tissue','$type','$len','cancer'); return false;\">$tissue</a></td>".$echo_first[$i].$echo[$i]."</tr>\n";
              /*
	      echo "<tr><td class=\"tdtissue\"><a href=\"javascript:OnlyOpenCloseExternalFile('$idnovo','$tissue',false,'heatmaps/show_libs_est.php?state=cancer&amp;gene=$show_gene&amp;libs_tissue=$_tissue')\"
id=\"$tissue\">$tissue</a></td>".$echo_first[$i].$echo[$i]."</tr>\n";
              */
	   }
            
	    
	    
	}
#     $tam=sizeof($tissues);
#     echo"<tr><td colspan=\"$tam\" align=\"center\" style=\"display:none;\" id=\"$idnovo\"
#class=\"RowTableMoreContent\">&nbsp;</td></tr>";
    }
    if(!$libs_tissue){
       echo "</table>\n";
    }
    else{echo "</tbody>";}
}

function strip_name($tissue){
    $tissue=str_replace("LSAGE_Brain_", "", $tissue);
    $tissue=str_replace("LSAGE_Breast_", "", $tissue);
    $tissue=str_replace("LSAGE_Colon_", "", $tissue);
    $tissue=str_replace("SAGE_Brain_", "", $tissue);
    $tissue=str_replace("SAGE_Breast_", "", $tissue);
    $tissue=str_replace("SAGE_Cartilage_", "", $tissue);
    $tissue=str_replace("SAGE_Liver_", "", $tissue);
    $tissue=str_replace("SAGE_Lung_", "", $tissue);
    $tissue=str_replace("SAGE_Ovary_", "", $tissue);
    $tissue=str_replace("SAGE_Stomach_", "", $tissue);
    $tissue=str_replace("SAGE_Prostate_", "", $tissue);
    $tissue=str_replace("SAGE_Pancreas_", "", $tissue);
    $tissue=str_replace("SAGE_Colon_", "", $tissue);
    return $tissue;
}

function color_summary($value, $step, $max, $title, $norm_factor_str){
    echo "<table class=\"captiontable\" align=\"center\">\n";
    echo "<tr ><td colspan=\"50\"><b>Color code (mouse over to see in tags per $norm_factor_str)</b></td></tr>\n";
    echo "<tr><td width=\"25%\" align=\"right\">lower expression</td>\n";
    echo "<td title=\"0 tags\" bgcolor=\"".color_pervalue(0, $value, $step)."\">&nbsp;&nbsp;</td>\n";
    echo "<td title=\">0 and <1 tags\" bgcolor=\"".color_pervalue(1, $value, $step)."\">&nbsp;&nbsp;</td>\n";
    if($value > 1){
	echo "<td title=\">1 and < $value tags\" bgcolor=\"".color_pervalue($value, $value, $step)."\">&nbsp;&nbsp;</td>\n";
    }
    for($i=$value+$step; $i<=$max; $i+=$step){
	echo "<td title=\"> ".($i-$step)." and < ".($i-$step+$step)." tags\" bgcolor=\"".color_pervalue($i, $value, $step)."\">&nbsp;&nbsp;\n";
    }
    echo "<td title=\"> ".($i-$step)." tags\" bgcolor=\"".color_pervalue($max+$step, $value, $step)."\">&nbsp;&nbsp;</td>\n";
    echo "<td>higher expression</td>\n";
    echo "</tr></table>\n";
}


function color_pervalue($norm_count, $parcel, $step){
    if($norm_count == 0){
	return "#f6f6f6";
    }
    else if($norm_count > 0 && $norm_count <= 1){
	return "#d3d3d3";
    }
    else if($norm_count > 1 && $norm_count <= $parcel){
	return "#CCFFFF";
    }
    else if($norm_count > $parcel &&  $norm_count <= $parcel+$step){
	return "#66ffff";
    }
    else if($norm_count > $parcel+$step &&  $norm_count <= $parcel+(2 * $step) ){
	return "#0099cc";
    }
    else if($norm_count > $parcel+(2 * $step) &&  $norm_count <= $parcel+(3 * $step) ){
	return "#0033ff";
    }
    else if($norm_count > $parcel+(3 * $step) &&  $norm_count <= $parcel+(4 * $step) ){
	return "#333399";
    }
    else if($norm_count > $parcel+(4 * $step) &&  $norm_count <= $parcel+(5 * $step) ){
	return "#9933cc";
    }
    else if($norm_count > $parcel+(5 * $step) &&  $norm_count <= $parcel+(6 * $step) ){
	return "#cc00cc";
    }
    else if($norm_count > $parcel+(6 * $step) &&  $norm_count <= $parcel+(7 * $step) ){
	return "#ff00ff";
    }
    else if($norm_count > $parcel+(7 * $step) &&  $norm_count <= $parcel+(8 * $step) ){
	return "#FF0099";
    }
    else if($norm_count > $parcel+(8 * $step)  ){
	return "#FF0000";
    }
}

function color_legend($value, $step, $max, $title){
    echo "<table border=1>\n";
    echo "<tr><td>Color</td><td>$title</td></tr>\n";
    echo "<tr><td bgcolor=\"".color_pervalue(0, $value, $step)."\">&nbsp;&nbsp;</td><td>0</td></tr>\n";
    echo "<tr><td bgcolor=\"".color_pervalue(1, $value, $step)."\">&nbsp;&nbsp;</td><td>> 0 and &le 1</td></tr>\n";
    if($value > 1){
	echo "<tr><td bgcolor=\"".color_pervalue($value, $value, $step)."\">&nbsp;&nbsp;</td><td>> 1 and &le $value</td></tr>\n";
    }
    for($i=$value+$step; $i<=$max; $i+=$step){
	echo "<tr><td bgcolor=\"".color_pervalue($i, $value, $step)."\">&nbsp;&nbsp;</td><td> > ".($i-$step)." and &le ".($i-$step+$step)."</td></tr>\n";
    }
    echo "<tr><td bgcolor=\"".color_pervalue($max+$step, $value, $step)."\">&nbsp;&nbsp;</td><td> > ".($i-$step)."</td></tr>\n";
    echo "</table>\n";
}


function leg_gene_status(){
    echo "<b>Genes shown in the heat map can be:</b><br />\n";
    echo "<img src=\"heatmaps/img/1.png\" alt=\"\"> trusted CT antigens, \n";
    echo "<img src=\"heatmaps/img/6.png\" alt=\"\"> non-revalidated as CT, \n";
    echo "<img src=\"heatmaps/img/7.png\" alt=\"\"> predicted CT antigens\n";
    //echo "trusted CT antigens,&nbsp;non-revalidated as CT,&nbsp;predicted CT antigens";

}








#
# outras
#

function get_tags($type, $len, $gene, $tag, $tagstatus){
    if($tagstatus == 'tail+signal'){$tagstatus=1; }
    if($tagstatus == 'tail'){       $tagstatus=2; }
    if($tagstatus == 'signal'){      $tagstatus=3; }
    if($tagstatus == 'w/o_tail_w/o_signal'){$tagstatus=4; }
    

    # para cada TAG do gene: 1-verifica se pertence a >1 gene
    $genes=""; $g=0;
    if($tag != "no_tag"){
	# verifica se a tag pertence a mais de um gene
	$query="select distinct gene from ".$type."_assign_$len where tag='$tag'";
	$result2 = mysql_query($query) or die('Query bib failed (Search failed): '. mysql_error());
	while( $dbdata2 = mysql_fetch_row($result2) ){
	    $gene=$dbdata2[0];
	    $g++;
	    $genes.=" ".$gene;
	}

	$polyAtag=0;
	$a=0;
	$tagarray=array();
	while($cur < strlen($tag) ){
	    $char = $tag{$cur};
	    array_push($tagarray,$char);
	    $cur = $cur + 1;
	}

	for($i=count($tagarray)-1;$i>0;$i--){
	    if($a>5){
		$polyAtag=1;
		$i=0;
	    }

	    if($tagarray[$i] == "A"){
		$a++;
	    }
	    else if($tagarray[$i] != "A"){
		$i=0;
	    }
	}
	
	# primeiro campo: OK, TRUNCADA, POLYA
	$avaliacao="OK, ";
	if( preg_match("[X]", $tag) ){
	    $avaliacao="TRUNCADA, ";
	}
	if($polyAtag && $tagstatus<3){
	    $avaliacao="POLYA, ";
	}
	
	str_replace("[ ]","", $genes);
    
	# segundo campo: reliable, unreliable
	# terceiro campo: numero de genes com mesma tag
	# quarto campo: status da tag
	# quinto campo: genes com mesma tag
	if($g > 1 && $tagstatus < 3){
	    $avaliacao.="unreliable, g$g, t$tagstatus, $genes";
	}
	else if($g > 1 && $tagstatus > 2){
	    $avaliacao.="unreliable, g$g, t$tagstatus, $genes";
	}
	else if($g == 1 && $tagstatus < 3){
	    $avaliacao.="reliable, g$g, t$tagstatus, $genes";
	}	
	else if($g == 1 && $tagstatus > 2){
	    $avaliacao.="mod. reliable, g$g, t$tagstatus, $genes";
	}
    }
    else{
	$avaliacao="NOTAG";
    }
    return $avaliacao;
}

?>
