<?php
namespace Rshop\Synchronization\Superfaktura;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Superfaktura InvoiceEmail
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class InvoiceEmail extends ApiObject
{
    /**
     * Mark as sent by email
     *
     * @return bool
     */
    public function markAsSent()
    {
        $this->_apiPost('invoices/mark_as_sent', $this->_data);

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
        $resolver->setDefined(array('invoice_id', 'email', 'subject', 'body'));

        $resolver->setRequired('invoice_id');
    }
}
