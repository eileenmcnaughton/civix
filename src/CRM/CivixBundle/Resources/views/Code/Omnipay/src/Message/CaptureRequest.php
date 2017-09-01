<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . '\\Message' . ";\n";
?>

/**
 * Capture Request
 */
class <?php echo $suffix ?>CaptureRequest extends <?php echo $suffix ?>AuthorizeRequest
{
    public function getTransactionType()
    {
        return 'capture';
    }
}
