<?php

namespace TopConcepts\CleverReach\Core\Prodsearch;


/**
 * Abstract form
 *
 * @Class Form
 */
abstract class Form {

    /**
     * type of the cleverreach search form
     *
     * @var string
     */
    protected $type             = null;

    /**
     * Settings for this formular element. they are fixed
     *
     * @var array
     */
    protected $settings         = array();

    /**
     * Values for an element, e.g. dropdown
     *
     * @var array
     */
    protected $values           = array();

    /**
     * Set settings
     *
     * @param array $settings
     */
    public function __construct(array $settings) {
        $this->settings = $settings;
    }

    /**
     * Get form element settings
     *
     * @return array
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * Get form element values
     *
     * @return array
     */
    public function getValues() {
        return $this->values;
    }

    /**
     * Returns the type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Builds the whole data set for json response
     *
     * @return mixed
     */
    abstract public function getFormularData();

}