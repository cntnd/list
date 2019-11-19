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
$count = 0;
if (!empty($template) AND $template!="false"){
  $handle = fopen($template, "r");
  $templateContent = fread($handle, filesize($template));
  fclose($handle);
  preg_match_all('@\{\w*?\}@is', $templateContent, $templateFields);
  $count = count(array_unique($templateFields[0]));
}
$data = json_decode(base64_decode("CMS_VALUE[3]"), true);

// includes
cInclude('module', 'includes/class.cntnd_list.php');
cInclude('module', 'includes/class.cntnd_list_output.php');
cInclude('module', 'includes/class.template.php');

// values
$cntndList = new CntndList($idart, $lang, $client, $listname);
$values = $cntndList->load();

// module
if ($editmode){
  if ($_POST){
    echo '<strong>POST</strong>';
    echo '<pre>';
    var_dump($_POST);
    echo '</pre>';
    if (array_key_exists('data',$_POST)){
      if (array_key_exists($listname,$_POST['data'])){
        $values[] = $_POST['data'][$listname];
        $serializeddata = json_encode($values);
        $cntndList->store($serializeddata);
      }
    }
  }

  echo '<pre>';
  var_dump($values);
  echo '</pre>';

	echo '<div class="content_box"><label class="content_type_label">'.mi18n("MODULE").'</label>';

  if (!$template OR empty($template) OR $template=="false"){
    echo '<div class="alert alert-info">'.mi18n("CHOOSE_TEMPLATE").'</div>';
  }
  else {
  	// input
    $formId = "LIST_".$listname;
  	?>
  	<form data-uuid="<?= $formId ?>" id="<?= $formId ?>" name="<?= $formId ?>" method="post">
      <?php
      $cntndListOutput = new CntndListOutput($cntndList->medien());
      for ($index=0;$index<$count;$index++){
          echo $cntndListOutput->input($data,$values,$index,$listname);
      }
      ?>
      <!-- onclick="javascript:document.getElementById('LIST_<?= $listname ?>').submit();" -->
  		<button class="btn btn-primary" type="submit"><?= mi18n("ADD") ?></button>
  	</form>
    <hr />
    liste aller einträge
    <?php
  }
}

$cntndList->render($templateContent, $values);

if ($editmode){
    echo '</div>';
}
?>
