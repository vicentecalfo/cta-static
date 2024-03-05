<?

# parametros obrigatorios. Caprichosa
$idGene=$_GET["id"];
$show_gene=$_GET["gene"];
$len=$_GET["len"];
$type=$_GET["type"];
$state=$_GET["state"];

$config=$type;
require "shared/bib.php";
require "../func.php";


# opcional
$libs_tissue=$_GET["libs_tissue"];
/*
if(isset($_GET["libs_tissue"])){
    cab("microarray");
    echo"<body>
         <div id=\"nav\">
         <h1><span class=\"bluelight\">CT</span>pedia</h1>
         </div>";
   echo "<div id=\"contentmicroarray\">"; 
  }

*/



if(!$libs_tissue){
    echo "<html><head><title></title></head><body>\n";
    leg_gene_status();
    echo "<br /><br />\n";    
    color_summary($parcel, $step, $max, $title, $norm_factor_str);
    echo "<br />\n";
#    echo '<a href="#" onclick="javascript:show_legend(); return false;">See color code</a>';

}


#
# dados da tabela
#
connect("cta02");


$query = "select idGene, symbol, status from Gene where status=1 or status=2 or status=4 or status=6 or status=7 order by status, symbol";
$result_genes = mysql_query($query) or die('Query genes failed (Search failed): '. mysql_error());
//$idGene=$result_genes[0];


if($libs_tissue){
    $query="select lib, size, id from ".$type."_lib where len='$len' and state='$state' and tissue='$libs_tissue' order by lib;";
}
else{
    $query="select tissue, sum(size), count(lib) from ".$type."_lib where len='$len' and state='$state' group by tissue, state";
}
echo $query."<br/>";
$i=0;
$result = mysql_query($query) or die('Query 1 failed (Search failed): '. mysql_error());
while( $dbdata = mysql_fetch_row($result) ){
    $tissue = $dbdata[0];
    $libsize= $dbdata[1];
    $nlibs  = $dbdata[2];
    if( ($nlibs >= $nlibs_min && $libsize >= $libsize_min && $tissue != "other") || $libs_tissue){
	//$libsizes{$tissue}=$libsize;
	$tissues[$i]=$tissue;
	if($type == "sage"){$lib_ids[$tissue]=$nlibs;}

	# alterar nomes dos tecidos para ficarem mais curtos
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
	$tissues_short[$i]=$tissue;

        $libsizes{$tissue}=$libsize;
 	
	$order[$tissue]=$i++;
        echo $tissue."<br/>";
    }
}



