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
if ($editmode){
  cInclude('module', 'includes/script.cntnd_list_output.php');
}

// values
$cntndList = new CntndList($idart, $lang, $client, $listname);
$values = $cntndList->load();

// module
if ($editmode){
  if ($_POST){
    if (array_key_exists('data',$_POST)){
      // INSERT
      if (array_key_exists($listname,$_POST['data'])){
        $values[] = $_POST['data'][$listname];
        $serializeddata = json_encode($values);
        $cntndList->store($serializeddata);
      }
      // UPDATE
      else if(array_key_exists('action',$_POST) && array_key_exists('key',$_POST) && $_POST['listname']==$listname) {
        $dataToUpdate=json_decode(base64_decode($_POST['data']), true);
        $values = $cntndList->update($_POST['action'],$_POST['key'],$dataToUpdate,$values);
      }
      // REORDER
      if(array_key_exists('reorder',$_POST) && !empty($_POST['reorder']) && $_POST['listname']==$listname) {
        $dataToReorder=json_decode(base64_decode($_POST['reorder']), true);
        $values = $cntndList->reorder($dataToReorder,$values);
      }
    }
  }

	echo '<div class="content_box"><label class="content_type_label">'.mi18n("MODULE").'</label>';

  if (!$template OR empty($template) OR $template=="false"){
    echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("NO_TEMPLATE_OUTPUT").'</div>';
  }
  else {
  	// input
    $formId = "LIST_".$listname;
    $entryFormId = "ENTRY_".$listname;
  	?>
  	<form data-uuid="<?= $formId ?>" id="<?= $formId ?>" name="<?= $formId ?>" method="post">
      <div class="cntnd_alert cntnd_alert-danger hide"><?= mi18n("INVALID_FORM") ?></div>
      <?php
      $cntndListOutput = new CntndListOutput($cntndList->medien(),$cntndList->images(),$cntndList->folders(),$listname);
      for ($index=0;$index<$count;$index++){
          echo $cntndListOutput->input($data,$values[$index],$index,$listname);
      }
      ?>
  		<button class="btn btn-primary" type="submit"><?= mi18n("ADD") ?></button>
  	</form>
    <hr />
    <strong><?= mi18n("LIST_ENTRIES") ?> (<?= count($values) ?>)</strong>
    <form data-uuid="<?= $entryFormId ?>" id="<?= $entryFormId ?>" name="<?= $entryFormId ?>" method="post">
      <input type="hidden" name="listname" value="<?= $listname ?>" />
      <input type="hidden" name="key" />
      <input type="hidden" name="data" />
      <input type="hidden" name="action" />
      <input type="hidden" name="reorder" />
      <button class="btn btn-dark hide" type="submit"><?= mi18n("REFRESH") ?></button>
    </form>
    <div id="cntnd_list_items-<?= $listname ?>">
    <?php
      foreach ($values as $key => $value) {
        echo '<div class="listitem" data-order="'.$key.'" id="'.$entryFormId.'_'.$key.'">'."\n";
        echo '<div class="cntnd_alert cntnd_alert-danger hide">'.mi18n("INVALID_FORM").'</div>'."\n";
        $index=0;
        foreach ($value as $name => $field) {
          $label = 'data['.$index.'][label]';
          $extra = 'data['.$index.'][extra]';
          echo $cntndListOutput->entry($name,$data[$label],$key,$field,$listname,$data[$extra]);
          $index++;
        }
        echo '<button class="cntnd_list_action btn btn-primary" type="button" data-uuid="'.$entryFormId.'" data-listitem="'.$key.'" data-action="update">'.mi18n("SAVE").'</button>'."\n";
        //echo '<button class="cntnd_list_action btn btn-light" type="reset">'.mi18n("RESET") .'</button>'."\n";
        echo '<button class="cntnd_list_action btn" type="button" data-uuid="'.$entryFormId.'" data-listitem="'.$key.'" data-action="delete">'.mi18n("DELETE") .'</button>'."\n";
        echo '</div>'."\n";
      }
    ?>
    </div>
    <?= $cntndList->doSortable() ?>
    <?php
  }

  echo '</div>';
}

if (!$editmode){
  $cntndList->render($templateContent, $values, $data);
}
?>
