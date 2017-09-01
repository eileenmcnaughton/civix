<?php
namespace CRM\CivixBundle\Command;

use CRM\CivixBundle\Services;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CRM\CivixBundle\Builder\Collection;
use CRM\CivixBundle\Builder\Dirs;
use CRM\CivixBundle\Builder\PhpData;
use CRM\CivixBundle\Builder\Omnipay;
use CRM\CivixBundle\Utils\Path;
use Exception;

/**
 * Class AddOmnipayProcessorCommand
 *
 * This class adds an Omnipay Processor package shell. To use it you will need to
 *
 * 1) install composer (https://getcomposer.org/doc/01-basic-usage.md) (useful tips at http://moquet.net/blog/5-features-about-composer-php/)
 *
 * 2) Install the Omnipay extension in CiviCRM
 *
 * 3) make sure you are in the Omnipay extension directory (nz.co.fuzion.omnipaymultiprocessor)
 *
 * 4) Generate a shell for your processor -e.g
civix generate:omnipay-processor Mercanet fuzion eileenmcnaughton "Eileen McNaughton" eileen@fuzion.co.nz -a "Merchant ID" -b "Secret Key" -t https://payment-webinit-mercanet.test.sips-atos.com/paymentInit
 *
 * Arguments above are ProcessorName, vendor name, github username, Author, author email. Options are -a & -b are the names of the first two credential keys mercanet uses (c is also possible) s is the site url & t is the test usrl.
 *
 * 5) Your shell will be generated in nz.co.fuzion.omnipaymultiprocessor/vendor/yourvendorname/omnipay-yourprocessorname - follow the instructions in the
 *generated README for next steps.
 *
 * @package CRM\CivixBundle\Command
 */
class AddOmnipayProcessorCommand extends \Symfony\Component\Console\Command\Command {
  const API_VERSION = 3;

