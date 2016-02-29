<?php
class AccountDocumentTemplateExtension extends DataExtension {

  public function updateDocumentTemplateSrcInCreate($typeSrc) {
    return 'Account';
  }

  public function updateDocumentTemplateFields(FieldList $fields, $typeSrc) {
    $src = $typeSrc;

    if(!DocumentTemplate::get()->find('DocumentType', 'Account')) {
      $src['Account'] = 'Account';
    }

    $fields->dataFieldbyName('DocumentType')
      ->setSource($src);

    if($this->owner->DocumentType == 'Account') {
      $fields->removeByName('Content3Tab');
      $fields->removeByName('Prefix');
      $fields->removeByName('FirstNumber');
      $fields->removeByName('YearlyReset');
      $fields->removeByName('ZeroFill');
      $fields->dataFieldbyName('Content1')
        ->setDescription('Wird nach der Betreffszeile dargestellt');
    }
  }
}