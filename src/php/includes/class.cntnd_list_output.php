<?php

namespace Cntnd\DynList;

require_once("class.cntnd_util.php");

/**
 * cntnd_list Output Class
 */
class CntndListOutput extends CntndUtil {

  private $cfgClient;
  private $listname;
  protected $documents=array();
  protected $images=array();
  protected $imageFolders=array();

  function __construct($documents,$images,$imageFolders,$listname,$cfgClient) {
    $this->cfgClient = $cfgClient;
    $this->documents=$documents;
    $this->images=$images;
    $this->imageFolders=$imageFolders;
    $this->listname=$listname;
  }

  private function downloadlink($label, $name, $value, $optional=array()){
    $disabled='disabled="disabled"';
    if (!$value){
      $value=array('value'=>'','link'=>'');
    }
    else if ($value['value']=='111111111' || $value['value']=='222222222') {
      $disabled='';
    }
    $input = $this->dropdownMedia($name.'[value]',$label,$this->restrictToFolder($this->documents,$optional[0]),'filename',$value['value'],true,true,true,$name.'[target]',$value['target']);
    $input.= '<div class="form-group '.$this->listname.' cntnd_url_path">';
    $input.= '<label><i>Pfad (URL, idart):</i></label>';
    $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" '.$disabled.' />';
    $input.= '</div>';
    return $input;
  }

  private function url($label, $name, $value, $extra, $optional=array()){
    if (!$value){
      $value=array('value'=>'','link'=>'');
    }
    if ($extra=='images'){
      $list = $this->restrictToFolder($this->images,$optional[0]);
    }
    else if($extra=='documents') {
      $list = $this->restrictToFolder($this->documents,$optional[0]);
    }
    else {
      if (empty($value['value'])){
        $value['value']=111111111;
      }
      $list = array();
    }
    $disabled='disabled="disabled"';
    if ($value['value']=='111111111' || $value['value']=='222222222') {
      $disabled='';
    }
    $input = $this->dropdownMedia($name.'[value]',$label,$list,'filename',$value['value'],false,true,true,$name.'[target]',$value['target']);

    $input.= '<div class="form-group '.$this->listname.' cntnd_url_path">';
    $input.= '<label><i>URL (oder idart):</i></label>';
    $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" placeholder="URL mit http" '.$disabled.' />';
    $input.= '</div>';
    return $input;
  }

  private function image($label, $name, $value, $extra, $optional=array()){
    if (!$value){
      $value=array('value'=>'','comment'=>'');
    }
    $input = $this->dropdownMedia($name.'[value]',$label,$this->restrictToFolder($this->images,$optional[0]),'filename',$value['value']);

    if ($extra){
      $input.= '<div class="form-group">';
      $input.= '<label><i>Kommentar:</i></label>';
      $input.= '<input type="text" name="'.$name.'[comment]" value="'.$value['comment'].'" />';
      $input.= '</div>';
    }
    return $input;
  }

  private function restrictToFolder($list, $restriction='') {
    if (!empty($restriction)) {
      return array_filter($list, function ($value) use ($restriction) {
        return self::startsWith($value['dirname'], $restriction);
      });
    }
    return $list;
  }


  private function dropdown($label, $name, $value, $optional=array()){
    $input = '<div class="form-group">';
    $input.= '<label>'.$label.'</label>';
    $input.= '<select name="'.$name.'" class="cntnd_dropdown" data-listname="'.$this->listname.'">'."\n";
    $input.= '<option value="">-- kein --</option>'."\n";
    $list = explode (",", $optional[0]);
    foreach ($list as $item) {
      $key = $this->dropdownValueToKey($item);
      ($value == $key) ? $sel = ' selected="selected"' : $sel = '';
      $input.= '<option value="'.$key.'" '.$sel.'>'.$item.'</option>'."\n";
    }
    $input.= '</select>'."\n";
    $input.= '</div>';
    return $input;
  }

  private function dropdownValueToKey($value) {
    $clean = str_replace('&nbsp;', ' ', $value);
    $clean = strip_tags($clean);
    $clean = trim($clean);
    return preg_replace( '/[\W]/', '', $clean);
  }

  private function gallery($label, $name, $value, $extra, $optional=array()){
    if (!$value){
      $value=array('value'=>'','link'=>'','thumbnail'=>'','comment'=>'');
    }
    $input = $this->dropdownMedia($name.'[value]',$label,$this->restrictToFolder($this->imageFolders,$optional[1]),'dirname',$value['value']);

    if ($extra=='link'){
      $input.= '<div class="form-group">';
      $input.= '<label><i>Linktitel:</i></label>';
      $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" />';
      $input.= '</div>';
    }
    else if ($extra=='thumbnail'){
      $input.= $this->dropdownMedia($name.'[thumbnail]','<i>Vorschaubild:</i>',$this->restrictToFolder($this->images,$optional[1]),'filename',$value['thumbnail']);
    }
    if ($optional[0]=='comment'){
      $folder=$this->cfgClient["upl"]["path"].$this->imageFolders[$value['value']]['dirname'];
      $input.= '<div class="form-group">';
      $input.= '<label><i>Bildlegende (.txt):</i></label>';
      $input.= $this->dropdownFilesInFolder($name.'[comment]', "bildlegende.txt", $folder, ".txt", $value['comment']);
      $input.= '</div>';
    }
    return $input;
  }

