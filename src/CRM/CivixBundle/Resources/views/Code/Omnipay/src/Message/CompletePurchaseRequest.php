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
        if (strtolower($this->httpRequest->request->get('x_MD5_Hash')) !== $this->getHash()) {
            throw new InvalidRequestException('Incorrect hash');
        }

        return $this->httpRequest->request->all();
    }
}
