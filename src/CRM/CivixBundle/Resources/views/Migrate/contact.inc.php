<?php
echo "<?php\n";
?>

/**
 * @file
 * A basic migration example.
 */

/**
 * Comments here are limited to CiviMigrate extension - more information is available in
 * the migrate_example module
 *
 * This is a very simple example drawing from a single table
 */
class <?php echo $fullName; ?>_Contact extends <?php echo $fullName; ?>_migrate {
  protected $base_table_id = 'id'; // name of id field
  protected $base_table_alias = '<?php
$base_table = !empty($table_map['contact']) ? $table_map['contact'] : "";
echo $base_table;
?>';
  protected $base_table = '<?php
$base_table = !empty($table_map['contact']) ? $table_map['contact'] : "";
echo $base_table;
?>';
  protected $_base_table_string;
  protected $entity = 'contact'; // this is the default
  // set to TRUE for debug info
  protected $debug = TRUE;

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    if (empty($this->base_table)) {
      $this->setPlaceHolder();
      return;
    }

    $this->description = t('Import contacts table');

    /**
     * If you have no defined the base table above & the base_table_id the parent class will define the mapping otherwise
     * see migrate_example module for how
     *
     * Destination is defined by parent based on the $entity defined above
     */

    /*
     * Define the query
     *
     * getQuery is a civimigrate shortcut for adding all the fields in the table with the table
     * name as a prefix - ie name is added as names_name. The id is not prefixes as this causes errors
     * the prefixing is convenient if you add more than one table with similarly named columns.
     * Alternatively you can use any db_select syntax to generate the query- check the beer migration for an example.
     *
     *
     *       ->condition('inactive', 0)
     *       ->condition('deceased', 0)
     *       ->addJoin('left', 'constituent_salutation', 'salutation', 'salutation.constit_id = records.constituent_id')
     *       ->addField('salutation', 'salutation', 'salutation')
     *       ->addExpression('IF(ISNULL(salutation), NULL, CONCAT(\'Dear\' , salutation))', 'salutation');
     *       ;
     */
    $query = $this->getQuery($this->base_table, $this->base_table_alias, $this->base_table_id)
      // Sample Condition
      ->condition('1', array('1', '2'), 'IN');

    $this->source = new MigrateSourceSQL($query);

    // Mappings can be stored in the database (civicrm_mapping (some UI is available)) or code (allowing version control).
    // You can mix them, but you wil get an error if you overlap on specific ones.
    // They are listed here for convenience to get you started.
    // At this stage we will disable  - but it might be an option for civix.
    $useHardCodedMappings = FALSE;

