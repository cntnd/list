?><?php
// cntnd_list_input

// input/vars
$listname = "CMS_VALUE[1]";
if (empty($listname)){
    $listname="cntnd_list";
}
$template = "CMS_VALUE[2]";
$content = json_decode(base64_decode("CMS_VALUE[3]"), true);

var_dump($content);

// todo refactoring!!!
$data=array();
foreach ($content as $item) {
  $key = key($item);
  $value = $item[$key];
  $data[$key]=$value;
}

// other/vars
$uuid = rand();
$template_dir   = $cfgClient[$client]["module"]["path"].'cntnd_list/template/'; // evtl auch mit __DIR__
$templateOptions= array();
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

$unique = rand();

$db=cRegistry::getDb();
$sql = "SELECT DISTINCT dirname from ".$cfg["tab"]["upl"];
$db->query($sql);
while ( $db->nextRecord() ) {
    $dirs[] = $db->f("dirname");
}

// includes
cInclude('module', 'includes/cntnd_list_input_functions.php');
cInclude('module', 'includes/cntnd_list_script.php');

if (!$template OR empty($template) OR $template=="false"){
 echo '<div class="alert alert-info">'.mi18n("CHOOSE_TEMPLATE").'</div>';
}
?>
<div class="form-vertical">
  <div class="form-group">
    <label for="listname"><?= mi18n("LISTNAME") ?></label>
    <input id="listname" name="CMS_VAR[1]" type="text" value="<?= $listname ?>" />
  </div>

  <div class="form-group">
    <label for="template"><?= mi18n("TEMPLATE") ?></label>
    <select name="CMS_VAR[2]" id="template" size="1" onchange="this.form.submit()">
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
  foreach(array_unique($fields[0]) as $field){
    $cms_var_field=100+$cms_var;
    $cms_var_type =200+$cms_var;
    $cms_var_name =300+$cms_var;
    $cms_var_extra=400+$cms_var;

    echo '<tr>
            <td><b>'.$field.'</b>:</td>
            <td><input data-uuid="'.$uuid.'" type="text" name="data['.$cms_var_name.']" value="'.$data['data['.$cms_var_name.']'].'" /></td>
            <td>
                <select data-uuid="'.$uuid.'" name="data['.$cms_var_type.']">
                '.getChooseFields($cms_var_type,$field,$data['data['.$cms_var_type.']']).'
                </select>
                <input data-uuid="'.$uuid.'" type="hidden" name="data['.$cms_var_field.']" value="'.$field.'" />
            </td>
            <td>';
            if (checkExtraFields($data['data['.$cms_var_type.']'])){
                  echo '<select data-uuid="'.$uuid.'" name="data['.$cms_var_extra.']">
                       '.getExtraFields($cms_var_extra,$data['data['.$cms_var_type.']'],$data['data['.$cms_var_extra.']']).'
                        </select>';
            }
    echo '  </td>
          </tr>';
    $cms_var++;
  }
  echo '<tr><td colspan="4">
      <input type="hidden" name="CMS_VAR[3]" id="dataDynList'.$unique.'" value="CMS_VALUE[3]" />
      <input type="hidden" name="CMS_VAR[4]" value="'.$cms_var.'" />
      <input type="hidden" name="update" value="false" id="update" />
      <a href="#" id="saveDynList'.$unique.'" style="float: right;">[SPEICHERN]</a>
    </td></tr>';
  echo "</table>";
}
?><?php
