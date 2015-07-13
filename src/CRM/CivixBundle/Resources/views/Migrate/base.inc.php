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
class <?php echo $fullName; ?>_migrate extends Civimigration {
  protected $joinToRecords = FALSE;
  protected $joinField = 'constit_id';

  /**
   * Code alterations after the civicrm entity has been prepared
   *
   * @param $row
   *
   * @return bool
   */
  function prepareRazorsEdgeRow($row) {
    static $options = array();

    if (empty($options[$this->base_table])) {
      $options[$this->base_table] = db_query("SELECT LOWER(fieldname) as fieldname, codetablenumber FROM {codetablemap} WHERE tablename = '{$this->base_table}'
        AND codetablenumber > 0")->fetchAllAssoc('fieldname');

      foreach ($options[$this->base_table] as $spec) {
        $options[$this->base_table][strtolower($spec->fieldname)] = db_query("
          SELECT  longdescription, tableentriesid FROM {tableentries} WHERE codetablesid = " . $spec->codetablenumber
        )->fetchAllAssoc('tableentriesid');
      }
    }
    if (!empty($options[$this->base_table])) {
      foreach ($options[$this->base_table] as $field => $spec) {
        $fieldName = $this->base_table_alias . '_' . $field;
        $row->$fieldName = $spec[$row->$fieldName]->longdescription;
      }
    }
    return TRUE;
  }

  /**
   * Use a parameter map to fix up a bad data issue.
   *
   * @param $entity
   * @param string $key
   * @param string $map
   *
   * @return bool
   */
  function useMap(&$entity, $key, $map) {
    $fn = 'map' . $map;
    $mappings = $this->$fn();
    if (!isset($entity->$key)) {
      return;
    }
    if (array_key_exists($entity->$key, $mappings)) {
      $entity->$key = $mappings[$entity->$key];
    }

    $fn = 'getOptions' . $map;
    if (method_exists($this, $fn)) {
      $mappings = $this->$fn();
      if (in_array($entity->$key, $mappings)) {
        return TRUE;
      }
    }

    $fn = 'get' . $map . 'WhiteList';
    if (method_exists($this, $fn)) {
      $white_list = $this->$fn();
      if (in_array($entity->$key, $white_list)) {
        $fn = 'create' . $map;
        $this->$fn($entity->$key);
        return TRUE;
      }
      if (in_array(str_replace('and', '&', $entity->$key), $white_list)) {
        $fn = 'create' . $map;
        $this->$fn(str_replace('and', '&', $entity->$key));
        return TRUE;
      }
    }
  }

  /**
   * Create a prefix from the whitelist.
   *
   * The whitelist is prefixes approved for creation if they do not exist.
   */
  protected function createPrefix($prefix) {
    $option = civicrm_api3('option_value', 'create', array(
      'option_group_id' => 'individual_prefix',
      'name' => $prefix,
    ));
    $this->getOptionsPrefix(array($option['id'] => $prefix));
  }

  /**
   * @param $table
   * @param $alias
   * @param string $idKey
   *
   * @return SelectQuery
   */
  protected function getQuery($table, $alias, $idKey ="") {
    $query = parent::getQuery($table, $alias, $idKey);
    if ($this->joinToRecords && $alias == $this->base_table_alias) {
      $query->addJoin('left', 'records', 'records', "records.id = {$alias}.{$this->joinField}");
      $query->addField('records', 'id', 'records_id');
      $query->isNotNull('records.id');
    }
    return $query;
  }

  /**
   * Get existing prefix options.
   *
   * @param array $addPrefix
   *
   * @return array
   */
  function getOptionsPrefix($addPrefix = array()) {
    static $options = array();
    if (empty($options)) {
      $optionValues = civicrm_api3('contact', 'getoptions', array('field' => 'prefix_id'));
      $options = $optionValues['values'];
    }
    $options = array_merge($options, $addPrefix);
    return $options;
  }

  /**
   * Get prefix whitelist.
   *
   * The whitelist is prefixes approved for creation if they do not exist.
   *
   * @return array
   */
  protected function getPrefixWhiteList() {
    return array(
      "Ambassador",
      "Assistant Prof",
      "Assoc Professor",
      "Bishop",
      "Captain",
      "Cr",
      "Dame",
      "Dr",
      "Dr & Dr",
      "Dr & Mrs",
      "Dr & Mr",
      "Dr & Prof",
      "Emeritus Prof",
      "Fa",
      "Father",
      "Gafauatau",
      "Hon",
      "Hon Dr",
      "Hon Vui",
      "Justice & Mrs",
      "Lady",
      "Master",
      "Miss",
      "Miss & Mr",
      "Misses",
      "Mr",
      "Mr & Mrs",
      "Mr & Ms",
      "Mrs",
      "Ms",
      "Pastor",
      "Prof",
      "Prof & Mrs",
      "Rev",
      "Rev & Mrs",
      "Rt Hon",
      "Rt Hon & Mrs",
      "Sir",
      "Sister",
      "Su`a",
      "The Very Reverend",
      "Viscount",
    );
  }

  /**
   * Set up a place holder so this can load when not yet configured.
   *
   * Once people add their own basetable it will load from there.
   */
   protected function setPlaceHolder() {
     $query = db_select('civimigrate_mappings', 'dummy_query');
     $query->addField('dummy_query', 'cmid');
     $query->addExpression(1, 'no_fields_yet');
     $this->source = new MigrateSourceSQL($query);
     $this->map = new MigrateSQLMap($this->machineName, array(
       'cmid' => array(
       'type' => 'int',
       'not null' => 'NOT NULL',
     )), MigrateDestinationCivicrmApi::getKeySchema());
     drupal_set_message(t('datasource not defined. Configure $this->base_table for ') . get_class($this));
   }
  }