    if ($useHardCodedMappings) {
      /*
       * Add the field mappings
       */
      $this->addFieldMapping('first_name')->issueGroup('To be mapped');
      $this->addFieldMapping('middle_name')->issueGroup('To be mapped');
      $this->addFieldMapping('nick_name')->issueGroup('To be mapped');
      $this->addFieldMapping('last_name')->issueGroup('To be mapped');
      $this->addFieldMapping('external_identifier', $this->base_table_id)->issueGroup('Done');
      $this->addFieldMapping('legal_identifier')->issueGroup('To be mapped');
      $this->addFieldMapping('contact_type', 'To be mapped')->defaultValue('Individual');
      $this->addFieldMapping('contact_sub_type', NULL, 'To be mapped');
      $this->addFieldMapping('do_not_email', NULL, 'To be mapped');
      $this->addFieldMapping('do_not_phone', NULL, 'To be mapped');
      $this->addFieldMapping('do_not_mail', NULL, 'To be mapped');
      $this->addFieldMapping('do_not_sms')->defaultValue(0)->issueGroup('Done');
      $this->addFieldMapping('do_not_trade')->defaultValue(0)->issueGroup('Done');
      $this->addFieldMapping('is_deceased')->defaultValue(0)->issueGroup('Done');
      $this->addFieldMapping('is_deleted')->defaultValue(0)->issueGroup('Done');
      $this->addFieldMapping('formal_title', NULL, 'To be mapped');
      $this->addFieldMapping('prefix_id', NULL, 'To be mapped');
      $this->addFieldMapping('suffix_id')->issueGroup('To be mapped');
      $this->addFieldMapping('source')->defaultValue(t('CiviMigrate import'));
      $this->addFieldMapping('job_title')->issueGroup('To be mapped');
      $this->addFieldMapping('gender_id')->issueGroup('To be mapped');
      $this->addFieldMapping('birth_date')->issueGroup('To be mapped');
      $this->addFieldMapping('employer_id')->issueGroup('To be mapped');

      /*
       * Organisation fields (optionally in a separate migration)
       */
      $this->addFieldMapping('organization_name', NULL, 'To be mapped');
      $this->addFieldMapping('legal_name', NULL, 'To be mapped');

      /*
       * Rarely used
       */
      $this->addFieldMapping('is_opt_out')->issueGroup('Do not map')->defaultValue(0);
      $this->addFieldMapping('image_URL')->issueGroup('Do not map');
      $this->addFieldMapping('preferred_communication_method')->issueGroup('Do not map');
      $this->addFieldMapping('preferred_language', NULL, 'Do not map');
      $this->addFieldMapping('preferred_mail_format', NULL, 'Do not map');
      $this->addFieldMapping('communication_style_id', NULL, 'Do not map');
      $this->addFieldMapping('email_greeting_id', NULL, 'Do not map');
      $this->addFieldMapping('email_greeting_custom')->issueGroup('Do not map');
      $this->addFieldMapping('email_greeting_display')->issueGroup('Do not map');
      $this->addFieldMapping('postal_greeting_id')->issueGroup('Do not map');
      $this->addFieldMapping('postal_greeting_custom')->issueGroup('Do not map');
      $this->addFieldMapping('postal_greeting_display')->issueGroup('Do not map');
      $this->addFieldMapping('addressee_id')->issueGroup('Do not map');
      $this->addFieldMapping('addressee_custom')->issueGroup('Do not map');
      $this->addFieldMapping('addressee_display')->issueGroup('Do not map');
      $this->addFieldMapping('sic_code')->issueGroup('Do not map');
      $this->addFieldMapping('user_unique_id')->issueGroup('Do not map');

      /*
       * Household fields.
       */
      $this->addFieldMapping('household_name')->issueGroup('Do not map');
      $this->addFieldMapping('primary_contact_id')->issueGroup('Do not map');
    }

  }

  /**
   * Code alterations after the civicrm entity has been prepared
   * @param $entity
   * @param $row
   */
  function prepare(&$entity, &$row) {
    parent::prepare($entity, $row);
    $this->fixGenders($entity);
    $this->fixNames($entity);
    $this->fixPrefix($entity);
    $this->fixDeath($entity);
    $this->useMap($entity, 'contact_type', 'ContactTypes');
    $this->fixOrganizationName($entity);
  }

  function mapGenders() {
    return array(
      1 => 'Male',
      2 => 'Female',
      3 => NULL,
    );
  }

  /**
   * Map contact types.
   *
   * This is a sample map - your dataset will probably be different!
   *
   * @return array
   */
  function mapContactTypes() {
    return array(
      'I' => 'Individual',
      'O' => 'Organization',
    );
  }

  function mapPrefix() {
    return array(
      'Juistice' => 'Justice',
      'The Hon' => 'Hon',
      'Em Prof' => 'Emeritus Prof',
      'E Prof' => 'Emeritus Prof',
      'Professor' => 'Prof',
      'Associate Professor' => 'Assoc Professor',
      'A/Prof' => 'Assoc Professor',
      'Sr' => 'Sister',
      'Mr Mrs' => 'Mr & Mrs',
      'Mrs & Mr' =>  'Mr & Mrs',
      'The Rev\'d' => 'Rev',
      'The Honorable' => 'Hon',
      'Very Rev' => 'The Very Reverend',
    );
  }

  function fixGenders(&$entity) {
    $this->useMap($entity, 'gender_id', 'Genders');
  }

  /**
   * Try to fix any prefix data issues.
   *
   * @param obj $entity
   */
  function fixPrefix(&$entity) {
    $entity->prefix_id = trim(str_replace('.', '', $entity->prefix_id));
    $entity->prefix_id = str_replace('and', '&', $entity->prefix_id);
    $this->useMap($entity, 'prefix_id', 'Prefix');
  }

  function fixDeath(&$entity) {
    if (empty($entity->deceased_date )) {
      return;
    }
    $entity->deceased_date = str_pad($entity->deceased_date, 8, '01');
    if (!empty($entity->deceased_date)) {
      $entity->is_deceased = 1;
    }
  }

  /**
  * Handle bad name data.
  *
  * Individuals : If first_name & last_name are empty but display name is not
  * guess them from the first & last names, also, possibly the prefix.
  *
  * @param array $entity
  */
  function fixNames(&$entity) {
    if ($entity->contact_type == 'Individual') {
      if (empty($entity->first_name) && empty($entity->last_name)
      && !empty($entity->display_name)
      ) {
        // Try to calculate them from the display_name

      }
      else {

      }
    }
  }

  /**
   * Fix organizaton name.
   *
   * Individuals should not have organization names, but they should have relationships to the relevant entities.
   * @param $entity
   */
  protected function fixOrganizationName(&$entity) {
    if ($entity->contact_type == 'Organization') {
      if (empty($entity->first_name) && empty($entity->last_name)) {
        return;
      }
      $entity->contact_type = 'Individual';
    }
    if (!empty($entity->organization_name)) {
      $organization = civicrm_api3('contact', 'get', array(
        'organization_name' => $entity->organization_name,
        'contact_type' => 'Organization',
      ));
      if (empty($organization['id'])) {
        $organization = civicrm_api3('contact', 'create', array(
          'organization_name' => $entity->organization_name,
          'contact_type' => 'Organization',
        ));
      }
      $entity->employer_id = $organization['id'];
    }
  }

}
