<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.ValidationContextInterface',
    'validation.ValidatorConfiguration'
  );

  /**
   *
   */
  interface ValidatorInterface {

    public function validate($value, ValidatorConfiguration $configuration, ValidationContextInterface $context);

  }
?>