<?php

namespace spec\Rshop\Synchronization\Superfaktura;

use PhpSpec\ObjectBehavior;
use Rshop\Synchronization\TestConfig;

class InvoiceSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(array(), TestConfig::SFAPI_URL, TestConfig::EMAIL, TestConfig::API_KEY);
    }

    public function it_is_initializable_and_extends_api_object()
    {
        $this->shouldHaveType('Rshop\Synchronization\Superfaktura\Invoice');
        $this->shouldHaveType('Rshop\Synchronization\Superfaktura\ApiObject');
    }

    public function it_creates_invoice()
    {
        $this->setClient(array(
            'name' => 'Test client name',
            'ico' => '12345678'
        ));

        $this->addItem(array(
            'name' => 'Test item name',
            'unit_price' => 10,
            'tax' => 10
        ));

        $this->save();

        // check by summary
        $this->get('Summary')->shouldReturn(array(
            'vat_base_separate' => array('10' => 10),
            'vat_base_total' => 10,
            'vat_separate' => array('10' => 1),
            'vat_total' => 1,
            'invoice_total' => 11,
            'discount' => 0
        ));

        // set id as a constant for other tests
        TestConfig::$id = $this->getId()->getWrappedObject();
    }

    public function it_marks_as_sent()
    {
        $this->setId(TestConfig::$id)->markAsSent()->shouldReturn(true);
    }

    public function it_sends_by_email()
    {
        $this->setId(TestConfig::$id)->sendByEmail(array('to' => TestConfig::EMAIL))->shouldReturn(true);
    }

    public function it_adds_payment()
    {
        $this->setId(TestConfig::$id)->pay(array('amount' => 11))->shouldReturn(true);
    }

    public function it_returns_pdf_link()
    {
        $this->setId(TestConfig::$id)->getPdf()->shouldReturn(TestConfig::SFAPI_URL . 'slo/invoices/pdf/' . $this['id']->getWrappedObject() . '/token:' . $this['token']->getWrappedObject());
    }

    public function it_deletes_invoice()
    {
        $this->setId(TestConfig::$id)->delete()->shouldReturn(true);
    }
}
