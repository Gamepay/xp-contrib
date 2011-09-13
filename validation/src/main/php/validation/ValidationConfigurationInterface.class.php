<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  /**
   *
   */
  interface ValidationConfigurationInterface {

    public function getName();

    public function addClassConfiguration(
      ValidatorConfigurationInterface $configuration
    );

    public function getClassConfigurations();

    public function getFieldNames();

    public function addFieldConfiguration(
      FieldValidatorConfigurationInterface $configuration
    );

    public function getFieldConfigurations($field);


  }
?>