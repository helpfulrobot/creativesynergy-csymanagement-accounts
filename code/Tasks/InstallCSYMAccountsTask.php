<?php
class InstallCSYMAccountsTask extends BuildTask {
  protected $title = 'CSY Management Accounts installieren';
  protected $description = 'Installiert creativeSynergy Management Accounts Modul';

  private static $allowed_actions = array(
    '*' => 'ADMIN'
  );

  public function run($request) {
    if(!CSYMTaskLog::get()->find('Task', 'csym-accounts-installed')) {
      global $databaseConfig;
      $username = $databaseConfig['username'];
      $password = $databaseConfig['password'];
      $database = $databaseConfig['database'];

      exec("mysql -u$username -p$password $database < ../csymanagement-accounts/_extras/DocumentTemplate.sql");
      exec("mysql -u$username -p$password $database < ../csymanagement-accounts/_extras/EmailTemplate.sql");

      $import = "@import '../../csymanagement-accounts/scss/pdf';";
      file_put_contents('../csymanagement-client/scss/pdf.scss', "\n" . $import, FILE_APPEND);

      $task = CSYMTaskLog::create();
      $task->Task = 'csym-accounts-installed';
      $task->write();
      
      echo 'CSY Management Accounts wurde erfolgreich installiert';
    }
  }
}