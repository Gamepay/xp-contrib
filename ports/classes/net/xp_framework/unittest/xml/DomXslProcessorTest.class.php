<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses(
    'xml.DomXSLProcessor',
    'net.xp_framework.unittest.xml.AbstractProcessorTest'
  );

  /**
   * ProcessorTest implementation that tests the DomXSL processor
   *
   * @see      xp://xml.DomXSLProcessor
   * @ext      domxml
   * @purpose  Unit Test
   */
  class DomXslProcessorTest extends AbstractProcessorTest {

    /**
     * Returns the PHP extension needed for this processor test to work
     *
     * @return  string
     */
    public function neededExtension() { 
      return 'domxml';
    }
  
    /**
     * Returns the XSL processor instance to be used
     *
     * @return  &xml.IXSLProcessor
     */
    public function processorInstance() {
      return new DomXSLProcessor();
    }

    /**
     * Returns the XSL processor's default output charset
     *
     * @return  string
     */
    public function processorCharset() { 
      return 'utf-8';
    }
  }
?>
