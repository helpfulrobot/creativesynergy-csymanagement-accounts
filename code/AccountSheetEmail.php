<?php
class AccountSheetEmail extends CSYMBaseEmail {

  public function __construct($from = null, $to, $bcc = false, $account, $types, $preview = false) {
    $config = SiteConfig::current_site_config();

    if(!$from) {
      $from = $config->Company()->Title . '<' . $config->SenderEmail . '>';
    }

    // - BCC
    $bccAddresses = null;
    if($bcc) {
      $bccAddresses = $bcc;
    }

    if($mail = $config->BCCAllEmailsTo) {
      if($bccAddresses) {
        $bccAddresses .= ';' . $mail;
      } else {
        $bccAddresses = $mail;
      }
    }

    if($bccAddresses) {
      $this->setBCC($bccAddresses);
    }

    // - Answer
    if($mail = $config->AnswerEmail) {
      $this->replyTo($mail);
    }

    // - Attachment
    $company = $account->Company();
    $items = $company->Accounts()->filter('TypeID', $types);
    $template = DocumentTemplate::get()->find('DocumentType', 'Account');

    $variables = [
      'Items' => GroupedList::create($items),
      'Template' => $template
    ];

    $pdf = new SS_PDF();
    $pdf->setFolderName('csymanagement/temp/');
    $pdf->setOption('header-html', BASE_PATH . '/assets/csymanagement/own/pdf-document-header.html');
    $pdf->setOption('footer-html', BASE_PATH . '/assets/csymanagement/own/pdf-document-footer.html');
    $pdf->setOption('user-style-sheet', BASE_PATH . '/csymanagement-client/css/pdf.css');
    $pdf->setOption('margin-top', 40);
    $pdf->setOption('margin-bottom', 40);
    $pdf->setOption('margin-left', 0.1);
    $pdf->setOption('margin-right', 0.1);

    $html = $pdf::getHtml($items->first(), $variables, 'AccountExportPdf');
    $pdf->add($html);
    $file = $pdf->save('Datenblatt-' . $company->CustomerID);

    if($file) {
      $this->attachFile($file->RelativeLink());
      $file->delete();
    }

    parent::__construct($from, $to, $account, $preview);
  }
}