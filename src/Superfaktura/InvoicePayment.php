<?php
namespace Rshop\Synchronization\Superfaktura;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Superfaktura InvoicePayment
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class InvoicePayment extends ApiObject
{
    /**
     * Add payment
     *
     * @return bool
     */
    public function pay()
    {
        $this->_apiPost('invoice_payments/add/ajax:1/api:1', $this->_data);

        return true;
    }

    /**
     * Configure options for options resolver
     *
     * @param Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected function _configureOptions(OptionsResolver $resolver)
    {
        // available options
        $resolver->setDefined(['invoice_id', 'payment_type', 'amount', 'currency', 'created']);

        $resolver->setRequired('invoice_id');
        $resolver->setAllowedValues('payment_type', ['cash', 'transfer', 'credit', 'paypal', 'cod']);
        $resolver->setRequired('amount');
        $resolver->setNormalizer('created', $resolver->dateNormalizer);

    }
}
