<?php
class AccountTestTask extends BuildTask {
  protected $title = 'Account Test Task';
  protected $description = 'Test für Accounts';

  private static $allowed_actions = array(
    '*' => 'ADMIN'
  );

  public function run($request) {
    // echo $this->text2bin('HalloMax');
    // echo '<br>';
    // echo $this->bin2text('1001000:1100001:1101100:1101100:1101111:1001101:1100001:1111000');

    $a = Account::get()->byID(1);
    $a->PasswordInput = 'MeinPW';
    $a->Comment = 456;
    $a->write();

    echo $a->getDecryptedPassword('123');

    // echo $a->getDecryptedPassword('123');
  }


}