  private function dropdownMedia($name,$label,$list,$labelList,$value,$without=false,$link=false,$internal=false,$target='',$targetValue=''){
    $w = '';
    $input = '';
    if ($link){
      $w = 'w-75';
      $input.= '<div class="d-flex justify-content-between">';
    }

    $input.= '<div class="form-group '.$w.'">';
    $input.= '<label>'.$label.'</label>';
    $input.= '<select name="'.$name.'" class="cntnd_dropdown_media" data-listname="'.$this->listname.'">'."\n";
    $input.= '<option value="0">-- kein --</option>'."\n";
    if ($without){
      ($value == 999999999) ? $sel = ' selected="selected"' : $sel = '';
      $input.= '<option value="999999999" '.$sel.'> -ohne Download/Link- </option>'."\n";
    }
    if ($link){
      ($value == 111111111) ? $sel = ' selected="selected"' : $sel = '';
      $input.= '<option value="111111111" '.$sel.'> -Link- </option>'."\n";
    }
    if ($internal){
      ($value == 222222222) ? $sel = ' selected="selected"' : $sel = '';
      $input.= '<option value="222222222" '.$sel.'> -Link intern (idart=)- </option>'."\n";
    }
    foreach ($list as $medium) {
      ($value == $medium['idupl']) ? $sel = ' selected="selected"' : $sel = '';
      $input.= '<option value="'.$medium['idupl'].'" '.$sel.'>'.$medium[$labelList].'</option>'."\n";
    }
    $input.= '</select>'."\n";
    $input.= '</div>';

    if ($link && !empty($target)){
      $input.= $this->urlTarget($target,$targetValue);
      $input.= '</div>';
    }
    return $input;
  }

  private function dropdownFilesInFolder($name, $default, $folder, $ext, $value){
    $input = '<select name="'.$name.'">'."\n";
    $input.= '<option value="'.$default.'">-- Standard: '.$default.' --</option>'."\n";
    foreach(scandir($folder) as $file) {
      if (!is_dir($folder.$file) && self::endsWith($file,$ext)) {
        ($value == $file) ? $sel = ' selected="selected"' : $sel = '';
        $input.= '<option value="'.$file.'" '.$sel.'>'.$file.'</option>'."\n";
      }
    }
    $input.= '</select>'."\n";
    return $input;
  }

  private function urlTarget($name, $value){
    $input = '<div class="form-group w-25">'."\n";
    $input.= '<label><i>Target:</i></label>'."\n";
    $input.= '<select name="'.$name.'">'."\n";
    ($value == '0' || empty($value)) ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="0">-- automatisch --</option>'."\n";
    ($value == '_blank') ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="_blank" '.$sel.'> _blank (neues Fenster)</option>'."\n";
    ($value == '_self') ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="_self" '.$sel.'> _self (im gleichen Fenster)</option>'."\n";
    ($value == '_parent') ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="_parent" '.$sel.'> _parent (im "parent" Frame, bei iFrames)</option>'."\n";
    ($value == '_top') ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="_top" '.$sel.'> _top (im ganzen Frame, bei iFrames)</option>'."\n";
    $input.= '</select>'."\n";
    $input.= '</div>'."\n";
    return $input;
  }

  private static function inputType($type){
    switch($type){
      case 'internal':
        return 'hidden';
      default:
        return 'text';
    }
  }

  public function input($data,$values,$index,$listname){
    $field = 'data['.$index.'][field]';
    $label = 'data['.$index.'][label]';
    $type = 'data['.$index.'][type]';
    $extra = 'data['.$index.'][extra]';
    $optional = 'data['.$index.'][optional]';

    $name = 'data['.$listname.']['.$data[$field].']';
    $valueName = $name.'[value]';

    $input = $this->renderInput($name, $data[$type], $data[$label], $data[$extra], self::optionals($data, $optional));
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$data[$type].'" />';
    return $input;
  }

  public function entry($fieldName,$label,$key,$field,$listname,$extra='',$optional=array()){
    $name = 'data['.$key.']['.$listname.']['.$fieldName.']';
    $input = $this->renderInput($name, $field['type'], $label, $extra, $optional, $field);
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$field['type'].'" />';
    return $input;
  }

  private function renderInput($name, $type, $label, $extra='', $optional=array(), $value=false){
    $valueName = $name.'[value]';
    if ($value){
      $valueValue = $value['value'];
    }

    $input = '';
    switch($type){
      case 'internal':
          $input.= '<input type="'.self::inputType($type).'" name="'.$valueName.'" value="'.$valueValue.'" />';
          break;
      case 'textarea':
          $input_extra='';
          if ($extra=="markdown"){
            $input_extra = ' <a href="https://devhints.io/markdown" target="_blank">(?)</a>';
          }
          $input.= '<div class="form-group">';
          $input.= '<label>'.$label.$input_extra.'</label>';
          $input.= '<textarea name="'.$valueName.'" rows="5">'.$valueValue.'</textarea>';
          $input.= '</div>';
          break;
      case 'downloadlink':
          $input.= $this->downloadlink($label,$name,$value,$optional);
          break;
      case 'url':
          $input.= $this->url($label,$name,$value,$extra,$optional);
          break;
      case 'image':
          $input.= $this->image($label,$name,$value,$extra,$optional);
          break;
      case 'gallery':
          $input.= $this->gallery($label,$name,$value,$extra,$optional);
          break;
      case 'dropdown':
        $input.= $this->dropdown($label,$valueName,$valueValue,$optional);
        break;
      default:
          $input.= '<div class="form-group">';
          $input.= '<label>'.$label.'</label>';
          $input.= '<input type="'.self::inputType($type).'" name="'.$valueName.'" value="'.$valueValue.'" />';
          $input.= '</div>';
    }
    return $input;
  }

  public static function optionals($data, $optional) {
    $optionals = array_filter($data, function($key) use ($optional) {
      return strpos($key, $optional) === 0;
    }, ARRAY_FILTER_USE_KEY);
    return array_values($optionals);
  }
}
?>
