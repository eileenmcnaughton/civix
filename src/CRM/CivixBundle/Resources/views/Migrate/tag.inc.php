<?php
echo "<?php\n";
?>
/**
 *
 */
class <?php echo $fullName; ?>_Tag extends <?php echo $fullName; ?>_migrate {
  protected $debug = 0; // Set to 1 for debug info.
  protected $json_folder = NULL;
  protected $options = array();
  protected $entity = 'tag';

  public function __construct($arguments) {
    parent::__construct($arguments);
    $basepath = $_SERVER['DOCUMENT_ROOT'] . base_path();
    $this->description = t('Create tags.');
    $this->json_folder = $basepath . drupal_get_path('module', '<?php echo $fullName; ?>');
    $this->uniqueKey = 'name';

    // This can also be an URL instead of a file path but we are assuming it shipe with the migrate module
    $json_folder = $this->json_folder;
    if(empty($json_folder)){
      throw new MigrateException('json folder must be defined');
    }

    $item_url = $json_folder . '/<?php echo $fullName; ?>_tags.json';
    $http_options = array();
    $this->map = new MigrateSQLMap($this->machineName,
      array(
        'fieldname' => array(
          'type' => 'varchar',
          'length' => 20,
          'not null' => TRUE,
        ),
      ),
      MigrateDestinationCivicrmApi::getKeySchema()
    );
    $this->source = new MigrateSourceList(
      new CiviMigrateListJSON($item_url, $this->uniqueKey),
      new CiviMigrateFileItemJSON($item_url, $http_options, $this->uniqueKey),
      $this->fields()
    );
    $this->addFieldMapping('description', 'description');
    $this->addFieldMapping('name', 'name');
  }
  /**
   * Return the fields (this is cleaner than passing in the array in the MigrateSourceList class above)
   * @return array
   */
  function fields() {
    return array(
      'description' => 'Tag Description',
      'name' => t('Tag Name'),
    );
  }
  /**
   * (non-PHPdoc)
   * @see Civimigration::prepare()
   */
  function prepare(&$entity, &$row){

  }
}

