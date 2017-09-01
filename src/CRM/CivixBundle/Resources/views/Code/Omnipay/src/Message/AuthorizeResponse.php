<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . '\\Message' . ";\n";
?>

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * Complete Authorize Response
 */
class <?php echo $suffix ?>AuthorizeResponse extends <?php echo $suffix ?>Response
{
}
