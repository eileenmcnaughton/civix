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
class <?php echo $fullName; ?>_Email extends <?php echo $fullName; ?>_migrate {
  protected $entity = 'email';
  // name of id field
  protected $base_table_id = 'id';
  protected $base_table_alias = '<?php
  $base_table = !empty($table_map['email']) ? $table_map['email'] : "";
  echo $base_table;
  ?>';
  protected $base_table = '<?php
  $base_table = !empty($table_map['email']) ? $table_map['email'] : "";
  echo $base_table;
  ?>';
  protected $_base_table_string;
  // Set to 1 for debug info
  protected $debug = 1;

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    if (empty($this->base_table)) {
      $this->setPlaceHolder();
      return;
    }
    $this->addFieldMapping('contact_id', '<?php
$base_table = '';
if (!empty($table_map['contact'])) {
  if (isset($table_map['email']) && $table_map['contact'] == $table_map['email']) {
    $base_table = '';
  }
  else {
    $base_table = $table_map['contact'] . '_';
  }
}
echo $base_table;
?>id')->sourceMigration('Contact');
  }

  public function prepare(&$entity, &$row) {
    parent::prepare($entity, $row);
    if (!empty($entity->on_hold)) {
      $entity->on_hold = 0;
      civicrm_api3('contact', 'create', array('id' => $entity->contact_id, 'do_not_email' => TRUE));
    }
  }
}
