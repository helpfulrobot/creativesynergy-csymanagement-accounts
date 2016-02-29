<?php
class AccountAdmin extends ModelAdmin {

  private static $menu_priority = 1;
  private static $menu_title = 'Accounts';
  // private static $menu_icon = 'mysite/imgs/icons/Icon.png';
  private static $url_segment = 'Accounts';

  private static $managed_models = array(
    'Account'
  );

  /*
  public function getEditForm($id = null, $fields = null) {
    $form = parent::getEditForm($id, $fields);

    if( $this->modelClass=='DataObjectName' && $gridField=$form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass)) ){
      if( $gridField instanceof GridField ){
        //$gridField->getConfig()
          ->addComponent(new GridFieldSortableRows('SortID'));
      }
    }
  
    return $form;
  }
  */

  /*
  public function getList() {
    $list = parent::getList();

    if($this->modelClass == 'DataObject'){
      $list = $list->exclude('DBField:GreaterThan', ' ');
    }

    return $list;
  }
  */

  // - Remove Import
  // private static $model_importers = array();
}