<?php

/**
 * Class WooCommerce_Carrier
 */
class WooCommerce_Carrier
{
    /**
     * @var array $supported_methods.
     */
    private $supported_methods = ['flat_rate', 'free_shipping'];

    /**
     *  Run the plugin.
     */
    public function run()
    {
        foreach ($this->supported_methods as $method) {
            add_filter('woocommerce_shipping_instance_form_fields_'.$method, [$this, 'fields']);
        }
        add_action('woocommerce_order_status_changed', [$this, 'woocommerce_carrier_order_status_changed'], 10, 3);
    }

    /**
     * New fields in shipping form.
     *
     * @param $fields
     * @return mixed
     */
    public function fields($fields)
    {
        $fields['carrier_id'] = [
            'title' => __('Carrier ID', 'woocommerce'),
            'type' => 'text',
            'description' => __('Carrier ID!', 'woocommerce'),
            'default' => '0',
            'desc_tip' => true,
        ];

        return $fields;
    }

    /**
     * Send a Tracks event when an order status is changed.
     *
     * @param int $id Order id.
     * @param string $previous_status the old WooCommerce order status.
     * @param string $next_status the new WooCommerce order status.
     * @throws \Exception
     */

    public function woocommerce_carrier_order_status_changed($id, $previous_status, $next_status)
    {
        switch ($next_status) {
            case 'processing':
                {
                    $order = wc_get_order($id);
                    $order_shipping_methods = array_values($order->get_shipping_methods());
                    if (count($order_shipping_methods)) {
                        $shipping = $order_shipping_methods[0];
                        $data = new stdClass();
                        $data->shipping_id = $shipping->get_id();
                        $data->method_id = $shipping->get_method_id();

                        $split = explode(':', $data->method_id);
                        $data->method_name = $split[0];
                        $data->method_instance = $split[1];

                        if (in_array($data->method_name, $this->supported_methods)) {

                            $data->method_options_id = 'woocommerce_'.$data->method_name.'_'.$data->method_instance.'_settings';
                            $data->options = get_option($data->method_options_id);
                            if (! is_array($data->options)) {
                                $data->options = [];
                            }

                            if (isset($data->options['carrier_id'])) {
                                wc_update_order_item_meta($data->shipping_id, 'carrier_id', $data->options['carrier_id']);
                            }
                        }
                    }
                    break;
                }
            default:
                break;
        }
    }
}
