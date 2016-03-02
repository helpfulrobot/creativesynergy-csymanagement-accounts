<?php
class AccountEmailTemplateExtension extends DataExtension {

  public function updateEmailTemplateSrcInCreate($typeSrc) {
    return 'Account';
  }

  public function updateEmailTemplateFields(FieldList $fields, $typeSrc) {
    $src = $typeSrc;

    if(!EmailTemplate::get()->find('DocumentType', 'Account')) {
      $src['Account'] = 'Account';
    }

    $fields->dataFieldbyName('DocumentType')
      ->setSource($src);
  }
}