<?php
  /* This class is part of the XP framework
   *
   * $Id$
   */

  #[@assert(
  #   array('type' => 'NotNullValidator'),
  #   array('field' => 'noAnnotationField', 'type' => 'NotNullValidator', 'groups' => array('noAnnotation'))
  #)]
  abstract class AnnotationTestBase extends Object {

    public $noAnnotationField;

    #[@assert(
    #   array('type' => 'NotNullValidator', 'groups' => array('protected', 'private'))
    #)]
    private $privateField = 'private Field';

    #[@assert(
    #   array('type' => 'NotNullValidator', 'groups' => array('protected'))
    #)]
    protected $protectedField = 'protected Field';

    #[@assert(
    #   array('type' => 'NotNullValidator')
    #)]
    public $publicField = 'public Field';

    #[@assert(
    #   array('type' => 'NullValidator', 'groups' => array('protected', 'private'))
    #)]
    public function privateField() {

    }

    #[@assert(
    #   array('type' => 'NullValidator', 'groups' => array('protected'))
    #)]
    public function isProtectedField() {

    }

    #[@assert(
    #   array('type' => 'NullValidator')
    #)]
    public function getPublicField() {

    }
  }

?>