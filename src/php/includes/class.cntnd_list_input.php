<?php

/**
 * cntnd_list Input Class
 */
class CntndListInput {

  public static function getChooseFields($field,$value){
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
                    <option value="titel" '.$titel.'> Titel (Einzeilig) </option>
                    <option value="text" '.$text.'> Eingabefeld (Einzeilig) </option>
                    <option value="textarea" '.$textarea.'> Eingabefeld (Mehrzeilig) </option>
                    <option value="plain" '.$plain.'> Eingabefeld (Plaintext) </option>
                    <option value="linktext" '.$linktext.'> Eingabefeld (für Linktitel) </option>
                    <option value="downloadlink" '.$downloadlink.'> Link-, Downloadfeld </option>
                    <option value="url" '.$url.'> URL (Webseite, Bild, Dokument, Link, etc.)</option>
                    <option value="image" '.$image.'> Bild / Bilderstreifen (jedes Bild auswählen) </option>
                    <option value="gallery" '.$gallery.'> Bildergalerie aus Ordner </option>';

    return $choose_fields;
  }

  public static function isExtraField($type){
    return ($type=="downloadlink" OR $type=="textarea" OR $type=="url" OR $type=="image" OR $type=="gallery");
  }

  public static function getExtraFields($type,$value,$dirs){
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
      case 'url':
          $extras[0]['value']='documents';
          $extras[0]['text'] ='Dokumente';
          $extras[1]['value']='images';
          $extras[1]['text'] ='Bilder';

          $ret= '<option value="0">  --bitte wählen-- </option> ';
          foreach ($extras as $extra){
            if ( $value == $extra['value']) {
              $ret.= '<option selected="selected" value="'.$extra['value'].'">'.$extra['text'].'</option>';
            } else {
              $ret.= '<option value="'.$extra['value'].'">'.$extra['text'].'</option>';
            }
          }
          break;
      case 'image':
          $extras[0]['value']='comment';
          $extras[0]['text'] ='mit Kommentaren';
          $extras[1]['value']='gallery';
          $extras[1]['text'] ='Bilderstreifen mit Kommentaren';

          $ret= '<option value="0">  --bitte wählen-- </option> ';
          foreach ($extras as $extra){
            if ( $value == $extra['value']) {
              $ret.= '<option selected="selected" value="'.$extra['value'].'">'.$extra['text'].'</option>';
            } else {
              $ret.= '<option value="'.$extra['value'].'">'.$extra['text'].'</option>';
            }
          }
          break;
        case 'gallery':
            $extras[0]['value']='link';
            $extras[0]['text'] ='nur Link anzeigen';
            $extras[1]['value']='thumbnail';
            $extras[1]['text'] ='nur Vorschaubild anzeigen';
            $extras[2]['value']='galleryonly';
            $extras[2]['text'] ='nur Fotogalerie';

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



?>
