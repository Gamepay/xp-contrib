<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  /**
   *
   */
  interface ValidationContextInterface {

    public function getValidationService();

    public function getParentContext();

    public function getChildContextNames();

    public function getChildContext($name, $create= FALSE);

    public function logMessage($message);

    public function getMessages();
  }

?>