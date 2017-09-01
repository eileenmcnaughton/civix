<?php
echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
?>
<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         bootstrap="vendor/autoload.php"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false"
         syntaxCheck="false">
  <testsuites>
    <testsuite name="Omnipay Test Suite">
      <directory>./tests/</directory>
    </testsuite>
  </testsuites>
  <listeners>
    <listener class="Mockery\Adapter\Phpunit\TestListener" file="vendor/mockery/mockery/library/Mockery/Adapter/Phpunit/TestListener.php" />
  </listeners>
  <filter>
    <whitelist>
      <directory>./src</directory>
    </whitelist>
  </filter>
</phpunit>