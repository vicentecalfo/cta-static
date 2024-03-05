<?
$config="est";
require "shared/bib.php";
require "../func.php";

$libs_tissue=$_GET["libs_tissue"];
$show_gene=$_GET["gene"];
$state=$_GET["state"];
$type=$config;




echo "
<script language='javascript'>
function show_libs(gene, tissue){
    open(\"heatmaps/show_libs_est.php?state=$state&gene=\"+gene+\"&libs_tissue=\"+tissue+\"\");

}


function show_legend(){
    open(\"legend.php?type=est\");
}
</script>
";


echo "<table bgcolor=\"cyan\"><tr><td>";
echo "
Mouse over gene symbols to see the number of ESTs for each gene.<br><br>
<img src=\"img/csize.png\"> indicates genes with less than 10 ESTs. A small number of ESTs may not be enough to reflect the real distribution of ESTs along tissues. In these cases, lack of expression may simply correspond to lack of ESTs from a given tissue.<br><br>
Mouse over each colored cell to see the normalized number of ESTs.<br><br>
Data presented in the colored cells are transformed to normalize for the different numbers of ESTs from each tissue. The numbers presented are: the absolute number of ESTs in a given tissue/total numbers of ESTs in that tissue X $norm_factor_str ESTs.<br><br>
<a href=\"#\"onclick=\"window.open('heatmaps/legend.php?type=$type','legend','height = 350 ,width = 200')\">See color code</a><br><br>";

echo "</td></tr></table><br>";


# fazendo busca dos genes CT no banco
connect("genes");
//start_mysql("genes","localhost","noboru","abc123");
$query = "select genes.gene from genes left join same_as on (genes.gene=same_as.gene) left join copies on (genes.gene=copies.gene) where same_as.gene is null and copies.gene is null";
/* and genes.active='Y';*/
$result_genes = mysql_query($query) or die('Query 1 failed (Search failed): '. mysql_error());



# buscando os tecidos a serem exibidos
connect("cta02");
//start_mysql("estprofiles","localhost","noboru","abc123");
if($libs_tissue){
    $query="select title, count, count, lid from est_libs where tissue='$libs_tissue' and state='$state' and norm='n';";

    if($libs_tissue == "skin"){
	$query="select title, count, count, lid from est_libs where tissue='$libs_tissue' and state='$state' and norm='n' and verbatim_tissue like '%melanoma%';";
    }
}
else{
    $query="select tissue, sum(count), count(lid) from est_libs where state='$state' and norm='N' and IF(tissue='skin',verbatim_tissue like '%melanoma%',verbatim_tissue like '%') group by tissue;";
}


$tissues=array();
$libsizes=$lids=$tissues=$order=$gene_array=array();
$array=get_tissues($query, $nlibs_min, $libsize_min, $libsizes, $lids, $tissues, $order, 0);
$libsizes=$array["libsizes"];
$lids    =$array["lids"];
$tissues =$array["tissues"];
$order   =$array["order"];
$i       =$array["i"];

if($state == "cancer"){
    $query="select annot, sum(count), count(lid), lid from est_libs where annot='leukemia' and state='cancer' and norm='n' group by norm;";
    $array=get_tissues($query, $nlibs_min, $libsize_min, $libsizes, $lids, $tissues, $order, $i);
    $libsizes=$array["libsizes"];
    $lids    =$array["lids"];
    $tissues =$array["tissues"];
    $order   =$array["order"];
    $i       =$array["i"];

    $query="select annot, sum(count), count(lid), lid from est_libs where annot='lymphoma' and state='cancer' and norm='n' group by norm;";
    $array=get_tissues($query, $nlibs_min, $libsize_min, $libsizes, $lids, $tissues, $order, $i);
    $libsizes=$array["libsizes"];
    $lids    =$array["lids"];
    $tissues =$array["tissues"];
    $order   =$array["order"];
    $i       =$array["i"];
}



# para todos os genes CT
$genes="";
$tissue_list=cell("", 0, 0, 0, $norm_factor_str, 0, $type);
# especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
while( $dbdata = mysql_fetch_row($result_genes) ){
    $gene=$dbdata[0];


    $query="select sum(count) from est_estprofiles where gene='$gene'";
    $result4 = mysql_query($query) or die('Query 4 failed (Search failed): '. mysql_error());
    $dbdata4 = mysql_fetch_row($result4); $csize=$dbdata4[0];

    if($csize){
	$gene_count++;
	
	$array=$gene_array=array();
	if(strtoupper($gene) == strtoupper($show_gene)){
	    $gene_first=cell($gene, 0, 0, 1, $csize, 1, $type);	
	    # especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
	}
	else{
	    $genes.=cell($gene, 0, 0, 0, $csize, 1, $type);
	    $col.='<col width="20">'."\n";
	    $width+=20;
	}
	if($gene_count % $block == 0){
	    $genes.=cell("", 0, 0, 0, $norm_factor_str, 0, $type);
	    $col.='<col width="200">'."\n";
	    $width+=200;
	}
	
	if($libs_tissue){
	    $query = "select count(tissue), est_libs.title from est_libs, est_clusters where est_clusters.lid=est_libs.lid and state='$state' and norm='n' and tissue='$libs_tissue' and  gene='$gene' group by libs.title;";
	}
	else{
	    $query = "select est_estprofiles.count, est_tissues.name, est_tissues.libsize from est_tissues, est_estprofiles where est_tissues.tissue_id=est_estprofiles.tissue_id and state='$state' and  status='data' and gene='$gene'";
	}

	$gene_array=get_counts($query, $libsizes, $norm_factor, $gene_array, $order, $libsizes, "");

	if($state == "cancer"){
	    $query="select count(est_libs.lid) from est_clusters, est_libs where est_clusters.lid=est_libs.lid and annot='leukemia' and gene='$gene';";
	    $gene_array=get_counts($query, $libsizes, $norm_factor, $gene_array, $order, $libsizes, "leukemia");
	    $query="select count(est_libs.lid) from est_clusters, est_libs where est_clusters.lid=est_libs.lid and annot='lymphoma' and gene='$gene';";
	    $gene_array=get_counts($query, $libsizes, $norm_factor, $gene_array, $order, $libsizes, "lymphoma");
	}
	
	# guardando os valores de expressao para cada tecido
	for( $i=0; $i<sizeof($tissues); $i++ ){
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
		$echo[$i].=cell($tissues[$i], 0, 0, 0, 0, 0, $type);
	    }
	}
    }
}

imprime_tabela($tissue_list, $gene_first, $genes, $tissues, $libs_tissue, $echo_first, $echo, $show_gene, $col, $width, "est", 0, $show_gene, 0, 0,0,$state);

echo "<br>";
$query = "select version, date from est_unigene;";
$result = mysql_query($query) or die('Query 5 failed (Search failed): '. mysql_error());
$dbdata = mysql_fetch_row($result);
$version= $dbdata[0];
$date   = $dbdata[1];
echo "Data source: UniGene $version, downloaded in $date";



function get_tissues($query, $nlibs_min, $libsize_min, $libsizes, $lids, $tissues, $order, $i){
    $result = mysql_query($query) or die('Query 1 failed (Search failed): '. mysql_error());
    while( $dbdata = mysql_fetch_row($result) ){
	$tissue = $dbdata[0];
	$libsize= $dbdata[1];
	$nlibs  = $dbdata[2];
	$lid    = $dbdata[3];

	if($tissue == "brain"){ $tissue="glioma (brain)";}
	if($tissue == "skin"){ $tissue="melanoma (skin)";}

	# aceitando somente tecidos selecionados
	if( ($nlibs >= $nlibs_min && $libsize >= $libsize_min &&
	     ($tissue != "uncharacterized tissue" && 
	      $tissue != "mixed"))
	    || $libs_tissue
	    ){
	    $libsizes[$tissue]=$libsize;
	    $lids[$i]=$lid;
	    $tissues[$i]=$tissue;
	    $order[$tissue]=$i++;
	}
    }
    $array["libsizes"]=$libsizes;
    $array["lids"]    =$lids;
    $array["tissues"] =$tissues;
    $array["order"]   =$order;
    $array["i"]       =$i;

    return $array;
}
function get_counts($query, $libsizes, $norm_factor, $gene_array, $order, $libsizes, $annot){
    $result2 = mysql_query($query) or die('Query 2 failed (Search failed): '. mysql_error());
    while( $dbdata2 = mysql_fetch_row($result2) ){
	$freq   = $dbdata2[0];
	$tissue = $dbdata2[1];

	if($tissue == "brain"){ $tissue="glioma (brain)";}
	if($tissue == "skin"){ $tissue="melanoma (skin)";}

	if($annot){$tissue=$annot;}
	if($libsizes[$tissue]){
	    $norm=$freq/$libsizes[$tissue]*$norm_factor;
	    $gene_array[$order[$tissue]]+=$norm;
	}
    }
    return $gene_array;
}
?>