  protected function configure() {
    $this
      ->setName('generate:omnipay-processor')
      ->setDescription('Add a new omnipay plugin (*EXPERIMENTAL AND INCOMPLETE*)')
      ->addArgument('ProcessorName', InputArgument::REQUIRED, 'The brief, unique name of the processor")')
      ->addArgument('Vendor', InputArgument::REQUIRED, 'vendor name")')
      ->addArgument('GithubUserName', InputArgument::REQUIRED, 'your github username")')
      ->addArgument('AuthorName', InputArgument::REQUIRED, 'your name")')
      ->addArgument('AuthorEmail', InputArgument::REQUIRED, 'your email")')
      ->addOption('credential1', 'a', InputOption::VALUE_OPTIONAL, 'Name of first credential - e.g Merchant ID or Username')
    ->addOption('credential2', 'b', InputOption::VALUE_OPTIONAL, 'Name of second credential - e.g Password or SecretKey')
      ->addOption('credential3', 'c', InputOption::VALUE_OPTIONAL, 'Name of second credential - e.g Hash or ApiKey')
      ->addOption('TestUrl', 't', InputOption::VALUE_OPTIONAL, 'URL for test transactions')
      ->addOption('SiteUrl', 's', InputOption::VALUE_OPTIONAL, 'URL for transactions')
      ->addOption('Suffix', 'u', InputOption::VALUE_OPTIONAL, 'Suffix to disambiguate gateways')
      ->addOption('TransparentRedirect', 'r', InputOption::VALUE_OPTIONAL, 'Suffix to disambiguate gateways');

  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $ctx = array();
    $ctx['basedir'] = \CRM\CivixBundle\Application::findExtDir() . DIRECTORY_SEPARATOR . 'vendor';
    $ctx['vendor'] = $input->getArgument('Vendor');
    $ctx['githubUserName'] = $input->getArgument('GithubUserName');
    $ctx['authorName'] = $input->getArgument('AuthorName');
    $ctx['authorEmail'] = $input->getArgument('AuthorEmail');
    $ctx['processorName'] = $input->getArgument('ProcessorName');
    $ctx['credential1'] = $input->getOption('credential1');
    $ctx['credential1_camel'] = str_replace(' ', '' , $input->getOption('credential1'));
    $ctx['credential1_property'] = strtolower(str_replace(' ', '_' , $input->getOption('credential1')));
    $ctx['credential2'] = $input->getOption('credential2');
    $ctx['credential2_camel'] = str_replace(' ', '' , $input->getOption('credential2'));
    $ctx['credential2_property'] = strtolower(str_replace(' ', '_' , $input->getOption('credential2')));
    $ctx['credential3'] = $input->getOption('credential3');
    $ctx['testUrl'] = $input->getOption('TestUrl');
    $ctx['siteUrl'] = ($input->getOption('SiteUrl') ? : $ctx['testUrl']);
    $ctx['suffix'] = $input->getOption('Suffix');
    $ctx['isTransparentRedirect'] = $input->getOption('TransparentRedirect');

    $packageName = 'omnipay-'. strtolower($ctx['processorName']);
    $basedir = new Path($ctx['basedir'] . DIRECTORY_SEPARATOR . $ctx['vendor'] . DIRECTORY_SEPARATOR . $packageName);
    $basedir->mkdir();
    $ext = new Collection();

    $ext->builders['dirs'] = new Dirs(array(
      $basedir->string() . '/src/Message',
      $basedir->string() . '/tests',
    ));
    $ext->builders['dirs']->save($ctx, $output);

    $files = array(
      'Omnipay' . DIRECTORY_SEPARATOR . 'composer.json.php' => array('name' => 'composer.json'),
      'Omnipay' . DIRECTORY_SEPARATOR . 'phpunit.xml.dist.php' => array('name' => 'phpunit.xml'),
      'Omnipay' . DIRECTORY_SEPARATOR . 'travis.yml.php' => array('name' => '.travis.yml'),
      'Omnipay' . DIRECTORY_SEPARATOR . 'README.md.php' => array('name' => 'README.md'),
      'Omnipay' . DIRECTORY_SEPARATOR . 'tests/' . $ctx['suffix'] . 'GatewayTest.php.php' => array('name' => 'tests'. DIRECTORY_SEPARATOR . $ctx['suffix'] . 'GatewayTest.php'),
      'Omnipay' . DIRECTORY_SEPARATOR . 'src/' . 'Gateway.php.php' => array('name' => 'src'. DIRECTORY_SEPARATOR . $ctx['suffix'] . 'Gateway.php'),
      'Omnipay' . DIRECTORY_SEPARATOR . 'managed.mgd.php.php' => array('name' => '../../../omnipay_' . $ctx['processorName'] . (!empty($ctx['suffix']) ? '_' . $ctx['suffix'] : '') . '.mgd.php'),
      'Omnipay' . DIRECTORY_SEPARATOR . 'gitignore.php' => array('name' => '.gitignore'),
    );

    $messageFiles = scandir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Resources/views/Code/Omnipay/src/Message');
    foreach ($messageFiles as $messageFile) {
      if (substr($messageFile, 0, 1) === '.') {
        continue;
      }
      $files['Omnipay' . DIRECTORY_SEPARATOR . 'src'. DIRECTORY_SEPARATOR . 'Message' . DIRECTORY_SEPARATOR . $messageFile] = array(
        'name' => 'src'. DIRECTORY_SEPARATOR . 'Message' .  DIRECTORY_SEPARATOR . $ctx['suffix'] . str_replace('.php.php', '.php', $messageFile),
      );
    }

    foreach ($files as $templateFile => $fileSpec) {
      $ext->builders[] = new Omnipay($templateFile, $basedir->string() . DIRECTORY_SEPARATOR . $fileSpec['name'], TRUE, Services::templating());
    }

    $ext->init($ctx);
    $ext->save($ctx, $output);
    $output->writeln(
      'Your shell has been generated in nz.co.fuzion.omnipaymultiprocessor/vendor/' . $ctx['vendor'] . '/' . $packageName . ' - follow the instructions in the
 generated README for next steps.');
  }

}
