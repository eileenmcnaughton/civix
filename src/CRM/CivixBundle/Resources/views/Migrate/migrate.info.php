name = "<?php echo $fullName ?> Migrate"
description = "<?php echo $fullName ?> CiviCRM Migrate extension"
core = 7.x
package = "Migrate"
dependencies[] = migrate (>7.x-2.6-beta1)
dependencies[] = civicrm

files[] = <?php echo $fullName ?>.inc
<?php
foreach ($classes as $class) {
  echo "files[] = {$fullName}_{$class}.inc\n";
}
?>
