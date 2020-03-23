<?php
/**
 * cntnd_list Util Class
 */
class CntndListUtil {

  public static function escapeData($string){
    $specialchars = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $base64 = base64_encode($specialchars);
    return $base64;
  }

  public static function unescapeData($string){
    $base64 = utf8_encode(base64_decode($string));
    $specialchars = htmlspecialchars_decode($base64, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $decode = json_decode($specialchars, true);
    return $decode;
  }
}

?>
