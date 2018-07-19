<?php

namespace TopConcepts\CleverReach\Core\Prodsearch;


/**
 * Reflects an input form in the cleverreach email creation backend
 *
 * @class FormInput
 */
class FormInput extends Form  {

    /**
     * The type of the form element
     *
     * @var string
     */
    protected $type = 'input';

    /**
     * Builds formular data for json response
     *
     * @return array
     */
    public function getFormularData() {

        $settings                = $this->getSettings();
        $settings['type']        = $this->type;
        return $settings ;

     /*
        EXAMPLE:
        $contents = array(
            1 => array(
                'name'          => 'Product',
                'description'   => 'Place description here or leave emtpy',
                'required'      => false,
                'query_key'     => 'product',
                'type'          => 'input',
            )

        );
     */

    }
}
