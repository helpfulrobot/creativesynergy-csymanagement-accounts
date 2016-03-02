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

  // - Better Buttons
  private static $better_buttons_actions = array(
    'sendAccountSheet'
  );

  public function updateBetterButtonsActions(&$actions) {
    $emailSrc = $this->owner->People()->column('Email');
    $companyEmail = $this->owner->Email;
    if(!in_array($companyEmail, $emailSrc)) {
      $emailSrc[] = $companyEmail;
    }
    $emailSrc = array_combine($emailSrc, $emailSrc);

    $types = [];
    foreach($this->owner->Accounts()->column('TypeID') as $id) {
      $type = AccountType::get()->byID($id);
      $types[$type->ID] = $type->Title;
    }


    $fields = FieldList::create(
      CheckboxsetField::create('Types', 'Account-Typen die versendet werden sollen', $types),
      DropdownField::create('Email', 'Empfänger E-Mail', $emailSrc, $this->owner->Email),
      DropdownField::create('PersonalSender', 'E-Mail unter meinem Namen versenden', array(1 => 'Ja', 0 => 'Nein'), 0),
      DropdownField::create('BCCToMe', 'Kopie an mich senden', array(1 => 'Ja', 0 => 'Nein'), 0)
    );

    if(!Account::checkIfPasswordIsUp2Date(true)) {
      $fields->insertBefore(PasswordField::create('MasterPassword', 'Master-Passwort')->addExtraClass('no-js'), 'Types');
    }

    if(count($emailSrc) && $this->owner->Accounts()->first()) {
      $actions->push(
        BetterButtonNestedForm::create('sendAccountSheet', 'Datenblatt versenden', $fields)
            ->addExtraClass('send-mail-btn')
      );
    }
  }

  // - Datenblatt per E-Mail versenden
  public function sendAccountSheet($data, $form) {
    if(isset($data['MasterPassword'])) {
      $e = new PasswordEncryptor_MySQLPassword();
      $pw = $data['MasterPassword'];
      $masterHash = SiteConfig::current_site_config()->AccountsMasterPassword;

      if($e->check($masterHash, $pw)) {
        Session::set('CSYMAccountsMasterPassword', $pw);
      } else {
        $form->sessionMessage('Falsches Passwort', 'bad');
        return false;
      }
    }

    if(!isset($data['Types'])) {
      $form->addErrorMessage('Types', 'Mindestens ein Typ muss ausgewählt werden', 'bad');
      return false;
    }

    $member = Member::currentUser();

    $activity = Activity::create();
    $activity->MemberID = $member->ID;
    $activity->CompanyID = $this->owner->ID;
    $activity->Title = 'Datenblatt per E-Mail versendet';
    $activity->Description = 'gesendet an ' . $data['Email'];
    $activity->Type = 4;
    $activity->write();

    $bcc = null;
    if($data['BCCToMe']) {
      $bcc = $member->Email;
    }

    $from = null;
    if($data['PersonalSender']) {
      $from = $member->Name . '<' . $member->Email . '>';
    }

    $mail = AccountSheetEmail::create($from, $data['Email'], $bcc, $this->owner->Accounts()->first(), $data['Types']);
    $mail->send();
    
    $form->sessionMessage('E-Mail wurde versandt', 'good');
  }
}