<?php

namespace Cntnd\DynList;

/**
 * cntnd_list Input Class
 */
class CntndListInput {

  public static function getChooseFields($field,$value){
    $internal="";
    $no_fields=array("{\$id}","{\$icon}","{\$img_over}","{\$img_icon}","{\$target}","{javascript}");

    if (in_array($field,$no_fields) OR substr($field,0,3)=="{\$_"){
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
                    <option value="dropdown" '.$dropdown.'> Dropdown </option>
                    <option value="downloadlink" '.$downloadlink.'> Link-, Downloadfeld </option>
                    <option value="url" '.$url.'> URL (Webseite, Bild, Dokument, Link, etc.)</option>
                    <option value="image" '.$image.'> Bild / Bilderstreifen (jedes Bild auswählen) </option>
                    <option value="gallery" '.$gallery.'> Bildergalerie aus Ordner </option>';

    return $choose_fields;
  }

  public static function isExtraField($type){
    return ($type=="downloadlink" OR $type=="text" OR $type=="textarea" OR $type=="url" OR $type=="image" OR $type=="gallery" OR $type=="dropdown");
  }

  public static function getExtraFields($type,$value){
    switch($type){
      case 'downloadlink':
        $extras[0]['value']=true;
        $extras[0]['text'] ='mit Piktogramm';
        $extras[1]['value']=false;
        $extras[1]['text'] ='ohne Piktogramm';
        break;
      case 'text':
      case 'dropdown':
        $extras[0]['value']='plain';
        $extras[0]['text'] ='nur Text, keine div-Tags';
        break;
      case 'textarea':
        $extras[0]['value']='extended';
        $extras[0]['text'] ='Extended-Text';
        $extras[1]['value']='markdown';
        $extras[1]['text'] ='Markdown';
        $extras[2]['value']='plain';
        $extras[2]['text'] ='nur Text, keine div-Tags';
        break;
      case 'url':
        $extras[0]['value']='documents';
        $extras[0]['text'] ='Dokumente';
        $extras[1]['value']='images';
        $extras[1]['text'] ='Bilder';
        break;
      case 'image':
        $extras[0]['value']='comment';
        $extras[0]['text'] ='mit Kommentaren';
        $extras[1]['value']='gallery';
        $extras[1]['text'] ='Bilderstreifen mit Kommentaren';
        break;
      case 'gallery':
        $extras[0]['value']='link';
        $extras[0]['text'] ='nur Link anzeigen';
        $extras[1]['value']='thumbnail';
        $extras[1]['text'] ='nur Vorschaubild anzeigen';
        $extras[2]['value']='galleryonly';
        $extras[2]['text'] ='nur Fotogalerie';
        break;
    }
    $ret= '<option value="0">  --bitte wählen-- </option> ';
    foreach ($extras as $extra){
      if ($value == $extra['value']) {
        $ret.= '<option selected="selected" value="'.$extra['value'].'">'.$extra['text'].'</option>';
      } else {
        $ret.= '<option value="'.$extra['value'].'">'.$extra['text'].'</option>';
      }
    }
    return $ret;
  }

  public static function hasOptionalField($type){
    return ($type=="gallery" OR $type=="image" OR $type=="downloadlink" OR $type=="dropdown" OR $type=="url");
  }

  public static function getOptionalFields($uuid,$optional,$type,$values,$client){
    $ret = "";
    switch($type){
      case 'downloadlink':
        $optionals[0]['value']='after';
        $optionals[0]['text'] ='Piktogramme nach dem Linktitel (Standard)';
        $optionals[1]['value']='before';
        $optionals[1]['text'] ='Piktogramme vor dem Linktitel';
        $ret = self::renderOptionalSelect($uuid,"Piktogramme",$optional,$optionals,$values);
        $ret.= self::renderOptionalSelect($uuid,"Ordner einschränken",$optional,self::optionalsFolder($client),$values,1);
        break;
      case 'gallery':
        $optionals[0]['value']='comment';
        $optionals[0]['text'] ='mit Bildlegende (.txt Datei im Ordner)';
        $ret = self::renderOptionalSelect($uuid,"Bildlegende",$optional,$optionals,$values);
        $ret.= self::renderOptionalSelect($uuid,"Ordner einschränken",$optional,self::optionalsFolderImages($client),$values,1);
        break;
      case 'image':
        $ret = self::renderOptionalSelect($uuid,"Ordner einschränken",$optional,self::optionalsFolderImages($client),$values);
        break;
      case 'url':
        $ret = self::renderOptionalSelect($uuid,"Ordner einschränken",$optional,self::optionalsFolder($client),$values);
        break;
      case 'dropdown':
        $ret = self::renderOptionalTextarea(
            $uuid,
            "Dropdown Werte",
            "Werte Kommagetrennt eintragen ohne Leerschlag oder neue Zeile",
            $optional,
            $values);
        break;
    }
    return $ret;
  }

  private static function renderOptionalSelect($uuid, $label, $optional, $optionals, $values, $index=0) {
    $id = $optional . '['.$index.']';
    $value = $values[$id];
    $ret = '<div class="form-group">';
    $ret.= '<label for="' . $id . '">'.$label.'</label>';
    $ret.= '<select data-uuid="' . $uuid . '" name="' . $id . '" id="optional">';
    $ret.= '<option value="0">  --bitte wählen-- </option> ';
    foreach ($optionals as $option){
      if ($value == $option['value']) {
        $ret.= '<option selected="selected" value="'.$option['value'].'">'.$option['text'].'</option>';
      } else {
        $ret.= '<option value="'.$option['value'].'">'.$option['text'].'</option>';
      }
    }
    $ret.= '</select>';
    $ret.= '</div>';
    return $ret;
  }

  private static function renderOptionalText($uuid, $label, $info, $optional, $values, $index=0) {
    $id = $optional . '['.$index.']';
    $value = $values[$id];
    $ret = '<div class="form-group">';
    $ret.= '<div class="form-group">';
    $ret.= '<label for="' . $id . '">'.$label.'</label>';
    $ret.= '<input data-uuid="' . $uuid . '" name="' . $id . '" id="optional" type="text" value="'.$value.'"/>';
    if (!empty($info)) {
      $ret .= '<small>' . $info . '</small>';
    }
    $ret.= '</div>';
    return $ret;
  }

  private static function renderOptionalTextarea($uuid, $label, $info, $optional, $values, $index=0) {
    $id = $optional . '['.$index.']';
    $value = $values[$id];
    $ret = '<div class="form-group">';
    $ret.= '<div class="form-group">';
    $ret.= '<label for="' . $id . '">'.$label.'</label>';
    $ret.= '<textarea data-uuid="' . $uuid . '" name="' . $id . '" id="optional">'.$value.'</textarea>';
    if (!empty($info)) {
      $ret .= '<small>' . $info . '</small>';
    }
    $ret.= '</div>';
    return $ret;
  }

  private static function optionalsFolderImages($client) {
    return self::optionalsFolder($client,"'jpeg','jpg','gif`','png'");
  }

  private static function optionalsFolderDocuments($client) {
    return self::optionalsFolder($client,"'pdf','docx','doc','xlsx','xls'");
  }

  private static function optionalsFolder($client,$types='') {
    $filetypes = '';
    if (!empty($types)) {
      $filetypes = " AND filetype IN ($types) ";
    }
    $db = new \cDb;
    $cfg = \cRegistry::getConfig();

    $sql = "SELECT DISTINCT dirname FROM :table WHERE idclient=:idclient $filetypes ORDER BY dirname ASC";
    $values = array(
        'table' => $cfg['tab']['upl'],
        'idclient' => \cSecurity::toInteger($client)
    );

    $db->query($sql, $values);
    $dirs = array();
    $i=0;
    while ($db->nextRecord()) {
      $dirname = $db->f('dirname');
      $dirs[$i]['value'] = $dirname;
      $dirs[$i]['text'] = $dirname;
      $i++;
    }
    return $dirs;
  }
}
?>
