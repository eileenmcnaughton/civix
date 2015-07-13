<?php
echo "<?php\n";
?>

/*
 * You must implement hook_migrate_api(), setting the API level to 2, for
 * your migration classes to be recognized by the Migrate module.
 */
function <?php echo $namespace; ?>_migrate_api() {
  $api = array(
    'api' => 2,
    'groups' => array(
      'civicrm' => array(
        'title' => t('CiviCRM'),
      ),
    ),
    'migrations' => array(
<?php
foreach ($classes as $class) {
  echo "      '" . ucfirst($class) . "' => array(\n";
  echo "        'class_name' => '" . $fullName . "_" . ucfirst($class) . "',\n";
  echo "        'group_name' => 'civicrm',\n";
  echo "      ),\n";
}
?>
    ),
  );
  return $api;
}
