<?php

class Lahanparkir extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id_lahan;

    /**
     *
     * @var string
     */
    public $deskripsi;

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
    public $max_kapasitas_mobil;

    /**
     *
     * @var integer
     */
    public $max_kapasitas_motor;

    /**
     *
     * @var string
     */
    public $jam_buka;

    /**
     *
     * @var string
     */
    public $jam_tutup;

    /**
     *
     * @var string
     */
    public $link_peta;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'lahanparkir';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Lahanparkir[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Lahanparkir
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
