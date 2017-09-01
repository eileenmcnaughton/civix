<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . '\\Message' . ";\n";
?>

/**
 * Authorize Request
 */
class <?php echo $suffix ?>CompletePurchaseRequest extends <?php echo $suffix ?>AbstractRequest
{
    public function sendData($data)
    {
        return $this->response = new <?php echo $suffix ?>CompletePurchaseResponse($this, $data);
    }

    public function getData()
    {
        return $this->httpRequest->request->all();
    }
}
