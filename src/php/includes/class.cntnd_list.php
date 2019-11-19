<?php


/**
 * cntnd_list Class
 */
class CntndList {

  private $idart;
  private $lang;
  private $listname;
  private $db;
  private $tpl;

  protected $medien=array();
  protected $images=array();
  protected $folders=array();

  function __construct($idart, $lang, $client, $listname) {
    $this->idart = $idart;
    $this->lang = $lang;
    $this->listname = $listname;
    $this->db = new cDb;
    $this->tpl = new Template();

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
          $data = json_decode(base64_decode($this->db->f('serializeddata')), true);
      }
    }

    return $data;
  }

  public function store($data){
    $values = array(
        'listname' => $this->listname,
        'idart' => cSecurity::toInteger($this->idart),
        'idlang' => cSecurity::toInteger($this->lang),
        'data' => $this->db->escape(base64_encode($data))
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

  public function render($template, $data){
    $this->tpl->reset();
    if (is_array($data)){
      foreach ($data as $key => $value) {
        foreach ($value as $name => $field) {
          $this->renderField(self::tplName($name), $field);
        }
        $this->tpl->next();
      }
    }
    $this->tpl->generate($template);
  }

  private function renderField($name, $field){
    switch($field['type']){
      case 'linktext':
          $this->doLinkField($name, $field);
          break;
      case 'downloadlink':
          $this->doDownloadLinkField($name, $field);
          break;
      /*
        case 'titel':
            $this->doTitelField($this->tplName($field['name']),$value);
            break;
        case 'plain':
            $this->doPlainField($this->tplName($field['name']),$value);
            break;
        case 'text':
        case 'textarea':
            $this->doField($this->tplName($field['name']),$value,$field['extra']);
            break;
            */
    }
  }

  private static function tplName($name){
    return str_replace(array("{","}"),"",$name);
  }

  private function doLinkField($name, $field){
    if (!empty($field['value'])){
      $this->tpl->set('d', $name, '<span class="'.$this->listname.' cntnd_linktext">'.stripslashes($field['value']).'</span>');
    }
    else {
      $this->tpl->set('d', $name, "");
    }
  }

  private function doDownloadLinkField($name, $field, $icons=true){
    if (!empty($field['value']) AND $field['value']!=0){
      $link_tag = '<a class="'.$this->listname.' cntnd_link" href="'.$link.'" target="'.$target.'">';
      $this->tpl->set('d', $name, $link_tag);
      $this->tpl->set('d', "_".$name."_end", '</a>');
    }
    else {
      $this->tpl->set('d', $name, "");
      $this->tpl->set('d', "_".$name."_end", '');
    }
  }
}

?>
