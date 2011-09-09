<?php
  /* This interface is part of the XP framework
   *
   * $Id$
   */

  uses(
    'validation.FieldValidatorConfiguration',
    'validation.ValidationConfigurationInterface',
    'validation.ValidationConfigurationProviderInterface',
    'validation.ValidatorConfiguration'
  );

  /**
   * You can put assert annotations at class, fields and methods
   *
   * #[@assert(
   * #    array('type' => 'NotNullValidator', 'field' => 'name'),
   * #    array('type' => 'NumberValidator')
   * #)]
   */
  class AnnotationValidationConfigurationProvider implements ValidationConfigurationProviderInterface {

    public function extendConfiguration(ValidationConfigurationInterface $configuration) {
      $classname= $configuration->getName();
      $class= XPClass::forName($classname);

      if ($class->hasAnnotation('assert')) {
        foreach ($class->getAnnotation('assert') as $parameter) {
          $fieldName= NULL;
          if (isset($parameter['field'])) {
            $fieldName= $parameter['field'];
            unset($parameter['field']);
          }

          if (!isset($parameter['type'])) {
            throw new Exception('missing type definition!');
          }
          $type= $parameter['type'];
          unset($parameter['type']);

          $mode= NULL;
          if (isset($parameter['mode'])) {
            $mode= $parameter['mode'];
            unset($parameter['mode']);
          }

          if (isset($fieldName)) {
            $classConfiguration= $this->newFieldValidatorConfiguration($fieldName, $type, $mode, $parameter);
          } else {
            $classConfiguration= $this->newValidatorConfiguration($type, $mode, $parameter);
          }
          $configuration->addClassConfiguration($classConfiguration);
        }
      }

      foreach ($class->getDeclaredFields() as $field) {
        if (!$field->hasAnnotation('assert')) {
          continue;
        }
        $defaultFieldName= $field->getName();
        foreach ($field->getAnnotation('assert') as $parameter) {
          $fieldName= $defaultFieldName;
          if (isset($parameter['field'])) {
            $fieldName= $parameter['field'];
            unset($parameter['field']);
          }

          if (!isset($parameter['type'])) {
            throw new Exception('missing type definition!');
          }
          $type= $parameter['type'];
          unset($parameter['type']);

          $mode= NULL;
          if (isset($parameter['mode'])) {
            $mode= $parameter['mode'];
            unset($parameter['mode']);
          }
          $fieldConfiguration= $this->newFieldValidatorConfiguration($fieldName, $type, $mode, $parameter);
          $configuration->addFieldConfiguration($fieldConfiguration);
        }
      }

      foreach ($class->getDeclaredMethods() as $method) {
        if (!$method->hasAnnotation('assert')) {
          continue;
        }
        $defaultFieldName= $this->extractFieldname($method);
        foreach ($method->getAnnotation('assert') as $parameter) {
          $fieldName= $defaultFieldName;
          if (isset($parameter['field'])) {
            $fieldName= $parameter['field'];
            unset($parameter['field']);
          }

          if (!isset($parameter['type'])) {
            throw new Exception('missing type definition!');
          }
          $type= $parameter['type'];
          unset($parameter['type']);

          $mode= NULL;
          if (isset($parameter['mode'])) {
            $mode= $parameter['mode'];
            unset($parameter['mode']);
          }
          $fieldConfiguration= $this->newFieldValidatorConfiguration($fieldName, $type, $mode, $parameter);
          $configuration->addFieldConfiguration($fieldConfiguration);
        }
      }
    }

    protected function extractFieldname($method) {
      $declaringClass= $method->getDeclaringClass();
      $methodName= $method->getName();

      $rest= NULL;
      if ('get' === strtolower(substr($methodName, 0, 3))) {
        $rest= substr($methodName, 3);
      } else if ('is' === strtolower(substr($methodName, 0, 2))) {
        $rest= substr($methodName, 2);
      }
      if (isset($rest)) {
        foreach ($declaringClass->getDeclaredFields() as $field) {
          $fieldName= $field->getName();
          if (strtolower($rest) === strtolower($fieldName)) {
            return $fieldName;
          }
        }
        return lcfirst($rest);
      }
      return $methodName;
    }

    protected function newValidatorConfiguration($type, $mode, array $parameter) {
      return new ValidatorConfiguration($type, $mode, $parameter);
    }

    protected function newFieldValidatorConfiguration($fieldName, $type, $mode, array $parameter) {
      return new FieldValidatorConfiguration($fieldName, $type, $mode, $parameter);
    }
  }
?>