<?php
class Account extends DataObject {

  private static $singular_name = 'Account';
  private static $plural_name = 'Accounts';

  private static $db = array();
  private static $has_one = array();
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

  /*
  public function canCreate($member = null) {
    return true;
  }

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

  public function getCMSFields() {
    // $fields = parent::getCMSFields();
    // $fields->addFieldsToTab('Root.Main', array());
    
    $fields = FieldList::create(
      TabSet::create('Root',
        Tab::create('Main', 'Haupt-Inhalt'

        )
      )
    );
    
    return $fields;
  }
}