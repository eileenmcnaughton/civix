<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . '\\Message' . ";\n";
?>

/**
 * Purchase Request
 */
class <?php echo $suffix ?>PurchaseRequest extends <?php echo $suffix ?>AuthorizeRequest
{
    public function getTransactionType()
    {
        return 'sale';
    }
}
