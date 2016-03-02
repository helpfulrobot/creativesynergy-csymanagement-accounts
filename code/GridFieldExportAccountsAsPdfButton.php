<?php
class GridFieldExportAccountsAsPdfButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler {

  protected $targetFragment;

  public function __construct($targetFragment = "after", $exportColumns = null) {
    $this->targetFragment = $targetFragment;
  }

  public function getHTMLFragments($gridField) {
    $button = new GridField_FormAction(
      $gridField, 
      'pdfaccountexport', 
      'Als PDF-Datei exportieren',
      'pdfaccountexport', 
      null
    );

    $button->setAttribute('data-icon', 'disk');
    $button->addExtraClass('no-ajax');

    $items = $gridField->getList();
    
    if($items->first() && Account::checkIfPasswordIsUp2Date()) {
      if(count(array_unique($items->column('CompanyID'))) == 1) {
        return array(
          $this->targetFragment => '<p class="grid-pdf-button">' . $button->Field() . '</p>',
        );
      }
    }
  }

  public function getActions($gridField) {
    return array('pdfaccountexport');
  }

  public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
    if($actionName == 'pdfaccountexport') {
      return $this->handlePdfAccountExport($gridField);
    }
  }

  public function getURLHandlers($gridField) {
    return array(
      'pdfaccountexport' => 'handlePdfAccountExport',
    );
  }

  public function handlePdfAccountExport($gridField, $request = null) {
    // $now = date('d-m-Y-H-i');
    // $customerID = $gridField->getList()->first()->Company()->CustomerID;
    // $fileName = "datenblatt-$customerID-$now.pdf";
    $this->generateExportFileData($gridField);
  }

  public function generateExportFileData($gridField) {
    $items = $gridField->getList();

    $companyCount = count(array_unique($items->column('CompanyID')));
    $template = DocumentTemplate::get()->find('DocumentType', 'Account');

    $variables = [
      'Items' => GroupedList::create($items),
      'CompanyCount' => $companyCount,
      'Template' => $template
    ];

    $pdf = new SS_PDF();
    $pdf->setOption('header-html', BASE_PATH . '/assets/csymanagement/own/pdf-document-header.html');
    $pdf->setOption('footer-html', BASE_PATH . '/assets/csymanagement/own/pdf-document-footer.html');
    $pdf->setOption('user-style-sheet', BASE_PATH . '/csymanagement-client/css/pdf.css');
    $pdf->setOption('margin-top', 40);
    $pdf->setOption('margin-bottom', 40);
    $pdf->setOption('margin-left', 0.1);
    $pdf->setOption('margin-right', 0.1);

    $html = $pdf::getHtml($items->first(), $variables, 'AccountExportPdf');
    $pdf->add($html);
    $pdf->preview();
  }
}