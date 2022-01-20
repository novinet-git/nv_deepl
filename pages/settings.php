<?php
$oDeepl = new nvDeepl;
$form = rex_config_form::factory($oAddon->name);
$field = $form->addInputField('text', 'api_key', null, ["class" => "form-control"]);
$field->setLabel($this->i18n('nv_deepl_api_key'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('nv_deepl_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');



return;