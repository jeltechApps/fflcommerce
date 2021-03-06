<?php
/**
 * @var $available_methods array List of available shipping methods.
 */
?>

<?php $available_methods = fflcommerce_shipping::get_available_shipping_methods(); ?>
<tr>
	<td colspan="2"><?php _e('Shipping', 'fflcommerce'); ?><br />
		<small><?php echo _x('To: ', 'shipping destination', 'fflcommerce').__(fflcommerce_customer::get_shipping_country_or_state(), 'fflcommerce'); ?></small>
	</td>
	<td>
		<?php	if (count($available_methods) > 0):	?>
		<select name="shipping_method" id="shipping_method">
			<?php foreach ($available_methods as $method): /** @var fflcommerce_shipping_method $method */ ?>
				<?php for ($i = 0; $i < $method->get_rates_amount(); $i++):
					$service = $method->get_selected_service($i);
					$price = $method->get_selected_price($i);
					$is_taxed = fflcommerce_cart::$shipping_tax_total > 0;
					?>
					<option value="<?php echo esc_attr($method->id.':'.$service.':'.$i); ?>" <?php selected($method->is_rate_selected($i)); ?>>
						<?php echo $service; ?> &ndash; <?php echo $price > 0 ? fflcommerce_price($price, array('ex_tax_label' => (int)$is_taxed)) : __('Free', 'fflcommerce'); ?>
					</option>
				<?php endfor; ?>
			<?php endforeach; ?>
		</select>
		<?php else: ?>
			<p><?php echo __(fflcommerce_shipping::get_shipping_error_message(), 'fflcommerce'); ?></p>
		<?php endif; ?>
	</td>
</tr>
