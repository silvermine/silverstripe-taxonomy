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
    * Helper function to return all the VocabularyTerms applied to the owner
    * object from a given vocabulary.
    *
    * @param string $vocabMN the vocabulary machine name
    * @return DataList
    */
   public function getAppliedTermsFromVocab($vocabMN) {
      return $this->owner->getManyManyComponents('VocabularyTerms')
         ->innerJoin('Vocabulary', '"VocabularyTerm".VocabularyID = vocab.ID', 'vocab')
         ->where(sprintf('vocab.MachineName = \'%s\'', Convert::raw2sql($vocabMN)));
   }

   /**
    * Helper function for determining if the owner object has any term from a
    * given vocabulary.
    *
    * @param string $vocabMN the vocabulary machine name
    * @return boolean true if the owner has any term from this vocab
    */
   public function HasTermFromVocab($vocabMN) {
      return $this->getAppliedTermsFromVocab($vocabMN)->exists();
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
      $terms = $this->owner->getManyManyComponents('VocabularyTerms', sprintf('"VocabularyTerm".MachineName = \'%s\'', Convert::raw2sql($termMN)));

      if (!empty($vocabMN)) {
         $terms = $terms->innerJoin('Vocabulary', '"VocabularyTerm".VocabularyID = "Vocabulary".ID')
            ->where(sprintf('"Vocabulary".MachineName = \'%s\'', Convert::raw2sql($vocabMN)));
      }

      return $terms->exists();
   }

}
