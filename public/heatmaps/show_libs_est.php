<?
$tissue   =$_GET['libs_tissue'];
$gene   =$_GET['gene'];
$state   =$_GET['state'];


$type=$config="est";
require "shared/bib.php";
require "../func.php";

 cab("microarray");
 echo"<body>
 <div id=\"nav\">
 <h1><span class=\"bluelight\">CT</span>pedia</h1>
 </div>";

echo "<div id=\"contentmicroarray\">";

connect("cta02");


//echo "<table class=\"GeneExpression\" align=\"left\" width=\"50%\">";
//echo "<thead><th colspan=\"5\" align=\"center\">EST libraries from $tissue ($state, non-normalized)</td></tr></thead>";
//echo "<col width='10%'><col width='20%'><col width='20%'>";
//echo "<tr><th>Library ID</th><th>Library name</th><th width='4%'></th><th>Cancer source</th><th>Tissue info</th></tr>";
//echo "</thead><tbody>";
$linha=libs($tissue, $norm_factor, $gene, $parcel, $step, $state);
//echo "</tbody></table>";

//if($linha){
   echo "<table class=\"GeneExpression\" align=\"left\" width=\"50%\">";
   echo "<thead><th colspan=\"5\" align=\"center\">EST libraries from $tissue ($state, non-normalized)</th></tr>";
   //echo "<col width='10%'><col width='20%'><col width='20%'>";
   echo "<tr><th>Library ID</th><th>Library name</th><th width='4%'></th><th>Cancer source</th><th>Tissue info</th></tr>";
   echo "</thead><tbody>";
   echo $linha;
   echo "</tbody></table>";
//}
/*
else{
   echo "<table class=\"GeneExpression\" align=\"left\" width=\"50%\">";
   echo "<thead><th align=\"center\">EST libraries from $tissue ($state, non-normalized)</th></tr></thead>"; 
}
*/

# layout da tabela
function table_row($lid, $state, $annot, $title, $verbatim_tissue, $cell){
    return "
<tr>
  <td>
    <a href='http://www.ncbi.nlm.nih.gov/UniGene/library.cgi?ORG=Hs&LID=".$lid."' target=_blank>$lid</a>
  </td>
  <td>
    $title
  </td>

    $cell

  <td>
    $annot
  </td>
  <td>
    $verbatim_tissue
  </td>
</tr>
";
}









# ATENCAO
# somente acesso ao banco, nao e' necessario mexer para fazer o layout
#
function libs($tissue, $norm_factor, $gene, $parcel, $step, $state){
    $query = "select libs.lid, count(tissue) from libs, clusters where clusters.lid=libs.lid and state='$state' and norm='n' and tissue='$tissue' and  gene='$gene' group by libs.lid;";
    if($tissue == "skin"){
	$query = "select libs.lid, count(tissue) from libs, clusters where clusters.lid=libs.lid and state='$state' and norm='n' and verbatim_tissue like '%melanoma%' and  gene='$gene' group by libs.lid;";
    }
    else if($tissue == "leukemia"){
	$query = "select libs.lid, count(tissue) from libs, clusters where clusters.lid=libs.lid and state='$state' and norm='n' and annot='leukemia' and  gene='$gene' group by libs.lid;";
    }
    else if($tissue == "lymphoma"){
	$query = "select libs.lid, count(tissue) from libs, clusters where clusters.lid=libs.lid and state='$state' and norm='n' and annot='lymphoma' and  gene='$gene' group by libs.lid;";
    }

    $result = mysql_query($query) or die('Query failed (Search failed): '. mysql_error());
    while( $dbdata = mysql_fetch_row($result) ){
	$lid       =$dbdata[0];
	$freq      =$dbdata[1];

	$freqs[$lid]=$freq;
    }

    if($tissue == "skin"){
	$query="select lid, state, annot, title, verbatim_tissue, count from libs where state='$state' and norm='n' and verbatim_tissue like '%melanoma%' and count>0;";
    }
    else if($tissue == "leukemia"){
	$query="select lid, state, annot, title, verbatim_tissue, count from libs where state='$state' and norm='n' and annot='leukemia' and count>0;";
    }
    else if($tissue == "lymphoma"){
	$query="select lid, state, annot, title, verbatim_tissue, count from libs where state='$state' and norm='n' and annot='lymphoma' and count>0;";
    }
    else{
	$query="select lid, state, annot, title, verbatim_tissue, count from libs where state='$state' and tissue='$tissue' and norm='n' and count>0" ;
    }
    $result = mysql_query($query) or die('Query failed (Search failed): '. mysql_error());
    while( $dbdata = mysql_fetch_row($result) ){
	$lid       =$dbdata[0];
	$state     =$dbdata[1];
	$annot     =$dbdata[2];
	$title     =$dbdata[3];
	$verbatim_tissue     =$dbdata[4];
	$libsize     =$dbdata[5];

	$cell=cell($freqs[$lid]/$libsize*$norm_factor, $parcel, $step, 0, $norm_factor, 0, 0, $type);
	$rows.=table_row($lid, $state, $annot, $title, $verbatim_tissue, $cell);
    }
    return $rows;
}
?>
