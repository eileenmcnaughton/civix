<?php
echo "<?php\n";
$_namespace = preg_replace(':/:', '_', $namespace);
?>

// AUTO-GENERATED FILE -- Civix may overwrite any changes made to this file

/**
 * The ExtensionUtil class provides small stubs for accessing resources of this
 * extension.
 */
class <?php echo $_namespace ?>_ExtensionUtil {
  const SHORT_NAME = '<?php echo $mainFile; ?>';
  const LONG_NAME = '<?php echo $fullName; ?>';
  const CLASS_PREFIX = '<?php echo $_namespace; ?>';

  /**
   * Translate a string using the extension's domain.
   *
   * If the extension doesn't have a specific translation
   * for the string, fallback to the default translations.
   *
   * @param string $text
   *   Canonical message text (generally en_US).
   * @param array $params
   * @return string
   *   Translated text.
   * @see ts
   */
  public static function ts($text, $params = []) {
    if (!array_key_exists('domain', $params)) {
      $params['domain'] = [self::LONG_NAME, NULL];
    }
    return ts($text, $params);
  }

  /**
   * Get the URL of a resource file (in this extension).
   *
   * @param string|NULL $file
   *   Ex: NULL.
   *   Ex: 'css/foo.css'.
   * @return string
   *   Ex: 'http://example.org/sites/default/ext/org.example.foo'.
   *   Ex: 'http://example.org/sites/default/ext/org.example.foo/css/foo.css'.
   */
  public static function url($file = NULL) {
    if ($file === NULL) {
      return rtrim(CRM_Core_Resources::singleton()->getUrl(self::LONG_NAME), '/');
    }
    return CRM_Core_Resources::singleton()->getUrl(self::LONG_NAME, $file);
  }

  /**
   * Get the path of a resource file (in this extension).
   *
   * @param string|NULL $file
   *   Ex: NULL.
   *   Ex: 'css/foo.css'.
   * @return string
   *   Ex: '/var/www/example.org/sites/default/ext/org.example.foo'.
   *   Ex: '/var/www/example.org/sites/default/ext/org.example.foo/css/foo.css'.
   */
  public static function path($file = NULL) {
    // return CRM_Core_Resources::singleton()->getPath(self::LONG_NAME, $file);
    return __DIR__ . ($file === NULL ? '' : (DIRECTORY_SEPARATOR . $file));
  }

  /**
   * Get the name of a class within this extension.
   *
   * @param string $suffix
   *   Ex: 'Page_HelloWorld' or 'Page\\HelloWorld'.
   * @return string
   *   Ex: 'CRM_Foo_Page_HelloWorld'.
   */
  public static function findClass($suffix) {
    return self::CLASS_PREFIX . '_' . str_replace('\\', '_', $suffix);
  }

}

use <?php echo $_namespace ?>_ExtensionUtil as E;

function _<?php echo $mainFile ?>_civix_mixin_polyfill() {
  if (!class_exists('CRM_Extension_MixInfo')) {
    $polyfill = __DIR__ . '/mixin/polyfill.php';
    (require $polyfill)(E::LONG_NAME, E::SHORT_NAME, E::path());
  }
}


/**
 * (Delegated) Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config
 */
