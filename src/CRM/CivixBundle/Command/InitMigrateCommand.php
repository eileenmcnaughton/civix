<?php
namespace CRM\CivixBundle\Command;

use CRM\CivixBundle\Builder\MigrateInfo;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CRM\CivixBundle\Builder\Collection;
use CRM\CivixBundle\Builder\Dirs;
use CRM\CivixBundle\Builder\Info;
use CRM\CivixBundle\Builder\License;
use CRM\CivixBundle\Builder\Module;
use CRM\CivixBundle\Utils\Path;

class InitMigrateCommand extends AbstractCommand {
  protected function configure() {
    $this
      ->setName('generate:migrate')
      ->setDescription('Create a new Civimigrate Module-Extension')
      ->addArgument('<full.ext.name>', InputArgument::REQUIRED, 'Partial extension name (e.g. "myorg")')
      ->addOption('table-map', NULL, InputOption::VALUE_OPTIONAL, 'mapping of tables to put in - e.g contact:ctable,contribution:ytable')
      ->addOption('license', NULL, InputOption::VALUE_OPTIONAL, 'License for the extension (' . implode(', ', $this->getLicenses()) . ')')
      ->addOption('author', NULL, InputOption::VALUE_REQUIRED, 'Name of the author')
      ->addOption('email', NULL, InputOption::VALUE_OPTIONAL, 'Email of the author');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $licenses = new \LicenseData\Repository();

    $ctx = array();
    $ctx['type'] = 'migrate';
    $ctx['fullName'] = $ctx['namespace'] = $input->getArgument('<full.ext.name>') . '_migrate';

    $ctx['basedir'] = $ctx['fullName'];

    if ($input->getOption('author') && $input->getOption('email')) {
      $ctx['author'] = $input->getOption('author');
      $ctx['email'] = $input->getOption('email');
    }
    else {
      $output->writeln("<error>Missing author name or email address</error>");
      $output->writeln("<error>Please pass --author and --email, or set defaults in ~/.gitconfig</error>");
      return;
    }
    $ctx['license'] = $input->getOption('license');
    $ctx['table_map'] = array();

    // This is all pretty cludgey - a yml would be a good plan.
    $tableMap = $input->getOption('table-map');
    if ($tableMap) {
      $mappings = explode(',', $tableMap);
      foreach ($mappings as $mapping) {
        $mapVars = explode(':', $mapping);
        $ctx['table_map'][$mapVars[0]] = $mapVars[1];
      }
    }

    if ($licenses->get($ctx['license'])) {
      $output->writeln(sprintf('<comment>License set to %s (authored by %s \<%s>)</comment>', $ctx['license'], $ctx['author'], $ctx['email']));
      $output->writeln('<comment>If this is in error, please correct info.xml and LICENSE.txt</comment>');
    }
    else {
      $output->writeln('<error>Unrecognized license (' . $ctx['license'] . ')</error>');
      return;
    }
    $ext = new Collection();

    $output->writeln("<info>Initalize module " . $ctx['fullName'] . "</info>");
    $basedir = new Path($ctx['fullName']);
    $ext->builders['dirs'] = new Dirs(array(
      $basedir->string(''),
    ));
    $ctx['classes'] = array(
      'contact',
      'contribution',
      'membership',
      'address',
      'relationship',
      'phone',
      'email',
      'entity_tag',
    );

    $ext->builders['info'] = new MigrateInfo($this->getContainer()->get('templating'));

    $ext->loadInit($ctx);
    $ext->save($ctx, $output);
  }

  /**
   * Attempt to enable the extension on the linked CiviCRM site
   *
   * @return bool TRUE on success; FALSE if there's no site or if there's an error
   */
  protected function tryEnable(InputInterface $input, OutputInterface $output, $key) {
    $civicrm_api3 = $this->getContainer()->get('civicrm_api3');
    if ($civicrm_api3 && $civicrm_api3->local && version_compare(\CRM_Utils_System::version(), '4.3.dev', '>=')) {
      $siteName = \CRM_Utils_System::baseURL(); // \CRM_Core_Config::singleton()->userSystem->cmsRootPath();

      $output->writeln("<info>Refresh extension list for \"$siteName\"</info>");
      if (!$civicrm_api3->Extension->refresh(array('local' => TRUE, 'remote' => FALSE))) {
        $output->writeln("<error>Refresh error: " . $civicrm_api3->errorMsg() . "</error>");
        return FALSE;
      }

      if ($this->confirm($input, $output, "Enable extension ($key) in \"$siteName\"? [Y/n] ")) {
        $output->writeln("<info>Enable extension ($key) in \"$siteName\"</info>");
        if (!$civicrm_api3->Extension->install(array('key' => $key))) {
          $output->writeln("<error>Install error: " . $civicrm_api3->errorMsg() . "</error>");
        }
      }
      return TRUE;
    }

    // fallback
    $output->writeln("NOTE: This might be a good time to refresh the extension list and install \"$key\".");
    return FALSE;
  }

  public function setApplication(Application $application = NULL) {
    parent::setApplication($application);

    // It would be preferable to set these when configure() calls addOption(), but the
    // application/kernel/container aren't available when running configure().
    $this->getDefinition()->getOption('author')->setDefault($this->getDefaultAuthor());
    $this->getDefinition()->getOption('email')->setDefault($this->getDefaultEmail());
    $this->getDefinition()->getOption('license')->setDefault($this->getDefaultLicense());
  }

  protected function getDefaultLicense() {
    $license = NULL;
    if ($this->getContainer()->hasParameter('license')) {
      $license = $this->getContainer()->getParameter('license');
    }
    return empty($license) ? 'AGPL-3.0' : $license;
  }

  protected function getDefaultEmail() {
    $value = NULL;
    if ($this->getContainer()->hasParameter('email')) {
      $value = $this->getContainer()->getParameter('email');
    }
    return empty($value) ? $this->getGitConfig('user.email', 'FIXME') : $value;
  }

  protected function getDefaultAuthor() {
    $value = NULL;
    if ($this->getContainer()->hasParameter('author')) {
      $value = $this->getContainer()->getParameter('author');
    }
    return empty($value) ? $this->getGitConfig('user.name', 'FIXME') : $value;
  }

  protected function getLicenses() {
    $licenses = new \LicenseData\Repository();
    return array_keys($licenses->getAll());
  }

  protected function getGitConfig($key, $default) {
    $result = NULL;
    if (\CRM\CivixBundle\Utils\Commands::findExecutable('git')) {
      $result = trim(`git config --get $key`);
    }
    if (empty($result)) {
      $result = $default;
    }
    return $result;
  }
}
