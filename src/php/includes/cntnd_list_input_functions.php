<?php
if (!function_exists('getChooseFields')) {
  function getChooseFields($cms_var,$field,$value){
    $internal="";
    $no_fields=array("{id}","{icon}","{img_over}","{img_icon}","{target}","{javascript}");

    if (in_array($field,$no_fields) OR substr($field,0,2)=="{_"){
      $internal="selected";
    }
    if (!empty($value)){
      $$value="selected";
    }

    $choose_fields='<option value="NULL" '.$NULL.'> --bitte wählen-- </option>
                    <option value="internal" '.$internal.'> -internes Feld- </option>
                    <option value="break" '.$break.'> Umbruch, Horizontale Linie </option>
                    <option value="titel" '.$titel.'> Titel (Einzeilig) </option>
                    <option value="text" '.$text.'> Eingabefeld (Einzeilig) </option>
                    <option value="plain" '.$plain.'> Eingabefeld (Plaintext) </option>
                    <option value="linktext" '.$linktext.'> Eingabefeld (für Linktitel) </option>
                    <option value="textarea" '.$textarea.'> Eingabefeld (Mehrzeilig) </option>
                    <option value="downloadlink" '.$downloadlink.'> Link-, Downloadfeld </option>';

    return $choose_fields;
  }
}

if (!function_exists('getExtraFields')) {
  function getExtraFields($cms_var,$type,$value){
    global $dirs;

    switch($type){
      case 'downloadlink':
        $extras[0]['value']=true;
        $extras[0]['text'] ='mit Piktogramm';
        $extras[1]['value']=false;
        $extras[1]['text'] ='ohne Piktogramm';

        $ret= '<option value="0">  --bitte wählen-- </option> ';
        foreach ($extras as $extra){
          if ( $value == $extra['value']) {
            $ret.= '<option selected="selected" value="'.$extra['value'].'">'.$extra['text'].'</option>';
          } else {
            $ret.= '<option value="'.$extra['value'].'">'.$extra['text'].'</option>';
          }
        }
        break;
      case 'textarea':
        $extras[0]['value']=true;
        $extras[0]['text'] ='Extended-Text';

        $ret= '<option value="0">  --bitte wählen-- </option> ';
        foreach ($extras as $extra){
          if ( $value == $extra['value']) {
            $ret.= '<option selected="selected" value="'.$extra['value'].'">'.$extra['text'].'</option>';
          } else {
            $ret.= '<option value="'.$extra['value'].'">'.$extra['text'].'</option>';
          }
        }
        break;
    }
    return $ret;
  }
}

if (!function_exists('checkExtraFields')) {
  function checkExtraFields($type){
    if ($type=="downloadlink" OR $type=="textarea"){
      return true;
    }
    return false;
  }
}
?>
