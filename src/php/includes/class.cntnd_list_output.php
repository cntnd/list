<?php

/**
 * cntnd_list Output Class
 */
class CntndListOutput {

  protected $documents=array();
  protected $images=array();
  protected $imageFolders=array();

  function __construct($documents,$images,$imageFolders) {
    $this->documents=$documents;
    $this->images=$images;
    $this->imageFolders=$imageFolders;
  }

  private function downloadlink($label, $name, $value){
    if (!$value){
      $value=array('value'=>'','link'=>'');
    }
    $input = $this->dropdownMedia($name,$label,$this->documents,'filename',$value['value'],true,true,true);

    // auch target als dropdown!!!

    $input.= '<div class="form-group">';
    $input.= '<label><i>Pfad (URL, idart):</i></label>';
    $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" />';
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
    $input = $this->dropdownMedia($name,$label,$list,'filename',$value['value'],false,true,true);
    // auch target als dropdown!!!

    $input.= '<div class="form-group">';
    $input.= '<label><i>URL (oder idart):</i></label>';
    $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" placeholder="URL mit http"/>';
    $input.= '</div>';
    return $input;
  }

  private function images($label, $name, $value, $extra){
    if (!$value){
      $value=array('value'=>'','comment'=>'');
    }
    $input = $this->dropdownMedia($name,$label,$this->images,'filename',$value['value']);

    if ($extra){
      $input.= '<div class="form-group">';
      $input.= '<label><i>Kommentar:</i></label>';
      $input.= '<input type="text" name="'.$name.'[comment]" value="'.$value['comment'].'" />';
      $input.= '</div>';
    }
    return $input;
  }

  private function gallery($label, $name, $value, $extra){
    if (!$value){
      $value=array('value'=>'','link'=>'','thumbnail'=>'');
    }
    $input = $this->dropdownMedia($name,$label,$this->imageFolders,'dirname',$value['value']);

    if ($extra=='link'){
      $input.= '<div class="form-group">';
      $input.= '<label><i>Linktitel:</i></label>';
      $input.= '<input type="text" name="'.$name.'[link]" value="'.$value['link'].'" />';
      $input.= '</div>';
    }
    else if ($extra=='thumbnail'){
      $input.= $this->dropdownMedia($name.'[thumbnail]','<i>Vorschaubild:</i>',$this->images,'filename',$value['thumbnail']);
    }
    return $input;
  }

  private function dropdownMedia($name,$label,$list,$labelList,$value,$without=false,$link=false,$internal=false){
    $input = '<div class="form-group">';
    $input.= '<label>'.$label.'</label>';
    $input.= '<select name="'.$name.'[value]">'."\n";
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

    $name = 'data['.$listname.']['.$data[$field].']';
    $valueName = $name.'[value]';

    $input = $this->renderInput($name, $data[$type], $data[$label], $data[$extra]);
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$data[$type].'" />';
    return $input;
  }

  public function entry($fieldName,$label,$key,$field,$listname,$extra=''){
    $name = 'data['.$key.']['.$listname.']['.$fieldName.']';
    $input = $this->renderInput($name, $field['type'], $label, $extra, $field);
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$field['type'].'" />';
    return $input;
  }

  private function renderInput($name, $type, $label, $extra='', $value=false){
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
          $input.= '<div class="form-group">';
          $input.= '<label>'.$label.'</label>';
          $input.= '<textarea name="'.$valueName.'">'.$valueValue.'</textarea>';
          $input.= '</div>';
          break;
      case 'downloadlink':
          $input.= $this->downloadlink($label,$name,$value);
          break;
      case 'url':
          $input.= $this->url($label,$name,$value,$extra);
          break;
      case 'image':
          $input.= $this->images($label,$name,$value,$extra);
          break;
      case 'gallery':
          $input.= $this->gallery($label,$name,$value,$extra);
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
