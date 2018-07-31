<?php

class WOO_Options
{
    public function __construct()
    {
        add_filter('wpcfm_configuration_items', array($this, 'get_configuration_items'));
    }

    public function get_configuration_items($items)
    {
        global $wpdb;

        $query = "
        SELECT option_name, option_value FROM $wpdb->options
        WHERE option_name NOT LIKE '_transient%' AND option_name NOT LIKE '_site_transient%'
        AND option_name LIKE 'woocommerce_%'
        ORDER BY option_name";

        $results = $wpdb->get_results($query);
        foreach ($results as $op) {
            $items['woo/' . $op->option_name] = array(
                'value' => "$op->option_value",
                'label' => "$op->option_name",
                'group' => 'WooCommerce Options',
            );
        }
        return $items;

    }

    /**
     * Tell WP-CFM to use import_terms() for taxonomy items
     */
    public function pull_callback($callback, $callback_params)
    {
        if ('woo/' == substr($callback_params['name'], 0, 3)) {
            return array($this, 'import_terms');
        }
        return $callback;
    }

    /**
     * Import (overwrite) taxonomies into the DB
     * @param string $params['name']
     * @param string $params['group']
     * @param string $params['old_value'] The old settings (DB)
     * @param string $params['new_value'] The new settings (file)
     */
    public function import_terms($params)
    {
        update_option($params["name"], $params["new_value"]);
    }
}

new WOO_Options;