function _<?php echo $mainFile ?>_civix_civicrm_config(&$config = NULL) {
  static $configured = FALSE;
  if ($configured) {
    return;
  }
  $configured = TRUE;

  $template = CRM_Core_Smarty::singleton();

  $extRoot = __DIR__ . DIRECTORY_SEPARATOR;
  $extDir = $extRoot . 'templates';

  if (is_array($template->template_dir)) {
    array_unshift($template->template_dir, $extDir);
  }
  else {
    $template->template_dir = [$extDir, $template->template_dir];
  }

  $include_path = $extRoot . PATH_SEPARATOR . get_include_path();
  set_include_path($include_path);

  _<?php echo $mainFile ?>_civix_mixin_polyfill();
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function _<?php echo $mainFile ?>_civix_civicrm_install() {
  _<?php echo $mainFile ?>_civix_civicrm_config();
  if ($upgrader = _<?php echo $mainFile ?>_civix_upgrader()) {
    $upgrader->onInstall();
  }
  _<?php echo $mainFile ?>_civix_mixin_polyfill();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function _<?php echo $mainFile ?>_civix_civicrm_postInstall() {
  _<?php echo $mainFile ?>_civix_civicrm_config();
  if ($upgrader = _<?php echo $mainFile ?>_civix_upgrader()) {
    if (is_callable([$upgrader, 'onPostInstall'])) {
      $upgrader->onPostInstall();
    }
  }
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function _<?php echo $mainFile ?>_civix_civicrm_uninstall() {
  _<?php echo $mainFile ?>_civix_civicrm_config();
  if ($upgrader = _<?php echo $mainFile ?>_civix_upgrader()) {
    $upgrader->onUninstall();
  }
}

/**
 * (Delegated) Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function _<?php echo $mainFile ?>_civix_civicrm_enable() {
  _<?php echo $mainFile ?>_civix_civicrm_config();
  if ($upgrader = _<?php echo $mainFile ?>_civix_upgrader()) {
    if (is_callable([$upgrader, 'onEnable'])) {
      $upgrader->onEnable();
    }
  }
  _<?php echo $mainFile ?>_civix_mixin_polyfill();
}

/**
 * (Delegated) Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 * @return mixed
 */
function _<?php echo $mainFile ?>_civix_civicrm_disable() {
  _<?php echo $mainFile ?>_civix_civicrm_config();
  if ($upgrader = _<?php echo $mainFile ?>_civix_upgrader()) {
    if (is_callable([$upgrader, 'onDisable'])) {
      $upgrader->onDisable();
    }
  }
}

/**
 * (Delegated) Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *   for 'enqueue', returns void
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function _<?php echo $mainFile ?>_civix_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  if ($upgrader = _<?php echo $mainFile ?>_civix_upgrader()) {
    return $upgrader->onUpgrade($op, $queue);
  }
}

/**
 * @return <?php echo $_namespace ?>_Upgrader
 */
function _<?php echo $mainFile ?>_civix_upgrader() {
  if (!file_exists(__DIR__ . '/<?php echo $namespace ?>/Upgrader.php')) {
    return NULL;
  }
  else {
    return <?php echo $_namespace ?>_Upgrader_Base::instance();
  }
}

/**
 * Search directory tree for files which match a glob pattern.
 *
 * Note: Dot-directories (like "..", ".git", or ".svn") will be ignored.
 * Note: Delegate to CRM_Utils_File::findFiles(), this function kept only
 * for backward compatibility of extension code that uses it.
 *
 * @param string $dir base dir
 * @param string $pattern , glob pattern, eg "*.txt"
 *
 * @return array
 */
function _<?php echo $mainFile ?>_civix_find_files($dir, $pattern) {
  return CRM_Utils_File::findFiles($dir, $pattern);
}

/**
 * Glob wrapper which is guaranteed to return an array.
 *
 * The documentation for glob() says, "On some systems it is impossible to
 * distinguish between empty match and an error." Anecdotally, the return
 * result for an empty match is sometimes array() and sometimes FALSE.
 * This wrapper provides consistency.
 *
 * @link http://php.net/glob
 * @param string $pattern
 *
 * @return array
 */
function _<?php echo $mainFile ?>_civix_glob($pattern) {
  $result = glob($pattern);
  return is_array($result) ? $result : [];
}

/**
 * Inserts a navigation menu item at a given place in the hierarchy.
 *
 * @param array $menu - menu hierarchy
 * @param string $path - path to parent of this item, e.g. 'my_extension/submenu'
 *    'Mailing', or 'Administer/System Settings'
 * @param array $item - the item to insert (parent/child attributes will be
 *    filled for you)
 *
 * @return bool
 */
function _<?php echo $mainFile ?>_civix_insert_navigation_menu(&$menu, $path, $item) {
  // If we are done going down the path, insert menu
  if (empty($path)) {
    $menu[] = [
      'attributes' => array_merge([
        'label'      => CRM_Utils_Array::value('name', $item),
        'active'     => 1,
      ], $item),
    ];
    return TRUE;
  }
  else {
    // Find an recurse into the next level down
    $found = FALSE;
    $path = explode('/', $path);
    $first = array_shift($path);
    foreach ($menu as $key => &$entry) {
      if ($entry['attributes']['name'] == $first) {
        if (!isset($entry['child'])) {
          $entry['child'] = [];
        }
        $found = _<?php echo $mainFile ?>_civix_insert_navigation_menu($entry['child'], implode('/', $path), $item);
      }
    }
    return $found;
  }
}

/**
 * (Delegated) Implements hook_civicrm_navigationMenu().
 */
function _<?php echo $mainFile ?>_civix_navigationMenu(&$nodes) {
  if (!is_callable(['CRM_Core_BAO_Navigation', 'fixNavigationMenu'])) {
    _<?php echo $mainFile ?>_civix_fixNavigationMenu($nodes);
  }
}

/**
 * Given a navigation menu, generate navIDs for any items which are
 * missing them.
 */
function _<?php echo $mainFile ?>_civix_fixNavigationMenu(&$nodes) {
  $maxNavID = 1;
  array_walk_recursive($nodes, function($item, $key) use (&$maxNavID) {
    if ($key === 'navID') {
      $maxNavID = max($maxNavID, $item);
    }
  });
  _<?php echo $mainFile ?>_civix_fixNavigationMenuItems($nodes, $maxNavID, NULL);
}

function _<?php echo $mainFile ?>_civix_fixNavigationMenuItems(&$nodes, &$maxNavID, $parentID) {
  $origKeys = array_keys($nodes);
  foreach ($origKeys as $origKey) {
    if (!isset($nodes[$origKey]['attributes']['parentID']) && $parentID !== NULL) {
      $nodes[$origKey]['attributes']['parentID'] = $parentID;
    }
    // If no navID, then assign navID and fix key.
    if (!isset($nodes[$origKey]['attributes']['navID'])) {
      $newKey = ++$maxNavID;
      $nodes[$origKey]['attributes']['navID'] = $newKey;
      $nodes[$newKey] = $nodes[$origKey];
      unset($nodes[$origKey]);
      $origKey = $newKey;
    }
    if (isset($nodes[$origKey]['child']) && is_array($nodes[$origKey]['child'])) {
      _<?php echo $mainFile ?>_civix_fixNavigationMenuItems($nodes[$origKey]['child'], $maxNavID, $nodes[$origKey]['attributes']['navID']);
    }
  }
}

/**
 * (Delegated) Implements hook_civicrm_entityTypes().
 *
 * Find any *.entityType.php files, merge their content, and return.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
<?php
$entityTypeLines = '';
$count = count($entityTypes);
$thisLineCount = 1;
// Add appropriate indentation
foreach($entityTypes as $entityName => $entityKeys) {
  $entityTypeLines .= "\n    '$entityName' => [\n";
  foreach ($entityKeys as $key => $value) {
    $entityTypeLines .= "      '$key' => '{$value}',\n";
  }
  if ($thisLineCount < $count) {
    $entityTypeLines .= '    ],';
    $thisLineCount++;
  }
  else {
    $entityTypeLines .= "    ],\n  ";
  }
}

?>
function _<?php echo $mainFile ?>_civix_civicrm_entityTypes(&$entityTypes) {
  $entityTypes = array_merge($entityTypes, [<?php echo $entityTypeLines ?>]);
}
