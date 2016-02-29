<?php
class AccountType extends DataObject {

  private static $singular_name = 'Accounttyp';
  private static $plural_name = 'Accounttypen';

  private static $db = array(
    'Title' => 'Varchar(255)',
    'Label' => 'Varchar(255)',
    'Comment' => 'HTMLText'
  );

  private static $has_many = array(
    'Accounts' => 'Account'
  );

  private static $summary_fields = array(
    'Title' => 'Titel'
  );

  // - Berechtigungen
  public function canView($member = null) {
    $can = Permission::check(['ADMIN', 'VIEW_ACCOUNTS']);
    return $can;
  }

  public function canCreate($member = null) {
    $can = Permission::check(['ADMIN', 'WRITE_ACCOUNTS']);
    return $can;
  }

  public function canEdit($member = null) {
    $can = Permission::check(['ADMIN', 'WRITE_ACCOUNTS']);
    return $can;
  }

  public function canDelete($member = null) {
    $can = Permission::check(['ADMIN', 'WRITE_ACCOUNTS']);
    
    if($this->Accounts()->first()) {
      $can = false;
    }

    return $can;
  }

  // - Validator
  public function getCMSValidator() {
    $requiredFields = RequiredFields::create('Title');
    return $requiredFields;
  }

  public function getCMSFields() {   
    $fields = FieldList::create(
      TabSet::create('Root',
        Tab::create('Main', 'Hauptteil',
          TextField::create('Title', 'Titel'),
          TextField::create('Label', 'Label für das erste Feld')
            ->setRightTitle('Ersetzt den Titel von URL / Server / IP / DB'),
          HTMLEditorField::create('Comment', 'Kommentar')
        )
      )
    );
    
    return $fields;
  }

  public static function getTypeLabels() {
    return '<div id="type-label-data" style="display:none!important;">' . json_encode(AccountType::get()->map('ID', 'Label')->toArray()) . '</div>';
  }
}