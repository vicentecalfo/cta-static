<?
$config="est";
require "shared/bib.php";
require "../func.php";

$libs_tissue=$_GET["libs_tissue"];
$show_gene=$_GET["gene"];
$state=$_GET["state"];
$idGene=$_GET["id"];
$type=$config;


echo "<html><head><title></title></head><body>\n";
echo "
<script type=\"text/javascript\">
function show_libs(gene, tissue){
    open(\"show_libs_est.php?state=$state&gene=\"+gene+\"&libs_tissue=\"+tissue+\"\");

}


function show_legend(){
    open(\"legend.php?type=est\");
}
</script>
";


echo "<table bgcolor=\"#cccccc\"><tr><td>";
echo "
Mouse over gene symbols to see the number of ESTs for each gene.<br /><br />
<img src=\"img/csize.png\" alt=\"\"> indicates genes with less than 10 ESTs. A small number of ESTs may not be enough to reflect the real distribution of ESTs along tissues. In these cases, lack of expression may simply correspond to lack of ESTs from a given tissue.<br><br>
Mouse over each colored cell to see the normalized number of ESTs.<br /><br />
Data presented in the colored cells are transformed to normalize for the different numbers of ESTs from each tissue. The numbers presented are: the absolute number of ESTs in a given tissue/total numbers of ESTs in that tissue X $norm_factor_str ESTs.
</td></tr></table>
";

leg_gene_status();
echo "<br>";
color_summary($parcel, $step, $max, $title, $norm_factor_str);
echo "<br>";





# fazendo busca dos genes CT no banco
connect("cta02");

$query = "select idGene, symbol, status from Gene where status=1 or status=2 or status=4 or status=6 or status=7 order by status, gene";
$result_genes = mysql_query($query) or die('Query 1 failed (Search failed): '. mysql_error());


# buscando os tecidos a serem exibidos

if($libs_tissue){
    $query="select title, count, count, lid from libs where tissue='$libs_tissue' and state='$state' and norm='n';";
}
else{
    $query="select tissue, sum(count), count(lid) from libs where state='$state' and norm='N' group by tissue;";
}


$tissues=array();
$libsizes=$lids=$tissues=$order=$gene_array=array();
$array=get_tissues($query, $nlibs_min, $libsize_min, $libsizes, $lids, $tissues, $order, 0);

$libsizes=$array["libsizes"];
$lids    =$array["lids"];
$tissues =$array["tissues"];
$order   =$array["order"];
$i       =$array["i"];

# para todos os genes CT
$genes="";
$tissue_list=cell("", 0, 0, 0, $norm_factor_str, 0, $type);
# especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
while( $dbdata = mysql_fetch_row($result_genes) ){
    $gene=$dbdata[0];
    $symbol=$dbdata[1];
    $gene_status=$dbdata[2];


    $query="select sum(count) from estprofiles where idGene='$gene'";
    $result4 = mysql_query($query) or die('Query 4 failed (Search failed): '. mysql_error());
    $dbdata4 = mysql_fetch_row($result4); $csize=$dbdata4[0];

    if($csize){
	$gene_count++;
	$array=$gene_array=array();

	#if(strtoupper($gene) == strtoupper($show_gene)){
	if($gene == $idGene){	    
	    $status_row.=cell($gene_status, 0, 0, 0, 0, 1, "status");	
	    $gene_first=cell($symbol, 0, 0, 1, $csize, 1, $type);	
	    # especificar o col width antes de todas as colunas so' na hora de imprimir a tabela
	}
	else{
	    # imprime os nomes dos genes em cima do heatmap
	    $status_row.=cell($gene_status, 0, 0, 0, 0, 1, "status");
	    $genes.=cell($symbol, 0, 0, 0, $csize, 1, $type);
	   // $col.='<col width="20">'."\n";
	    $width+=20;
	    $print_genes.="$gene\t";
	}
	if($gene_count % $block == 0){
	    # repete o nome do tecido a cada $block genes
	    $status_row.=cell("white_square", 0, 0, 0, 0, 1, "status");
	    $genes.=cell("", 0, 0, 0, $norm_factor_str, 0, $type);
	    //$col.='<col width="200">'."\n";
	    $width+=200;
	}
	
	if($libs_tissue){
	    $query = "select count(tissue), libs.title from libs, clusters where clusters.lid=libs.lid and state='$state' and norm='n' and tissue='$libs_tissue' and  idGene='$gene' group by libs.title;";
	}
	else{
	    $query = "select estprofiles.count, tissues.name, tissues.libsize from tissues, estprofiles where tissues.tissue_id=estprofiles.tissue_id and state='$state' and  status='data' and idGene='$gene'";
	}
	$gene_array=get_counts($query, $libsizes, $norm_factor, $gene_array, $order, $libsizes, "");

	
	# guardando os valores de expressao para cada tecido
	for( $i=0; $i<sizeof($tissues); $i++ ){
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
		    $print_values[$i].="0\t";
		}
		else{
		    $echo[$i].=cell($gene_array[$i], $parcel, $step, 0, $norm_factor_str, 0, $type);
		    $print_values[$i].=round($gene_array[$i],1)."\t";
		}
	    }
	    if($gene_count % $block == 0){
		$echo[$i].=cell($tissues[$i], 0, 0, 0, 0, 0, $type);
	    }
	}
    }
}

# exportanto dados do heatmaps em um arquivo .dat
#writeFile("\t$print_genes\n", "/tmp/heatmap.dat", "w");
#for( $i=0; $i<sizeof($tissues); $i++ ){
#    writeFile("$tissues[$i]\t$print_values[$i]\n", "/tmp/heatmap.dat", "a");
#}
$tam=$gene_count*15+150;
echo "<div style=\"width:$tam"."px;\">";
imprime_tabela($tissue_list, $gene_first, $genes, $tissues, $libs_tissue, $echo_first, $echo, $show_gene, $col, $width, "est", 0, $show_gene, 0, 0,0, $status_row,$idGene,$gene_count);
echo "</div>";


echo "<br>";
$query = "select version, date from unigene;";
$result = mysql_query($query) or die('Query 5 failed (Search failed): '. mysql_error());
$dbdata = mysql_fetch_row($result);
$version= $dbdata[0];
$date   = $dbdata[1];
echo "Data source: UniGene $version, downloaded in $date";
echo "</body></html>\n";



function get_tissues($query, $nlibs_min, $libsize_min, $libsizes, $lids, $tissues, $order, $i){
    $result = mysql_query($query) or die('Query 1 failed (Search failed): '. mysql_error());
    while( $dbdata = mysql_fetch_row($result) ){
	$tissue = $dbdata[0];
	$libsize= $dbdata[1];
	$nlibs  = $dbdata[2];
	$lid    = $dbdata[3];

	if($tissue == "brain"){ $tissue="glioma (brain)";}

	# aceitando somente tecidos selecionados

	if( ($nlibs >= $nlibs_min && $libsize >= $libsize_min &&
	     ($tissue != "uncharacterized tissue" && 
	      $tissue != "mixed" &&
	      $tissue != "skin"))
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

	if($annot){$tissue=$annot;}
	if($libsizes[$tissue]){
	    $norm=$freq/$libsizes[$tissue]*$norm_factor;
	    $gene_array[$order[$tissue]]+=$norm;
	}
    }
    return $gene_array;
}
?>
