<?php

/**
 * cntnd Util Class
 */
class CntndUtil {

  public static function escapeData($string){
    $specialchars = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $base64 = base64_encode($specialchars);
    return $base64;
  }

  public static function unescapeData($string,$decode_specialchars=true){
    $base64 = utf8_encode(base64_decode($string));
    if ($decode_specialchars){
      $base64 = htmlspecialchars_decode($base64, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    }
    $decode = json_decode($base64, true);
    return $decode;
  }

  public static function startsWith($haystack, $needle){
    return strncmp($haystack, $needle, strlen($needle)) === 0;
  }

  public static function endsWith($haystack, $needle){
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
  }

  public static function templates($module, $client){
    $cfgClient = cRegistry::getClientConfig();
    $templates = array();
    $template_dir   = $cfgClient[$client]["module"]["path"].$module.'/template/';
    $handle         = opendir($template_dir);
    while ($entryName = readdir($handle)){
      if (is_file($template_dir.$entryName) && !CntndUtil::startsWith($entryName, "_")){
        $templates[]=$entryName;
      }
    }
    closedir($handle);
    asort($templates);

    return $templates;
  }

  public static function isTemplate($module, $client, $template){
    $cfgClient = cRegistry::getClientConfig();
    $template_file   = $cfgClient[$client]["module"]["path"].$module.'/template/'.$template;

    if (!empty($template) && CntndUtil::endsWith($template, ".html")){
      if (file_exists($template_file)){
        return true;
      }
    }
    return false;
  }
}

?>
