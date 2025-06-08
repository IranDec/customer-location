<?php
/**
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class LocationAddress extends ObjectModel
{
    /** @var int PrestaShop Address ID */
    public $id_address;

    /** @var float GPS Latitude */
    public $gps_latitude;

    /** @var float GPS Longitude */
    public $gps_longitude;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'location_address', // Table name without prefix
        'primary' => 'id_address',     // Primary key
        'fields' => array(
            // Primary key (also a foreign key to ps_address.id_address)
            'id_address' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),

            // GPS Coordinates
            'gps_latitude' => array('type' => self::TYPE_FLOAT, 'validate' => 'isCoordinate', 'required' => true, 'size' => 12), // Precision handled by DB schema (DECIMAL 10,8)
            'gps_longitude' => array('type' => self::TYPE_FLOAT, 'validate' => 'isCoordinate', 'required' => true, 'size' => 13),// Precision handled by DB schema (DECIMAL 11,8)
        ),
    );

    /**
     * LocationAddressCore constructor.
     *
     * @param int|null $id
     * @param int|null $id_lang
     * @param int|null $id_shop
     */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        // PrestaShop ObjectModels usually call Shop::addTableAssociation() if they are shop-specific.
        // For this table, it's tied to id_address, which is already shop-associated if relevant.
        // Assuming this table does not need multi-shop specific entries beyond what id_address provides.
        // If LocationAddress rows were to be specific to a shop *independently* of the address's shop,
        // then Shop::addTableAssociation('location_address', array('type' => 'fk_shop')); would be needed
        // and an id_shop field in the table & ObjectModel.
        // Given the context (1-to-1 with address), this is likely not needed.
        parent::__construct($id, $id_lang, $id_shop);
    }
}
