<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . '\\Message' . ";\n";
?>

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Abstract Request
 */
abstract class <?php echo $suffix ?>AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    public function getData()
    {
        foreach ($this->getRequiredCoreFields() as $field) {
            $this->validate($field);
        }
        $this->validateCardFields();
        return $this->getBaseData() + $this->getTransactionData();
    }

    public function validateCardFields()
    {
        $card = $this->getCard();
        foreach ($this->getRequiredCardFields() as $field) {
            $fn = 'get' . ucfirst($field);
            $result = $card->$fn();
            if (empty($result)) {
                throw new InvalidRequestException("The $field parameter is required");
            }
        }
    }
<?php if (!empty($credential1_camel)) { echo
"    public function get" . $credential1_camel . "()
    {
        return \$this->getParameter('" . $credential1_property . "');
    }\n\n"; echo
"    public function set" . $credential1_camel . "(\$value)
    {
        return \$this->setParameter('" . $credential1_property . "', \$value);
    }\n";
} ?>

<?php if (!empty($credential2_camel)) { echo
  "    public function get" . $credential2_camel . "()
    {
        return \$this->getParameter('" . $credential2_property . "');
    }\n\n"; echo
  "    public function set" . $credential2_camel . "(\$value)
    {
        return \$this->setParameter('" . $credential2_property . "', \$value);
    }\n";
} ?>

    public function getTransactionType()
    {
        return $this->getParameter('transactionType');
    }

    public function setTransactionType($value)
    {
        return $this->setParameter('transactionType', $value);
    }
}
