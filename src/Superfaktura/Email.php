<?php
namespace Rshop\Synchronization\Superfaktura;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Superfaktura Email
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class Email extends ApiObject
{
    /**
     * Send email
     *
     * @return bool
     */
    public function send()
    {
        $this->_apiPost('invoices/send', $this->_data);

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
        $resolver->setDefined(['invoice_id', 'to', 'cc', 'bcc', 'subject', 'body']);

        $resolver->setRequired('invoice_id');
        $resolver->setRequired('to');
    }
}
