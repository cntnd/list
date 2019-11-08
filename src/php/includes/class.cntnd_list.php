<?php
class cntndDynList {

  protected  $medien=array();
  protected  $images=array();
  protected  $folders=array();

  function __construct($index) {
    global $edit, $client, $cfg, $cfgClient, $idart, $lang;

    $this->db = new cDb;
    $this->tpl = cSmartyFrontend::getInstance();
    $this->serialization();
    $this->index = $index;

    // prüfen ob liste bereits vorhanden oder nicht
    $this->db->query("SELECT idlist FROM cntnd_dynlist WHERE listname='".$this->index."' AND idart = ".$idart." AND idlang = ".$lang);
    if (!$this->db->nextRecord()){
        $this->db->query("INSERT INTO cntnd_dynlist (listname, idart, idlang) VALUES ('".$this->index."',".$idart.",".$lang.")");
    }

    // Dateien aus dem Dateisystem lesen
    $this->db->query("SELECT * FROM %s WHERE idclient=%d AND filetype IN ('pdf','docx','doc','xlsx','xls') ORDER BY dirname ASC, filename ASC",$cfg['tab']['upl'],$client);
    while ($this->db->nextRecord()) {
      if (!empty($this->db->f('filetype'))){
        $this->medien[$this->db->f('idupl')] = array ('idupl' => $this->db->f('idupl'), 'filename' => $this->db->f('dirname').$this->db->f('filename'), 'dirname' => $this->db->f('dirname'));
      }
    }

    // Bilder und Ordner mit Bildern aus dem Dateisystem lesen
    $this->db->query("SELECT * FROM %s WHERE idclient=%d AND filetype IN ('jpeg','jpg','gif','png') ORDER BY dirname ASC, filename ASC",$cfg['tab']['upl'],$client);
    while ($this->db->nextRecord()) {
      // Bilder
      $this->images[$this->db->f('idupl')] = array('idupl' => $this->db->f('idupl'), 'filename' => $this->db->f('dirname').$this->db->f('filename'), 'dirname' => $this->db->f('dirname'));
      // Ordner
      if ($prev_dir!=$this->db->f('dirname')){
        $this->folders[$this->db->f('idupl')] = array('idupl' => $this->db->f('idupl'), 'dirname' => $this->db->f('dirname'));
      }
      $prev_dir = $this->db->f('dirname');
    }
  }
}
?>
