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
class <?php echo $fullName; ?>_Memberships extends <?php echo $fullName; ?>_migrate {
  protected $entity = 'membership'; // this is the default
  protected $debug = 0; // set to 1 for debug info
  protected $base_table_id = 'id'; // name of id field
  protected $base_table_alias = 'mt';
  // protected $_db;
  protected $base_table = 'membershiptransaction';
  protected $_base_table_string;
  protected $joinToRecords = FALSE;
  protected $joinField = 'constitid';

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    $this->addFieldMapping('contact_id', 'records_id')->sourceMigration('Contacts');
  }


  public function prepareRow($row) {
    parent::prepareRow($row);
  }


  /**
   * @param $table
   * @param $alias
   * @param string $idKey
   *
   * @return SelectQuery
   */
  function getQuery($table, $alias, $idKey ="") {
    $query = parent::getQuery($table,$alias, $idKey);
    if ($alias == $this->base_table_alias) {
      $query->addJoin('left', 'member', 'member', 'mt.membershipid = member.id');
      $query->addJoin('left', 'records', 'records', 'member.constitid = records.id');
      $query->addField('records', 'id', 'records_id');
      $query->condition('mt.category', array(1, 13, 14, 15, 16), 'NOT IN');
      $query->condition('mt.type', array(6), 'NOT IN');
      $query->isNotNull('records.id');
    }
    return $query;
  }

  function prepare(&$entity, &$row) {
    parent::prepare($entity, $row);
    $entity->join_date = $entity->start_date;
    if (strtotime($entity->join_date) > strtotime('now')) {
      $entity->status_id = 9;
      $entity->skipStatusCal = TRUE;
    }
    /*
     *
     if ($entity->start_date == '1900-01-01 00:00:00') {
      //$entity->start_date = $row->member_date_added;
    }
    else {
        $entity->join_date = $entity->start_date;
    }
    */
    $mapping = array(
      1 => 16, //not required
      3 => 14,
      4 => 15,
      5 => 3,
      6 => 4,
      7 => 5,
      8 => 6,
      9 => 7,
      10 => 8,
      11 => 9,
      12 => 10,
      13 => 16,//not required
      14 => 16,//not required
      15 => 16,//not required
      16 => 16,//not required
      17 => 15,
      18 => 2,
      19 => 1,
      //students are unwaged
      20 => 1,
    );

    if (in_array($entity->membership_type_id, array_keys($mapping))) {
      $entity->membership_type_id = $mapping[$entity->membership_type_id];
    }

  }

}
