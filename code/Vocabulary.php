<?php

/* A named grouping of VocabularyTerms used to classify content nodes.
 *
 * @author Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @copyright (c) 2012 Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @package silverstripe-taxonomy
 * @subpackage code
 */
class Vocabulary extends DataObject {

   static $db = array(
      'Name'        => 'VARCHAR(64)',
      'MachineName' => 'VARCHAR(32)',
   );

   static $has_many = array(
      'Terms' => 'VocabularyTerm'
   );

   static $default_sort = 'Name';

   // config for data model admin:
   static $summary_fields = array(
      'Name',
      'MachineName',
   );

   public static function find_by_machine_name($vocabMachName) {
      return DataObject::get_one(
         'Vocabulary',
         sprintf('"Vocabulary"."MachineName" = \'%s\'',
            Convert::raw2sql($vocabMachName)
         )
      );
   }

   public function getCMSValidator() {
      return new RequiredFields('Name', 'MachineName');
   }
}
