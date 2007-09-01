<?php
/* This class is part of the XP framework
 *
 * $Id: CreatedAfterFilter.class.php 10594 2007-06-11 10:04:54Z friebe $
 */

  namespace io::collections::iterate;

  ::uses('io.collections.iterate.AbstractDateComparisonFilter');

  /**
   * Date comparison filter
   *
   * @purpose  Iteration Filter
   */
  class CreatedAfterFilter extends AbstractDateComparisonFilter {

    /**
     * Accepts an element
     *
     * @param   io.collections.IOElement element
     * @return  bool
     */
    public function accept($element) { 
      return ($cmp= $element->createdAt()) && $cmp->isAfter($this->date);
    }
  }
?>