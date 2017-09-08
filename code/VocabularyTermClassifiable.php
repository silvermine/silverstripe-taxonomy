<?php

/**
 * DataExtension that can be added to SiteTree nodes to allow them to be
 * classified by VocabularyTerms from the Taxonomy plugin.
 *
 * @author Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @copyright (c) 2012 Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @package silverstripe-taxonomy
 * @subpackage code
 */
class VocabularyTermClassifiable extends DataExtension {

   static $many_many = array(
      'VocabularyTerms' => 'VocabularyTerm',
   );

   public function updateCMSFields(FieldList $fields) {
      // allow adding existing, but not editing the actual vocabulary terms
      $config = GridFieldConfig_Base::create($itemsPerPage = 20)
         ->addComponent(new GridFieldButtonRow('before'))
         ->addComponent($autocomplete = new VocabularyTermClassifiableGridFieldAddExistingAutocompleter('buttons-before-left'))
         ->addComponent(new GridFieldDeleteAction(true))
      ;
      $autocomplete->setResultsFormat(VocabularyTerm::AUTO_COMPLETE_FORMAT);

      $picker = GridField::create(
         'VocabularyTerms',
         _t('Vocabulary.TermsLabel', 'Vocabulary Terms'),
         $this->owner->VocabularyTerms(),
         $config
      );
      $fields->addFieldToTab('Root.Taxonomy', $picker);
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
    * a particular vocabulary.
    *
    * @param string $vocabMN the vocabulary machine name
    * @param string $termMN the term machine name
    * @return boolean true if the owner has this term
    */
   public function HasVocabTerm($vocabMN, $termMN) {
      foreach ($this->owner->VocabularyTerms() as $term) {
         if ($term->MachineName == $termMN && (empty($vocabMN) || $term->Vocabulary()->MachineName == $vocabMN)) {
            return true;
         }
      }

      return false;
   }

}
