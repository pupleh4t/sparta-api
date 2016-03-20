<?php

class Arealahanparkir extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id_area;

    /**
     *
     * @var integer
     */
    public $id_lahan;

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
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'arealahanparkir';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Arealahanparkir[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Arealahanparkir
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
