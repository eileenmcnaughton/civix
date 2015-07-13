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
class <?php echo $fullName; ?>_Contribution extends <?php echo $fullName; ?>_migrate {
  protected $entity = 'contribution'; // this is the default
  protected $debug = 0; // set to 1 for debug info
  protected $base_table_id = 'id'; // name of id field
  protected $base_table_alias = '<?php
$base_table = !empty($table_map['contribution']) ? $table_map['contribution'] : "";
echo $base_table;
?>';
  protected $base_table = '<?php
$base_table = !empty($table_map['contribution']) ? $table_map['contribution'] : "";
echo $base_table;
?>';
  protected $_base_table_string;

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    if (empty($this->base_table)) {
      $this->setPlaceHolder();
      return;
    }

$this->addFieldMapping('contact_id', '<?php
$base_table = '';
if (!empty($table_map['contact'])) {
  if (isset($table_map['contribution']) && $table_map['contact'] == $table_map['contribution']) {
    $base_table = '';
  }
  else {
    $base_table = $table_map['contact'] . '_';
  }
}
echo $base_table;
?>id')->sourceMigration('Contact');
  }


  function prepare(&$entity, &$row) {
    parent::prepare($entity, $row);
    $entity->total_amount = number_format($entity->total_amount, 2);
  }


}
