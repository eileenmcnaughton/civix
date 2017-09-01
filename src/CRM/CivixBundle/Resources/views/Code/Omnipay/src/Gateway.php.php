<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . ";\n";
?>

use Omnipay\Common\AbstractGateway;

/**
 * Gateway class.
 */
class <?php echo $suffix ?>Gateway extends AbstractGateway
{

    /**
     * Get the processor name.
     *
     * @return string
     */
    public function getName()
    {
        return '<?php echo $processorName  . (empty($suffix) ? '' : '_' . $suffix) ?>';
    }

    /**
     * Declare the parameters that will be used to authenticate with the site.
     *
     * A getter (e.g getUsername for username) is required for each of these.
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
          '<?php echo str_replace(' ', '_', strtolower($credential1)) ?>' => '',
          '<?php echo str_replace(' ', '_', strtolower($credential2)) ?>' => '',
          'testMode' => false,
        );
    }

    /**
     * Authorize credentials.
     *
     * @param array $parameters
     *
     * @return \Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>AuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>AuthorizeRequest', $parameters);
    }

    /**
     *
     * @param array $parameters
     * @return \Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>CaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>CaptureRequest', $parameters);
    }

    /**
     *
     * @param array $parameters
     * @return \Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>PurchaseRequest', $parameters);
    }

    /**
     *
     * @param array $parameters
     * @return \Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>CompletePurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\<?php echo $processorName ?>\Message\CompleteAuthorizeRequest
     */
    public function completeAuthorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>CompleteAuthorizeRequest', $parameters);
    }

    public function get<?php echo str_replace(' ', '', ucfirst($credential1)) ?>()
    {
        return $this->getParameter('<?php echo str_replace(' ', '_', strtolower($credential1)) ?>');
    }

    public function set<?php echo str_replace(' ', '', ucfirst($credential1)) ?>($value)
    {
        return $this->setParameter('<?php echo str_replace(' ', '_', strtolower($credential1)) ?>', $value);
    }
    public function get<?php echo str_replace(' ', '', ucfirst($credential2)) ?>()
    {
        return $this->getParameter('<?php echo str_replace(' ', '_', strtolower($credential2)) ?>');
    }

    public function set<?php echo str_replace(' ', '', ucfirst($credential2)) ?>($value)
    {
        return $this->setParameter('<?php echo str_replace(' ', '_', strtolower($credential2)) ?>', $value);
    }
}
