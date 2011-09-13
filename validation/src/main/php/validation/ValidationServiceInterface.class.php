<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  /**
   *
   */
  interface ValidationServiceInterface {

    public function validate(
      $object,
      array $groups= NULL,
      ValidationContextInterface $context= NULL
    );

  }
?>