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

    public function getGroups();

    public function setGroups(array $groups= NULL);

    public function getParameter();

    public function setParameter(array $parameter);

    public function addParameter(array $parameter);

  }
?>