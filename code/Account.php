<?php
class Account extends DataObject {

  private static $singular_name = 'Account';
  private static $plural_name = 'Accounts';

  private static $db = array(
    'Title' => 'Varchar(255)',
    'User' => 'Varchar(255)',
    'Password' => 'Text',
    'Resource' => 'Text',
    'Comment' => 'Text'
  );

  private static $has_one = array(
    'Type' => 'AccountType',
    'Company' => 'Company'
  );

  private static $belongs_to = array();
  private static $has_many = array();
  private static $many_many = array();
  private static $belongs_many_many = array();
  private static $many_many_extraFields = array(
    // 'RelationName' => array('FieldName' => 'FieldType')
  );

  public function searchableFields() {   
    $typeField = DropdownField::create('TypeID', 'Typ', AccountType::get()->map()->toArray())
      ->setEmptyString('(alle)');

    return array (
      'TypeID' => array(
        'title' => 'Typ',
        'filter' => 'ExactMatchFilter',
        'field' => $typeField
      ),
      'Resource' => array(
        'title' => 'URL / Server / IP / DB / ...',
        'filter' => 'PartialMatchFilter'
      ),
      'User' => array(
        'title' => 'Benutzername',
        'filter' => 'PartialMatchFilter'
      ),
      'Company.CustomerID' => array(
        'title' => 'Kundennummer',
        'filter' => 'PartialMatchFilter'
      ),
      'Company.Title' => array(
        'title' => 'Unternehmen',
        'filter' => 'PartialMatchFilter'
      ),
      'Created' => array(
        'title' => 'Erstellungsdatum',
        'filter' => 'StartsWithFilter',
        'field' => 'DateField'
      )
    );
  }

  private static $summary_fields = array(
    'Type.Title' => 'Typ',
    'Resource' => 'URL / Server / IP / DB / ...',
    'User' => 'Benutzername',
    'CommentAvailablbe' => 'Kommentar vorhanden',
    'Company.Title' => 'Unternehmen',
    'Company.CustomerID' => 'Kundenummer'
  );

  private static $default_sort = 'CompanyID, TypeID';

  public function populateDefaults() {
    parent::populateDefaults();
  }

  public function onBeforeWrite() {
    parent::onBeforeWrite();

    if($this->isChanged('Resource') || $this->isChanged('User') || !$this->Title) {
      $link = null;
      if($this->Resource) {
        $link = $this->Resource . ' | ';
      }

      $this->Title = $link . $this->User;
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

  public function getCMSValidator() {
    $requiredFields = RequiredFields::create('User', 'Password');
    return $requiredFields;
  }

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
          DropdownField::create('CompanyID', 'Kunde', Company::get()->map()->toArray()),
          DropdownField::create('TypeID', 'Typ', AccountType::get()->map()->toArray()),
          TextField::create('Resource', 'URL / Server / IP / DB / ...'),
          TextField::create('User', 'Benutzername'),
          TextField::create('PasswordInput', 'Passwort')
            ->setRightTitle($decryptedPassword),
          TextareaField::create('Comment', 'Kommentar'),
          LiteralField::create('LabelData', AccountType::getTypeLabels())
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

  public function CommentAvailablbe() {
    if($this->Comment) {
      return 'Ja';
    } else {
      return 'Nein';
    }
  }

  public function TypeTitle() {
    return $this->Type()->Title;
  }
}