<?php
class AccountCompanyExtension extends DataExtension {

  private static $has_many = array(
    'Accounts' => 'Account'
  );

  public function onBeforeDelete() {
    parent::onBeforeDelete();

    foreach($this->owner->Accounts as $acc) {
      $acc->delete();
    }
  }

  public function updateCompanyCMSFields(FieldList $fields) {
    if(Permission::check(['ADMIN', 'VIEW_ACCOUNTS']) && $this->owner->ID) {
      $fields->addFieldsToTab('Root.Accounts', array(
        GridField::create('Accounts', 'Accounts', $this->owner->Accounts(), $accGridConf = CSYGrid::create(100))
      ));

      $accGridConf->addComponent(new GridFieldExportAccountsAsPdfButton('before'));
    }
  }
}