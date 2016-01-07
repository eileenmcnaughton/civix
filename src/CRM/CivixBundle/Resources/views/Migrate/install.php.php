<?php
echo "<?php\n";
?>


/**
 * Implementation of hook_install().
 */
function <?php echo $fullName; ?>_install() {
<?php
foreach ($classes as $class) {
  if (!empty($table_map[$class])) {
    echo "civimigrate_populate_mappings('{$table_map[$class]}', array('migration' => '{$class}', 'issuegroup' => 'needs mapping'));\n";
  }
}
?>

}

/**
* Implementation of hook_uninstall().
*/
function <?php echo $fullName; ?>_uninstall() {
}
