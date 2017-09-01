<?php
namespace CRM\CivixBundle\Builder;

use Symfony\Component\Console\Output\OutputInterface;
use CRM\CivixBundle\Builder;
use CRM\CivixBundle\Utils\Path;

/**
 * Read/write a serialized data file based on PHP's var_export() format
 */
class Omnipay extends Template implements Builder{

  function loadInit(&$ctx) {
  }

  function init(&$ctx) {
  }

  function load(&$ctx) {
  }

  function save(&$ctx, OutputInterface $output) {
    /*
    $basedir = new Path($ctx['basedir']);
    $module = new Template(
      'CRMCivixBundle:Code:Omnipay:composer.json.php',
      $basedir->string($ctx['fullName'] . '.info'),
      'ignore',
      $this->templateEngine
    );



    $module->save($ctx, $output);
    */
    if (file_exists($this->path) && $this->overwrite === 'ignore') {
      // do nothing
    }
    elseif (file_exists($this->path) && !$this->overwrite) {
      $output->writeln("<error>Skip " . $this->path . ": file already exists</error>");
    }
    else {
      file_put_contents($this->path, $this->templateEngine->render($this->template, $ctx));
    }
  }

}
