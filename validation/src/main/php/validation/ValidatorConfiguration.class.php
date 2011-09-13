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
  class ValidatorConfiguration implements ValidatorConfigurationInterface {

    protected
      $type     = NULL,
      $groups   = NULL,
      $parameter= array();

    public function __construct(
      $type,
      array $parameter= array(),
      array $groups= NULL
    ) {
      $this->setType($type);
      $this->setGroups($groups);
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

    public function getGroups() {
      return $this->groups;
    }

    public function setGroups(array $groups= NULL) {
      if (isset($groups)) {
        $toset= array();
        foreach ($groups as $group) {
          if (!is_string($group) || empty($group)) {
            throw new IllegalArgumentException(
              '$groups must be null or an array of non empty strings!'
            );
          }
          $toset[]= $group;
        }
        $groups= $toset;
      }
      $this->groups= $groups;
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