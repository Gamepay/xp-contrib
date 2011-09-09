<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  /**
   *
   */
  interface ValidatorConfigurationInterface {

    public function getType();

    public function setType($type);

    public function getMode();

    public function setMode($mode);

    public function getParameter();

    public function setParameter(array $parameter);

    public function addParameter(array $parameter);

  }
?>