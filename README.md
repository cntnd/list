# cntnd_list

* includes in "php" files: `cInclude('module', 'includes/class.module.mparticleinclude.php');`
* includes in "includes" php files: `include_once($moduleHandler->getModulePath() . 'vendor/xyz.php');`

*contenido php functions*

* `$client = cRegistry::getClientId();`
* `$lang = cRegistry::getLanguageId();`  
* `mi18n("SELECT_ARTICLE")`
* `buildArticleSelect("CMS_VAR[2]", $oModule->cmsCatID, $oModule->cmsArtID);`
