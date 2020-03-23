<?php
/**
 * cntnd_list Util Class
 */
class CntndListUtil {

  private static function escapeData($string){
    $specialchars = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $base64 = base64_encode($specialchars);
    return $base64;
  }

  private static function unescapeData($string){
    $base64 = base64_decode($string);
    $specialchars = htmlspecialchars_decode($base64, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $decode = json_decode($specialchars, true);
    return $decode;
  }
}

?>
