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
 *
 * SELECT count(*) FROM constit_address constit_address
inner JOIN `sacoss_re`.constit_address_phones cap ON constit_address.id = cap.constitaddressid
INNER JOIN `sacoss_re`.phones phones ON phones.phonesid = cap.phonesid
WHERE  (phonetypeid IN  ('367', '1130')) AND (num <> '') AND (num <> 'www.') AND (constit_address.indicator = '2')
 */
class <?php echo $fullName; ?>_Phone extends <?php echo $fullName; ?>_migrate {
  protected $entity = 'phone'; // this is the default
  protected $debug = 0; // set to 1 for debug info
  protected $_base_table_string;
  protected $base_table = '';
  protected $base_table_alias = '';
  protected $base_table_id = 'id'; // name of id field

  public function __construct($arguments = array()) {
    parent::__construct($arguments);
    if (empty($this->base_table)) {
      $this->setPlaceHolder();
      return;
    }

  }

  function prepareRow($row) {
    static $options = array();
   if (empty($options['phones'])) {
     $options['phones'] = db_query("SELECT LOWER(fieldname) as fieldname, codetablenumber FROM {codetablemap} WHERE tablename = 'phones'
        AND codetablenumber > 0")->fetchAllAssoc('fieldname');

      foreach ($options['phones'] as $spec) {
        $options['phones'][strtolower($spec->fieldname)] = db_query("
          SELECT  longdescription, tableentriesid FROM {tableentries} WHERE codetablesid = " . $spec->codetablenumber
        )->fetchAllAssoc('tableentriesid');
      }
    }
    if (!empty($options['phones'])) {
      foreach ($options['phones'] as $field => $spec) {
        $fieldName = 'phones_' . $field;
        $row->$fieldName = $spec[$row->$fieldName]->longdescription;
      }
    }
    return TRUE;
  }

  /**
   * @param $entity
   * @param $row
   */
  function prepare(&$entity, &$row) {
    parent::prepare($entity, $row);
    if (empty($entity->location_type_id)) {
      return;
    }

    if (stristr($entity->location_type_id, 'Fax')) {
      $entity->phone_type_id = 'Fax';
    }
    if (stristr($entity->location_type_id, 'Mobile')) {
      $entity->phone_type_id = 'Mobile';
    }
    if (in_array($entity->location_type_id, array('Business', 'Business1'))) {
      $entity->is_primary = 1;
    }
    $this->useMap($entity, 'location_type_id', 'LocationTypes');
    if (isset($entity->phone)) {
      $entity->phone = str_replace('Direct', 'Dir', $entity->phone);
    }

  }

  function mapLocationTypes() {
    return array(
      'Business1' => 'Business',
      'Business2' => 'Business',
      'Business3' => 'Business',
      'Business4' => 'Business',
      'Fax' => 'Business',
      'Fax1' => 'Business',
      'Fax2' => 'Business',
      'Mobile' => 'Home',
    );
  }

}
