<?php
namespace CRM\CivixBundle\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use CRM\CivixBundle\Builder;
use CRM\CivixBundle\Utils\Path;

/**
 * Read/write a serialized data file based on PHP's var_export() format
 */
class MigrateInfo implements Builder {
  function __construct($templateEngine) {
    $this->templateEngine = $templateEngine;
  }

  function loadInit(&$ctx) {
  }

  function init(&$ctx) {
  }

  function load(&$ctx) {
  }

  function save(&$ctx, OutputInterface $output) {
    $basedir = new Path($ctx['basedir']);
    $module = new Template(
      'CRMCivixBundle:Migrate:migrate.info.php',
      $basedir->string($ctx['fullName'] . '.info'),
      'ignore',
      $this->templateEngine
    );

    $module->save($ctx, $output);

    $moduleCivix = new Template(
      'CRMCivixBundle:Migrate:migrate.module.php',
      $basedir->string($ctx['fullName'] . '.module'),
      TRUE,
      $this->templateEngine
    );
    $moduleCivix->save($ctx, $output);

    $moduleCivix = new Template(
      'CRMCivixBundle:Migrate:base.inc.php',
      $basedir->string($ctx['fullName'] . '.inc'),
      TRUE,
      $this->templateEngine
    );
    $moduleCivix->save($ctx, $output);

    $moduleCivix = new Template(
      'CRMCivixBundle:Migrate:install.php.php',
      $basedir->string($ctx['fullName'] . '.install'),
      TRUE,
      $this->templateEngine
    );
    $moduleCivix->save($ctx, $output);

    foreach ($ctx['classes'] as $class) {
      $moduleCivix = new Template(
        'CRMCivixBundle:Migrate:' .  $class . '.inc.php',
        $basedir->string($ctx['fullName'] . "_" . $class . '.inc'),
        TRUE,
        $this->templateEngine
      );
      $moduleCivix->save($ctx, $output);
    }


  }

}
