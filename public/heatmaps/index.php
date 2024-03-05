<?
$gene    =$_GET['gene'];


echo '
<script>
function sage(){
  alert("SAGE (Serial Analysis of Gene Expression) is an experimental technique that generates thousands of short (10-17 nt) sequences from the 3\' end of mRNA transcripts with a poly-A tail. These short sequences are called sequence tags and their frequencies should be proportional to the gene expression level. Find more info in the help section.");
}

function mpss(){
  alert("MPSS (Massive Parallel Signature Sequencing) is an experimental technique that generates thousands of short (10-17 nt) sequences from the 3\' end of mRNA transcripts with a poly-A tail. These short sequences are called sequence tags and their frequencies should be proportional to the gene expression level. Find more info in the help section.");
}

function est(){
  alert("ESTs are segments of mRNA transcripts that are sequenced in large amounts to represent gene expression levels.\
The expression levels presented here were obtained from UniGene clusters and EST libraries curated by UniGene using a controlled-vocabulary. Normalized libraries were excluded from this dataset because they could bias the results.\
Only tissues with more than 9 libraries summing at least 50 000 ESTs are shown.\
");
}

</script>
';

echo "

<a href='heatmap_tag.php?gene=$gene&type=sage&len=short&state=cancer'>SAGE</a> (short, 10 nt tags)<sup><a href='javascript:sage()'>?</a></sup><br>
<a href='heatmap_tag.php?gene=$gene&type=sage&len=long&state=cancer'>SAGE</a> (long, 17 nt tags)<sup><a href='javascript:sage()'>?</a></sup><br>
<a href='heatmap_tag.php?gene=$gene&type=mpss&len=short&state=cancer'>MPSS</a> (short, 13 nt tags)<sup><a href='javascript:mpss()'>?</a></sup><br>
<a href='heatmap_est.php?gene=$gene&state=cancer'>EST</a><sup><a href='javascript:est()'>?</a></sup><br>


";
?>
