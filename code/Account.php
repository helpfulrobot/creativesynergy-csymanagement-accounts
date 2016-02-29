<?php
class Account extends DataObject {

  private static $singular_name = 'Account';
  private static $plural_name = 'Accounts';

  private static $db = array(
    'User' => 'Varchar(255)',
    'Password' => 'Text',
    'Link' => 'Text',
    'Comment' => 'Text'
  );

  private static $has_one = array(
    'Type' => 'AccountType',
    'Customer' => 'Company'
  );

  private static $belongs_to = array();
  private static $has_many = array();
  private static $many_many = array();
  private static $belongs_many_many = array();
  private static $many_many_extraFields = array(
    // 'RelationName' => array('FieldName' => 'FieldType')
  );

  // private static $searchable_fields = array();
  // private static $summary_fields = array();

  private static $defaults = array();

  public function populateDefaults() {
    parent::populateDefaults();
  }

  public function onBeforeWrite() {
    parent::onBeforeWrite();

    if($this->PasswordInput) {
      $this->Password = $this->setEncryptedPassword($this->PasswordInput, '123');
    }
  }

  public function onAfterWrite() {
    parent::onAfterWrite();
  }

  public function onBeforeDelete() {
    parent::onBeforeDelete();
  }

  public function onAfterDelete() {
    parent::onAfterDelete();
  }

  /*
  public function canCreate($member = null) {
    return true;
  }

  public function canEdit($member = null) {
    return true;
  }

  public function canDelete($member = null) {
    return true;
  }

  public function canView($member = null) {
    return true;
  }
  */

  public function getCMSValidator() {
    // $requiredFields = parent::getCMSValidator();
    // $requiredFields->addRequiredField('FieldName');
    $requiredFields = RequiredFields::create('Title');
    return $requiredFields;
  }

  /*
  public function validate() {
    $result = parent::validate();
    if($this->Value == 'Key') {
      $result->error('Custom Error Message');
    }
    return $result;
  }
  */

  public function setEncryptedPassword($password, $userSuppliedKey) {
    global $csymAccountsSecretKey;

    $key = $csymAccountsSecretKey . $userSuppliedKey;

    $e = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
    $encryptedData = $e->encrypt($password, $key);

    return $this->text2bin($encryptedData);
  }

  public function getDecryptedPassword($userSuppliedKey = false) {
    if(!$userSuppliedKey) {
      return false;
    }

    global $csymAccountsSecretKey;

    $key = $csymAccountsSecretKey . $userSuppliedKey;

    $e = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
    
    if($this->Password) {
      $data = $e->decrypt($this->bin2text($this->Password), $key);
    }

    return $data;
  }

  public function text2bin($string) { 
    $bin = null;

    for($i = 0; $i < strlen($string); $i++) { 
      if(($c = ord($string{$i})) != 0) $bin .= decbin($c); 
      if($i != (strlen($string) -1) ) $bin .= ':'; 
    } 

    return $bin; 
  } 

  public function bin2text($binstr) { 
    $txt = null;

    $bin = explode(':',$binstr); 

    for($i=0; $i<count($bin); $i++) 
      $txt .= chr(bindec($bin[$i]));

    return $txt;     
  }

  public function getCMSFields() {
    $decryptedPassword = $this->getDecryptedPassword('123');

    $fields = FieldList::create(
      TabSet::create('Root',
        Tab::create('Main', 'Hauptteil',
          DropdownField::create('CustomerID', 'Kunde', Company::get()->map()->toArray()),
          // DropdownField::create('TypeID', 'Typ', AccountType::get()->map()->toArray()),
          TextField::create('Link', 'URL'),
          TextField::create('User', 'Benutzername'),
          TextField::create('PasswordInput', 'Passwort')
            ->setRightTitle('Entschlüsseltes Passwort: <strong>' . $decryptedPassword . '</strong>'),
          TextareaField::create('Comment', 'Kommentar')
        )
      )
    );
    
    return $fields;
  }
}