<?php
/**
 * cntnd_list Class
 */
class CntndList {

  private $idart;
  private $lang;
  private $client;
  private $listname;
  private $db;
  private $tpl;
  private $uploadDir;

  protected $medien=array();
  protected $images=array();
  protected $folders=array();

  function __construct($idart, $lang, $client, $listname) {
    $this->idart = $idart;
    $this->lang = $lang;
    $this->listname = $listname;
    $this->client = $client;
    $this->db = new cDb;
    $this->tpl = new Template();

    $cfgClient = cRegistry::getClientConfig();
    $this->uploadDir = $cfgClient[$client]["upl"]["htmlpath"];

    // medien, images, folders
    $cfg = cRegistry::getConfig();
    $mediatypes=array('pdf','docx','doc','xlsx','xls');
    $imagetypes=array('jpeg','jpg','gif','png');

    $sql = "SELECT * FROM :table WHERE idclient=:idclient ORDER BY dirname ASC, filename ASC";
    $values = array(
        'table' => $cfg['tab']['upl'],
        'idclient' => cSecurity::toInteger($client)
    );
    $this->db->query($sql, $values);
    while ($this->db->nextRecord()) {
      // Medien
      if (in_array($this->db->f('filetype'),$mediatypes)){
        $this->medien[$this->db->f('idupl')] = array ('idupl' => $this->db->f('idupl'), 'filename' => $this->db->f('dirname').$this->db->f('filename'), 'dirname' => $this->db->f('dirname'));
      }

      // Bilder
      if (in_array($this->db->f('filetype'),$imagetypes)){
        $this->images[$this->db->f('idupl')] = array ('idupl' => $this->db->f('idupl'), 'filename' => $this->db->f('dirname').$this->db->f('filename'), 'dirname' => $this->db->f('dirname'));
        // Ordner
        if ($prev_dir!=$this->db->f('dirname')){
            $this->folders[$this->db->f('idupl')] = array ('idupl' => $this->db->f('idupl'), 'dirname' => $this->db->f('dirname'));
        }
        $prev_dir = $this->db->f('dirname');
      }
    }
  }

  public function medien(){
    return $this->medien;
  }

  public function images(){
    return $this->images;
  }

  public function folders(){
    return $this->folders;
  }

  public function load(){
    $data=[];

    $sql = "SELECT serializeddata FROM cntnd_dynlist WHERE listname=':listname' AND idart=:idart AND idlang=:idlang";
    $values = array(
        'listname' => $this->listname,
        'idart' => cSecurity::toInteger($this->idart),
        'idlang' => cSecurity::toInteger($this->lang)
    );
    $this->db->query($sql, $values);
    while ($this->db->nextRecord()) {
      if (is_string($this->db->f('serializeddata'))){
        $data = $this->unescapeDate($this->db->f('serializeddata'));
      }
    }
    return $data;
  }

  public function store($data){
    $values = array(
        'listname' => $this->listname,
        'idart' => cSecurity::toInteger($this->idart),
        'idlang' => cSecurity::toInteger($this->lang),
        'data' => $this->escapeDate($data)
    );
    $this->db->query("SELECT idlist FROM cntnd_dynlist WHERE listname=':listname' AND idart=:idart AND idlang=:idlang", $values);
    if (!$this->db->nextRecord()){
        $sql = "INSERT INTO cntnd_dynlist (listname, idart, idlang, serializeddata) VALUES (':listname',:idart,:idlang,':data')";
    }
    else {
        $sql = "UPDATE cntnd_dynlist SET serializeddata=':data' WHERE listname=':listname' AND idart=:idart AND idlang=:idlang";
    }
    $this->db->query($sql, $values);
  }
  

  private function escapeDate($string){
    $specialchars = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $base64 = base64_encode($specialchars);
    return $base64;
  }

