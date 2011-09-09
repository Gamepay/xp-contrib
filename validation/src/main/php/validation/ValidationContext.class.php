<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.ValidationContextInterface',
    'validation.ValidationServiceInterface'
  );

  /**
   *
   */
  class ValidationContext implements ValidationContextInterface {

    protected
      $name             = NULL,
      $validationService= NULL,
      $parentContext    = NULL,
      $childContext     = array(),
      $messages         = array();

    public function __construct(
      $name,
      ValidationServiceInterface $validationService,
      ValidationContextInterface $parentContext= NULL
    ) {
      if (!isset($name) || empty($name)) {
        throw new IllegalArgumentException('$name must be a non empty string!');
      }
      $this->name= $name;
      $this->validationService= $validationService;
      $this->parentContext= $parentContext;
    }

    public function getValidationService() {
      return $this->validationService;
    }

    public function getParentContext() {
      return $this->parentContext;
    }

    public function getChildContextNames() {
      return array_keys($this->childContext);
    }

    public function getChildContext($name, $create= FALSE) {
      if (!is_string($name) || empty($name)) {
        throw new IllegalArgumentException('$name must be a non empty string!');
      }
      if (!is_bool($create)) {
        throw new IllegalArgumentException('$create must be a boolean!');
      }
      if (!isset($this->childContext[$name])) {
        if ($create) {
          $childContext= $this->newChildContext($name);
          $this->childContext[$name]= $childContext;
        } else {
          throw new IllegalStateException('no child context with name '.$name.'found!');
        }
      }
      return $this->childContext[$name];
    }

    public function logMessage($message) {
      if (!is_string($message) || empty($message)) {
        throw new IllegalArgumentException('$message must be a non empty string!');
      }
      $this->messages[]= $message;
    }

    public function hasMessages($recursive= TRUE) {
      if (!empty($this->messages)) {
        return TRUE;
      }
      if ($recursive) {
        foreach ($this->childContext as $context) {
          if ($context->hasMessages($recursive)) {
            return TRUE;
          }
        }
      }
      return FALSE;
    }

    public function getMessages($recursive= TRUE) {
      $allMessages= array();
      if (!empty($this->messages)) {
        $allMessages[$this->name]= $this->messages;
      }
      if ($recursive) {
        foreach ($this->childContext as $context) {
          $messages= $context->getMessages();
          foreach ($messages as $childName => $message) {
            $allMessages[$this->name.'.'.$childName]= $message;
          }
        }
      }
      return $allMessages;
    }

    protected function newChildContext($name) {
      $classname= get_class($this);
      return new $classname($name, $this->validationService, $this);
    }
  }

?>