$echo=array();
# para todos os genes CT
$genes="";
$tissue_list=cell("", 0, 0, 0, $norm_factor_str, 0, $type);
# especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
while( $dbdata = mysql_fetch_row($result_genes) ){
    $gene=$dbdata[0];
    $symbol=$dbdata[1];
    $gene_status=$dbdata[2];

    $array=$gene_array=array();
    $taglist=array();
    $value=0;
    
    #
    # 2- verificando se a(s) TAG(s) do gene estao OK
    #
    echo "M1: $symbol<br/>";
    $query = "select distinct tag, tagstatus, refseq from ".$type."_assign_$len where idGene='$gene'  ";
    $result2 = mysql_query($query) or die('Query 2 failed (Search failed): '. mysql_error()); 
    while(  $dbdata2 = mysql_fetch_row($result2) ){
	$tag       = $dbdata2[0];
	$tagstatus =$dbdata2[1];
	$refseq    =$dbdata2[2];
        echo "M2: $tag $tagstatus<br/>";
	$avaliacao=preg_split("[, ]", get_tags($type, $len, $gene, $tag, $tagstatus));
	$status=$avaliacao[0];
	$aval  =$avaliacao[1];
	$g     =$avaliacao[2];
	$t     =$avaliacao[3];
	$genesc=$avaliacao[4];
        $genescaux=str_replace($show_gene,"",$genesc);
        $genescaux=str_replace(','," ",$genescaux);
        $copystatus=analisa_copias($symbol, $genesc) ;
	# se o gene possui copias, caso a lista de copias seja = a lista de genes com mesma TAG, OK, aceitar


	if( $copystatus && ($status == "OK" || $status == "POLYA")
	    && ($t == "t1" || $t == "t2") 
	    ){
	    $taglist[$tag]=$refseq;
	    $value=1;
	}
	#else if(strtoupper($gene) == strtoupper($show_gene) ){
	else if($gene == $idGene){
	    if( $status == "NOTAG" ){
		$reason1="&nbsp;&nbsp;[no tag] mRNA has no tag.\n";
	    }
	    else if( $status == "TRUNCADA" ){
		$reason1="&nbsp;&nbsp;[truncated] mRNA has a truncated tag.\n";
	    }
	    else{
		if($t == "t3"){
		    $reason1="&nbsp;&nbsp;[unreliable] mRNA has no poly-A tail.\n";
		}
		if($t == "t4"){
		    $reason1="&nbsp;&nbsp;[unreliable] mRNA has no poly-A tail.\n";
		}
	    }
	    if(!$copystatus && $tag != "no_tag"){
		$genesc=preg_replace("[ ]", "", $genesc, 1);
		$genesc=preg_replace("[ ]", ", ", $genesc);
                if($reason1){
		    $reason2="<br />\n";
		    $reason2.="&nbsp;&nbsp;[redundant] Tag belongs to other non-related genes ($genescaux).\n";
		}
		else{
		    $reason2="&nbsp;&nbsp;[redundant] Tag belongs to other non-related genes ($genescaux).\n";
		}
	    }
	    if( $status == "POLYA" ){
		if($reason1 || $reason2 || $reason3){$reason3.="<br />";}
		$reason3.="&nbsp;&nbsp;[polya] mRNA has a tag with parts of the POLY-A tail (> 5 As in a row in the 3' end), which may render this tag redundant.\n";
	    }
	}
        
    }
    
    #
    # 3- obtendo expressao/TAG
    #
    
    if( $value ){
	if($gene == $idGene){
	    $status_row.=cell($gene_status, 0, 0, 0, 0, 1, "status");	
	    $gene_first=cell($symbol, 0, 0, 1, $norm_factor_str, 1, $type);
            # especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
	}
	else{
	    $status_row.=cell($gene_status, 0, 0, 0, 0, 1, "status");	
	    $genes.=cell($symbol, 0, 0, 0, $norm_factor_str, 1, $type);
	    $col.='<col width="20">'."\n";
	    $width+=20;
	}
	$gene_count++;

	if($gene_count % $block == 0){
	    $status_row.=cell("white_square", 0, 0, 0, 0, 1, "status");
	    $genes.=cell("", 0, 0, 0, $norm_factor_str, 0, $type);
	    $col.='<col width="200">'."\n";
	    $width+=200;
	}
    }
    foreach ($taglist as $tag => $refseq){
	if($libs_tissue){
	    $query="select ".$type."_freq_$len.freq, ".$type."_lib.lib from ".$type."_freq_$len, ".$type."_lib where ".$type."_freq_$len.id=".$type."_lib.id and tag='$tag' and state='$state' ";
	}
	else{
	    $query="select sum(".$type."_freq_$len.freq), ".$type."_lib.tissue from ".$type."_freq_$len, ".$type."_lib where ".$type."_freq_$len.id=".$type."_lib.id and tag='$tag' and state='$state' group by ".$type."_lib.tissue;";
            
	}
	$result3 = mysql_query($query) or die('Query 3 failed (Search failed): '. mysql_error());
	while( $dbdata3 = mysql_fetch_row($result3) ){
	    $freq    = $dbdata3[0];
	    $tissue  = $dbdata3[1];
            
            # alterar nomes dos tecidos para ficarem mais curtos
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
             
	    if($libsizes[$tissue]){
		$norm=$freq/$libsizes[$tissue]*$norm_factor;
		
#		if($norm > $filter){
#		$norm=10;
#		}
#		else{
#		$norm=$norm;
#		}
		
		# soma todas as expressões de um certo gene (soma das contagens/TAG)
		$gene_array[$order[$tissue]]+=$norm;
	    }
	}
	#if(strtoupper($gene) == strtoupper($show_gene)){
	if($gene == $idGene){            
	    $tags2show.="$refseq: $tag<br />";
	}
    }
    
# printing ROWS in any order
    for( $i=0; $i<sizeof($tissues) && $value; $i++ ){
	#if(strtoupper($gene) == strtoupper($show_gene)){
	if($gene == $idGene){            
	    if( !$gene_array[$i] ){
		$echo_first[$i]=cell(0, $parcel, $step, 0, $norm_factor_str, 0, $type);
	    }
	    else{
		$echo_first[$i]=cell($gene_array[$i], $parcel, $step, 0, $norm_factor_str, 0, $type);
	    }
	}
	else{
	    if( !$gene_array[$i] ){
		$echo[$i].=cell(0, $parcel, $step, 0, $norm_factor_str, 0, $type);
	    }
	    else{
		$echo[$i].=cell($gene_array[$i], $parcel, $step, 0, $norm_factor_str, 0, $type);
	    }
	}
	if($gene_count % $block == 0){
	    $echo[$i].=cell($tissues_short[$i], 0, 0, 0, 0, 0, $type);
	}
    }
}

//echo "<div><!--div style=\"width:$tam"."px;\"-->\n";
imprime_tabela($tissue_list, $gene_first, $genes, $tissues, $libs_tissue, $echo_first, $echo, $show_gene, $col, $width, $type, "$reason1$reason2$reason3", $show_gene, $tags2show, $lib_ids, $len, $status_row,$idGene,$gene_count);
//echo $gene.'-'.$idGene.'-'.$_GET["id"];
//echo "</div>\n";
echo "</body></html>\n";


    
function analisa_copias($gene, $genesc){
    $c=1;
    $array_copies=array();
    $query = "select gene from copies where copy_of='$gene';";


    $result4 = mysql_query($query) or die('Query 4 failed (Search failed): '. mysql_error());
    while( $dbdata4 = mysql_fetch_row($result4) ){
	$copia = $dbdata4[0];

	array_push($array_copies, $copia);
	$c++;
    }
    array_push($array_copies, $gene);
    sort($array_copies);
    
    $array_tags=preg_split("[ ]", $genesc);
    sort($array_tags);
    
    $sorted_tags = implode(" ", $array_tags);
    $sorted_copies = implode(" ", $array_copies);
    $sorted_tags=preg_replace("[ ]", "", $sorted_tags, 1);
    

    # t1 e t2 sao mRNAs com cauda poly-A
    # t3 so' tem o sinal, t4 nao tem nada
    if( $sorted_copies == $sorted_tags
	|| ($sorted_tags == $gene && $c>1)
	){

	return 1;
    }

    return 0;
}
?>
