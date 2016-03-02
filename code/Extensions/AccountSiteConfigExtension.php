<?php
class AccountSiteConfigExtension extends DataExtension {

  private static $db = array(
    'AccountsMasterPassword' => 'Text'
  );

  public function onBeforeWrite() {
    parent::onBeforeWrite();

    if(Permission::check(['ADMIN', 'WRITE_ACCOUNTS'])) {
      if($this->owner->AccountsMasterPasswordInput) {
        $e = new PasswordEncryptor_MySQLPassword();

        $this->owner->AccountsMasterPassword = $e->encrypt($this->owner->AccountsMasterPasswordInput);
      }

      if($this->owner->AccountsOldMasterPasswordInput && $this->owner->AccountsNewMasterPasswordInput) {
        $this->masterPasswordChanged($this->owner->AccountsOldMasterPasswordInput, $this->owner->AccountsNewMasterPasswordInput);

        $e = new PasswordEncryptor_MySQLPassword();
        $this->owner->AccountsMasterPassword = $e->encrypt($this->owner->AccountsNewMasterPasswordInput);
      }

      if($this->owner->AccountsDeleteMasterPasswordInput && $this->owner->AccountsDeleteMasterPasswordInputSecure) {
        $this->owner->AccountsMasterPassword = null;
      }
    }
  }

  public function validate(ValidationResult $validationResult) {
    if(
      ($this->owner->AccountsOldMasterPasswordInput && !$this->owner->AccountsNewMasterPasswordInput) ||
      (!$this->owner->AccountsOldMasterPasswordInput && $this->owner->AccountsNewMasterPasswordInput))
    {
      $validationResult->error('Zum ändern des Master-Passworts müssen das alte sowie das neue Passwort eingegebn werden.');
    }

    if($this->owner->AccountsOldMasterPasswordInput && $this->owner->AccountsNewMasterPasswordInput) {
      $e = new PasswordEncryptor_MySQLPassword();
      
      $pw = $this->owner->AccountsOldMasterPasswordInput;
      $masterHash = $this->owner->AccountsMasterPassword;
      if(!$e->check($masterHash, $pw)) {
        $validationResult->error('Das alte Master-Passwort ist falsch');
      }
    }

    if(
      ($this->owner->AccountsMasterPasswordInput !== $this->owner->AccountsMasterPasswordInputRepeat) ||
      ($this->owner->AccountsNewMasterPasswordInput !== $this->owner->AccountsNewMasterPasswordInputRepeat))
    {
        $validationResult->error('Die Master-Passwörter stimmen nicht überein.');
    }
  }

  public function updateCMSFields(FieldList $fields) {
    if(Permission::check(['ADMIN', 'WRITE_ACCOUNTS'])) {
      $fields->addFieldsToTab('Root.Accounts', array());

      if($this->owner->AccountsMasterPassword) {
        $fields->addFieldsToTab('Root.Accounts', array(
          HeaderField::create('Master-Passwort ändern', 4),
          PasswordField::create('AccountsOldMasterPasswordInput', 'Altes Master-Passwort'),
          PasswordField::create('AccountsNewMasterPasswordInput', 'Neues Master-Passwort'),
          PasswordField::create('AccountsNewMasterPasswordInputRepeat', 'Neues Master-Passwort wiederholen'),
          DropdownField::create('AccountsDeleteMasterPasswordInput', 'Master-Passwort löschen', array(
            1 => 'Ja',
            0 => 'Nein'
          ), 0),
          $warning = DisplayLogicWrapper::create(LiteralField::create('AccountsWarning', '<div class="message bad"><strong>Achtung! Wird das Master-Passwort gelöscht, werden alle gespeicherten Accounts unbrauchbar!</strong></div>')),
          $check = DropdownField::create('AccountsDeleteMasterPasswordInputSecure', 'Wirklich löschen?', array(
            1 => 'Ja - Passwort löschen und Accounts zerstören',
            0 => 'Nein'
          ), 0)
        ));

        $warning->displayIf('AccountsDeleteMasterPasswordInput')->isEqualTo(1);
        $check->displayIf('AccountsDeleteMasterPasswordInput')->isEqualTo(1);
      } else {
        $fields->addFieldsToTab('Root.Accounts', array(
          HeaderField::create('Master-Passwort setzen', 4),
          PasswordField::create('AccountsMasterPasswordInput', 'Master-Passwort'),
          PasswordField::create('AccountsMasterPasswordInputRepeat', 'Master-Passwort wiederholen')
        ));
      }
    }
  }

  // - Alle Accounts entschlüsseln und mit neuem PW verschlüsseln
  private function masterPasswordChanged($oldPW, $newPW) {
    $accounts = Account::get();

    foreach($accounts as $acc) {
      $decryptedPassword = $acc->getDecryptedPassword($oldPW);
      $acc->Password = $acc->setEncryptedPassword($decryptedPassword, $newPW);
      $acc->write();
    }
  }
}