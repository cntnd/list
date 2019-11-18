<?php
/*
$sql_create = "
CREATE TABLE IF NOT EXISTS cntnd_dynlist (
  idlist int(11) NOT NULL AUTO_INCREMENT,
  listname varchar(200) NOT NULL,
  idart int(11) NOT NULL,
  idlang int(11) NOT NULL,
  serializeddata longtext,
  PRIMARY KEY (idlist)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
";
if (!empty($sql_create)){
 $dbC = new DB_Contenido;
 $dbC->query($sql_create);
 echo mysql_error();
}
*/

// cntnd_list_output

// assert framework initialization
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// editmode
$editmode = cRegistry::isBackendEditMode();

// input/vars
$listname = "CMS_VALUE[1]";
if (empty($listname)){
    $listname="cntnd_list";
}
$template = "CMS_VALUE[2]";
if (!empty($template) AND $template!="false"){
  $handle = fopen($template, "r");
  $templateContent = fread($handle, filesize($template));
  fclose($handle);
  preg_match_all('@\{\w*?\}@is', $templateContent, $fields);
}

$data = json_decode(base64_decode("CMS_VALUE[3]"), true);
echo '<pre>';
var_dump($data);
echo '</pre>';
// includes
//cInclude('module', 'includes/class.cntnd_list.php');

// module
if ($editmode){
	echo '<div class="content_box"><label class="content_type_label">'.mi18n("MODULE").'</label>';

  if (!$template OR empty($template) OR $template=="false"){
    echo '<div class="alert alert-info">'.mi18n("CHOOSE_TEMPLATE").'</div>';
  }
  else {
  	// input
  	ksort($data);
  	?>
  	<form id="LIST_<?= $listname ?>" name="LIST_<?= $listname ?>" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
  		<?php
  			foreach($data as $key => $field){
  				echo $key."::".$field."<br />";
  			}
  		?>
  		<h1>form</h1>
      <?php
      echo '<table class="cntnd_list">';
      foreach(array_unique($fields[0]) as $field){
          echo '<tr>';
          echo '<td><b>'.$field.'</b></td>';
          echo '</tr>';
      }
      echo '</table>';
      ?>
  		<button class="btn btn-primary" type="button"><?= mi18n("SAVE") ?></button>
  		<hr />
  		liste aller eintäge
  	</form>
  	<?php
  }
}

if ($editmode){
	echo '</div>';
}
?>
