<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.AnnotationValidationConfigurationProvider',
    'validation.DefaultValidatorProvider',
    'validation.ValidationConfiguration',
    'validation.ValidationConfigurationProviderInterface',
    'validation.ValidationContext',
    'validation.ValidationContextInterface',
    'validation.ValidationServiceInterface',
    'validation.ValidatorInterface',
    'validation.ValidatorProviderInterface'
  );

  /**
   *
   */
  class ValidationService implements ValidationServiceInterface {

    protected
      $configuration        = array(),
      $configurationProvider= NULL,
      $validator            = array(),
      $validatorProvider    = NULL;

    public function __construct(
      ValidationConfigurationProviderInterface $configurationProvider= NULL,
      ValidatorProviderInterface $validatorProvider= NULL
    ) {
      if (!isset($configurationProvider)) {
        $this->configurationProvider= new AnnotationValidationConfigurationProvider();
      }
      if (!isset($validatorProvider)) {
        $this->validatorProvider= new DefaultValidatorProvider();
      }
    }

    protected function getConfiguration($name) {
      if (!isset($this->configuration[$name])) {
        $configuration= $this->newValidationConfiguration($name);
        $this->configurationProvider->extendConfiguration($configuration);
        $this->configuration[$name]= $configuration;
      }
      return $this->configuration[$name];
    }

    protected function getValidator($type) {
      if (!isset($this->validator[$type])) {
        $validator= $this->validatorProvider->getValidator($type);
        if (!isset($validator)) {
          throw new Exception('unable to locate validator type '.$type);
        }
        if (! $validator instanceof ValidatorInterface) {
          throw new Exception('validator is not in instanceof ValidatorInterface');
        }
        $this->validator[$type]= $validator;
      }
      return $this->validator[$type];
    }

    public function validate($object, $mode= NULL, ValidationContextInterface $context= NULL) {
      if (!is_object($object)) {
        throw new IllegalArgumentException('$object must be an object');
      }
      if (isset($mode) && (!is_string($mode) || empty($mode))) {
        throw new IllegalArgumentException('$mode must be null or a non empty string!');
      }
      $class= XPClass::forName(xp::typeOf($object));
      if (!isset($context)) {
        $context= $this->newValidationContext($class->getSimpleName());
      }
      $done= array();
      $queue= array($class->getName() => $class);
      while (!empty($queue)) {
        $class= array_shift($queue);

        if (isset($done[$class->getName()])) {
          continue;
        }
        $done[$class->getName()]= TRUE;

        $parentClass= $class->getParentClass();
        if (isset($parentClass)) {
          if (isset($done[$parentClass->getName()])) {
            continue;
          }
          if (isset($queue[$parentClass->getName()])) {
            continue;
          }
          $queue[$parentClass->getName()]= $parentClass;
        }
        foreach ($class->getInterfaces() as $interface) {
          if (isset($done[$interface->getName()])) {
            continue;
          }
          if (isset($queue[$interface->getName()])) {
            continue;
          }
          $queue[$interface->getName()]= $interface;
        }

        $configuration= $this->getConfiguration($class->getName());

        foreach ($configuration->getClassConfigurations() as $validatorConf) {
          $validator= $this->getValidator($validatorConf->getType());
          $validator->validate($object, $validatorConf, $context);
        }

        $fieldNames= $configuration->getFieldNames();
        foreach ($fieldNames as $fieldName) {
          $read= FALSE;
          $value= NULL;

          if (!$read && $class->hasField($fieldName)) {
            $field= $class->getField($fieldName);
            if ($field->getModifiers() & MODIFIER_PUBLIC) {
              $value= $field->get($object);
              $read= TRUE;
            }
          }

          if (!$read) {
            $read= $this->collectValue($object, $class, $fieldName, $value);
          }
          if (!$read) {
            $read= $this->collectValue($object, $class, 'is'.$fieldName, $value);
          }
          if (!$read) {
            $read= $this->collectValue($object, $class, 'get'.$fieldName, $value);
          }

          if (!$read) {
            throw new Exception('unable to read '.$fieldName);
          }

          $childContext= $context->getChildContext($fieldName, TRUE);

          foreach ($configuration->getFieldConfigurations($fieldName) as $validatorConf) {
            $validator= $this->getValidator($validatorConf->getType());
            $validator->validate($value, $validatorConf, $childContext);
          }
        }

        $class= $class->getParentClass();
      }
      return $context;
    }

    protected function collectValue($object, XPClass $class, $methodName, &$value) {
      if (!$class->hasMethod($methodName)) {
        return FALSE;
      }
      $method= $class->getMethod($methodName);
      if (!($method->getModifiers() & MODIFIER_PUBLIC)) {
        return FALSE;
      }
      if ($method->numParameters() > 0) {
        $parameter= $method->getParameter(0);
        if (!$parameter->isOptional()) {
          return FALSE;
        }
      }
      try {
        $value= $method->invoke($object);
        return TRUE;
      } catch (Exception $exc) {
      }
      return FALSE;
    }

    protected function newValidationConfiguration($name) {
      return new ValidationConfiguration($name);
    }

    protected function newValidationContext($name) {
      return new ValidationContext($name, $this);
    }
  }
?>