<?php

class Recordlahanparkir extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id_record;

    /**
     *
     * @var integer
     */
    public $id_lahan;

    /**
     *
     * @var integer
     */
    public $slot_number;

    /**
     *
     * @var string
     */
    public $status;

    /**
     *
     * @var integer
     */
    public $id_kendaraan;

    /**
     *
     * @var string
     */
    public $timestamp;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'recordlahanparkir';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Recordlahanparkir[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Recordlahanparkir
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function initialize()
    {
        $this->belongsTo("id_lahan", "Lahanparkir", "id_lahan");
        $this->belongsTo("id_kendaraan", "Userkendaraan", "id_kendaraan");
    }
}
