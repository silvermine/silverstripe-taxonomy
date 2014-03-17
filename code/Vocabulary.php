<?php

/**
 * A named grouping of VocabularyTerms used to classify content nodes.
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
      // TODO: need other validation here.  probably want just alphanumeric, maybe period and hyphen
      // SS templates need to be able to use the machine name as parameters to "if" statements
      return new RequiredFields('Name', 'MachineName');
   }

   public function getCMSFields() {
      $fields = new FieldList();
      $fields->add(new TextField('Name'));
      $fields->add(new TextField('MachineName'));

      if (!$this->ID) {
         // if we haven't been saved, we don't want to confuse the page
         // more with terms management since you can't actually manage
         // the terms relationships until you are persisted.
         return $fields;
      }

      $config = GridFieldConfig_RecordEditor::create($itemsPerPage = 25)
         ->removeComponentsByType('GridFieldDetailForm')
         ->addComponent($termDetailForm = new VocabularyTermDetailForm());

      // change button name on "add new"
      $config->getComponentByType('GridFieldAddNewButton')->setButtonName(_t('Vocabulary.AddNewTermButtonLabel', 'Add new term to this vocabulary'));

      // TODO: after a term is added, you should be taken back to the Vocabulary view or edit screen

      $termsGrid = new GridField(
         'Terms',
         'Terms',
         $this->Terms(),
         $config);
      $fields->add($termsGrid);

      // Update fields in extensions
      $this->extend('updateCMSFields', $fields);

      return $fields;
   }
}
