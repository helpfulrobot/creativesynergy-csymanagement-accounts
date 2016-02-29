<?php
class AccountCompanyExtension extends DataExtension {

  private static $has_many = array(
    'Accounts' => 'Account'
  );

  public function updateCMSFields(FieldList $fields) {
    $fields->addFieldsToTab('Root.Main', array(
      GridField::create('Accounts', 'Accounts', $this->owner->Accounts(), CSYGrid::create(100))
    ));
  }
}