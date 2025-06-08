<?php
/**
 * طراح
 * Mohammad Babaei
 * وب‌سایت https://adschi.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Autoload classes
// require_once __DIR__ . '/vendor/autoload.php'; // If using composer
// If not using composer for this specific module's classes, ensure LocationAddress is loadable.
// PrestaShop's autoloader should handle classes in the 'classes' directory if named correctly (e.g. LocationAddress.php for class LocationAddress)

class Pslocationlocator extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'pslocationlocator';
        $this->tab = 'shipping_logistics'; // Or 'address'; 'others'
        $this->version = '1.0.0';
        $this->author = 'Mohammad Babaei';
        $this->need_instance = 1; // 0 if not using $this->instance in older PS, 1 if using it. For 1.7.8.x, 1 is typical.
        $this->bootstrap = true; // Use Bootstrap for configuration page

        parent::__construct();

        $this->displayName = $this->l('Location Locator');
        $this->description = $this->l('Captures GPS location on address form and enhances order details with location data.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '1.7.8.0', 'max' => _PS_VERSION_);

        // Define configuration keys
        define('PSLL_ALLOWED_CITIES', 'PSLL_ALLOWED_CITIES');
        define('PSLL_ALLOWED_CARRIERS', 'PSLL_ALLOWED_CARRIERS');
        define('PSLL_ENABLED_COUNTRIES_FOR_CITIES', 'PSLL_ENABLED_COUNTRIES_FOR_CITIES');
    }

    // Install/Uninstall methods will be fully implemented in a later step
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||
            !$this->registerHook('actionCustomerAddressFormSubmit') ||
            !$this->registerHook('actionCarrierProcess') ||
            !$this->registerHook('displayOrderDetail') ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionPDFInvoiceRender') ||
            !$this->registerHook('displayPDFInvoice') ||
            !$this->registerHook('displayAddressForm') || // For address form modifications
            !$this->registerHook('actionFrontControllerSetMedia') // For JS/CSS
        ) {
            return false;
        }

        if (!$this->installDb()) {
            $this->_errors[] = $this->l('Failed to install database table.');
            return false;
        }

        Configuration::updateValue(PSLL_ALLOWED_CITIES, '');
        Configuration::updateValue(PSLL_ALLOWED_CARRIERS, '');
        // Add other default configs if any

        // Set Iran as the default country for enabled city dropdown
        $id_iran_default = (int)Country::getByIso('IR');
        if ($id_iran_default == 0) {
            // If Iran by ISO is not found, try by name as a fallback, though less reliable
            $id_iran_default = (int)Country::getIdByName($this->context->language->id, 'Iran');
        }
        Configuration::updateValue(PSLL_ENABLED_COUNTRIES_FOR_CITIES, (string)$id_iran_default);

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName(PSLL_ALLOWED_CITIES);
        Configuration::deleteByName(PSLL_ALLOWED_CARRIERS);
        Configuration::deleteByName(PSLL_ENABLED_COUNTRIES_FOR_CITIES);

        if (!$this->uninstallDb()) {
            // Log or handle error, but usually don't prevent uninstallation for this
            PrestaShopLogger::addLog('Pslocationlocator: uninstallDb failed during uninstall.', 2);
        }

        // parent::uninstall() will unregister hooks
        return parent::uninstall();
    }

    protected function installDb()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "location_address` (
            `id_address` INT(10) UNSIGNED NOT NULL,
            `gps_latitude` DECIMAL(10, 8) DEFAULT NULL,
            `gps_longitude` DECIMAL(11, 8) DEFAULT NULL,
            PRIMARY KEY (`id_address`)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

        if (!Db::getInstance()->execute($sql)) {
            $this->_errors[] = Db::getInstance()->getMsgError();
            return false;
        }
        return true;
    }

    protected function uninstallDb()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "location_address`;";
        if (!Db::getInstance()->execute($sql)) {
            $this->_errors[] = Db::getInstance()->getMsgError();
            return false;
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        if (((bool)Tools::isSubmit('submitPslocationlocatorModule')) == true) {
            $output .= $this->postProcess();
        }

        // $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        // The configure.tpl can contain a link to the HelperForm rendered part or general info.
        // For now, let's keep it simple and render the form directly.
        // Fetching the template is not strictly needed if it's empty and we append form, but good practice.
        if (file_exists($this->local_path.'views/templates/admin/configure.tpl')) {
            $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        }


        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPslocationlocatorModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add data to your form */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        // Get available carriers
        $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::ALL_CARRIERS);
        $carrier_options = array();
        foreach ($carriers as $carrier) {
            $carrier_options[] = array(
                'id_carrier' => $carrier['id_carrier'],
                'name' => $carrier['name'] . ' (ID: ' . $carrier['id_carrier'] . ')', // Clarify with ID
            );
        }

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Allowed Cities'),
                        'name' => PSLL_ALLOWED_CITIES,
                        'desc' => $this->l('Enter city names, separated by commas. Case-sensitive.'),
                        'cols' => 60,
                        'rows' => 5,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Enabled Countries for City Dropdown'),
                        'name' => PSLL_ENABLED_COUNTRIES_FOR_CITIES . '[]', // Name for array submission
                        'desc' => $this->l('Select countries where the city dropdown will be active. (Ctrl+Click for multiple)'),
                        'multiple' => true,
                        'class' => 'chosen',
                        'options' => array(
                            'query' => $this->getCountryOptions(), // Method to get countries
                            'id' => 'id_country',    // Key for option value
                            'name' => 'name'         // Key for option label
                        )
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('Allowed Carriers'),
                        'name' => PSLL_ALLOWED_CARRIERS, // Name for the group of checkboxes
                        'desc' => $this->l('Select carriers for which this location logic applies.'),
                        'values' => array(
                            'query' => $carrier_options, // Prepared carrier data
                            'id' => 'id_carrier',        // Value for checkbox field name suffix
                            'name' => 'name'             // Label for checkbox
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $allowed_carriers_csv = Configuration::get(PSLL_ALLOWED_CARRIERS);
        $allowed_carriers_array = array();
        if ($allowed_carriers_csv !== false && !empty($allowed_carriers_csv)) {
             $allowed_carriers_array = explode(',', $allowed_carriers_csv);
        }

        $config_values = array(
            PSLL_ALLOWED_CITIES => Configuration::get(PSLL_ALLOWED_CITIES),
        );

        // For checkboxes, HelperForm expects keys like PSLL_ALLOWED_CARRIERS_1, PSLL_ALLOWED_CARRIERS_2 if '1', '2' are carrier IDs
        // The 'id' in 'values' array of the checkbox field definition in getConfigForm is used as suffix.
        // So, if id_carrier is the 'id', then fields are PSLL_ALLOWED_CARRIERS_1, PSLL_ALLOWED_CARRIERS_2 etc.
        foreach ($allowed_carriers_array as $carrier_id) {
            $config_values[PSLL_ALLOWED_CARRIERS . '_' . trim($carrier_id)] = true;
        }

        // For the multi-select country field
        $enabled_countries_str = Configuration::get(PSLL_ENABLED_COUNTRIES_FOR_CITIES);
        $selected_countries_array = array();
        if (!empty($enabled_countries_str)) {
            $selected_countries_array = explode(',', $enabled_countries_str);
        }
        // Ensure the array contains integers, as expected by HelperForm for multi-select chosen
        $selected_countries_array = array_map('intval', $selected_countries_array);

        // The key must match the 'name' attribute of the input field, including '[]'
        $config_values[PSLL_ENABLED_COUNTRIES_FOR_CITIES . '[]'] = $selected_countries_array;


        return $config_values;
    }

    protected function getCountryOptions()
    {
        $countries = Country::getCountries($this->context->language->id, true);
        $options = array();
        foreach ($countries as $country) {
            $options[] = array(
                'id_country' => $country['id_country'],
                'name' => $country['name'],
            );
        }
        return $options;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $output = '';

        if (Tools::isSubmit('submitPslocationlocatorModule')) {
            $allowed_cities = Tools::getValue(PSLL_ALLOWED_CITIES);

            Configuration::updateValue(PSLL_ALLOWED_CITIES, trim($allowed_cities));
            $output .= $this->displayConfirmation($this->l('Settings updated for cities.'));

            // Handle enabled countries for city dropdown
            $enabled_countries_for_cities = Tools::getValue(PSLL_ENABLED_COUNTRIES_FOR_CITIES); // This will be an array due to [] in name
            if (is_array($enabled_countries_for_cities)) {
                $enabled_countries_for_cities_ids = array_map('intval', $enabled_countries_for_cities);
                Configuration::updateValue(PSLL_ENABLED_COUNTRIES_FOR_CITIES, implode(',', $enabled_countries_for_cities_ids));
                $output .= $this->displayConfirmation($this->l('Enabled countries for city dropdown updated.'));
            } else {
                // Handle case where it might not be an array (e.g. if nothing is selected and form submits empty value)
                Configuration::updateValue(PSLL_ENABLED_COUNTRIES_FOR_CITIES, '');
                 $output .= $this->displayWarning($this->l('No countries selected for city dropdown. Setting was cleared.'));
            }


            // Handle allowed carriers checkboxes
            $selected_carriers = array();
            $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::ALL_CARRIERS);
            foreach ($carriers as $carrier) {
                // Checkboxes are submitted as PSLL_ALLOWED_CARRIERS_1, PSLL_ALLOWED_CARRIERS_2 etc. if ID is 1, 2
                // The value from Tools::getValue will be the 'value_on' if checked, or not present/empty if not.
                // HelperForm typically uses '1' or 'on' as value for checked.
                if (Tools::getValue(PSLL_ALLOWED_CARRIERS . '_' . $carrier['id_carrier'])) {
                    $selected_carriers[] = (int)$carrier['id_carrier'];
                }
            }

            // Validate that all selected_carriers are actual integer IDs (already done by (int))
            // $validated_carriers = array_map('intval', $selected_carriers);
            // $validated_carriers = array_filter($validated_carriers, function($id) { return $id > 0; });
            // The above filtering is good if input could be non-numeric, but here we control the IDs.

            if (!empty($selected_carriers)) {
                Configuration::updateValue(PSLL_ALLOWED_CARRIERS, implode(',', $selected_carriers));
                $output .= $this->displayConfirmation($this->l('Settings updated for carriers.'));
            } else {
                Configuration::updateValue(PSLL_ALLOWED_CARRIERS, ''); // No carriers selected
                $output .= $this->displayConfirmation($this->l('No carriers selected. Allowed carriers list cleared.'));
            }
        }
        return $output;
    }

    // Placeholder for the hook to add fields to address form - will be detailed in a later step
    // public function hookAdditionalCustomerAddressFields($params) { }
    // Or hookDisplayAddressForm(), or hookOverrideTpl
    // Need to also register this hook and hook to load JS/CSS
    // public function hookActionFrontControllerSetMedia() { }

    /**
     * Hook executed after an address form is submitted.
     * Saves GPS coordinates if provided.
     *
     * @param array $params Hook parameters
     * @return void
     */
    public function hookActionCustomerAddressFormSubmit($params)
    {
        // Check if the Address object is available
        if (!isset($params['address']) || !is_a($params['address'], 'Address') || !(int)$params['address']->id) {
            // Log or handle error: Address object not available or invalid
            PrestaShopLogger::addLog('Pslocationlocator: Address object not available in hookActionCustomerAddressFormSubmit.', 2);
            return;
        }

        $id_address = (int)$params['address']->id;

        // Get GPS coordinates from the form submission
        // Tools::getValue will return false if not set, or the value.
        $gps_latitude = Tools::getValue('gps_latitude');
        $gps_longitude = Tools::getValue('gps_longitude');

        // Only proceed if both latitude and longitude are provided and appear to be valid numbers
        // is_numeric is a basic check; Validate::isCoordinate would be better if it allows empty strings.
        // Let's assume empty strings mean "don't save/update GPS".
        if ($gps_latitude !== false && $gps_longitude !== false &&
            trim($gps_latitude) !== '' && trim($gps_longitude) !== '' &&
            is_numeric(trim($gps_latitude)) && is_numeric(trim($gps_longitude))) {

            $latitude = (float)trim($gps_latitude);
            $longitude = (float)trim($gps_longitude);

            // Validate coordinates (basic validation, ObjectModel will use isCoordinate)
            if (!Validate::isCoordinate($latitude) || !Validate::isCoordinate($longitude)) {
                // Log or handle invalid coordinate format after basic checks
                PrestaShopLogger::addLog(
                    sprintf('Pslocationlocator: Invalid GPS coordinates received for address ID %d: Lat "%s", Lng "%s".', $id_address, $gps_latitude, $gps_longitude),
                    2 // Severity: Warning
                );
                return; // Do not save invalid coordinates
            }

            // Load or create LocationAddress object
            // Since id_address is the PK, we pass it to the constructor.
            // The LocationAddress.php ObjectModel expects the primary key in constructor.
            try {
                $locationAddress = new LocationAddress($id_address);
                // If the object is loaded, $locationAddress->id will be $id_address.
                // If it's a new record, $locationAddress->id will be null initially,
                // but we want to set it to $id_address.
                // ObjectModel handles this: if $id is provided and exists, it loads. Otherwise, it's a new object with that ID set.

                // For our PK 'id_address', the constructor should correctly load if an entry with this id_address exists.
                // If it doesn't exist, it's a new object. We then set its properties.
                // $locationAddress->id will be the value of the primary key.
                // $locationAddress->id_address is the actual field.

                $locationAddress->id_address = $id_address; // Ensure PK field is set
                $locationAddress->gps_latitude = $latitude;
                $locationAddress->gps_longitude = $longitude;

                if (!$locationAddress->save()) {
                    // Log error during save
                    PrestaShopLogger::addLog(
                        sprintf('Pslocationlocator: Failed to save LocationAddress for address ID %d. DB Error: %s', $id_address, Db::getInstance()->getMsgError()),
                        3 // Severity: Error
                    );
                } else {
                     PrestaShopLogger::addLog(
                        sprintf('Pslocationlocator: Successfully saved/updated GPS for address ID %d.', $id_address),
                        1 // Severity: Informative
                    );
                }
            } catch (PrestaShopException $e) {
                PrestaShopLogger::addLog(
                    sprintf('Pslocationlocator: Exception while saving LocationAddress for address ID %d. Exception: %s', $id_address, $e->getMessage()),
                    3 // Severity: Error
                );
            }
        } elseif (($gps_latitude !== false && trim($gps_latitude) !== '') || ($gps_longitude !== false && trim($gps_longitude) !== '')) {
            // Case: One field is filled, the other is empty, or values are non-numeric after being present
             PrestaShopLogger::addLog(
                sprintf('Pslocationlocator: Incomplete or non-numeric GPS data for address ID %d. Lat: "%s", Lng: "%s". Data not saved.', $id_address, $gps_latitude, $gps_longitude),
                2 // Severity: Warning
            );
            // Optionally, if there's an existing record for this id_address, and new data is empty/invalid,
            // one might choose to delete the existing GPS data.
            // For now, we only save if both are present and valid. We don't delete existing on empty submission.
        }
        // If $gps_latitude and $gps_longitude are empty or not set, do nothing (don't save or delete existing).
    }

    /**
     * Hook executed during carrier selection process.
     * Validates if the selected carrier and customer city are allowed.
     *
     * @param array $params Hook parameters, typically includes the cart
     * @return mixed Returns an error message string if validation fails, or false/void if it passes.
     */
    public function hookActionCarrierProcess($params)
    {
        // Ensure cart object is available
        if (!isset($params['cart']) || !is_a($params['cart'], 'Cart') || !$params['cart']->id) {
            PrestaShopLogger::addLog('Pslocationlocator: Cart object not available in hookActionCarrierProcess.', 2);
            return false; // Or some indication not to block if cart is missing
        }

        /** @var Cart $cart */
        $cart = $params['cart'];

        // Get delivery address from cart
        $id_address_delivery = $cart->id_address_delivery;
        if (!$id_address_delivery) {
            PrestaShopLogger::addLog('Pslocationlocator: No delivery address set in cart for hookActionCarrierProcess.', 2);
            // This can happen if no address is selected yet.
            // Depending on when hookActionCarrierProcess runs, this might be normal or an error.
            // If it's before address selection, we can't check city.
            // For now, assume an address should be present if carriers are being processed for it.
            return false;
        }

        $address = new Address($id_address_delivery);
        if (!Validate::isLoadedObject($address)) {
            PrestaShopLogger::addLog('Pslocationlocator: Could not load delivery address: ' . $id_address_delivery, 3);
            return $this->l('Could not verify shipping address. Please try again.'); // Error message
        }

        // 1. Check Allowed Cities
        $customer_city = trim(Tools::strtolower($address->city));
        $allowed_cities_str = Configuration::get(PSLL_ALLOWED_CITIES, '');

        if (!empty($allowed_cities_str)) {
            $allowed_cities_list = array_map('trim', explode(',', Tools::strtolower($allowed_cities_str)));
            // Filter out empty values that might result from multiple commas like "city1,,city2"
            $allowed_cities_list = array_filter($allowed_cities_list, function($city) {
                return !empty($city);
            });

            if (!empty($allowed_cities_list) && !in_array($customer_city, $allowed_cities_list)) {
                PrestaShopLogger::addLog(
                    sprintf('Pslocationlocator: City "%s" not in allowed list "%s".', $customer_city, $allowed_cities_str),
                    1 // Info
                );
                // The hook expects a string (error message) to block, or false to allow.
                return $this->l('Shipping method not available for your city.');
            }
        }

        // 2. Check Allowed Carriers
        // The selected carrier ID should be available. It might be in $cart->id_carrier or passed differently
        // depending on when hookActionCarrierProcess is called.
        // Often, this hook is used to validate a carrier *after* it's been tentatively selected.
        // Let's assume the cart's current carrier is the one being processed.
        $id_selected_carrier = (int)$cart->id_carrier;

        if ($id_selected_carrier > 0) {
            $allowed_carriers_str = Configuration::get(PSLL_ALLOWED_CARRIERS, '');
            if (!empty($allowed_carriers_str)) {
                $allowed_carriers_list = array_map('intval', explode(',', $allowed_carriers_str));
                // Filter out zeros or invalid IDs if any
                $allowed_carriers_list = array_filter($allowed_carriers_list, function($carrier_id) {
                    return $carrier_id > 0;
                });

                if (!empty($allowed_carriers_list) && !in_array($id_selected_carrier, $allowed_carriers_list, true)) {
                     PrestaShopLogger::addLog(
                        sprintf('Pslocationlocator: Carrier ID %d not in allowed list "%s".', $id_selected_carrier, $allowed_carriers_str),
                        1 // Info
                    );
                    return $this->l('Shipping method not available for your city.'); // Generic message as per spec
                }
            }
        } else {
            // No carrier selected yet, or invalid carrier ID in cart.
            // This hook might be called multiple times in checkout.
            // If no carrier is selected, we probably shouldn't block yet.
            PrestaShopLogger::addLog('Pslocationlocator: No carrier ID found in cart for validation in hookActionCarrierProcess.', 1);
        }

        return false; // No restriction, proceed
    }

    /**
     * Hook to display content in the order detail page (FO).
     * Shows GPS coordinates if available for the order's delivery address.
     *
     * @param array $params Hook parameters, includes 'order' object
     * @return string HTML content to display or empty string
     */
    public function hookDisplayOrderDetail($params)
    {
        if (!isset($params['order']) || !is_a($params['order'], 'Order') || !$params['order']->id) {
            PrestaShopLogger::addLog('Pslocationlocator: Order object not available in hookDisplayOrderDetail.', 2);
            return '';
        }

        /** @var Order $order */
        $order = $params['order'];
        $id_address_delivery = $order->id_address_delivery;

        if (!$id_address_delivery) {
            PrestaShopLogger::addLog('Pslocationlocator: No delivery address ID in order for hookDisplayOrderDetail. Order ID: ' . $order->id, 1);
            return '';
        }

        try {
            $locationAddress = new LocationAddress($id_address_delivery);

            if (Validate::isLoadedObject($locationAddress) && $locationAddress->id_address == $id_address_delivery) {
                // Check if coordinates are not zero or empty, as they are float
                if (!empty($locationAddress->gps_latitude) && !empty($locationAddress->gps_longitude) &&
                    ((float)$locationAddress->gps_latitude != 0 || (float)$locationAddress->gps_longitude != 0) ) {

                    $this->context->smarty->assign(array(
                        'psll_gps_latitude' => $locationAddress->gps_latitude,
                        'psll_gps_longitude' => $locationAddress->gps_longitude,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/displayOrderDetail.tpl');
                } else {
                    PrestaShopLogger::addLog('Pslocationlocator: GPS coordinates are zero/empty for address ID: ' . $id_address_delivery . ' in hookDisplayOrderDetail.', 1);
                    return ''; // No valid coordinates to display
                }
            } else {
                 PrestaShopLogger::addLog('Pslocationlocator: No LocationAddress record found for address ID: ' . $id_address_delivery . ' in hookDisplayOrderDetail.', 1);
                return ''; // No record found
            }
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog('Pslocationlocator: Exception in hookDisplayOrderDetail for address ID: ' . $id_address_delivery . '. Exc: ' . $e->getMessage(), 3);
            return ''; // Error case
        }
    }

    /**
     * Hook to display content in the admin order view page.
     * Shows GPS coordinates if available for the order's delivery address.
     *
     * @param array $params Hook parameters, includes 'id_order'
     * @return string HTML content to display or empty string
     */
    public function hookDisplayAdminOrder($params)
    {
        if (!isset($params['id_order']) || !(int)$params['id_order']) {
            PrestaShopLogger::addLog('Pslocationlocator: Order ID not available in hookDisplayAdminOrder.', 2);
            return '';
        }

        $id_order = (int)$params['id_order'];
        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            PrestaShopLogger::addLog('Pslocationlocator: Could not load order object in hookDisplayAdminOrder. Order ID: ' . $id_order, 3);
            return '';
        }

        $id_address_delivery = $order->id_address_delivery;

        if (!$id_address_delivery) {
            PrestaShopLogger::addLog('Pslocationlocator: No delivery address ID in order for hookDisplayAdminOrder. Order ID: ' . $id_order, 1);
            return '';
        }

        try {
            $locationAddress = new LocationAddress($id_address_delivery);

            if (Validate::isLoadedObject($locationAddress) && $locationAddress->id_address == $id_address_delivery) {
                 // Check if coordinates are not zero or empty
                if (!empty($locationAddress->gps_latitude) && !empty($locationAddress->gps_longitude) &&
                    ((float)$locationAddress->gps_latitude != 0 || (float)$locationAddress->gps_longitude != 0) ) {

                    $this->context->smarty->assign(array(
                        'psll_gps_latitude' => $locationAddress->gps_latitude,
                        'psll_gps_longitude' => $locationAddress->gps_longitude,
                        'psll_id_order' => $id_order, // Potentially useful for template or JS
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl');
                } else {
                    PrestaShopLogger::addLog('Pslocationlocator: GPS coordinates are zero/empty for address ID: ' . $id_address_delivery . ' in hookDisplayAdminOrder.', 1);
                }
            } else {
                PrestaShopLogger::addLog('Pslocationlocator: No LocationAddress record found for address ID: ' . $id_address_delivery . ' in hookDisplayAdminOrder.', 1);
            }
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog('Pslocationlocator: Exception in hookDisplayAdminOrder for address ID: ' . $id_address_delivery . '. Exc: ' . $e->getMessage(), 3);
        }
        return ''; // Return empty string if no data or error
    }

    public function hookActionPDFInvoiceRender($params)
    {
        if (!isset($params['object']) || !is_a($params['object'], 'OrderInvoice')) {
            PrestaShopLogger::addLog('Pslocationlocator: OrderInvoice object not available in hookActionPDFInvoiceRender.', 2);
            return;
        }
        if (!isset($params['smarty'])) {
            PrestaShopLogger::addLog('Pslocationlocator: Smarty object not available in hookActionPDFInvoiceRender.', 2);
            return;
        }

        /** @var OrderInvoice $invoice */
        $invoice = $params['object'];
        $id_order = (int)$invoice->id_order;

        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            PrestaShopLogger::addLog('Pslocationlocator: Could not load order object for invoice. Order ID: ' . $id_order, 3);
            return;
        }

        $id_address_delivery = (int)$order->id_address_delivery;
        if (!$id_address_delivery) {
            PrestaShopLogger::addLog('Pslocationlocator: No delivery address ID in order for invoice QR code. Order ID: ' . $id_order, 1);
            return;
        }

        try {
            // Ensure LocationAddress class is loaded (PrestaShop's autoloader should handle it)
            if (!class_exists('LocationAddress')) {
                 PrestaShopLogger::addLog('Pslocationlocator: LocationAddress class not found in hookActionPDFInvoiceRender.', 3);
                 return; // Cannot proceed without the class
            }
            $locationAddress = new LocationAddress($id_address_delivery);

            if (Validate::isLoadedObject($locationAddress) && $locationAddress->id_address == $id_address_delivery &&
                isset($locationAddress->gps_latitude) && isset($locationAddress->gps_longitude) &&
                trim((string)$locationAddress->gps_latitude) !== '' && trim((string)$locationAddress->gps_longitude) !== '' && // Check for non-empty strings explicitly
                ((float)$locationAddress->gps_latitude != 0 || (float)$locationAddress->gps_longitude != 0)) {

                $phpqrcode_path = $this->local_path . 'vendor/phpqrcode.php';
                if (file_exists($phpqrcode_path)) {
                    require_once $phpqrcode_path;
                } else {
                    PrestaShopLogger::addLog('Pslocationlocator: PHP QR Code library not found at ' . $phpqrcode_path, 3);
                    $params['smarty']->assign('psll_qr_code_error', $this->l('QR Code library not found.'));
                    return;
                }

                if (!class_exists('QRcode')) {
                    PrestaShopLogger::addLog('Pslocationlocator: QRcode class not found after including phpqrcode.php.', 3);
                    $params['smarty']->assign('psll_qr_code_error', $this->l('QR Code class not found.'));
                    return;
                }

                $qr_content = $locationAddress->gps_latitude . ',' . $locationAddress->gps_longitude;
                $qr_code_dir = $this->local_path . 'views/images/qrcodes/';
                // The placeholder creates a .png named file but with text content. The actual library would create a binary PNG.
                $qr_code_filename = 'qr_' . $id_order . '.png';
                $qr_code_filepath = $qr_code_dir . $qr_code_filename;

                if (!is_dir($qr_code_dir)) {
                    if (!@mkdir($qr_code_dir, 0755, true)) {
                        PrestaShopLogger::addLog('Pslocationlocator: Failed to create QR code directory: ' . $qr_code_dir . '. Check permissions.', 3);
                        $params['smarty']->assign('psll_qr_code_error', $this->l('QR code directory creation failed.'));
                        return;
                    }
                }

                QRcode::png($qr_content, $qr_code_filepath, 'L', 4, 2);

                if (file_exists($qr_code_filepath)) {
                    $params['smarty']->assign('psll_qr_code_image_path', $qr_code_filepath);
                    PrestaShopLogger::addLog('Pslocationlocator: QR Code (placeholder) generated for Order ID ' . $id_order . ' at ' . $qr_code_filepath, 1);
                } else {
                    PrestaShopLogger::addLog('Pslocationlocator: Failed to generate QR code (placeholder) file for Order ID ' . $id_order, 3);
                    $params['smarty']->assign('psll_qr_code_error', $this->l('QR code generation failed.'));
                }
            } else {
                 PrestaShopLogger::addLog('Pslocationlocator: No valid GPS data for QR Code. Order ID: ' . $id_order . ', Address ID: ' . $id_address_delivery, 1);
                 $params['smarty']->assign('psll_qr_code_image_path', null); // Ensure it's null if no data
            }
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog('Pslocationlocator: PrestaShopException in hookActionPDFInvoiceRender for Order ID ' . $id_order . '. Exc: ' . $e->getMessage(), 3);
            $params['smarty']->assign('psll_qr_code_error', $this->l('QR code generation error (PrestaShopException).'));
        } catch (Exception $e) { // Catch generic exceptions too
            PrestaShopLogger::addLog('Pslocationlocator: Generic Exception in hookActionPDFInvoiceRender for Order ID ' . $id_order . '. Exc: ' . $e->getMessage(), 3);
            $params['smarty']->assign('psll_qr_code_error', $this->l('QR code generation error (Exception).'));
        }
    }

    public function hookDisplayPDFInvoice($params)
    {
        if (!isset($params['smarty'])) {
            PrestaShopLogger::addLog('Pslocationlocator: Smarty object not available in hookDisplayPDFInvoice.', 2);
            return '';
        }

        $smarty = $params['smarty'];
        // Variables assigned in hookActionPDFInvoiceRender should be accessible here.
        $qr_code_path = $smarty->getTemplateVars('psll_qr_code_image_path');
        $qr_code_error = $smarty->getTemplateVars('psll_qr_code_error'); // Check if an error was already set

        if (!empty($qr_code_error)) {
             PrestaShopLogger::addLog('Pslocationlocator: Not rendering QR code due to prior error: ' . $qr_code_error . (isset($params['object']->id_order) ? ' Order ID: '.$params['object']->id_order : ''), 2);
             return '';
        }

        if (!empty($qr_code_path) && file_exists($qr_code_path)) {
            // All good, display the template that shows the QR code.
            // The smarty variable psll_qr_code_image_path is already set from the previous hook.
            return $this->display(__FILE__, 'views/templates/hook/displayPDFInvoice.tpl');
        } else {
            // Only log if no specific error was previously set, and path is missing/invalid.
            $order_id_log = isset($params['object']->id_order) ? ' Order ID: '.$params['object']->id_order : ' (Order ID not available in params)';
            if (empty($qr_code_error)) { // Avoid double logging if error was already set and handled
                 PrestaShopLogger::addLog('Pslocationlocator: QR code path not found or file does not exist in hookDisplayPDFInvoice (no prior error set).'.$order_id_log , 1); // Info level
            }
        }
        return ''; // Return empty if no QR code to display or error occurred.
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        // Only load on specific controllers (address, order, identity)
        $allowed_controllers = array('address', 'identity', 'order', 'orderopc'); // orderopc for one-page checkout
        $current_controller = Tools::strtolower($this->context->controller->php_self);

        if (in_array($current_controller, $allowed_controllers)) {
            // Check if country is Iran to potentially pass JsDef, or rely on hookDisplayAddressForm condition
            // For now, always load JS/CSS on address pages, JS itself checks for button.
            $this->context->controller->addCSS($this->_path . 'views/assets/css/gps_address.css');
            $this->context->controller->addJS($this->_path . 'views/assets/js/gps_address.js');

            // Pass Country ID for Iran to JavaScript if needed by JS logic
            // $id_iran = (int)Country::getByIso('IR');
            // Media::addJsDef(array('psll_id_country_iran' => $id_iran));
            // The current JS doesn't strictly require this if button is conditionally rendered by PHP.
        }
    }

    public function hookDisplayAddressForm($params)
    {
        // This hook is typically called for an existing address.
        // $params['address'] contains the Address object.
        // If it's a new address form, we might need to check default country or selected country.

        $id_country_current_form = 0;
        if (Tools::isSubmit('id_country')) {
            $id_country_current_form = (int)Tools::getValue('id_country');
        } elseif (isset($params['address']->id_country) && $params['address']->id_country) {
            $id_country_current_form = (int)$params['address']->id_country;
        } elseif (isset($this->context->country->id)) { // Default context country
            $id_country_current_form = (int)$this->context->country->id;
        }

        $id_iran = (int)Country::getByIso('IR');
        $show_gps_button = ($id_country_current_form == $id_iran);

        // City Dropdown Logic
        $enabled_countries_for_cities_str = Configuration::get(PSLL_ENABLED_COUNTRIES_FOR_CITIES, '');
        $enabled_country_ids = array();
        if (!empty($enabled_countries_for_cities_str)) {
            $enabled_country_ids = array_map('intval', explode(',', $enabled_countries_for_cities_str));
        }

        $show_city_dropdown = false;
        $cities_list = array();

        if (in_array($id_country_current_form, $enabled_country_ids)) {
            $show_city_dropdown = true;
            $allowed_cities_str = Configuration::get(PSLL_ALLOWED_CITIES, '');
            if (!empty($allowed_cities_str)) {
                $cities_list = array_map('trim', explode(',', $allowed_cities_str));
                // Remove empty city names that might result from ",," or trailing commas
                $cities_list = array_filter($cities_list, function($city) {
                    return !empty($city);
                });
            }
        }

        // Get existing address data (city, gps)
        $existing_city = '';
        $gps_latitude = '';
        $gps_longitude = '';

        if (isset($params['address']->id) && $params['address']->id) {
            $address_object = $params['address']; // Address Object
            $existing_city = $address_object->city;
            try {
                $locationAddress = new LocationAddress((int)$params['address']->id);
                if (Validate::isLoadedObject($locationAddress)) {
                    $gps_latitude = $locationAddress->gps_latitude;
                    $gps_longitude = $locationAddress->gps_longitude;
                }
            } catch (PrestaShopException $e) {
                PrestaShopLogger::addLog('Pslocationlocator: Error loading LocationAddress: ' . $e->getMessage(), 2);
            }
        } elseif (Tools::isSubmit('city')) { // Pre-fill from form submission if validation fails
            $existing_city = Tools::getValue('city');
        }


        $this->context->smarty->assign(array(
            'psll_show_gps_button' => $show_gps_button, // For GPS button
            'psll_existing_latitude' => $gps_latitude,
            'psll_existing_longitude' => $gps_longitude,

            'psll_show_city_dropdown' => $show_city_dropdown, // For City Dropdown
            'psll_cities_list' => $cities_list,
            'psll_current_city' => $existing_city, // Pass current city for pre-selection
        ));

        // The main template file path should be returned.
        // It will decide internally what to show based on the assigned smarty variables.
        return $this->display(__FILE__, 'views/templates/hook/displayAddressFormFields.tpl');
    }
}
