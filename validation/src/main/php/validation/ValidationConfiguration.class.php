<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.ValidationConfigurationInterface'
  );

  /**
   *
   */
  class ValidationConfiguration implements ValidationConfigurationInterface {

    protected
      $name                = NULL,
      $classConfigurations = array(),
      $fieldsConfigurations= array();

    public function __construct($name) {
      if (!is_string($name) || empty($name)) {
        throw new IllegalArgumentException('$name must be a non empty string!');
      }
      $this->name= $name;
    }

    public function getName() {
      return $this->name;
    }

    public function addClassConfiguration(
      ValidatorConfigurationInterface $configuration
    ) {
      $this->classConfigurations[]= $configuration;
    }

    public function getClassConfigurations() {
      return $this->classConfigurations;
    }

    public function getFieldNames() {
      return array_keys($this->fieldsConfigurations);
    }

    public function addFieldConfiguration(
      FieldValidatorConfigurationInterface $configuration
    ) {
      $fieldName= $configuration->getFieldName();
      if (!isset($this->fieldsConfigurations[$fieldName])) {
        $this->fieldsConfigurations[$fieldName]= array();
      }
      $this->fieldsConfigurations[$fieldName][]= $configuration;
    }

    public function getFieldConfigurations($field) {
      if (!is_string($field) || empty($field)) {
        throw new IllegalArgumentException(
          '$field must be a non empty string!'
        );
      }
      if (!isset($this->fieldsConfigurations[$field])) {
        $retval= array();
        return $retval;
      }
      return $this->fieldsConfigurations[$field];
    }
  }
?>