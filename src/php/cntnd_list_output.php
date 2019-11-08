<?php
/*
CREATE TABLE IF NOT EXISTS `cntnd_dynlist` (
  `idlist` int(11) NOT NULL AUTO_INCREMENT,
  `listname` varchar(200) NOT NULL,
  `idart` int(11) NOT NULL,
  `idlang` int(11) NOT NULL,
  `serializeddata` longtext,
  PRIMARY KEY (`idlist`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
$sql_create = "
CREATE TABLE IF NOT EXISTS cntnd_dynlist (
  idlist int(11) NOT NULL AUTO_INCREMENT,
  listname varchar(200) NOT NULL,
  idart int(11) NOT NULL,
  idlang int(11) NOT NULL,
  serializeddata longtext,
  PRIMARY KEY (idlist)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;
";
if (!empty($sql_create)){
 $dbC = new DB_Contenido;
 $dbC->query($sql_create);
 echo mysql_error();
}
 *
 **/

$listname = "CMS_VALUE[1]";
$template = "CMS_VALUE[2]";
$object = json_decode(base64_decode("CMS_VALUE[3]"), true);
$data=array();
foreach ($object as $item) {
	$key = key($item);
	$value = $item[$key];
	$data[$key]=$value;
}
$cms_var  = "CMS_VALUE[4]";

$editmode = false;
if($contenido&&($view=="edit")){
    $editmode = true;
}

if ($editmode){
	echo '<hr /><p />Dynamische Liste:<div class="dyn-list">';
}
cInclude('includes', 'class.template.php');
cInclude("includes", "functions.upl.php");

