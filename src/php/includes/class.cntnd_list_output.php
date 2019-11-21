<?php

/**
 * cntnd_list Output Class
 */
class CntndListOutput {

  protected $medien=array();

  function __construct($medien) {
    $this->medien=$medien;
  }

  private static function downloadlink($label, $name, $value, $list){
    if (!$value){
      $value=array('value'=>'','idart'=>'');
    }
    $input = '<div class="form-group">';
    $input.= '<label>'.$label.'</label>';
    $input.= '<select name="'.$name.'[value]">'."\n";
    $input.= '<option value="0">-- kein --</option>'."\n";
    ($value['value'] == 999999999) ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="999999999" '.$sel.'> -ohne Download/Link- </option>'."\n";
    ($value['value'] == 111111111) ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="111111111" '.$sel.'> -Link- </option>'."\n";
    ($value['value'] == 222222222) ? $sel = ' selected="selected"' : $sel = '';
    $input.= '<option value="222222222" '.$sel.'> -Link intern (idart=)- </option>'."\n";
    foreach ($list as $medium) {
       ($value['value'] == $medium['idupl']) ? $sel = ' selected="selected"' : $sel = '';
       $input.= '<option value="'.$medium['idupl'].'" '.$sel.'>'.$medium['filename'].'</option>'."\n";
    }
    $input.= '</select>'."\n";
    $input.= '</div>';

    // auch target als dropdown!!!

    $input.= '<div class="form-group">';
    $input.= '<label><i>Pfad (URL, idart):</i></label>';
    $input.= '<input type="text" name="'.$name.'[idart]" value="'.$value['idart'].'" />';
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

    $name = 'data['.$listname.']['.$data[$field].']';
    $valueName = $name.'[value]';

    $input = $this->renderInput($name, $data[$type], $data[$label]);
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$data[$type].'" />';
    return $input;
  }

  public function entry($fieldName,$label,$key,$field,$listname){
    $name = 'data['.$key.']['.$listname.']['.$fieldName.']';
    $input = $this->renderInput($name, $field['type'], $label, $field);
    $input.= '<input type="hidden" name="'.$name.'[type]" value="'.$field['type'].'" />';
    return $input;
  }

  private function renderInput($name, $type, $label, $value=false){
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
          $input.= self::downloadlink($label,$name,$value,$this->medien);
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
