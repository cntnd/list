?><?php
// cntnd_list_input

// input/vars
$listname = "CMS_VALUE[1]";
if (empty($listname)){
    $listname="cntnd_list";
}
$template = "CMS_VALUE[2]";
$data = json_decode(base64_decode("CMS_VALUE[3]"), true);

// other/vars
$uuid = rand();
$templateOptions= array();
$template_dir   = $cfgClient[$client]["module"]["path"].'cntnd_list/template/';
$handle         = opendir($template_dir);
while ($entryName = readdir($handle)){
    if (is_file($template_dir.$entryName)){
      $selected="";
      if ($template==$template_dir.$entryName){
        $selected = 'selected="selected"';
      }
      $templateOptions[]='<option '.$selected.' value="'.$template_dir.$entryName.'">'.$entryName.'</option>';
    }
}
closedir($handle);
asort($templateOptions);

$db=cRegistry::getDb();
$sql = "SELECT DISTINCT dirname from ".$cfg["tab"]["upl"];
$db->query($sql);
while ( $db->nextRecord() ) {
    $dirs[] = $db->f("dirname");
}

// includes
cInclude('module', 'includes/class.cntnd_list_input.php');
cInclude('module', 'includes/script.cntnd_list_input.php');
cInclude('module', 'includes/style.cntnd_list_input.php');

if (!$template OR empty($template) OR $template=="false"){
  echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("CHOOSE_TEMPLATE").'</div>';
}
?>
<div class="cntnd_alert cntnd_alert-danger cntnd_list-duplicate hide"><?= mi18n("DUPLICATE_CONFIG") ?></div>
<div class="form-vertical">
  <div class="form-group">
    <label for="listname_<?= $uuid ?>"><?= mi18n("LISTNAME") ?></label>
    <input id="listname_<?= $uuid ?>" name="CMS_VAR[1]" type="text" class="cntnd_list_id" value="<?= $listname ?>" />
  </div>

  <div class="form-group">
    <label for="template_<?= $uuid ?>"><?= mi18n("TEMPLATE") ?></label>
    <select name="CMS_VAR[2]" id="template_<?= $uuid ?>" size="1" onchange="this.form.submit()">
      <option value="false"><?= mi18n("SELECT_CHOOSE") ?></option>
      <?php
        foreach ($templateOptions as $value) {
          echo $value;
        }
      ?>
    </select>
  </div>
</div>

<hr />
<?php
if (!empty($template) AND $template!="false"){
  $handle = fopen($template, "r");
  $templateContent = fread($handle, filesize($template));
  fclose($handle);
  preg_match_all('@\{\w*?\}@is', $templateContent, $fields);

  echo '<table class="cntnd_list" data-uuid="'.$uuid.'">';
  $index=0;
  $count = count(array_unique($fields[0]));
  foreach(array_unique($fields[0]) as $field){
      $tpl_field = 'data['.$index.'][field]';
      $label = 'data['.$index.'][label]';
      $type ='data['.$index.'][type]';
      $extra ='data['.$index.'][extra]';

      echo '<tr>';
      echo '<td><b>'.$field.'</b><input data-uuid="'.$uuid.'" type="hidden" name="'.$tpl_field.'" value="'.$field.'" /></td>';
      echo '<td><input data-uuid="'.$uuid.'" type="text" name="'.$label.'" value="'.$data[$label].'" /></td>';
      echo '<td><select data-uuid="'.$uuid.'" name="'.$type.'">'.CntndListInput::getChooseFields($field,$data[$type]).'</select></td>';
      echo '<td>';
      if (CntndListInput::isExtraField($data[$type])){
        echo '<select data-uuid="'.$uuid.'" name="'.$extra.'">'.CntndListInput::getExtraFields($data[$type],$data[$extra],$dirs).'</select>';
      }
      echo '</td>';
      echo '</tr>';

      $index++;
  }
  echo '</table>';
  echo '<input type="hidden" name="CMS_VAR[3]" id="content_'.$uuid.'" value="CMS_VALUE[3]" />';
}
?><?php
