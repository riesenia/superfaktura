<?php
namespace Rshop\Synchronization\Superfaktura;

/**
 * Superfaktura Exception
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class Exception extends \Exception
{
    /**
     * Errors
     *
     * @var array
     */
    protected $errors;

    /**
     * Errors setter
     *
     * @param array
     * @return void
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Errors getter
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
