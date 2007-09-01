<?php
/* This class is part of the XP framework
 *
 * $Id: XMLParser.class.php 10594 2007-06-11 10:04:54Z friebe $
 */

  namespace xml::parser;

  uses('xml.XML', 'xml.XMLFormatException');
  
  /**
   * XML Parser
   *
   * Example:
   * <code>
   *   uses('xml.parser.XMLParser');
   *
   *   $parser= &new XMLParser();
   *   try(); {
   *     $parser->parse($xml);
   *   } if (catch('XMLFormatException', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   * </code>
   *
   * @purpose  Parse XML
   */
  class XMLParser extends lang::Object {
    public
      $encoding     = '',
      $callback     = NULL;
    

    /**
     * Constructor
     *
     * @param   string encoding default ''
     */
    public function __construct($encoding= '') {
      $this->encoding= $encoding;
    }

    /**
     * Set callback
     *
     * @param   xml.parser.ParserCallback callback
     */
    public function setCallback($callback) {
      $this->callback= $callback;
    }

    /**
     * Set Encoding
     *
     * @param   string encoding
     */
    public function setEncoding($encoding) {
      $this->encoding= $encoding;
    }

    /**
     * Get Encoding
     *
     * @return  string
     */
    public function getEncoding() {
      return $this->encoding;
    }
    
    /**
     * Parse XML data
     *
     * @param   string data
     * @param   string source default NULL optional source identifier, will show up in exception
     * @return  bool
     * @throws  xml.XMLFormatException in case the data could not be parsed
     * @throws  lang.NullPointerException in case a parser could not be created
     */
    public function parse($data, $source= ) {
      if ($parser = xml_parser_create($this->encoding)) {
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, FALSE);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'ISO-8859-1');

        // Register callback
        if ($this->callback) {
          xml_set_object($parser, $this->callback);
          xml_set_element_handler($parser, 'onStartElement', 'onEndElement');
          xml_set_character_data_handler($parser, 'onCData');
          xml_set_default_handler($parser, 'onDefault');
        }
      
        // Parse data
        if (!xml_parse($parser, $data, TRUE)) {
          $type= xml_get_error_code($parser);
          $line= xml_get_current_line_number($parser);
          $column= xml_get_current_column_number($parser);
          xml_parser_free($parser);
          libxml_clear_errors();
          throw new xml::XMLFormatException(
            xml_error_string($type),
            $type,
            $source,
            $line,
            $column
          );
        }
        xml_parser_free($parser);
        return TRUE;
      }

      throw new lang::NullPointerException('Could not create parser');
    }
  }
?>