if (!class_exists('cntndDynList')) {
   class cntndDynList {

      protected  $medien=array();
      protected  $images=array();
      protected  $folders=array();

      function __construct($index) {

         global $edit, $client, $cfg, $cfgClient, $idart, $lang;

         // Member aufnehmen
         $this->edit = $edit;
         $this->index = 'var_'.$index;

         // error
         $this->errorMsg="";

         if (!empty($_SESSION['IDART'])){
             //echo "<p>SESSION SET!</p>";
             $this->idart = $_SESSION['IDART'];
         }
         else {
             $this->idart = $idart;
         }
         $this->lang = $lang;

         // Initialisierung des Datenbankzugriffs

         $this->db = new cDb;

         // Initialisierung des Templates
         $this->tpl = new Template();

         // prüfen ob liste bereits vorhanden oder nicht

         $this->db->query("SELECT idlist FROM cntnd_dynlist WHERE listname='".$this->index."' AND idart = ".$this->idart." AND idlang = ".$this->lang);
         if (!$this->db->nextRecord()){
             $this->db->query("INSERT INTO cntnd_dynlist (listname, idart, idlang) VALUES ('".$this->index."',".$this->idart.",".$this->lang.")");
         }

         // CMS_VALUE als Member aufnehmen
         $this->serialization();

		 $this->socialmedia=array(
								array(
									"code" => "twitter",
									"name" => "Twitter"),
								array(
									"code" => "facebook",
									"name" => "Facebook"),
								array(
									"code" => "google",
									"name" => "Google+"),
								array(
									"code" => "youtube",
									"name" => "YouTube"),
								array(
									"code" => "instagram",
									"name" => "Instagram")
							);
          // TODO FILES!!!
          // alle arrays aufnehmen
          /*
          $this->medien=array();
          $this->images=array();
          $this->folders=array();
          */
          // Dateien aus dem Dateisystem lesen
		  //$this->db->query("SELECT * FROM %s WHERE idclient=%d AND (dirname LIKE concat('pdf','/') OR dirname LIKE concat('excel','/') OR dirname LIKE concat('word','/') OR dirname LIKE concat('files','/')) ORDER BY dirname ASC, filename ASC",$cfg['tab']['upl'],$client);

          //$this->db->query("SELECT * FROM %s WHERE idclient=%d AND (dirname LIKE %d OR dirname LIKE %d OR dirname LIKE %d OR dirname LIKE %d) ORDER BY dirname ASC, filename ASC",$cfg['tab']['upl'],$client,'pdf%','excel%','word%','files');

		  //$this->db->query("SELECT * FROM ".$cfg['tab']['upl']." WHERE idclient=".$client." AND (dirname LIKE concat('pdf','/%') OR dirname LIKE concat('excel','/%') OR dirname LIKE concat('word','/%') OR dirname LIKE concat('files','/%')) ORDER BY dirname ASC, filename ASC");
		  $this->db->query("SELECT * FROM %s WHERE idclient=%d AND filetype IN ('pdf','docx','doc','xlsx','xls') ORDER BY dirname ASC, filename ASC",$cfg['tab']['upl'],$client);

		  while ($this->db->nextRecord()) {
			  if (!empty($this->db->f('filetype'))){
              	$this->medien[$this->db->f('idupl')] = array ('idupl' => $this->db->f('idupl'), 'filename' => $this->db->f('dirname').$this->db->f('filename'), 'dirname' => $this->db->f('dirname'));
			  }
          }

          // Bilder und Ordner mit Bildern aus dem Dateisystem lesen
          //$this->db->query("SELECT * FROM %s WHERE idclient=%d AND (dirname LIKE concat('pdf','/') OR dirname LIKE concat('excel','/') OR dirname LIKE concat('word','/') OR dirname LIKE concat('files','/')) ORDER BY dirname ASC, filename ASC",$cfg['tab']['upl'],$client);

		  $this->db->query("SELECT * FROM %s WHERE idclient=%d AND filetype IN ('jpeg','jpg','gif','png') ORDER BY dirname ASC, filename ASC",$cfg['tab']['upl'],$client);
          while ($this->db->nextRecord()) {
              // Bilder
              $this->images[$this->db->f('idupl')] = array ('idupl' => $this->db->f('idupl'), 'filename' => $this->db->f('dirname').$this->db->f('filename'), 'dirname' => $this->db->f('dirname'));

              // Ordner
              if ($prev_dir!=$this->db->f('dirname')){
                  $this->folders[$this->db->f('idupl')] = array ('idupl' => $this->db->f('idupl'), 'dirname' => $this->db->f('dirname'));
              }
              $prev_dir = $this->db->f('dirname');
          }
      }

       function setMedien($medien){
        $this->medien = $medien;
       }
       function setImages($images){
           $this->images = $images;
       }
       function setFolders($folders){
           $this->folders = $folders;
       }

      function serialization() {
        global $cfg;

        $this->db->query("SELECT serializeddata FROM cntnd_dynlist WHERE listname='".$this->index."' AND idart = ".$this->idart." AND idlang = ".$this->lang);
        while ($this->db->nextRecord()) {
            if (is_string($this->db->f('serializeddata'))){
                $this->cmsValue = unserialize($this->db->f('serializeddata'));
            }
        }

        if ($_POST[$this->index.'_action']=="delete") {
            unset($this->cmsValue[$_POST['DYNLIST_delete']]);

            $serializedData = $this->db->escape(serialize($this->cmsValue));
            $this->db->query("UPDATE cntnd_dynlist SET serializeddata = '".$serializedData."' WHERE listname='".$this->index."' AND idart = ".$this->idart." and idlang = ".$this->lang);
        }
        else if ($_POST[$this->index.'_action']=="swap") {
            $this->cmsValue=$this->swap_array_elements($this->cmsValue ,$_POST['DYNLIST_swap_id1'], $_POST['DYNLIST_swap_id2']);

            $serializedData = $this->db->escape(serialize($this->cmsValue));
            $this->db->query("UPDATE cntnd_dynlist SET serializeddata = '".$serializedData."' WHERE listname='".$this->index."' AND idart = ".$this->idart." and idlang = ".$this->lang);
        }
        else {
             if (!empty($_POST[$this->index]) && $this->edit) {
                if (!$this->checkEmpty($_POST[$this->index]) AND $_POST[$this->index.'_action']=="save"){
                    $this->errorMsg="error";
                }
                else {
                    $this->cmsValue=$_POST[$this->index];
                    $check=current($_POST[$this->index][$_POST[$this->index.'_check']]);
                    if (empty($check['value'])){
                        unset($this->cmsValue[$_POST[$this->index.'_check']]);
                    }
                    $serializedData = $this->db->escape(serialize($this->cmsValue));
                    $this->db->query("UPDATE cntnd_dynlist SET serializeddata = '".$serializedData."' WHERE listname='".$this->index."' AND idart = ".$this->idart." and idlang = ".$this->lang);
                }
            }
        }
      }

      function checkEmpty($postCmsValue){
         $return=false;
         if (is_array($postCmsValue)){

             foreach (current($postCmsValue) as $field){
                // field - name, label, type, value
                if (!empty($field['value'])){
                    $return = true;
                }
             }
         }
         return $return;
      }

      function getRequestUri() {

         $returnValue = $_SERVER['PHP_SELF'];

         $start = true;
         if (!empty ($_GET)) {
            foreach ($_GET as $key => $value) {
               if ($key != 'moveUp' && $key != 'downloadlist') {
                  if ($start) {
                     $start = false;
                     $returnValue .= '?'.$key.'='.$value;
                  } else {
                     $returnValue .= '&'.$key.'='.$value;
                  }
               }
            }
         }
         // neu 20.08.2008---
         $returnValue .= '&downloadlist='.$this->index;


         return $returnValue;
      }

      function show($template) {
         if ($this->edit) {
           if (!$template OR empty($template)){
             echo "<br /><strong>Bitte in der Konfiguration das Modul-Template auswählen ansonsten wird die Liste nicht angezeigt.</strong>";
           }
           echo $this->edit();
         } else {
           $this->setMask($template);
           $this->showOutput();
         }
      }

      function edit() {

         global $cfg;
         $edit = '<div>';
         if ($this->errorMsg=="error"){ $edit .= '<div class="error">Bitte mindestens einen Wert eingeben</div>'; }
         $edit .= '<form id="DYNLIST_'.$this->index.'" name="DYNLIST_'.$this->index.'" action="'.$PHP_SELF.'" method="POST">
                      <table class="dyn" border="0" cellspacing="0" cellpadding="0" width="100%">';

		  //'.$this->getRequestUri().'

         // FOR----
         $count=count($this->cmsValue);
         $id=$this->index.$this->getKey();
         $first=true;

         foreach ($this->fields as $field){
            $edit.=$this->genField($field,$value,$id,$first);
            $first=false;
         }
         $edit.='<tr><td style="border-bottom: 2px solid black;">
                    <a href="javascript:speichern(\'DYNLIST_'.$this->index.'\',\''.$this->index.'_action\');" class="button">Speichern</a><p />
                 </td></tr>';

         //echo "<pre>"; var_dump($this->cmsValue);  echo "</pre>";
         $first=true;
         if (is_array($this->cmsData)){
             $first=false;
         }
         if (is_array($this->cmsValue)){
             foreach($this->cmsValue as $id => $row){
                 foreach ($this->fields as $field){
                    $edit.=$this->genField($field,$row[$field['name']],$id,$first);
                 }

                 $edit.='<tr><td style="border-bottom: 2px solid black;"><a href="javascript:document.getElementById(\'DYNLIST_'.$this->index.'\').submit();" class="button">Speichern</a>&nbsp;&nbsp;&nbsp;<a href="javascript:del(\'DYNLIST_'.$this->index.'\',\''.$this->index.'_action\',\''.$id.'\');" class="button">Löschen</a>';
                 if ($count>1 AND !$first){
                    $edit.='&nbsp;&nbsp;&nbsp;<a href="javascript:swap(\'DYNLIST_'.$this->index.'\',\''.$this->index.'_action\',\''.$id.'\',\''.$id_old.'\');" class="button">Nach oben verschieben</a>';
                 }
                 $edit.='</td></tr>';
                 $first=false;
                 $id_old=$id;
             }
         }

         $edit.= '    </table>
                    <p />
                    <input type="hidden" id="'.$this->index.'_action" name="'.$this->index.'_action" />
                    <input type="hidden" id="'.$this->index.'_check" name="'.$this->index.'_check" value="'.$this->index.$this->getKey().'" />
                    <input type="hidden" id="DYNLIST_delete" name="DYNLIST_delete" />
                    <input type="hidden" id="DYNLIST_swap_id1" name="DYNLIST_swap_id1" />
                    <input type="hidden" id="DYNLIST_swap_id2" name="DYNLIST_swap_id2" />
                    </form>
                  </div>';

         return $edit;
      }

      private function getKey(){
        if (is_array($this->cmsValue)){
            end((array_keys($this->cmsValue)));
            $last_key = key($this->cmsValue);
            $last_key++;
        }
        else {
            $last_key=0;
        }
        return $last_key;
      }

      private function swap_array_elements($rg ,$i1, $i2) {
          $erg1 = $rg[$i1];
          $rg[$i1] = $rg[$i2];
          $rg[$i2] = $erg1;
          return $rg;
      }

      function setMask($mask) {
         $this->mask = $mask;
      }

      function setField($name,$type,$label,$extra){
        $this->fields[]=array("name"=>$name, "type"=>$type, "label"=>$label, "extra"=>$extra);
      }

      function genField($field,$value,$id,$first=false){
        $first_id="";
        if ($first){
            $first_id='id="'.$this->index.'_check"';
        }
        switch($field['type']){
            case 'break':
				$genField.= '<tr><td>'.$field['label'].':<br />';
                $genField.= '<select '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value]">';
                $genField .= "<option value='0'>-- kein --</option>\n";

                ($value['value'] == "br") ? $sel = ' selected="selected" ' : $sel = '';
                $genField.= '<option value="br" '.$sel.'> einfacher Umbruch </option>';

                ($value['value'] == "p") ? $sel = ' selected="selected" ' : $sel = '';
                $genField.= '<option value="p" '.$sel.'> doppelter Umbruch </option>';

                ($value['value'] == "hr") ? $sel = ' selected="selected" ' : $sel = '';
                $genField.= '<option value="hr" '.$sel.'> horizontale Linie </option>';
                $genField.= '</select></td></tr>';
                break;
            case 'titel':
            case 'text':
            case 'linktext':
			           $genField.= '<tr><td>'.$field['label'].':<br /><input '.$first_id.' type="text" name="'.$this->index.'['.$id.']['.$field['name'].'][value]" class="text" value="'.$value['value'].'" /></td></tr>';
                break;
            case 'textarea':
                $genField.= '<tr><td>'.$field['label'].':<br /><textarea '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value]" class="text">'.$value['value'].'</textarea></td></tr>';
                break;
            case 'downloadlink':
                $genField .= '<tr><td>'.$field['label'].':<br /><select '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value]">';
                $genField .= "<option value='0'>-- kein --</option>\n";

                ($value['value'] == 999999999) ? $sel = ' selected="selected" ' : $sel = '';
                $genField .= "<option value='999999999' ".$sel."> -ohne Download/Link- </option>\n";

                ($value['value'] == 111111111) ? $sel = ' selected="selected" ' : $sel = '';
                $genField .= "<option value='111111111' ".$sel."> -Link- </option>\n";

                ($value['value'] == 222222222) ? $sel = ' selected="selected" ' : $sel = '';
                $genField .= "<option value='222222222' ".$sel."> -Link intern (idart=)- </option>\n";

                foreach ($this->medien as $medium) {
                   ($value['value'] == $medium['idupl']) ? $sel = ' selected="selected" ' : $sel = '';
                   $genField .= '<option value="'.$medium['idupl'].'" '.$sel.'>'.$medium['filename'].'</option>'."\n";
                }
                $genField .= '</select></td></tr>';
                $genField .= '<tr><td><i>Pfad (URL, idart):</i><br /><input class="text" type="text" name="'.$this->index.'['.$id.']['.$field['name'].'][link]" value="'.$value['link'].'" size="35" /></td></tr>';
                break;
            case 'image':
                $genField .= '<tr><td>'.$field['label'].':<br /><select '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value]">';
                $genField .= "<option value='0'>-- kein --</option>\n";

                foreach ($this->images as $medium) {
                  if (empty($field['extra']) OR (!empty($field['extra']) && $medium['dirname']==$field['extra'])){
                     ($value['value'] == $medium['idupl']) ? $sel = ' selected="selected" ' : $sel = '';
                     $genField .= '<option value="'.$medium['idupl'].'" '.$sel.'>'.$medium['filename'].'</option>'."\n";
                  }
                }
                $genField .= '</select></td></tr>';
                break;
            case 'gallery2':
			case 'gallery4':
			case 'gallery5':
                $genField .= '<tr><td>'.$field['label'].':<br /><select '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value]">';
                $genField .= "<option value='0'>-- kein --</option>\n";

                foreach ($this->folders as $medium) {
                   ($value['value'] == $medium['idupl']) ? $sel = ' selected="selected" ' : $sel = '';
                   $genField .= '<option value="'.$medium['idupl'].'" '.$sel.'>'.$medium['dirname'].'</option>'."\n";
                }
                $genField .= '</select><input type="hidden" name="'.$this->index.'['.$id.']['.$field['name'].'][viewer]" value="'.$id.'" /></td></tr>';
                break;
            case 'gallery':
            case 'gallery3':
                $genField .= '<tr><td>'.$field['label'].' - Kommentar:<br /><textarea '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value][kommentar]">'.$value['value']['kommentar'].'</textarea></td></tr>';
                $genField .= '<tr><td>'.$field['label'].' - Bild:<br /><select '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value][bild]">';
                $genField .= "<option value='0'>-- kein --</option>\n";

                foreach ($this->images as $medium) {
                   ($value['value']['bild'] == $medium['filename']) ? $sel = ' selected="selected" ' : $sel = '';
                   $genField .= '<option value="'.$medium['filename'].'" '.$sel.'>'.$medium['filename'].'</option>'."\n";
                }
                $genField .= '</select></td></tr>';
                break;
            case 'socialmedia':
                $genField.= '<tr><td>'.$field['label'].':<br /><select '.$first_id.' name="'.$this->index.'['.$id.']['.$field['name'].'][value]">';
                $genField .= "<option value='0'>-- kein --</option>\n";

                foreach ($this->socialmedia as $soc) {
                   ($value['value'] == $soc['code']) ? $sel = ' selected="selected" ' : $sel = '';
                   $genField .= '<option value="'.$soc['code'].'" '.$sel.'>'.$soc['name'].'</option>'."\n";
                }
                $genField .= '</select></td></tr>';
                $genField .= '<tr><td>'.$field['label'].' - Link:<br /><input '.$first_id.' type="text" name="'.$this->index.'['.$id.']['.$field['name'].'][link]" class="text" value="'.$value['link'].'" />';
                break;
            default:
                $genField.= '<tr><td>'.$field['label'].':<br /><input '.$first_id.' type="text" name="'.$this->index.'['.$id.']['.$field['name'].'][value]" class="text" value="'.$value['value'].'" /></td></tr>';
        }
        return $genField;
      }

      private function tplName($name){
        return str_replace(array("{","}"),"",$name);
      }

      private function doBreakField($name,$value){
        if ($value['value']=="br"){
            $this->tpl->set('d', $name, '<br class="'.$this->index.' cntnd_break" />');
        }
        else if ($value['value']=="p"){
            $this->tpl->set('d', $name, '<br /><br class="'.$this->index.' cntnd_break" />');
        }
        else if ($value['value']=="hr"){
            $this->tpl->set('d', $name, '<hr class="'.$this->index.' cntnd_break" />');
        }
        else {
            $this->tpl->set('d', $name, "");
        }
      }

	  private function doPlainField($name,$value){
        if (!empty($value['value'])){
            $this->tpl->set('d', $name, $value['value']);
        }
        else {
            $this->tpl->set('d', $name, "");
        }
      }

      private function doField($name,$value,$extra=false){
        if (!empty($value['value'])){
        	// Extended > - ghdgshd = List, etc.
        	if ($extra){
        		$arr = explode("\n", $value['value']);
        		$text = '<ul>';
        		foreach($arr as $value){
        			$text .= '<li>'.$value.'</li>';
        		}
        		$text .= '</ul>';
        	}
        	else {
        		$text = $value['value'];
        	}
            $this->tpl->set('d', $name, '<div class="'.$this->index.' cntnd_text">'.stripslashes($text).'</div>');
        }
        else {
            $this->tpl->set('d', $name, "");
        }
      }

      private function doLinkField($name,$value){
        if (!empty($value['value'])){
            $this->tpl->set('d', $name, '<span class="'.$this->index.' cntnd_linktext">'.stripslashes($value['value']).'</span>');
        }
        else {
            $this->tpl->set('d', $name, "");
        }
      }

      private function doTitelField($name,$value){
        if (!empty($value['value'])){
            $this->tpl->set('d', $name, '<h2 class="'.$this->index.' cntnd_title">'.stripslashes($value['value']).'</h2>');
        }
        else {
            $this->tpl->set('d', $name, "");
        }
      }

      private function doDownloadLinkField($name,$value,$piktogramme=true){
        if (!empty($value['value']) AND $value['value']!=0){
            global $cfg, $client, $cfgClient;

            $DL_type=$value['value'];
            $DL_link=$value['link'];

            $target="_self";
            if ($DL_type!=999999999 AND $DL_type!=111111111 AND $DL_type!=222222222){
                $filename = $this->medien[$DL_type]['filename'];
                $link = $cfgClient[$client]["upl"]["htmlpath"].$filename;
                $icon = substr($filename,strrpos($filename,".")+1);
                $target="_blank";
            }
            if ($DL_type==111111111){
                $link = $DL_link;
                $icon = "link";
                $target="_blank";
            }
            if ($DL_type==222222222){
                $link = "front_content.php?idart=".$DL_link;
                $icon = "linkintern";
            }

            switch ($icon){
                case 'doc':
                case 'docx':
                case 'dot':
                case 'dotx':
                        $icon="word";
                        break;
                case 'xls':
                case 'xlsx':
                        $icon="excel";
                        break;
                case 'pdf':
                        $icon="pdf";
                        break;
                case 'ppt':
                case 'pptx':
                case 'pps':
                case 'ppsx':
                        $icon="powerpoint";
                        break;
                case 'qt':
                case 'avi':
                case 'mpeg':
                        $icon="video";
                        break;
                case 'zip':
                        $icon="zip";
                        break;
                case 'link':
                        $icon="link";
                        break;
                case 'linkintern':
                        $icon="link-intern";
                        break;
                default:
                        $icon="default";
            }

            // template ausfüllen
            $id=$this->index.mt_rand();
            $pikto='';
            if ($DL_type!=999999999 && $piktogramme){
                $pikto = 'pikto-after pikto--'.$icon;
            }

            $link_tag = '<a class="'.$this->index.' cntnd_link '.$pikto.'" href="'.$link.'" target="'.$target.'">';
            $this->tpl->set('d', $name, $link_tag);
            $this->tpl->set('d', "_".$name."_end", '</a>');
        }
        else {
            $this->tpl->set('d', $name, '');
            $this->tpl->set('d', "_".$name."_end", '');
        }
      }

      private function doImageField($name,$value){
        if (!empty($value['value']) AND $value['value']!=0){
            $this->tpl->set('d', $name, '<img src="upload/'.$this->images[$value['value']]['filename'].'" class="'.$this->index.' cntnd_img" />');
            //$this->tpl->set('d', $name, 'upload/'.$this->images[$value['value']]['filename']);
        }
        else {
            $this->tpl->set('d', $name, "");
        }
      }


      private function doSOCField($name,$value){
        if (!empty($value['value'])){
        	if (empty($value['link'])){
        		$this->tpl->set('d', $name, '<span class="socicon-'.$value['value'].'"></span>');
        	}
        	else {
				$this->tpl->set('d', $name, '<a href="'.$value['link'].'" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\''.$value['value'].'\',\'\',\'upload/logo/'.$value['value'].'-25-over.png\',1)" target="_blank"><img name="'.$value['value'].'" src="upload/logo/'.$value['value'].'-25.png" /></a>');
        	}
        }
        else {
            $this->tpl->set('d', $name, "");
        }
      }

	  private function doGallery5($name,$value){
        if (!empty($value['value']) AND $value['value']!=0){
            global $cfg, $client, $cfgClient;
			$id = rand(100,999);
            $galleryId = 'gallery'.$id;
			$dirname = $this->folders[$value['value']]['dirname'];
			$comments = file($cfgClient[$client]['upl']['path'].$dirname.'comments.txt');

            $this->db->query("SELECT filename FROM ".$cfg["tab"]["upl"]." WHERE dirname = '".$dirname."' AND filetype != '' AND filetype != 'txt'  ORDER BY filename ");
            $i=0;
			while ($this->db->nextRecord()) {
            	$file = $this->db->f('filename');
            	if (!empty($file)){
					$title='';
					if ($comments){
						$title = 'title="'.$comments[$i].'"';
					}
					$thumbs .= "\n".'<div class="cntnd_galerie5_thumb_layer">
										<a class="fancybox'.$id.'" rel="'.$galleryId.'" '.$title.' href="'.$cfgClient[$client]['upl']['htmlpath'].$dirname.$file.'">
											<img  class="cntnd_galerie5_thumb" src="'.$cfgClient[$client]['upl']['htmlpath'].$dirname.'thumbs/'.$file.'" />
										</a>
									</div>'."\n";
					$i++;
				}
			}

            $javascript='<script language="" type="text/javascript">
			<!--
			// Activate fancyBox
			$(".fancybox'.$id.'")
				.attr("rel", "'.$galleryId.'")
				.fancybox({
					padding : 0
				});


			// Launch fancyBox on first element
			$(".fancybox'.$id.'").eq(0).trigger("click");
			-->
			</script>';

            $this->tpl->set('d', '_javascript', $javascript);
            $this->tpl->set('d', 'galerie5', $galleryId);
            $this->tpl->set('d', '_thumbs', $thumbs);
        }
        else {
            $this->tpl->set('d', '_javascript', '');
            $this->tpl->set('d', 'galerie5', '');
            $this->tpl->set('d', '_thumbs', '');
		}
	  }


	  private function doGallery4($name,$value){
        if (!empty($value['value']) AND $value['value']!=0){
            global $cfg, $client, $cfgClient;
            $galleryId = 'gallery'.rand(100,999);

			$dirname = $this->folders[$value['value']]['dirname'];
            $this->db->query("SELECT filename FROM ".$cfg["tab"]["upl"]." WHERE dirname = '".$dirname."' AND filetype != '' ORDER BY filename ");
            while ($this->db->nextRecord()) {
            	$file = $this->db->f('filename');
            	if (!empty($file)){
					$pictures .= "'".$cfgClient[$client]['upl']['htmlpath'].$dirname.$file."',";
				}
			}

            $javascript="<script language=\"\" type=\"text/javascript\">
			<!--
			$( document ).ready(function() {
				$('#".$galleryId."').click(function() {
					$.fancybox([
						".substr($pictures, 0, -1)."
					], {
						'padding'			: 0,
						'transitionIn'		: 'none',
						'transitionOut'		: 'none',
						'type'              : 'image',
						'changeFade'        : 0
					});
				});
			});
			-->
			</script>";
            $this->tpl->set('d', '_javascript', $javascript);
            $this->tpl->set('d', '_gallery_id', $galleryId);
        }
        else {
            $this->tpl->set('d', '_javascript', '');
            $this->tpl->set('d', '_gallery_id', '');
        }
      }
	  /*

    $("#manual2").click(function() {
		$.fancybox([
			'http://farm8.staticflickr.com/7308/15783866983_27160395b9_b.jpg',
			'http://farm3.staticflickr.com/2880/10346743894_0cfda8ff7a_b.jpg',
			{
				'href'	: 'http://farm6.staticflickr.com/5612/15344856989_449794889d_b.jpg',
				'title'	: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit'
			}
		], {
			'padding'			: 0,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'              : 'image',
			'changeFade'        : 0
		});
	});
	  */

      private function doGallery2($name,$value,$extra=''){
        if (!empty($value['value']) AND $value['value']!=0){
            global $cfg, $client, $cfgClient;

            $javascript='<script language="" type="text/javascript">
			<!--
			// Activate fancyBox
			$(".fancybox")
				.attr("rel", "gallery")
				.fancybox({
					padding : 0
				});


			// Launch fancyBox on first element
			$(".fancybox").eq(0).trigger("click");
			-->
			</script>';
            $this->tpl->set('s', '_javascript', $javascript);

            $dirname = $this->folders[$value['value']]['dirname'];
            $this->db->query("SELECT filename FROM ".$cfg["tab"]["upl"]." WHERE dirname = '".$dirname."' AND filetype != '' ORDER BY filename ");
            $first=true;
            while ($this->db->nextRecord()) {
            	if (!$first){
					$this->tpl->next();
            	}
            	$file = $this->db->f('filename');
            	if (!empty($file)){
					$this->tpl->set('d', $name, $cfgClient[$client]['upl']['htmlpath'].$dirname.$file);
					if (!empty($extra)){
						$this->tpl->set('d', '_'.$name.'_url', $cfgClient[$client]['upl']['htmlpath'].$extra.$file);
					}
				}
				$first=false;
			}
        }
        else {
            $this->tpl->set('d', $name, "");
            $this->tpl->set('d', '_'.$name.'_url', "");
            $this->tpl->set('d', '_javascript', "");
        }
      }

      private function doGallery3($name,$value,$first,$count,$current){
        if (is_array($value['value'])){
            global $cfg, $client, $cfgClient;

            /*				{
            href : 'bildergalerien/beispiel/birne.jpg',
					title : 'Es werde Licht.'
				}, {*/

            if ($current==1){
                $viewer="viewer_".$this->idart."_".$value['viewer'];
                $this->tpl->set('s', $name, $viewer);
                $this->tpl->set('s', '_'.$name.'_thumb', "upload/".$value['value']['bild']);
                $this->tpl->set('s', '_'.$name.'_comment', nl2br($value['value']['kommentar']));
            }

            if ($current>1){
                $javascript .= ",";
            }
            $javascript .= "\n{\n'href' :'".$cfgClient[$client]['upl']['htmlpath'].$value['value']['bild']."',\n'title' :'".str_replace(array("\n", "\r"), ' ', nl2br($value['value']['kommentar']))."'\n}";

            $this->tpl->set('d', '_'.$name.'_bild', $javascript);
        }
        else {
            $this->tpl->set('s', $name, "");
            $this->tpl->set('d', '_'.$name.'_bild', "");
            $this->tpl->set('s', '_'.$name.'_thumb', "");
            $this->tpl->set('s', '_'.$name.'_comment', "");
        }
      }

      private function doGallery($name,$value,$first,$count,$current){
           if (is_array($value['value'])){
               global $cfg, $client, $cfgClient;
               /*
               $javascript='';
               if ($current>1){
                   $javascript = ",";
               }
               $javascript .= "\n{\n'href' :'".$cfgClient[$client]['upl']['htmlpath'].$value['value']['bild']."',\n'title' :'".str_replace(array("\n", "\r"), ' ', nl2br($value['value']['kommentar']))."'\n}";
               $this->tpl->set('d', '_gallery_array', $javascript);
               */
               $this->tpl->set('d', 'gallery', "upload/".$value['value']['bild']);
               $this->tpl->set('d', '_gallery_comment', str_replace(array("\n", "\r"), ' ', nl2br($value['value']['kommentar'])));
           }
           else {
               //$this->tpl->set('s', $name, "");
               $this->tpl->set('d', 'gallery', "");
               $this->tpl->set('d', '_gallery_comment', "");
           }
      }

      function genHtmlField($field,$value,$first,$count,$current){
        switch($field['type']){
            case 'break':
                $this->doBreakField($this->tplName($field['name']),$value);
                break;
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
            case 'linktext':
                $this->doLinkField($this->tplName($field['name']),$value);
                break;
            case 'image':
                $this->doImageField($this->tplName($field['name']),$value);
                break;
            case 'downloadlink':
                $this->doDownloadLinkField($this->tplName($field['name']),$value,$field['extra']);
                break;
            case 'gallery':
                $this->doGallery($this->tplName($field['name']),$value,$first,$count,$current);
                break;
            case 'gallery2':
                $this->doGallery2($this->tplName($field['name']),$value,$field['extra']);
                break;
            case 'gallery3':
                $this->doGallery3($this->tplName($field['name']),$value,$first,$count,$current);
                break;
			case 'gallery4':
                $this->doGallery4($this->tplName($field['name']),$value,$field['extra']);
                break;
			case 'gallery5':
                $this->doGallery5($this->tplName($field['name']),$value,$field['extra']);
                break;
            case 'socialmedia':
                $this->doSOCField($this->tplName($field['name']),$value);
                break;
        }
      }

      function showOutput() {
        global $client, $cfgClient;

        if (is_array($this->cmsValue)){
         $this->tpl->reset();
         $count=count($this->cmsValue);
         $current=1;
         foreach($this->cmsValue as $row){
            $first=true;
            foreach ($this->fields as $field){
                $this->genHtmlField($field,$row[$field['name']],$first,$count,$current);
                $first=false;
                $current++;
            }
            $this->tpl->next();
         }
         $this->tpl->generate($this->mask);
        }
       }
   }
}

$cntndList = new cntndDynList($listname);
for($i=0;$i<$cms_var;$i++){
    $cms_var_field=100+$i;
    $cms_var_type =200+$i;
    $cms_var_name =300+$i;
    $cms_var_extra=400+$i;

    $name=$data['data['.$cms_var_field.']'];
    $type=$data['data['.$cms_var_type.']'];
    $label=$data['data['.$cms_var_name.']'];
    $extra=$data['data['.$cms_var_extra.']'];
    if ($type!=="internal" AND $type!=="NULL" AND !empty($type)){
        $cntndList->setField($name,$type,$label,$extra);
    }
}
$cntndList->show($template);

if ($editmode){
    echo '</div>';
}
?>
