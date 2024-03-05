<?

# parametros obrigatorios
$show_gene=$_REQUEST["gene"];
$len=$_REQUEST["len"];
$type=$_REQUEST["type"];
$state=$_REQUEST["state"];

$config=$type;
require "shared/bib.php";
require "../func.php";


# opcional
$libs_tissue=$_GET["libs_tissue"];

# HTML
/*
echo "
<script language='javascript'>
function show_libs_details(id){
    window.open('\"heatmaps/show_libs_sage.php?type=$type&id=\"+id+\"&len=$len\"','lib','height = 350 ,width = 200',scroolbar=yes);

}

function show_libs(gene, tissue,len){
    window.open('heatmaps/heatmap_tag.php?state=$state&type=$type&len='+len+'&gene=+gene+&libs_tissue=+tissue+','lib','height = 500 ,width = 350,scrollbars=yes');
}

function show_legend(){
    open('legend.php?type=$type');
}


</script>
";
*/

echo "<html><body>";
echo "Gene - $show_gene<br/>
      Tam - $len<br/>
      Tipo - $type<br/>
      Estado - $state<br/>";
if(!$libs_tissue){
    echo "<a href=\"#\"onclick=\"window.open('heatmaps/legend.php?type=$type','legend','height = 350 ,width = 200')\">See color code</a><br><br>";
}
#
# dados da tabela
#
$genedb=connect("genes");
//start_mysql("genes","localhost","noboru","abc123");
$query = "select genes.gene from genes left join same_as on (genes.gene=same_as.gene) left join copies on (genes.gene=copies.gene) where same_as.gene is null and copies.gene is null";
 /* and genes.active='Y';*/
$result_genes = mysql_query($query) or die('Query genes failed (Search failed): '. mysql_error());



$ctdb=connect("cta02");
//start_mysql("sage_mpss","localhost","noboru","abc123");
if($libs_tissue){
    $query="select lib, size, id from ".$type."_lib where len='$len' and state='$state' and tissue='$libs_tissue' order by lib;";
}
else{
    $query="select tissue, sum(size), count(lib) from ".$type."_lib where len='$len' and state='$state' group by tissue, state";
}

$i=0;
$result = mysql_query($query) or die('Query 1 failed (Search failed):$query '. mysql_error());
while( $dbdata = mysql_fetch_row($result) ){
    $tissue = $dbdata[0];
    $libsize= $dbdata[1];
    $nlibs  = $dbdata[2];
    if( ($nlibs >= $nlibs_min && $libsize >= $libsize_min && $tissue != "other") || $libs_tissue){
	$libsizes{$tissue}=$libsize;
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


	$order[$tissue]=$i++;
    }
}



$echo=array();
# para todos os genes CT
$genes="";
$tissue_list=cell("", 0, 0, 0, $norm_factor_str, 0, $type);
# especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
while( $dbdata = mysql_fetch_row($result_genes) ){
    $gene=$dbdata[0];
    $array=$gene_array=array();
    $taglist=array();
    $value=0;
    
    #
    # 2- verificando se a(s) TAG(s) do gene estao OK
    #
    $query = "select distinct tag, tagstatus, refseq from ".$type."_assign_$len where gene='$gene'  ";
    $result2 = mysql_query($query) or die('Query 2 failed (Search failed): '. mysql_error());
    while(  $dbdata2 = mysql_fetch_row($result2) ){
	$tag       = $dbdata2[0];
	$tagstatus =$dbdata2[1];
	$refseq    =$dbdata2[2];

	$avaliacao=preg_split("[, ]", get_tags($type, $len, $gene, $tag, $tagstatus));
	$status=$avaliacao[0];
	$aval  =$avaliacao[1];
	$g     =$avaliacao[2];
	$t     =$avaliacao[3];
	$genesc=$avaliacao[4];
        connect("genes");
	//start_mysql("genes","localhost","noboru","abc123");
	$copystatus=analisa_copias($gene, $genesc) ;
	# se o gene possui copias, caso a lista de copias seja = a lista de genes com mesma TAG, OK, aceitar
	if( $copystatus && ($status == "OK" || $status == "POLYA") && ($t == "t1" || $t == "t2")){
	    $taglist[$tag]=$refseq;
	    $value=1;
	  }
	else 
        if(strtoupper($gene) == strtoupper($show_gene) ){
	    if( $status == "NOTAG" ){
		$reason1="&nbsp;&nbsp;[no tag] mRNA has no tag.";
	      }
	    else 
             if( $status == "TRUNCADA" ){
		 $reason1="&nbsp;&nbsp;[truncated] mRNA has a truncated tag.";
	       }
	    else{
		if($t == "t3"){
		    $reason1="&nbsp;&nbsp;[unreliable] mRNA has no poly-A tail.";
		  }
		if($t == "t4"){
		    $reason1="&nbsp;&nbsp;[unreliable] mRNA has no poly-A tail.";
		  }
	       }
	    if(!$copystatus && $tag != "no_tag"){
		$genesc=preg_replace("[ ]", "", $genesc, 1);
		$genesc=preg_replace("[ ]", ", ", $genesc);
		if($reason1){
		    $reason2="<br>";
		    $reason2.="&nbsp;&nbsp;[redundant] Tag also belongs to other genes ($genesc).";
		}
		else{
		    $reason2="&nbsp;&nbsp;[redundant] Tag also belongs to other genes ($genesc).";
		}
	    }
	    if( $status == "POLYA" ){
		if($reason1 || $reason2 || $reason3){$reason3.="<br>";}
		$reason3.="&nbsp;&nbsp;[polya] mRNA has a tag with parts of the POLY-A tail (> 5 As in a row in the 3' end), which may render this tag redundant.";
	    }
	}
        connect("cta02");
	//start_mysql("sage_mpss","localhost","noboru","abc123");
    }
    
    #
    # 3- obtendo expressao/TAG
    #
    if( $value ){
	if(strtoupper($gene) == strtoupper($show_gene)){
	    $gene_first=cell($gene, 0, 0, 1, $norm_factor_str, 1, $type);	
            # especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
	}
	else{
	    $genes.=cell($gene, 0, 0, 0, $norm_factor_str, 1, $type);
	    $col.='<col width="20">'."\n";
	    $width+=20;
	}
	$gene_count++;

	if($gene_count % $block == 0){
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
	    if($libsizes[$tissue]){
		$norm=$freq/$libsizes[$tissue]*$norm_factor;
		
#		if($norm > $filter){
#		$norm=10;
#		}
#		else{
#		$norm=$norm;
#		}
		
		# soma todas as express�s de um certo gene (soma das contagens/TAG)
		$gene_array[$order[$tissue]]+=$norm;
	    }
	}
	if(strtoupper($gene) == strtoupper($show_gene)){
	    $tags2show.="$refseq: $tag<br>";
	}
    }
    
# printing ROWS in any order
    for( $i=0; $i<sizeof($tissues) && $value; $i++ ){
	if(strtoupper($gene) == strtoupper($show_gene)){
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

imprime_tabela($tissue_list, $gene_first, $genes, $tissues, $libs_tissue, $echo_first, $echo, $show_gene, $col, $width, $type, "$reason1$reason2$reason3", $show_gene, $tags2show, $lib_ids,$len,$state);


if($type == "sage"){
    echo "<br>";
    $query = "select date from sage;";
    $result = mysql_query($query) or die('Query 5 failed (Search failed): '. mysql_error());
    $dbdata = mysql_fetch_row($result);
    $date   = $dbdata[0];
    echo "SAGE data downloaded in $date from http://cgap.nci.nih.gov/SAGE";
}

echo "</body></html>"; 



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
