<?php

/**
 * A term that can be applied to content nodes to identify and classify them
 * into a secondary categorization (where the primary categorization comes from
 * the natural ordering of the nodes within the site tree).
 *
 * @author Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @copyright (c) 2012 Jeremy Thomerson <jeremy@thomersonfamily.com>
 * @package silverstripe-taxonomy
 * @subpackage code
 */
class VocabularyTerm extends DataObject implements PermissionProvider {

   const AUTO_COMPLETE_FORMAT = '$Term.RAW ($Vocabulary.MachineName.RAW:$MachineName.RAW)';

   static $db = array(
      'Term'        => 'VARCHAR(128)',
      'MachineName' => 'VARCHAR(32)',
   );

   static $has_one = array(
      'Vocabulary' => 'Vocabulary',
   );

   static $many_many = array(
      'Children' => 'VocabularyTerm',
   );

   static $belongs_many_many = array(
      'Parents' => 'VocabularyTerm',
   );

   static $default_sort = 'Term';

   // config for data model admin:
   static $summary_fields = array(
      'Term',
      'MachineName',
      'ParentsTerms',
      'ChildrenTerms',
   );

   static $searchable_fields = array(
      'Term',
      'MachineName',
   );

   public static function find_by_machine_names($vocabMachName, $termMachName) {
      $vocab = Vocabulary::find_by_machine_name($vocabMachName);
      if (!$vocab) {
         return null;
      }

      return self::get_one(
         'VocabularyTerm',
         sprintf('"VocabularyTerm"."VocabularyID" = %d AND "VocabularyTerm"."MachineName" = \'%s\'',
            $vocab->ID,
            Convert::raw2sql($termMachName)
         )
      );
   }

   public function getChildrenTerms() {
      return implode(', ', $this->Children()->map('ID', 'Term')->toArray());
   }

   public function getCMSValidator() {
      return new RequiredFields('Term', 'MachineName', 'VocabularyID');
   }

   function getCMSFields() {
      $fields = new FieldList();
      $fields->add(new TextField('Term'));
      $fields->add(new TextField('MachineName'));

      if (!$this->ID) {
         // if we haven't been saved, we don't want to confuse the page
         // more with children management since you can't actually manage
         // the children relationships until you are persisted.
         return $fields;
      }

      $config = GridFieldConfig_RelationEditor::create($itemsPerPage = 25);
      // Don't allow creating a new term from this page (at this time).
      // It is simply for linking to existing ones.  In the future we
      // could add support for creating children directly from their parent.
      $config->removeComponentsByType('GridFieldAddNewButton');
      $config->getComponentByType('GridFieldAddExistingAutocompleter')->setResultsFormat(self::AUTO_COMPLETE_FORMAT);

      $childrenGrid = new GridField(
         'Children',
         'Children',
         $this->Children(),
         $config
      );
      $fields->add($childrenGrid);

      // Update fields in extensions
      $this->extend('updateCMSFields', $fields);

      return $fields;
   }

   public function getParentsTerms() {
      return implode(', ', $this->Parents()->map('ID', 'Term')->toArray());
   }

   public function onBeforeDelete() {
      parent::onBeforeDelete();

      // don't actually delete them, but remove
      // the association
      $this->Children()->removeAll();
   }

   public function getTitle() {
      // this is used by the SS admin UI as what shows in breadcrumbs,
      // successful save/edit messages, etc
      return SSViewer::fromString(self::AUTO_COMPLETE_FORMAT)->process($this);
   }

   public function canView($member = null) {
      return Permission::check(array('ADMIN', 'VIEW_TERM'), 'any', $member);
   }

   public function providePermissions() {
      return array(
         'VIEW_TERM' => array(
            'name' => _t('VocabularyTerm.VIEW_ALL_DESCRIPTION', 'View any vocabulary term'),
            'category' => _t('Permissions.TAXONOMY_CATEGORY', 'Taxonomy permissions'),
            'sort' => -100,
            'help' => _t('VocabularyTerm.VIEW_ALL_HELP', 'Ability to see any vocabulary term on the site')
         ),
      );
   }
}
