<?php

/**
 * cntnd_list Output Class
 */
class CntndListOutput {

  protected $medien=array();

  function __construct($medien) {
    $this->medien=$medien;
  }

  private static function dropdown($label, $name, $value, $list){
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

    $input.= '<div class="form-group">';
    $input.= '<label><i>Pfad (URL, idart):</i></label>';
    $input.= '<input type="text" name="'.$name.'[idart]" value="'.$value[$idart].'" />';
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

  public function input($data,$values,$index){
    $label = 'data['.$index.'][label]';
    $type ='data['.$index.'][type]';
    $value ='data['.$index.'][value]';

    $input = '';
    switch($data[$type]){
      case 'internal':
          $input.= '<input type="'.self::inputType($data[$type]).'" name="'.$value.'" value="'.$values[$value].'" />';
          break;
      case 'textarea':
          $input.= '<div class="form-group">';
          $input.= '<label>'.$data[$label].'</label>';
          $input.= '<textarea name="'.$value.'">'.$values[$value].'</textarea>';
          $input.= '</div>';
          break;
      case 'downloadlink':
          $input.= self::dropdown($data[$label], $value,$values[$value],$this->medien);
          break;
      default:
          $input.= '<div class="form-group">';
          $input.= '<label>'.$data[$label].'</label>';
          $input.= '<input type="'.self::inputType($data[$type]).'" name="'.$value.'" value="'.$values[$value].'" />';
          $input.= '</div>';
    }
    return $input;
  }

  public function outputData($data,$index){
    $field = 'data['.$index.'][field]';
    $type ='data['.$index.'][type]';
    $extra ='data['.$index.'][extra]';

    $input = '<input type="hidden" name="'.$field.'" value="'.$data[$field].'" />';
    $input.= '<input type="hidden" name="'.$type.'" value="'.$data[$type].'" />';
    $input.= '<input type="hidden" name="'.$extra.'" value="'.$data[$extra].'" />';
    return $input;
  }
}
?>
