<?php
class AccountAdmin extends ModelAdmin {

  private static $menu_priority = 1;
  private static $menu_title = 'Accounts';
  private static $menu_icon = 'csymanagement-accounts/imgs/account-admin.png';
  private static $url_segment = 'Accounts';

  private static $managed_models = array(
    'Account' => array(
      'title' => 'Accounts'
    ),
    'AccountType' => array(
      'title' => 'Typen'
    ),
  );

  public function getEditForm($id = null, $fields = null) {
    $form = parent::getEditForm($id, $fields);

    if($this->modelClass=='Account' && $gridField=$form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))){
      if($gridField instanceof GridField){
        $gridField->getConfig()
          ->addComponent(new GridFieldExportAccountsAsPdfButton('before'))
          ->removeComponentsByType('GridFieldExportButton')
          ->removeComponentsByType('GridFieldPrintButton');
      }
    }
  
    return $form;
  }
}