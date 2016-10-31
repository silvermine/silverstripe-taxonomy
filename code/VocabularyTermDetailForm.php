<?php

/**
 * Detail form for adding a term.  Only additional logic we add here is custom
 * redirect back to vocabulary page after the term is added (instead of the edit
 * page for the new term, which is confusing because it looks like the form didn't
 * do anything since you still see your field values of the term you just added
 * along with a blank list of children).
 *
 * @author Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @copyright (c) 2013 Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @package silverstripe-taxonomy
 * @subpackage code
 */
class VocabularyTermDetailForm extends GridFieldDetailForm {

   // need this as a placeholder so that we use our own _ItemRequest class below

}

class VocabularyTermDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {

   public function doSave($data, $form) {
      parent::doSave($data, $form);
      return Controller::curr()->redirect($this->getBackLink());
   }

}
