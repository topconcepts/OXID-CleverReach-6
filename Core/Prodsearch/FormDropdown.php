<?php

namespace TopConcepts\CleverReach\Core\Prodsearch;


/**
 * Drop down menu for product search in CR backend
 *
 * @class tc_cleverreach_prodsearch_form_dropdown
 */
class FormDropdown extends Form {

    /**
     * Type of form element
     *
     * @var string
     */
    protected $type = 'dropdown';

    /**
     * Builds formular data for this specific element
     *
     * @return array|mixed
     */
    public function getFormularData() {

        $settings           = $this->getSettings();
        $settings['type']   = $this->type;
        $values             = $this->getValues();
        $settings['values'] = $values;
        return $settings;

       /*
       EXAMPLE:
       $contents = array(
            0 => array(
                'name'          => 'Category',
                'description'   => 'Place description here or leave emtpy',
                'required'      => false,
                'query_key'     => 'category',
                'type'          => 'dropdown',
                'values'        => array(
                    0 => array(
                        'text'  => '',
                        'value' => '',
                    ),
                    1 => array(
                        'text' => 'cat 1',
                        'value' => 1,
                    ),
                    2 => array(
                        'text' => 'cat 2',
                        'value' => 2,
                    ),
                    3 => array(
                        'text' => 'cat 3',
                        'value' => 3,
                    )
                )
            ),
        );*/
    }

    /**
     * Adds a value for dropdown menu
     *
     * @param $value
     * @param $text
     */
    public function addValue($text, $value) {
        $this->values[] = array('text' => $text, 'value' => $value);
    }
}
