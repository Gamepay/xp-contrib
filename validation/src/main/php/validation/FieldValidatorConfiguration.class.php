<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.FieldValidatorConfigurationInterface',
    'validation.ValidatorConfiguration'
  );

  /**
   *
   */
  class FieldValidatorConfiguration extends ValidatorConfiguration implements FieldValidatorConfigurationInterface {

    protected
      $fieldName= NULL;

    public function __construct($fieldName, $type, $mode, array $parameter) {
      parent::__construct($type, $mode, $parameter);
      $this->setFieldName($fieldName);
    }

    public function getFieldName() {
      return $this->fieldName;
    }

    public function setFieldName($fieldName) {
      if (!is_string($fieldName) || empty($fieldName)) {
        throw new IllegalArgumentException('$fieldName must be a non empty string!');
      }
      $this->fieldName= $fieldName;
    }
  }
?>