<?php
class AccountTestTask extends BuildTask {
  protected $title = 'Account Test Task';
  protected $description = 'Test für Accounts';

  private static $allowed_actions = array(
    '*' => 'ADMIN'
  );

  public function run($request) {

  }
}