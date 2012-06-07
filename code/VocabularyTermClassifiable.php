<?php

/* DataObjectDecorator that can be added to SiteTree nodes to allow them to be
 * classified by VocabularyTerms from the Taxonomy plugin.
 *
 * @author Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @copyright (c) 2012 Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @package silverstripe-taxonomy
 * @subpackage code
 */
class VocabularyTermClassifiable extends DataObjectDecorator {

   function extraStatics() {
      return array(
         'many_many' => array(
            'VocabularyTerms' => 'VocabularyTerm',
         ),
      );
   }

   public function updateCMSFields(FieldSet &$fields) {
      $fields->addFieldToTab('Root.Taxonomy', new VocabularyTermClassifiableManyManyPickerField(
         $this->owner,
        'VocabularyTerms',
        _t('Vocabulary.Terms.Label', 'Vocabulary Terms'),
        array(
           'ShowPickedInSearch' => false,
        )
      ));
   }

   /**
    * Helper function for determining if the owner object has any term from a
    * given vocabulary.
    *
    * @param string $vocabMN the vocabulary machine name
    * @return boolean true if the owner has any term from this vocab
    */
   public function HasTermFromVocab($vocabMN) {
      foreach ($this->owner->VocabularyTerms() as $term) {
         if ($term->Vocabulary()->MachineName == $vocabMN) {
            return true;
         }
      }

      return false;
   }

   /**
    * Helper function for templates to see if a particular page has a term in
    * a particular vocabulary.  Note that since SS (2.4.x) will not parse
    * <% uf HasVocabName($termMN, $vocabMN) %> we must result to a hack to
    * allow checking a particular vocabulary and term.  If you need to do this,
    * use the form <% if HasVocabName(vocMN_termMN) %> where you separate the
    * vocabulary machine name and term machine name by an underscore and place
    * the vocabulary machine name first.
    *
    * @param string $termMN the term machine name (or both vocab and term - see above)
    * @param string $vocabMN the vocabulary machine name
    * @return boolean true if the owner has this term
    */
   public function HasVocabTerm($termMN, $vocabMN = '') {
      if (strpos($termMN, '_') !== false) {
         $names = explode('_', $termMN);
         $vocabMN = $names[0];
         $termMN = $names[1];
      }

      foreach ($this->owner->VocabularyTerms() as $term) {
         if ($term->MachineName == $termMN && (empty($vocabMN) || $term->Vocabulary()->MachineName == $vocabMN)) {
            return true;
         }
      }

      return false;
   }

}

class VocabularyTermClassifiableManyManyPickerField extends ManyManyPickerField {

   public function __construct($parent, $name, $title = null, $options = null) {
      parent::__construct($parent, $name, $title, $options);
   }

   /**
    * Add not just the term, but its parents as well if we have them.
    */
   public function Add($data, $term, $write = true) {
      $accessor = $this->name;
      $this->parent->$accessor()->add($term);

      $parents = $term ? $term->Parents() : false;
      if ($parents) {
         foreach ($parents as $parent) {
            $this->Add($data, $parent, false);
         }
      }

      if ($write) {
         $this->parent->write();
         return Director::is_ajax() ? $this->renderWith('ItemSetField') : Director::redirectBack();
      }

      return null;
   }

}
