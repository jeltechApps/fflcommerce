<?php
/**
 * Order Data Save
 *
 * Function for processing and storing all order data.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Admin
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Inc. & Jigoshop.
 * @license             GNU General Public License v3
 * 
 */
add_action('fflcommerce_process_shop_order_meta', 'fflcommerce_process_shop_order_meta', 1, 1);

function fflcommerce_process_shop_order_meta($post_id)
{
	$fflcommerce_options = FFLCommerce_Base::get_options();
	$fflcommerce_errors = array();
	$order = new fflcommerce_order($post_id);

	// Get old data + attributes
	$data = (array)maybe_unserialize(get_post_meta($post_id, 'order_data', true));

	//Get old order items
	$old_order_items = (array)maybe_unserialize(get_post_meta($post_id, 'order_items', true));

	// Add/Replace data to array
	$customerDetails = array(
		'billing_first_name',
		'billing_last_name',
		'billing_company',
		'billing_address_1',
		'billing_address_2',
		'billing_city',
		'billing_postcode',
		'billing_country',
		'billing_state',
		'billing_email',
		'billing_phone',
		'shipping_first_name',
		'shipping_last_name',
		'shipping_company',
		'shipping_address_1',
		'shipping_address_2',
		'shipping_city',
		'shipping_postcode',
		'shipping_country',
		'shipping_state'
	);

	$order_fields = array(
		'shipping_method',
		'shipping_service',
		'payment_method',
		'order_subtotal',
		'order_discount_subtotal',
		'order_shipping',
		'order_discount',
		'order_discount_coupons',
		'order_tax_total', // value from order totals for tax
		'order_shipping_tax',
		'order_total',
		'order_total_prices_per_tax_class_ex_tax'
	);

	/* Pre-fill the customer addresses */
	foreach ($customerDetails as $key){
		$order_fields[] = $key;

		/* Checks if this is a new order from "Add Order" button */
		if (!empty($_POST['auto_draft']) && !empty($_POST['customer_user']) && empty($_POST[$key])){
			$data[$key] = get_user_meta($_POST['customer_user'], $key, true);
		}
	}

	//run stripslashes on all valid fields
	foreach ($order_fields as $field_name) {
		if (isset($_POST[$field_name])) {
			$data[$field_name] = stripslashes($_POST[$field_name]);
		}
	}

	// Sanitize numeric values
	$data['order_total'] = fflcommerce_sanitize_num($data['order_total']);
	$data['order_subtotal'] = fflcommerce_sanitize_num($data['order_subtotal']);

	// if a shipping or payment methods has changed, update the method title for pretty display
	if (isset($_POST['shipping_method'])) {
		$data['shipping_service'] = '';
		$shipping_methods = fflcommerce_shipping::get_all_methods();
		if (!empty($shipping_methods)) {
			foreach ($shipping_methods as $method) {
				if ($_POST['shipping_method'] == $method->id) {
					$data['shipping_service'] = $method->title;
				}
			}
		}
	}
	if (isset($_POST['payment_method'])) {
		$data['payment_method_title'] = '';
		$payment_methods = fflcommerce_payment_gateways::get_available_payment_gateways();
		if (!empty($payment_methods)) {
			foreach ($payment_methods as $method) {
				if ($_POST['payment_method'] == $method->id) {
					$data['payment_method_title'] = $method->title;
				}
			}
		}
	}

	// if total tax has been modified from order tax, then create a customized tax array
	// just for the order. At this point, we no longer know about multiple tax classes.
	// Even if we used the old tax array data, we still don't know how to break down
	// the amounts since they're customized.
	if (isset($data['order_tax_total']) && $order->get_total_tax() != $data['order_tax_total']) {
		$new_tax = $data['order_tax_total'];
		$data['order_tax'] = fflcommerce_tax::create_custom_tax($data['order_total'] - $data['order_tax_total'], $data['order_tax_total'], $data['order_shipping_tax'], isset($data['order_tax_divisor']) ? $data['order_tax_divisor'] : null);
	}

	// Customer
	update_post_meta($post_id, 'customer_user', (int)$_POST['customer_user']);

	// Order items
	$order_items = array();

	if (isset($_POST['item_id'])) {
		$item_id = $_POST['item_id'];
		$item_variation = $_POST['item_variation_id'];
		$item_name = $_POST['item_name'];
		$item_quantity = $_POST['item_quantity'];
		$item_cost = $_POST['item_cost'];
		$item_tax_rate = $_POST['item_tax_rate'];

		for ($i = 0; $i < count($item_id); $i++) {
			if (!isset($item_id[$i]) || !isset($item_name[$i]) || !isset($item_quantity[$i]) || !isset($item_cost[$i]) || !isset($item_tax_rate[$i])) {
				continue;
			}

			$variation_id = '';
			$variation = '';
			if (!empty($item_variation[$i])) {
				$variation_id = (int)$item_variation[$i];

				// if this is a variation, we should check if it is an old one
				// and copy the 'variation' field describing details of variation
				foreach ($old_order_items as $old_item_index => $old_item) {
					if ($old_item['variation_id'] == $variation_id) {
						$variation = $old_item['variation'];
						unset($old_order_items[$old_item_index]);
						break;
					}
				}

				// override variation with values from $_POST
				if (isset($_POST['order_attributes'][$i]) && is_array($_POST['order_attributes'][$i])) {
					foreach ($_POST['order_attributes'][$i] as $var_key => $var_value) {
						$variation[$var_key] = $var_value;
					}
				}
			}

			$cost_inc_tax = $fflcommerce_options->get('fflcommerce_prices_include_tax') == 'yes' ? number_format((float)fflcommerce_clean($item_cost[$i]), 2, '.', '') : -1;
			$order_items[] = apply_filters('update_order_item', array(
				'id' => htmlspecialchars(stripslashes($item_id[$i])),
				'variation_id' => $variation_id,
				'variation' => $variation,
				'name' => htmlspecialchars(stripslashes($item_name[$i])),
				'qty' => (int)$item_quantity[$i],
				'cost' => number_format((float)fflcommerce_clean($item_cost[$i]), 2, '.', ''),
				'cost_inc_tax' => $cost_inc_tax,
				'taxrate' => number_format((float)fflcommerce_clean($item_tax_rate[$i]), 4, '.', '')
			));
		}
	}

	// Save
	update_post_meta($post_id, 'order_data', $data);
	update_post_meta($post_id, 'order_items', $order_items);

	// Order status
	$order->update_status($_POST['order_status']);

	// Handle button actions
	if (isset($_POST['reduce_stock']) && $_POST['reduce_stock'] && count($order_items) > 0) {
		$order->add_order_note(__('Manually reducing stock.', 'fflcommerce'));

		foreach ($order_items as $order_item) {
			$_product = $order->get_product_from_item($order_item);

			if ($_product->exists) {
				if ($_product->managing_stock()) {
					$old_stock = $_product->stock;
					$new_quantity = $_product->reduce_stock($order_item['qty']);
					$order->add_order_note(sprintf(__('Item #%s stock reduced from %s to %s.', 'fflcommerce'), $order_item['id'], $old_stock, $new_quantity));

					if ($new_quantity < 0) {
						if ($old_stock < 0) {
							$backorder_qty = $order_item['qty'];
						} else {
							$backorder_qty = $old_stock - $order_item['qty'];
						}

						do_action('fflcommerce_product_on_backorder_notification', $post_id, $_product, $backorder_qty);
					}

					// stock status notifications
					if ($fflcommerce_options->get('fflcommerce_notify_no_stock') == 'yes' && $fflcommerce_options->get('fflcommerce_notify_no_stock_amount') >= 0 && $fflcommerce_options->get('fflcommerce_notify_no_stock_amount') >= $new_quantity) {
						do_action('fflcommerce_no_stock_notification', $_product);
					} else if ($fflcommerce_options->get('fflcommerce_notify_low_stock') == 'yes' && $fflcommerce_options->get('fflcommerce_notify_low_stock_amount') >= $new_quantity) {
						do_action('fflcommerce_low_stock_notification', $_product);
					}
				}
			} else {
				$order->add_order_note(sprintf(__('Item %s %s not found, skipping.', 'fflcommerce'), $order_item['id'], $order_item['name']));
			}
		}

		$order->add_order_note(__('Manual stock reduction complete.', 'fflcommerce'));
	} else if (isset($_POST['restore_stock']) && $_POST['restore_stock'] && sizeof($order_items) > 0) {
		$order->add_order_note(__('Manually restoring stock.', 'fflcommerce'));

		foreach ($order_items as $order_item) {
			$_product = $order->get_product_from_item($order_item);

			if ($_product->exists) {
				if ($_product->managing_stock()) {
					$old_stock = $_product->stock;
					$new_quantity = $_product->increase_stock($order_item['qty']);
					$order->add_order_note(sprintf(__('Item #%s stock increased from %s to %s.', 'fflcommerce'), $order_item['id'], $old_stock, $new_quantity));
				}
			} else {
				$order->add_order_note(sprintf(__('Item %s %s not found, skipping.', 'fflcommerce'), $order_item['id'], $order_item['name']));
			}
		}

		$order->add_order_note(__('Manual stock restore complete.', 'fflcommerce'));
	} else if (isset($_POST['invoice']) && $_POST['invoice']) {
		// Mail link to customer
		fflcommerce_send_customer_invoice($order->id);
	}

	// Error Handling
	if (count($fflcommerce_errors) > 0) {
		$fflcommerce_options->set('fflcommerce_errors', $fflcommerce_errors);
	}
}
