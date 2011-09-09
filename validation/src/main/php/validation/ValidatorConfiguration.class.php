<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  /**
   *
   */
  class ValidatorConfiguration implements ValidatorConfigurationInterface {

    protected
      $type     = NULL,
      $mode     = NULL,
      $parameter= array();

    public function __construct($type, $mode, array $parameter) {
      $this->setType($type);
      $this->setMode($mode);
      $this->setParameter($parameter);
    }

    public function getType() {
      return $this->type;
    }

    public function setType($type) {
      if (!is_string($type) || empty($type)) {
        throw new IllegalArgumentException('$type must be a non empty string!');
      }
      $this->type= $type;
    }

    public function getMode() {
      return $this->mode;
    }

    public function setMode($mode) {
      if (isset($mode) && (!is_string($mode) || empty($mode))) {
        throw new IllegalArgumentException('$mode must be null or a non empty string!');
      }
      $this->mode= $mode;
    }

    public function getParameter() {
      return $this->parameter;
    }

    public function setParameter(array $parameter) {
      $this->parameter= $parameter;
    }

    public function addParameter(array $parameter) {
      $this->parameter= array_merge($this->parameter, $parameter);
    }

  }
?>