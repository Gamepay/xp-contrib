<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  /**
   *
   */
  interface ValidationContextInterface {

    public function getName();

    public function getValidationService();

    public function getParentContext();

    public function getChildContextNames();

    public function getChildContext($name, $create= FALSE);

    public function logMessage($message);

    public function hasMessages($recursive= TRUE);

    public function getMessages($recursive= TRUE);
  }

?>