  private function unescapeDate($string){
    $base64 = base64_decode($string);
    $specialchars = htmlspecialchars_decode($base64, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $decode = json_decode($specialchars, true);
    return $decode;
  }

  public function update($action, $index, $data, $values){
    if ($action=='delete'){
      unset($values[$index]);
    }
    elseif ($action=='update') {
      foreach ($values[$index] as $key => $value) {
        foreach ($value as $valueKey => $valueValue) {
          $values[$index][$key][$valueKey]=$data["data[$index][$this->listname][$key][$valueKey]"];
        }
      }
    }
    $serializeddata = json_encode($values);
    $this->store($serializeddata);
    return $values;
  }

  public function reorder($data, $values){
    if (is_array($data)){
      $reordered=[];
      foreach ($data as $value) {
        $reordered[$value['new']]=$values[$value['old']];
      }
      $serializeddata = json_encode($reordered);
      $this->store($serializeddata);
      return $reordered;
    }
    return $values;
  }

  public function render($template, $values, $data){
    $this->tpl->reset();
    if (is_array($values)){
      foreach ($values as $key => $value) {
        $index=0;
        foreach ($value as $name => $field) {
          $extra = 'data['.$index.'][extra]';
          $this->renderField(self::tplName($name), $field, $data[$extra]);
          $index++;
        }
        $this->tpl->next();
      }
    }
    $this->tpl->generate($template);
  }

  private function renderField($name, $field, $extra){
    switch($field['type']){
      case 'downloadlink':
          $this->doDownloadLinkField($name, $field, $extra);
          break;
      case 'url':
          $this->doUrlField($name, $field, $extra);
          break;
      case 'image':
          $this->doImageField($name, $field, $extra);
          break;
      case 'gallery':
          $this->doGalleryField($name, $field, $extra);
          break;
      case 'linktext':
          $this->doLinkField($name, $field);
          break;
      case 'titel':
          $this->doTitleField($name, $field);
          break;
      case 'plain':
          $this->doPlainField($name, $field);
          break;
      case 'text':
      case 'textarea':
          $this->doField($name, $field, $extra);
          break;
    }
  }

  private static function tplName($name){
    return str_replace(array("{","}"),"",$name);
  }

  private function doGalleryField($name,$field,$extra){
    if (!empty($field['value'])){
      $gallery = "";
      $galleryId = 'gallery'.rand(100,999);
      $cfg = cRegistry::getConfig();
      $dirname = $this->folders[$field['value']]['dirname'];
      $this->db->query("SELECT filename FROM ".$cfg["tab"]["upl"]." WHERE dirname = '".$dirname."' AND filetype != '' ORDER BY filename ");
      while ($this->db->nextRecord()) {
      	$file = $this->db->f('filename');
      	if (!empty($file)){
          if (!empty($extra) && $extra!="0"){
					  $pictures .= "{src:'".$this->uploadDir.$dirname.$file."'},";
          }
          else {
            $gallery .= $this->doImage($this->uploadDir.$dirname.$file,
                                       $galleryId,
                                       $this->uploadDir.$dirname.'thumb/'.$file);
          }
				}
			}

      if (!empty($extra) && $extra!="0"){
        $trigger = $galleryId;
        $gallery = $galleryId;
        if ($extra=="link"){
          $trigger.=" > .cntnd_link";
          $link = '<a href="javascript:;" class="'.$this->listname.' cntnd_link cntnd_gallery">'.$field['link'].'</a>';
          $this->tpl->set('d', '_'.$name.'_link', $link);
        }
        else if ($extra=="thumbnail"){
          $thumbnail = '<img src="'.$this->uploadDir.$this->images[$field['thumbnail']]['filename'].'" class="'.$this->listname.' cntnd_img cntnd_gallery" />';
          $this->tpl->set('d', '_'.$name.'_thumbnail', $thumbnail);
        }

        $javascript ='<script language="javascript" type="text/javascript">
                			<!--
                			$(document).ready(function() {
                				$("#'.$trigger.'").click(function() {
                					$.fancybox.open(['.substr($pictures, 0, -1).']);
                				});
                			});
                			-->
                			</script>';
        $this->tpl->set('d', '_'.$name.'_js', $javascript);
      }
      $this->tpl->set('d', $name, $gallery);
    }
    else {
      $this->tpl->set('d', '_'.$name.'_js', "");
      $this->tpl->set('d', '_'.$name.'_link', "");
      $this->tpl->set('d', '_'.$name.'_thumbnail', "");
      $this->tpl->set('d', $name, "");
    }
  }

  private function doImage($image,$gallery='',$thumb='',$comment=''){
    $caption='';
    if (!empty($comment)){
      $caption = 'data-caption="'.$comment.'"';
    }
    if (empty($thumb) || !file_exists($thumb)){
      $thumb = $image;
    }
    $fancybox='data-fancybox';
    if (!empty($gallery)){
      $fancybox='data-fancybox="'.$gallery.'"';
    }
    $input = '<a href="'.$image.'" '.$fancybox.' '.$caption.' class="'.$this->listname.' cntnd_gallery">'."\n";
    $input.= '<img src="'.$thumb.'" class="'.$this->listname.' cntnd_img" alt="'.$comment.'" />'."\n";
    $input.= '</a>'."\n";
    return $input;
  }

  private function doImageField($name,$field,$extra){
    if (!empty($field['value'])){
      $image = $this->uploadDir.$this->images[$field['value']]['filename'];
      if (empty($extra) || $extra=='comment'){
        $alt="";
        if (!empty($extra)){
          $alt = $field['comment'];
        }
        $img = '<img src="'.$image.'" class="'.$this->listname.' cntnd_img" alt="'.$alt.'" />';
      }
      else if ($extra=='gallery') {
        $img = $this->doImage($image,$this->listname,"",$field['comment']);
      }
      $this->tpl->set('d', $name, $img);
    }
    else {
      $this->tpl->set('d', $name, "");
    }
  }

  private function doUrlField($name,$field,$extra){
    if (!empty($field['value'])){
      $list = $this->medien;
      if ($extra=='images'){
        $list = $this->images;
      }

      if ($field['value']==111111111){
        $link = $field['link'];
      }
      else if ($field['value']==222222222){
        $link = "front_content.php?idart=".$field['link'];
      }
      else {
        $filename = $list[$field['value']]['filename'];
        $link = $this->uploadDir.$filename;
      }
      $this->tpl->set('d', $name, $link);
      if (!empty($field['target']) && $field['target']!="0"){
        $this->tpl->set('d', '_'.$name.'_target', $field['target']);
      }
    }
    else {
      $this->tpl->set('d', $name, "");
    }
  }

  private function doField($name,$field,$extra=false){
    if (!empty($field['value'])){
      // Extended > - lorem = List, etc.
      if ($extra){
        $arr = explode("\n", $field['value']);
        $text = '<ul>';
        foreach($arr as $value){
          $text .= '<li>'.$value.'</li>';
        }
        $text .= '</ul>';
      }
      else {
        $text = $field['value'];
      }
        $this->tpl->set('d', $name, '<div class="'.$this->listname.' cntnd_text">'.stripslashes($text).'</div>');
    }
    else {
        $this->tpl->set('d', $name, "");
    }
  }

  private function doPlainField($name,$field){
    if (!empty($field['value'])){
        $this->tpl->set('d', $name, $field['value']);
    }
    else {
        $this->tpl->set('d', $name, "");
    }
  }

  private function doTitleField($name, $field){
    if (!empty($value['value'])){
        $this->tpl->set('d', $name, '<h2 class="'.$this->listname.' cntnd_title">'.stripslashes($field['value']).'</h2>');
    }
    else {
        $this->tpl->set('d', $name, "");
    }
  }

  private function doLinkField($name, $field){
    if (!empty($field['value'])){
      $this->tpl->set('d', $name, '<span class="'.$this->listname.' cntnd_linktext">'.stripslashes($field['value']).'</span>');
      if (!empty($field['target']) && $field['target']!="0"){
        $this->tpl->set('d', '_'.$name.'_target', $field['target']);
      }
    }
    else {
      $this->tpl->set('d', $name, "");
    }
  }

  private function doDownloadLinkField($name, $field, $extra=false){
    if (!empty($field['value']) AND $field['value']!=0){
      $target="_self";
      if ($field['value']!=999999999 AND $field['value']!=111111111 AND $field['value']!=222222222){
          $filename = $this->medien[$field['value']]['filename'];
          $link = $this->uploadDir.$filename;
          $icon = substr($filename,strrpos($filename,".")+1);
          $target="_blank";
      }
      if ($field['value']==111111111){
          $link = $field['link'];
          $icon = "link";
          $target="_blank";
      }
      if ($field['value']==222222222){
          $link = "front_content.php?idart=".$field['link'];
          $icon = "linkintern";
      }
      $pikto='';
      if ($field['value']!=999999999 && $extra){
        $pikto = 'pikto-after pikto--'.self::getLinkIcon($icon);
      }

      if (!empty($field['target']) && $field['target']!="auto"){
        $target = $field['target'];
      }

      $link_tag = '<a class="'.$this->listname.' cntnd_link '.$pikto.'" href="'.$link.'" target="'.$target.'">';
      $this->tpl->set('d', $name, $link_tag);
      $this->tpl->set('d', "_".$name."_end", '</a>');
    }
    else {
      $this->tpl->set('d', $name, "");
      $this->tpl->set('d', "_".$name."_end", '');
    }
  }

  private static function getLinkIcon($icon){
    switch ($icon){
      case 'doc':
      case 'docx':
      case 'dot':
      case 'dotx':
              return "word";
      case 'xls':
      case 'xlsx':
              return "excel";
      case 'pdf':
              return "pdf";
      case 'ppt':
      case 'pptx':
      case 'pps':
      case 'ppsx':
              return "powerpoint";
      case 'qt':
      case 'avi':
      case 'mpeg':
              return "video";
      case 'zip':
              return "zip";
      case 'link':
              return "link";
      case 'linkintern':
              return "link-intern";
      default:
              return "default";
    }
  }

  public function doSortable(){
    echo '<script>'."\n";
    echo 'function onReordering(uuid){
            var order=[];
            $("#cntnd_list_items-"+uuid+" .listitem").each(function(index){
              order.push({new:index,old:$(this).data("order")});
            });
            $("#ENTRY_"+uuid+" input[name=action]").val("reorder");
            $("#ENTRY_"+uuid+" input[name=reorder]").val(window.btoa(JSON.stringify(order)));
            $("#ENTRY_"+uuid+" button[type=submit]").removeClass("hide");
          };'."\n";
    echo "var elements_$this->listname = document.getElementById('cntnd_list_items-$this->listname');\n";
    echo "Sortable.create(elements_$this->listname, { draggable: '.listitem', onEnd: function(){ onReordering('$this->listname') }});\n";
    echo '</script>'."\n";
  }
}

?>
