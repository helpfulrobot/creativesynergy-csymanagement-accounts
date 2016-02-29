<?php
class Account extends DataObject {

  private static $singular_name = 'Account';
  private static $plural_name = 'Accounts';

  private static $db = array(
    'Title' => 'Varchar(255)',
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

    if($this->isChanged('Link') || $this->isChanged('User') || !$this->Title) {
      $this->Title = $this->Link . ' | ' . $this->User;
    }

    if($this->PasswordInput) {
      $this->Password = $this->setEncryptedPassword($this->PasswordInput, Session::get('CSYMAccountsMasterPassword'));
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

  public function canCreate($member = null) {
    $can = Permission::check('ADMIN');
    
    if(!SiteConfig::current_site_config()->AccountsMasterPassword) {
      $can = false;
    }

    return $can;
  }

  /*
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
    $csymAccountsSecretKey = Config::inst()->get('Account', 'secret_key');

    $key = $csymAccountsSecretKey . $userSuppliedKey;

    $e = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
    $encryptedData = $e->encrypt($password, $key);

    return $this->text2bin($encryptedData);
  }

  public function getDecryptedPassword($userSuppliedKey = false) {
    if(!$userSuppliedKey) {
      $userSuppliedKey = Session::get('CSYMAccountsMasterPassword');

      if(!$userSuppliedKey) {
        return false;
      }
    }

    $csymAccountsSecretKey = Config::inst()->get('Account', 'secret_key');

    $key = $csymAccountsSecretKey . $userSuppliedKey;

    $e = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
    
    if($this->Password) {
      $data = $e->decrypt($this->bin2text($this->Password), $key);
      return $data;
    }
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

  // - Better Buttons
  private static $better_buttons_actions = array(
    'submitMasterPassword'
  );

  public function getBetterButtonsActions() {
    $fields = parent::getBetterButtonsActions();

    $fields->push(
      $sMP = BetterButtonNestedForm::create('submitMasterPassword', 'Master-Passwort eingeben', FieldList::create(
        PasswordField::create('MasterPassword', 'Master-Passwort')
      ))
    );

    if(Session::get('CSYMAccountsMasterPassword')) {
      $sMP->addExtraClass('hide-master-pw-btn');
    }

    return $fields;
  }


  public function submitMasterPassword($data, $form) {
    $e = new PasswordEncryptor_MySQLPassword();
    $pw = $data['MasterPassword'];
    $masterHash = SiteConfig::current_site_config()->AccountsMasterPassword;

    if($e->check($masterHash, $pw)) {
      $form->sessionMessage('Passwort akzeptiert, bitte laden Sie die Seite neu.', 'good');
      Session::set('CSYMAccountsMasterPassword', $pw);
    } else {
      $form->sessionMessage('Falsches Passwort', 'bad');
    }
  }

  public function getCMSFields() {
    if(!$this->getDecryptedPassword() && $this->Password) {
      $decryptedPassword = 'Entschlüsseltes Passwort: <strong>> Bitte Master-Passwort eingeben <</strong>';
    } else {
      if(!$this->Password) {
        $decryptedPassword = null;

        if(Session::get('CSYMAccountsMasterPassword')) {
          $decryptedPassword = '<strong>Noch kein Passwort hinterlegt</strong>';
        }
      }

      if($pw = $this->getDecryptedPassword()) {
        $decryptedPassword = 'Entschlüsseltes Passwort: <strong>' . $pw . '</strong>';
      }
    }

    $this->checkIfPasswordIsUp2Date();

    $fields = FieldList::create(
      TabSet::create('Root',
        Tab::create('Main', 'Hauptteil',
          DropdownField::create('CustomerID', 'Kunde', Company::get()->map()->toArray()),
          // DropdownField::create('TypeID', 'Typ', AccountType::get()->map()->toArray()),
          TextField::create('Link', 'URL'),
          TextField::create('User', 'Benutzername'),
          TextField::create('PasswordInput', 'Neues Passwort')
            ->setRightTitle($decryptedPassword),
          TextareaField::create('Comment', 'Kommentar')
        )
      )
    );
    
    return $fields;
  }

  public function checkIfPasswordIsUp2Date() {
    $pw = Session::get('CSYMAccountsMasterPassword');
    $masterHash = SiteConfig::current_site_config()->AccountsMasterPassword;

    $e = new PasswordEncryptor_MySQLPassword();

    if(!$e->check($masterHash, $pw)) {
      Session::clear('CSYMAccountsMasterPassword');
    }
  }
}