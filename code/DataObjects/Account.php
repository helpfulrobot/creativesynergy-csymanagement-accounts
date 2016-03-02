<?php
class Account extends DataObject implements PermissionProvider {

  private static $singular_name = 'Account';
  private static $plural_name = 'Accounts';

  private $firstWrite = false;

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

  public function searchableFields() {   
    $typeField = DropdownField::create('TypeID', 'Typ', AccountType::get()->map()->toArray())
      ->setEmptyString('(alle)');

    $companyField = DropdownField::create('CompanyID', 'Unternehmen', $this->CompanyWithCustomerID())
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
      'CompanyID' => array(
        'title' => 'Unternehmen',
        'filter' => 'ExactMatchFilter',
        'field' => $companyField
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
    'getNiceEncryptedPassword' => 'Passwort',
    'CommentAvailablbe' => 'Kommentar vorhanden',
    'Company.Title' => 'Unternehmen',
    'Company.CustomerID' => 'Kundenummer'
  );

  private static $default_sort = 'CompanyID, TypeID';

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

    if(!$this->ID) {
      $this->firstWrite = true;
    }
  }

  public function onAfterWrite() {
    parent::onAfterWrite();

    if($this->firstWrite) {
      $activity = Activity::create();
      $activity->MemberID = Member::currentUserID();
      $activity->CompanyID = $this->CompanyID;
      $activity->Title = $this->Type()->Title . ' Account wurde angelegt';
      $activity->Description = 'Benutzer: ' . $this->User . ' ID: ' . $this->ID;
      $activity->EditLink = $this->EditLink();
      $activity->Type = 1;
      $activity->write();
    }
  }

  public function onBeforeDelete() {
    parent::onBeforeDelete();

    $activity = Activity::create();
    $activity->MemberID = Member::currentUserID();
    $activity->CompanyID = $this->CompanyID;
    $activity->Title = $this->Type()->Title . ' Account wurde gelöscht';
    $activity->Description = 'ID: ' . $this->ID . ' Benutzer: ' . $this->User;
    $activity->Type = 3;
    $activity->write();
  }

  // - Berechtigungen
  public function canView($member = null) {
    $can = Permission::check(['ADMIN', 'VIEW_ACCOUNTS']);
    return $can;
  }

  public function canCreate($member = null) {
    $can = Permission::check(['ADMIN', 'WRITE_ACCOUNTS']);
    
    if(!SiteConfig::current_site_config()->AccountsMasterPassword) {
      $can = false;
    }

    return $can;
  }

  public function canEdit($member = null) {
    $can = Permission::check(['ADMIN', 'WRITE_ACCOUNTS']);
    return $can;
  }

  public function canDelete($member = null) {
    $can = Permission::check(['ADMIN', 'WRITE_ACCOUNTS']);
    return $can;
  }

  // - Validator
  public function getCMSValidator() {
    $requiredFields = RequiredFields::create('User', 'Password');
    return $requiredFields;
  }

  // - Passwort verschlüsseln
  public function setEncryptedPassword($password, $userSuppliedKey) {
    $csymAccountsSecretKey = Config::inst()->get('Account', 'secret_key');

    $key = $csymAccountsSecretKey . $userSuppliedKey;

    $e = new Encryption(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
    $encryptedData = $e->encrypt($password, $key);

    return $this->text2bin($encryptedData);
  }

  // - Entschlüsseltes Passwort holen
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

  public function getNiceEncryptedPassword() {
    $pw = $this->getDecryptedPassword();
    if(!$pw) {
      return '********';
    } else {
      return $pw;
    }
  }

  // - Password in Binary wandeln damit es in die DB geschrieben werden kann
  public function text2bin($string) { 
    $bin = null;

    for($i = 0; $i < strlen($string); $i++) { 
      if(($c = ord($string{$i})) != 0) $bin .= decbin($c); 
      if($i != (strlen($string) -1) ) $bin .= ':'; 
    } 

    return $bin; 
  } 

  // - Password in Text wandeln
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

  // - Master Passwort in Session speichern
  public function submitMasterPassword($data, $form) {
    $e = new PasswordEncryptor_MySQLPassword();
    $pw = $data['MasterPassword'];
    $masterHash = SiteConfig::current_site_config()->AccountsMasterPassword;

    if($e->check($masterHash, $pw)) {
      $form->sessionMessage('Passwort akzeptiert! Die Seite wird jetzt neu geladen.', 'good master-password-accepted');
      Session::set('CSYMAccountsMasterPassword', $pw);
    } else {
      $form->sessionMessage('Falsches Passwort', 'bad');
    }
  }

  // - Felder definieren
  public function getCMSFields() {
    $pwLabel = 'Passwort';
    
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
        $pwLabel = 'Neues Passwort';
      }
    }

    Account::checkIfPasswordIsUp2Date();

    $fields = FieldList::create(
      TabSet::create('Root',
        Tab::create('Main', 'Hauptteil',
          DropdownField::create('CompanyID', 'Kunde', Company::get()->map()->toArray()),
          DropdownField::create('TypeID', 'Typ', AccountType::get()->map()->toArray()),
          TextField::create('Resource', 'URL / Server / IP / DB / ...'),
          TextField::create('User', 'Benutzername'),
          TextField::create('PasswordInput', $pwLabel)
            ->setRightTitle($decryptedPassword),
          TextareaField::create('Comment', 'Kommentar'),
          LiteralField::create('LabelData', AccountType::getTypeLabels())
        )
      )
    );
    
    return $fields;
  }

  // - Überprüfen ob das PW in der Session das richtige ist
  public static function checkIfPasswordIsUp2Date($return = false) {
    $valid = false;
    $pw = Session::get('CSYMAccountsMasterPassword');
    $masterHash = SiteConfig::current_site_config()->AccountsMasterPassword;

    $e = new PasswordEncryptor_MySQLPassword();

    if(!$e->check($masterHash, $pw)) {
      Session::clear('CSYMAccountsMasterPassword');
    } else {
      $valid = true;
    }

    if($return) {
      return $valid;
    }
  }

  // - Kommentar vorhanden
  public function CommentAvailablbe() {
    if($this->Comment) {
      return 'Ja';
    } else {
      return 'Nein';
    }
  }

  // - Typentitel für die Gruppierung
  public function TypeTitle() {
    return $this->Type()->Title;
  }

  // - Berechtigungen erstellen
  public function providePermissions() {
    return array(
      'VIEW_ACCOUNTS' => array(
        'name' => 'Kann Accounts einsehen',
        'category' => 'Accounts',
        'sort' => 100
      ),
      'WRITE_ACCOUNTS' => array(
        'name' => 'Kann Accounts erzeugen und bearbeiten',
        'category' => 'Accounts',
        'sort' => 200
      ),
      'CHANGE_ACCOUNT_MASTERPW' => array(
        'name' => 'Kann das Master-Passwort verändern oder löschen',
        'category' => 'Accounts',
        'sort' => 300
      )
    );
  }

  // - Edit- / Adminlink
  public function EditLink() {
    return "/admin/unternehmen/Company/EditForm/field/Company/item/$this->CompanyID/ItemEditForm/field/Accounts/item/$this->ID";
  }

}