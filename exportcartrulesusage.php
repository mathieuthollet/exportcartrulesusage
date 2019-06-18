<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ExportCartRulesUsage extends Module
{
    private $html = '';

    protected $config_form = false;
    //protected $support_url = 'https://addons.prestashop.com/contact-form.php?id_product=30813';

    public function __construct()
    {
        $this->name = 'exportcartrulesusage';
        $this->tab = 'export';
        $this->version = '1.0.0';
        $this->author = 'Mathieu Thollet';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '89665a4f0c4ab095d8f48e9e063750a2';

        parent::__construct();

        $this->displayName = $this->l('Export cart rules usage orders CSV');
        $this->description = $this->l('Export CSV file of usage of cart rules in orders. Filter by cart rule, order status, date and country.');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        if (((bool)Tools::isSubmit('submitExport_cart_rules_usageModule')) == true) {
            $this->postProcess();
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        //$this->context->smarty->assign('support_url', $this->support_url);
        $output = $this->html .
            $this->context->smarty->fetch($this->local_path . 'views/templates/admin/export.tpl') .
            $this->renderForm()
            /*$this->context->smarty->fetch($this->local_path.'views/templates/admin/support.tpl')*/;
        return $output;
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
        $helper->submit_action = 'submitExport_cart_rules_usageModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $helper->fields_value = $_POST;
        if (empty($helper->fields_value['id_cart_rule'])) {
            $helper->fields_value['id_cart_rule'] = '';
        }
        if (empty($helper->fields_value['code_cart_rule'])) {
            $helper->fields_value['code_cart_rule'] = '';
        }
        if (empty($helper->fields_value['id_country'])) {
            $helper->fields_value['id_country'] = '';
        }
        if (empty($helper->fields_value['date_from'])) {
            $helper->fields_value['date_from'] = '';
        }
        if (empty($helper->fields_value['date_to'])) {
            $helper->fields_value['date_to'] = '';
        }
        if (empty($helper->fields_value['id_country'])) {
            $helper->fields_value['id_country'] = '';
        }

        return $helper->generateForm(array($this->getExportForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getExportForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Export Cart Rules usage'),
                    'icon' => 'icon-file',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Cart rule (ID)'),
                        'name' => 'id_cart_rule',
                        'maxlength' => 10,
                        'required' => false,
                        'class' => 'fixed-width-xl',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Cart rule (code)'),
                        'name' => 'code_cart_rule',
                        'maxlength' => 10,
                        'required' => false,
                        'class' => 'fixed-width-xl',
                    ),
                    array(
                        'type' => 'datetime',
                        'label' => $this->l('From'),
                        'name' => 'date_from',
                        'maxlength' => 10,
                        'hint' => $this->l('Format: 2011-12-31 (inclusive).')
                    ),
                    array(
                        'type' => 'datetime',
                        'label' => $this->l('To'),
                        'name' => 'date_to',
                        'maxlength' => 10,
                        'hint' => $this->l('Format: 2012-12-31 (inclusive).')
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Country'),
                        'name' => 'id_country',
                        'options' => array(
                            'query' => Country::getCountries($this->context->language->id, true),
                            'id' => 'id_country',
                            'name' => 'name',
                            'default' => array(
                                'label' => $this->l('-- All --'),
                                'value' => 0
                            )
                        )
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('Order statuses'),
                        'name' => 'id_order_state',
                        'values' => array(
                            'query' => OrderState::getOrderStates($this->context->language->id),
                            'id' => 'id_order_state',
                            'name' => 'name'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('CSV Export'),
                    'id' => 'submitDownload',
                    'icon' => 'process-icon-download-alt'
                ),
            ),
        );
    }


    /**
     * PostProcess
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('submitExport_cart_rules_usageModule')) {
            $this->processExport();
        }
    }


    /**
     * Export data
     */
    protected function processExport()
    {
        set_time_limit(3600);

        // Get filters params
        $id_cart_rule = Tools::getValue('id_cart_rule');
        $code_cart_rule = Tools::getValue('code_cart_rule');
        $date_to = Tools::getValue('date_to');
        $date_from = Tools::getValue('date_from');
        $id_country = Tools::getValue('id_country');
        $id_order_state = array();
        foreach (OrderState::getOrderStates($this->context->language->id) as $order_state) {
            if (Tools::getValue('id_order_state_' . $order_state['id_order_state'])) {
                $id_order_state[] = $order_state['id_order_state'];
            }
        }
        if (!$id_cart_rule && !$code_cart_rule) {
            $this->html = '<p class="alert alert-danger">' . $this->l('You must provide at least Cart Rule ID or Cart Rule Code') . '</p>';
            return;
        }

        // Get data
        $sql = 'SELECT o.`reference` AS order_reference, o.`date_add` AS order_date, o.`id_shop`, o.`total_paid_tax_incl`, o.`total_paid_tax_excl`, o.`total_shipping_tax_incl`, o.`total_shipping_tax_excl`
                    , osl.`name` AS order_state
                    , oi.`id_order_invoice`, oi.`date_add` AS invoice_date
                    , c.`email`
                    , ai.`company` AS invoice_company, ai.`firstname` AS invoice_firstname, ai.`lastname` AS invoice_lastname, ai.`address1` AS invoice_address1, ai.`address2` AS invoice_address2, ai.`postcode` AS invoice_postcode, ai.`city` AS invoice_city 
                    , ci.`name` AS invoice_country
                    , ad.`company` AS delivery_company, ad.`firstname` AS delivery_firstname, ad.`lastname` AS delivery_lastname, ad.`address1` AS delivery_address1, ad.`address2` AS delivery_address2, ad.`postcode` AS delivery_postcode, ad.`city` AS delivery_city 
                    , cd.`name` AS delivery_country
			FROM `' . _DB_PREFIX_ . 'orders` o
			LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (osl.`id_order_state` = o.`current_state` AND osl.`id_lang` = ' . $this->context->language->id . ')
			LEFT JOIN `' . _DB_PREFIX_ . 'order_invoice` oi ON (oi.`id_order` = o.`id_order`)
			LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = o.`id_customer`)
			LEFT JOIN `' . _DB_PREFIX_ . 'address` ai ON (ai.`id_address` = o.`id_address_invoice`)
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` ci ON (ci.`id_country` = ai.`id_country` AND ci.`id_lang` = ' . $this->context->language->id . ')
			LEFT JOIN `' . _DB_PREFIX_ . 'address` ad ON (ad.`id_address` = o.`id_address_delivery`)
			LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cd ON (cd.`id_country` = ad.`id_country` AND cd.`id_lang` = ' . $this->context->language->id . ')
			WHERE 1=1 ';
        if ($date_to != '') {
            $sql .= ' AND o.date_add <= \'' . pSQL($date_to) . '\' ';
        }
        if ($date_from != '') {
            $sql .= ' AND o.date_add >= \'' . pSQL($date_from) . '\' ';
        }
        if ($id_country != 0) {
            $sql .= ' AND (ai.id_country = ' . $id_country . ' OR ad.id_country = ' . $id_country . ') ';
        }
        if (is_array($id_order_state) && count($id_order_state) > 0) {
            $sql .= ' AND o.current_state IN (' . implode(',', $id_order_state) . ') ';
        }
        $sql .= ' AND o.id_order IN (
            SELECT ocr.id_order
            FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr
            INNER JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cr.id_cart_rule = ocr.id_cart_rule
            WHERE '
                . ($id_cart_rule ? 'cr.id_cart_rule = ' . $id_cart_rule : '')
                . ($code_cart_rule ? 'cr.code = \'' . pSQL($code_cart_rule) . '\'' : '')
        . ')';
        $sql .= Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o');
        $sql .= ' ORDER BY o.date_add ASC ';
        $order_list = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (count($order_list) == 0) {
            $this->html = '<p class="alert alert-danger">' . $this->l('No order is matching your query') . '</p>';
            return;
        }

        // construct CSV
        $fields = array(
            'order_reference',
            'order_date',
            'total_paid_tax_incl',
            'total_paid_tax_excl',
            'total_shipping_tax_incl',
            'total_shipping_tax_excl',
            'order_state',
            'invoice_number',
            'invoice_date',
            'email',
            'invoice_company',
            'invoice_firstname',
            'invoice_lastname',
            'invoice_address1',
            'invoice_address2',
            'invoice_postcode',
            'invoice_city',
            'invoice_country',
            'delivery_company',
            'delivery_firstname',
            'delivery_lastname',
            'delivery_address1',
            'delivery_address2',
            'delivery_postcode',
            'delivery_city',
            'delivery_country',
        );
        $fh = fopen('php://temp', 'rw');
        fputcsv($fh, $fields);
        foreach ($order_list as $row) {
            $data = array();
            foreach ($fields as $field) {
                switch ($field) {
                    case 'invoice_number':
                        $invoice = new OrderInvoice($row['id_order_invoice'], $this->context->language->id);
                        $data[$field] = $invoice->getInvoiceNumberFormatted($this->context->language->id, $row['id_shop']);
                        break;
                    default:
                        $data[$field] = $row[$field];
                        break;
                }
            }
            fputcsv($fh, $data);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        header('Content-Type: text/csv');
        header('Content-disposition: attachment; filename=cart-rules-usage.csv');
        die($csv);
    }
}
