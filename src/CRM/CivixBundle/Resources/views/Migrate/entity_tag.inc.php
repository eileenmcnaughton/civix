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
class <?php echo $fullName; ?>_Entity_tag extends <?php echo $fullName; ?>_migrate {
  protected $entity = 'entity_tag'; // this is the default
  protected $debug = 0; // set to 1 for debug info
  protected $base_table_id = 'id'; // name of id field
  protected $base_table_alias = '';
  // protected $_db;
  protected $base_table = '';
  protected $_base_table_string;

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    if (empty($this->base_table)) {
      $this->setPlaceHolder();
      return;
    }

    $this->addFieldMapping('entity_id', 'id')->sourceMigration('Contact')->issueGroup('Done');
    $this->addFieldMapping('entity_table')->defaultValue('civicrm_contact')->issueGroup('Done');
    $this->addFieldMapping('tag_id', 'codes_code')->issueGroup('Doing');
  }


  public function prepareRow($row) {
    parent::prepareRow($row);
  }

  public function prepare(&$entity, &$row) {
    $this->useMap($entity, 'tag_id', 'Tags');
  }

  function mapTags() {
    return array(
      'Atrs' => 'Arts'
    );
  }


}
