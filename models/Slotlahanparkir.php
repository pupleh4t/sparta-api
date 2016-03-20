<?php

class Slotlahanparkir extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id_slot;

    /**
     *
     * @var string
     */
    public $status;

    /**
     *
     * @var string
     */
    public $latitude;

    /**
     *
     * @var string
     */
    public $longitude;

    /**
     *
     * @var integer
     */
    public $id_lahan;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'slotlahanparkir';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Slotlahanparkir[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Slotlahanparkir
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
