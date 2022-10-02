?><?php
// cntnd_list_input
$cntnd_module = "cntnd_list";

// includes
cInclude('module', 'includes/class.cntnd_list.php');
cInclude('module', 'includes/class.cntnd_list_input.php');
cInclude('module', 'includes/script.cntnd_list_input.php');
cInclude('module', 'includes/style.cntnd_list.php');

// input/vars
$listname = "CMS_VALUE[1]";
if (empty($listname)){
    $listname="cntnd_list";
}
$template = "CMS_VALUE[2]";
$data = Cntnd\DynList\CntndList::unescapeData("CMS_VALUE[3]");

// other/vars
$uuid = rand();
$templates= Cntnd\DynList\CntndList::templates($cntnd_module, $client);

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
        <select name="CMS_VAR[2]" id="template_<?= $uuid ?>" size="1">
            <option value="false"><?= mi18n("SELECT_CHOOSE") ?></option>
            <?php
            foreach ($templates as $template_file) {
                $selected="";
                if ($template==$template_file){
                    $selected = 'selected="selected"';
                }
                echo '<option value="'.$template_file.'" '.$selected.'>'.$template_file.'</option>';
            }
            ?>
        </select>
    </div>
</div>

<hr />
<?php
if (!empty($template) AND $template!="false"){
  $fields = \Cntnd\DynList\CntndList::template($cntnd_module, $client, $template);

  echo '<div class="cntnd_list d-flex" data-uuid="'.$uuid.'">';
  $index=0;
  $count = count(array_unique($fields[0]));
  foreach(array_unique($fields[0]) as $field){
      $tpl_field = 'data['.$index.'][field]';
      $label = 'data['.$index.'][label]';
      $type ='data['.$index.'][type]';
      $extra ='data['.$index.'][extra]';
      $optional ='data['.$index.'][optional]';
      $enabled = '';
      if ($data[$type]=="internal") {
          $enabled = 'disabled';
      }

      echo '<div class="form-vertical w-100">'."\n";
      echo '<fieldset class="form-vertical d-flex"><legend>'.\Cntnd\DynList\CntndList::tplName($field).'</legend>'."\n";
      echo '<div class="form-group w-25">'."\n";
      echo '<input data-uuid="'.$uuid.'" type="hidden" name="'.$tpl_field.'" value="'.$field.'" />';
      echo '<label for="'.$label.'">'.mi18n("FIELD_LABEL").' <strong>'.\Cntnd\DynList\CntndList::tplName($field).'</strong></label>'."\n";
      echo '<input data-uuid="'.$uuid.'" id="'.$label.'" name="'.$label.'" type="text" value="'.$data[$label].'" '.$enabled.'/>'."\n";
      echo '</div>'."\n";

      echo '<div class="form-group w-25">'."\n";
      echo '<label for="'.$type.'">'.mi18n("FIELD_TYPE").'</label>'."\n";
      echo '<select data-uuid="'.$uuid.'" name="'.$type.'">'.Cntnd\DynList\CntndListInput::getChooseFields($field,$data[$type]).'</select>'."\n";
      echo '</div>'."\n";

      if (Cntnd\DynList\CntndListInput::isExtraField($data[$type]) OR Cntnd\DynList\CntndListInput::hasOptionalField($data[$type])) {
          echo '<fieldset class="w-33"><legend>' . mi18n("FIELD_EXTRAS") . '</legend>' . "\n";
          if (Cntnd\DynList\CntndListInput::isExtraField($data[$type])) {
              echo '<div class="form-group">';
              echo '<label for="extras">Extras:</label>';
              echo '<select data-uuid="' . $uuid . '" name="' . $extra . '" id="extras">' . Cntnd\DynList\CntndListInput::getExtraFields($data[$type], $data[$extra]) . '</select>';
              echo '</div>';
          }
          if (Cntnd\DynList\CntndListInput::hasOptionalField($data[$type])) {
              echo Cntnd\DynList\CntndListInput::getOptionalFields($uuid, $optional, $data[$type], $data, $client);
          }
          echo '</fieldset>' . "\n";
      }
      echo '</div>'."\n";
      $index++;
  }
  echo '</div>';
  echo '<input type="hidden" name="CMS_VAR[3]" id="content_'.$uuid.'" value="CMS_VALUE[3]" />';
}
?><?php
