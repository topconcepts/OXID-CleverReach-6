<?php

namespace TopConcepts\CleverReach\Core;


use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class for custom mapping handling
 */
class CleverReachTldMapping {

    /**
     * class instance object
     * @var CleverReachTldMapping
     */
    protected static $instance;

    /**
     * TLD => Sprache , Country
     *
     * @var array
     */
    protected static $defaultLanguage = array(
        'default' => array('lg' => 'en', 'iso2' =>''),

        'de' => array('lg' => 'de', 'iso2' => 'de'),
        'at' => array('lg' => 'de', 'iso2' => 'at'),
        'ch' => array('lg' => 'de', 'iso2' => 'ch'),
        'fr' => array('lg' => 'fr', 'iso2' => 'fr'),
        'it' => array('lg' => 'it', 'iso2' => 'it'),
        'es' => array('lg' => 'es', 'iso2' => 'es'),
        'pt' => array('lg' => 'pt', 'iso2' => 'pt'),
        'nl' => array('lg' => 'nl', 'iso2' => 'nl'),
        'fi' => array('lg' => 'fi', 'iso2' => 'fi'),
        'sk' => array('lg' => 'sk', 'iso2' => 'sk'),
        'cz' => array('lg' => 'cs', 'iso2' => 'cz'),
        'se' => array('lg' => 'sv', 'iso2' => 'se'),
        'dk' => array('lg' => 'da', 'iso2' => 'dk'),
        'si' => array('lg' => 'is', 'iso2' => 'si'),
        'ru' => array('lg' => 'ru', 'iso2' => 'ru'),
        'ro' => array('lg' => 'ro', 'iso2' => 'ro'),
        'gr' => array('lg' => 'el', 'iso2' => 'gr'),
        'lv' => array('lg' => 'lv', 'iso2' => 'lv'),
        'pl' => array('lg' => 'pl', 'iso2' => 'pl'),
        'hu' => array('lg' => 'hu', 'iso2' => 'hu'),
        'ae' => array('lg' => 'ar', 'iso2' => 'ae'),
        'no' => array('lg' => 'no', 'iso2' => 'no'),
        'al' => array('lg' => 'sq', 'iso2' => 'al'),
        'hr' => array('lg' => 'hr', 'iso2' => 'hr'),
        'lt' => array('lg' => 'lt', 'iso2' => 'lt'),
        'tr' => array('lg' => 'tr', 'iso2' => 'tr'),
        'ua' => array('lg' => 'uk', 'iso2' => 'ua'),
        'uk' => array('lg' => 'en', 'iso2' => 'gb'),
        'us' => array('lg' => 'en', 'iso2' => 'us'),
        'au' => array('lg' => 'en', 'iso2' => 'au'),
    );

    /**
     * Language array
     *
     * @var array
     */
    protected $languages;

    /**
     * Singleton instance getter
     *
     * @return CleverReachTldMapping
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = oxNew(self::class);
        }
        return self::$instance;
    }

    /**
     * Constructor of tc_cleverreach_tldmapping
     */
    public function __construct() {
        $configMapping = Registry::getConfig()->getConfigParam('tc_cleverreach_tldmapping');
        $configMapping = (is_array($configMapping) === false) ? array() : $configMapping;

        $this->languages = array_merge(self::$defaultLanguage, $configMapping);
    }

    /**
     * Liefert alle Sprachen
     *
     * @return array
     */
    public function getAllLanguages() {
        return $this->languages;
    }

    /**
     * Liefert die Sprach anhand der Tld
     *
     * @param string $tld
     * @return string
     */
    public function getLanguageByTld($tld) {
        if (isset($this->languages[$tld]) ===  true) {
            return $this->languages[$tld]['lg'];
        }
        return $this->languages['default']['lg'];
    }

    /**
     * Liefert den ISO 2 Ländercode
     *
     * @param string $tld
     * @return string
     */
    public function getCountryISO2ByTld($tld) {
        if (isset($this->languages[$tld]) ===  true) {
            return $this->languages[$tld]['iso2'];
        }
        return $this->languages['default']['iso2'];
    }

    /**
     * Liefert den Namen des Landes
     *
     * @param string $tld
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getCountryByTld($tld) {
        $iso2    = $this->getCountryIso2ByTld($tld);
        $country = DatabaseProvider::getDb()->getOne("select oxtitle from oxcountry where oxisoalpha2 = ?", [$iso2]);

        return $country;
    }

    /**
     * Liefert die Sprache
     *
     * @param string $email
     * @return string
     */
    public function getLanguageByEMail($email) {
        $tld      = $this->getTldByEMail($email);
        $language = $this->getLanguageByTld($tld);

        return $language;
    }

    /**
     * Liefert den Namen des Landes
     *
     * @param string $email
     * @return string
     */
    public function getCountryByEMail($email) {
        $tld     = $this->getTldByEMail($email);
        $country = $this->getCountryByTld($tld);

        return $country;
    }

    /**
     * Liefert den ISO 2 Ländercode
     *
     * @param string $email
     * @return string
     */
    public function getCountryISO2ByEMail($email) {
        $tld  = $this->getTldByEMail($email);
        $iso2 = $this->getCountryISO2ByTld($tld);

        return $iso2;
    }

    /**
     * Liefert die Sprache
     *
     * @param string $iso2
     * @return string
     */
    public function getLanguageByISO2($iso2) {
        $iso2 = strtolower($iso2);

        foreach ($this->languages as $tld => $country) {

            if ($country['iso2'] === $iso2) {
                return $country['lg'];
            }

        }
        return $this->languages['default']['lg'];
    }

    /**
     * Liefert die Top Level Domain
     *
     * @param string $email
     * @param string
     *
     * @return string
     */
    public function getTldByEMail($email) {
        $tld = strrchr($email, '.');
        $tld = substr($tld, 1);

        return $tld;
    }
}
