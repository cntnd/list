<?php
// cntnd_list_output
$cntnd_module = "cntnd_list";

// assert framework initialization
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// editmode
$editmode = \cRegistry::isBackendEditMode();

// includes
cInclude('module', 'includes/class.cntnd_list.php');
cInclude('module', 'includes/class.cntnd_list_output.php');
if ($editmode) {
    cInclude('module', 'includes/style.cntnd_list.php');
    cInclude('module', 'includes/script.cntnd_list_output.php');
}

// input/vars
$listname = "CMS_VALUE[1]";
if (empty($listname)) {
    $listname = "cntnd_list";
}
$template = "CMS_VALUE[2]";
$count = 0;
if (!empty($template) and $template != "false") {
    $templateFields = \Cntnd\DynList\CntndList::template($cntnd_module, $client, $template);
    $count = count(array_unique($templateFields[0]));
}
$data = Cntnd\DynList\CntndListOutput::unescapeData("CMS_VALUE[3]");

// other vars
$uuid = rand();

// values
$tpl = cSmartyFrontend::getInstance();
$cntndList = new Cntnd\DynList\CntndList($idart, $lang, $client, $listname, $tpl);
$values = $cntndList->load();

// module
if ($editmode) {
    echo '<div class="w-100">';
    echo '<span class="module_box"><label class="module_label">'.mi18n("MODULE").': '.$listname.'</label></span>';
    echo '<div class="input_form">';
    if ($_POST) {
        if (array_key_exists('data', $_POST)) {
            // INSERT
            if (array_key_exists($listname, $_POST['data'])) {
                $values[] = $_POST['data'][$listname];
                $serializeddata = json_encode($values);
                $cntndList->store($serializeddata);
            } // UPDATE
            else if (array_key_exists('action', $_POST) && array_key_exists('key', $_POST) && $_POST['listname'] == $listname) {
                $dataToUpdate = Cntnd\DynList\CntndListOutput::unescapeData($_POST['data']);
                $values = $cntndList->update($_POST['action'], $_POST['key'], $dataToUpdate, $values);
            }
            // REORDER
            if (array_key_exists('reorder', $_POST) && !empty($_POST['reorder']) && $_POST['listname'] == $listname) {
                $dataToReorder = json_decode(base64_decode($_POST['reorder']), true);
                $values = $cntndList->reorder($dataToReorder, $values);
            }
        }
    }

    if (!$template or empty($template) or $template == "false") {
        echo '<div class="cntnd_alert cntnd_alert-primary">' . mi18n("NO_TEMPLATE_OUTPUT") . '</div>';
    } else {
        // input
        $formId = "LIST_" . $listname;
        $entryFormId = "ENTRY_" . $listname;
        ?>
            <form data-uuid="<?= $formId ?>" id="<?= $formId ?>" name="cntnd_list" method="post">
                <div class="cntnd_alert cntnd_alert-danger hide"><?= mi18n("INVALID_FORM") ?></div>
                <?php
                $cfgClient = \cRegistry::getClientConfig();
                $cntndListOutput = new Cntnd\DynList\CntndListOutput($cntndList->medien(), $cntndList->images(), $cntndList->folders(), $listname, $cfgClient[$client]);
                for ($index = 0; $index < $count; $index++) {
                    echo $cntndListOutput->input($data, $values[$index], $index, $listname);
                }
                ?>
                <button class="btn btn-primary" type="submit"><?= mi18n("ADD") ?></button>
            </form>
        </div>
        <hr/>
        <strong><?= mi18n("LIST_ENTRIES") ?> (<?= count($values) ?>)</strong>
        <form data-uuid="<?= $entryFormId ?>" id="<?= $entryFormId ?>" name="<?= $entryFormId ?>" method="post">
            <input type="hidden" name="listname" value="<?= $listname ?>"/>
            <input type="hidden" name="key"/>
            <input type="hidden" name="data"/>
            <input type="hidden" name="action"/>
            <input type="hidden" name="reorder"/>
            <button class="btn btn-dark hide" type="submit"><?= mi18n("REFRESH") ?></button>
        </form>
        <div id="cntnd_list_items-<?= $listname ?>">
            <?php
            foreach ($values as $key => $value) {
                echo '<div class="listitem d-flex" data-order="' . $key . '" id="' . $entryFormId . '_' . $key . '">' . "\n";
                echo '<div><span class="handle"></span></div>' . "\n";
                echo '<div>' . "\n";
                echo '<div class="cntnd_alert cntnd_alert-danger hide">' . mi18n("INVALID_FORM") . '</div>' . "\n";
                $index = 0;
                foreach ($value as $name => $field) {
                    $label = 'data[' . $index . '][label]';
                    $extra = 'data[' . $index . '][extra]';
                    $optional = 'data[' . $index . '][optional]';

                    echo $cntndListOutput->entry($name, $data[$label], $key, $field, $listname, $data[$extra], \Cntnd\DynList\CntndListOutput::optionals($data, $optional));
                    $index++;
                }
                echo '<button class="cntnd_list_action btn btn-primary" type="button" data-uuid="' . $entryFormId . '" data-listitem="' . $key . '" data-action="update">' . mi18n("SAVE") . '</button>' . "\n";
                //echo '<button class="cntnd_list_action btn btn-light" type="reset">'.mi18n("RESET") .'</button>'."\n";
                echo '<button class="cntnd_list_action btn" type="button" data-uuid="' . $entryFormId . '" data-listitem="' . $key . '" data-action="delete">' . mi18n("DELETE") . '</button>' . "\n";
                echo '</div></div>' . "\n";
            }
            ?>
        </div>
        <?= $cntndList->doSortable() ?>
        <?php
    }
    echo '</div>';
    echo '</div>';
}

if (!$editmode) {
    if (!empty($template)) {
        $cntndList->render($template, $values, $data);
    }
}
?>
