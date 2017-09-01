<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . '\\Message' . ";\n";
?>

/**
 * Complete purchase response.
 *
 * This is the action taken when an IPN, webhook or other callback comes in
 * from the payment gateway provider.
 */
class <?php echo $suffix ?>CompletePurchaseResponse extends <?php echo $suffix ?>CompleteAuthorizeRequest
{
}
