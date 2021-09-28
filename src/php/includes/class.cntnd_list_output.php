<?php

namespace Cntnd\DynList;

include_once("class.cntnd_util.php");

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

  private function downloadlink($label, $name, $value){
    $disabled='disabled="disabled"';
    if (!$value){
      $value=array('value'=>'','link'=>'');
    }
    else if ($value['value']=='111111111' || $value['value']=='222222222') {
      $disabled='';
    }
    $input = $this->dropdownMedia($name.'[value]',$label,$this->documents,'filename',$value['value'],true,true,true,$name.'[target]',$value['target']);
    $input.= '<div class="form-group '.$this->listname.' cntnd_url_path">';
    $input.= '<label><i>Pfad (URL, idart):</i></label>';
    $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" '.$disabled.' />';
    $input.= '</div>';
    return $input;
  }

  private function url($label, $name, $value, $extra){
    if (!$value){
      $value=array('value'=>'','link'=>'');
    }
    if ($extra=='images'){
      $list = $this->images;
    }
    else if($extra=='documents') {
      $list = $this->documents;
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

  private function image($label, $name, $value, $extra){
    if (!$value){
      $value=array('value'=>'','comment'=>'');
    }
    $input = $this->dropdownMedia($name.'[value]',$label,$this->images,'filename',$value['value']);

    if ($extra){
      $input.= '<div class="form-group">';
      $input.= '<label><i>Kommentar:</i></label>';
      $input.= '<input type="text" name="'.$name.'[comment]" value="'.$value['comment'].'" />';
      $input.= '</div>';
    }
    return $input;
  }

  private function gallery($label, $name, $value, $extra, $optional){
    if (!$value){
      $value=array('value'=>'','link'=>'','thumbnail'=>'','comment'=>'');
    }
    $input = $this->dropdownMedia($name.'[value]',$label,$this->imageFolders,'dirname',$value['value']);

    if ($extra=='link'){
      $input.= '<div class="form-group">';
      $input.= '<label><i>Linktitel:</i></label>';
      $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" />';
      $input.= '</div>';
    }
    else if ($extra=='thumbnail'){
      $input.= $this->dropdownMedia($name.'[thumbnail]','<i>Vorschaubild:</i>',$this->images,'filename',$value['thumbnail']);
    }
    if ($optional=='comment'){
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

    $input = $this->renderInput($name, $data[$type], $data[$label], $data[$extra], $data[$optional]);
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$data[$type].'" />';
    return $input;
  }

  public function entry($fieldName,$label,$key,$field,$listname,$extra='',$optional=''){
    $name = 'data['.$key.']['.$listname.']['.$fieldName.']';
    $input = $this->renderInput($name, $field['type'], $label, $extra, $optional, $field);
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$field['type'].'" />';
    return $input;
  }

  private function renderInput($name, $type, $label, $extra='', $optional='', $value=false){
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
          $input.= $this->downloadlink($label,$name,$value);
          break;
      case 'url':
          $input.= $this->url($label,$name,$value,$extra);
          break;
      case 'image':
          $input.= $this->image($label,$name,$value,$extra);
          break;
      case 'gallery':
          $input.= $this->gallery($label,$name,$value,$extra,$optional);
          break;
      default:
          $input.= '<div class="form-group">';
          $input.= '<label>'.$label.'</label>';
          $input.= '<input type="'.self::inputType($type).'" name="'.$valueName.'" value="'.$valueValue.'" />';
          $input.= '</div>';
    }
    return $input;
  }
}
?>
