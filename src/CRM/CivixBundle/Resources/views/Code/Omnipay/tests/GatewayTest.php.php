<?php
echo "<?php\n";
echo "namespace " . 'Omnipay\\' . $processorName . ";\n";
?>

use Omnipay\Tests\GatewayTestCase;
use Omnipay\<?php echo $processorName ?>\OffsiteGateway;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\CreditCard;

class <?php echo $suffix ?>GatewayTest extends GatewayTestCase
{
  /**
   * @var Omnipay/<?php echo $processorName ?>/SystemGateway
   */
    public $gateway;

    /**
     * @var CreditCard
     */
    public $card;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new <?php echo $suffix ?>Gateway($this->getHttpClient(), $this->getHttpRequest());
<?php if (!empty($credential1)) { echo
  "        \$this->gateway->set" . $credential1_camel . "('Billy');\n";
} ?>
<?php if (!empty($credential2)) { echo
  "        \$this->gateway->set" . $credential2_camel . "('really_secure');\n";
} ?>
        $this->card = new CreditCard(array('email' => 'mail@mail.com'));
    }

    public function testPurchase()
    {
        $response = $this->gateway->purchase(array('amount' => '10.00', 'currency' => 978, 'card' => $this->card))->send();
        $this->assertInstanceOf('Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>AuthorizeResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
        $this->assertSame('<?php echo $siteUrl ?>?type=sale&<?php echo $credential1_property ?>=Billy&<?php echo $credential2_property ?>=really_secure&total=10.00', $response->getRedirectUrl());
        $this->assert<?php echo ($isTransparentRedirect ? 'True' : 'False')?>($response->isTransparentRedirect());
    }

    public function testAuthorize()
    {
        $response = $this->gateway->authorize(array('amount' => '10.00', 'currency' => 978, 'card' => $this->card))->send();
        $this->assertInstanceOf('Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>AuthorizeResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
        $this->assertSame('<?php echo $siteUrl ?>?type=Authorize&<?php echo $credential1_property ?>=Billy&<?php echo $credential2_property ?>=really_secure&total=10.00', $response->getRedirectUrl());
        $this->assert<?php echo ($isTransparentRedirect ? 'True' : 'False')?>($response->isTransparentRedirect());
    }

    public function testCapture()
    {
        $response = $this->gateway->capture(array('amount' => '10.00', 'currency' => 978, 'card' => $this->card))->send();
        $this->assertInstanceOf('Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>AuthorizeResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
        $this->assertSame('<?php echo $siteUrl ?>?type=capture&<?php echo $credential1_property ?>=Billy&<?php echo $credential2_property ?>=really_secure&total=10.00', $response->getRedirectUrl());
        $this->assert<?php echo ($isTransparentRedirect ? 'True' : 'False')?>($response->isTransparentRedirect());
    }

    public function testCompletePurchase()
    {
        $request = $this->gateway->completePurchase(array('amount' => '10.00',));

        $this->assertInstanceOf('Omnipay\<?php echo $processorName ?>\Message\<?php echo $suffix ?>CompletePurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testCompletePurchaseSendMissingEmail()
    {
        $this->gateway->purchase(array('amount' => '10.00', 'currency' => 'USD', 'card' => array(
            'firstName' => 'Pokemon',
            'lastName' => 'The second',
        )))->send();
    }
}
