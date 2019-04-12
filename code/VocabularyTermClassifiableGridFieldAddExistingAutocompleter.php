<?php

/**
 * Autocompleter that automatically adds all parent vocabulary terms whenever
 * a term is added to something that has the VocabularyTermClassifiable extension
 * on it.
 *
 * @author Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @copyright (c) 2013 Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @package silverstripe-taxonomy
 * @subpackage code
 */
class VocabularyTermClassifiableGridFieldAddExistingAutocompleter extends GridFieldAddExistingAutocompleter {

   /**
    * Add not just the term, but its parents as well if we have them.
    *
    * @see GridFieldAddExistingAutocompleter->getManipulatedData(GridField, SS_List)
    */
   public function getManipulatedData(GridField $gridField, SS_List $dataList) {
      $origID = $gridField->State->GridFieldAddRelation(null);
      $origCount = $dataList->count();

      $manipulatedDataList = parent::getManipulatedData($gridField, $dataList);

      if ($origID && ($manipulatedDataList->count() > $origCount)) {
         $term = DataObject::get_by_id($dataList->dataclass(), Convert::raw2sql($origID));
         if ($term) {
            $this->addAllParents($manipulatedDataList, $term);
         }
      }
      return $manipulatedDataList;
   }

   private function addAllParents($dataList, $term) {
      $parents = $term->Parents();
      foreach ($term->Parents() as $parent) {
         $dataList->add($parent);
         $this->addAllParents($dataList, $parent);
      }
   }
}
