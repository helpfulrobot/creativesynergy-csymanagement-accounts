<?php
class AccountCompanyExtension extends DataExtension {

  private static $has_many = array(
    'Accounts' => 'Account'
  );

  public function updateCompanyCMSFields(FieldList $fields) {
    $fields->addFieldsToTab('Root.Accounts', array(
      GridField::create('Accounts', 'Accounts', $this->owner->Accounts(), CSYGrid::create(100))
    ));
  }
}