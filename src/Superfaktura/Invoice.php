<?php
namespace Rshop\Synchronization\Superfaktura;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Superfaktura Invoice
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class Invoice extends ApiObject
{
    /**
     * Set client
     *
     * @param array data
     * @return Rshop\Synchronization\Superfaktura\Invoice
     */
    public function setClient($data)
    {
        // fetch data
        $this->_fetch();

        $this->_data['Client'] = new Client($data, $this->_api['url'], $this->_api['email'], $this->_api['key']);

        return $this;
    }

    /**
     * Add invoice item
     *
     * @param array data
     * @return Rshop\Synchronization\Superfaktura\Invoice
     */
    public function addItem($data)
    {
        // fetch data
        $this->_fetch();

        if (!isset($this->_data['InvoiceItem'])) {
            $this->_data['InvoiceItem'] = [];
        }

        $this->_data['InvoiceItem'][] = new InvoiceItem($data, $this->_api['url'], $this->_api['email'], $this->_api['key']);

        return $this;
    }

    /**
     * Alias of getInvoiceItem
     *
     * @return array
     */
    public function getItems()
    {
        return $this->get('InvoiceItem');
    }

    /**
     * Save invoice
     *
     * @return Rshop\Synchronization\Superfaktura\Invoice
     */
    public function save()
    {
        $data = array_intersect_key($this->_data, ['Invoice' => 1, 'Client' => 1, 'InvoiceItem' => 1]);

        $response = $this->_apiPost($this->_id ? 'invoices/edit' : 'invoices/create', $data);

        // new invoice
        if (!$this->_id) {
            $this->_id = $response['data']['Invoice']['id'];
        }

        $this->_synced = false;
        $this->_fetch();

        return $this;
    }

    /**
     * Delete invoice
     *
     * return bool
     */
    public function delete()
    {
        if (!$this->_id) {
            return false;
        }

        $this->_apiGet('invoices/delete/' . $this->_id);
        $this->_data = [];
        $this->_id = null;

        return true;
    }

    /**
     * Mark as sent by email
     *
     * @param array data
     * @return bool
     */
    public function markAsSent($data = [])
    {
        if (!$this->_id) {
            return false;
        }

        $data['invoice_id'] = $this->_id;
        $this->_data['InvoiceEmail'] = new InvoiceEmail($data, $this->_api['url'], $this->_api['email'], $this->_api['key']);

        return $this->_data['InvoiceEmail']->markAsSent();
    }

    /**
     * Send by email
     *
     * @param array data
     * @return bool
     */
    public function sendByEmail($data = [])
    {
        if (!$this->_id) {
            return false;
        }

        $data['invoice_id'] = $this->_id;
        $this->_data['Email'] = new Email($data, $this->_api['url'], $this->_api['email'], $this->_api['key']);

        return $this->_data['Email']->send();
    }

    /**
     * Pay
     *
     * @param array data
     * @return bool
     */
    public function pay($data = [])
    {
        if (!$this->_id) {
            return false;
        }

        $data['invoice_id'] = $this->_id;
        $this->_data['InvoicePayment'] = new InvoicePayment($data, $this->_api['url'], $this->_api['email'], $this->_api['key']);

        return $this->_data['InvoicePayment']->pay();
    }

    /**
     * Get link to PDF
     *
     * @param string optional language
     * @return string
     */
    public function getPdf($language = 'slo')
    {
        if (!$this->_id) {
            return false;
        }

        return $this->_api['url'] . $language . '/invoices/pdf/' . $this->_id . '/token:' . $this['token'];
    }

    /**
     * Fetch data from Superfaktura
     *
     * @return bool true if entity was not synced
     */
    protected function _fetch()
    {
        if (parent::_fetch()) {
            try {
                $this->_data = $this->_apiGet('invoices/view/' . $this->_id . '.json');
            } catch (Exception $e) {
                // fail silently
            }

            return true;
        }

        return false;
    }

    /**
     * Configure options for options resolver
     *
     * @param Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected function _configureOptions(OptionsResolver $resolver)
    {
        // available options
        $resolver->setDefined(['already_paid', 'created', 'comment', 'constant', 'delivery', 'delivery_type', 'deposit', 'discount', 'due', 'estimate_id', 'header_comment', 'internal_comment', 'invoice_currency', 'invoice_no_formatted', 'issued_by', 'issued_by_phone', 'issued_by_email', 'name', 'payment_type', 'proforma_id', 'rounding', 'specific', 'sequence_id', 'tax_document', 'type', 'variable']);

        // validate / format options
        $resolver->setNormalizer('already_paid', $resolver->boolNormalizer);
        $resolver->setNormalizer('created', $resolver->dateNormalizer);
        $resolver->setNormalizer('delivery', $resolver->dateNormalizer);
        $resolver->setNormalizer('deposit', $resolver->floatNormalizer);
        $resolver->setNormalizer('due', $resolver->dateNormalizer);
        $resolver->setAllowedValues('invoice_currency', ['EUR', 'USD', 'GBP', 'HUF', 'CZK', 'PLN', 'CHF', 'RUB']);
        $resolver->setAllowedValues('rounding', ['document', 'item']);
        $resolver->setNormalizer('tax_document', $resolver->boolNormalizer);
        $resolver->setAllowedValues('type', ['regular', 'proforma', 'cancel', 'estimate', 'order']);
    }
}
