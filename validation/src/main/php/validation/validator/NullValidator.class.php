<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.ValidationContextInterface',
    'validation.ValidatorConfiguration',
    'validation.ValidatorInterface'
  );

  /**
   *
   */
  class NullValidator implements ValidatorInterface {

    public function validate($value, ValidatorConfiguration $configuration, ValidationContextInterface $context) {
      if (isset($value)) {
        $context->logMessage('Value must be null!');
      }
    }

  }
?>