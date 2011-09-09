<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.ValidatorConfigurationInterface'
  );

  /**
   *
   */
  interface FieldValidatorConfigurationInterface extends ValidatorConfigurationInterface {

    public function getFieldName();

    public function setFieldName($fieldName);
  }
?>