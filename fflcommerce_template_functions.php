<?php
/**
 * Functions used in template files
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade FFL Commerce to newer
 * versions in the future. If you wish to customise FFL Commerce core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             FFLCommerce
 * @category            Core
 * @author              Tampa Bay Tactical Supply, Inc.
 * @copyright           Copyright © 2011-2014 Tampa Bay Tactical Supply, Ind. & Jigoshop.
 * @license             GNU General Public License v3
 */

/**
 * Front page archive/shop template
 */
if (!function_exists('fflcommerce_front_page_archive')) {
	function fflcommerce_front_page_archive()
	{
		global $paged;

		// TODO: broken
		// is_page() fails for this - still testing -JAP-
		// is_shop() works, but only with a [recent_products] shortcode on the Shop page
		// however, if shortcode is used when not front page, double product listings appear
		//
		if (is_front_page() && is_page(fflcommerce_get_page_id('shop'))) :
//		if ( is_front_page() && is_shop() ) :

			if (get_query_var('paged')) {
				$paged = get_query_var('paged');
			} else if (get_query_var('page')) {
				$paged = get_query_var('page');
			} else {
				$paged = 1;
			}

			query_posts(array('page_id' => '', 'post_type' => 'product', 'paged' => $paged));
			define('SHOP_IS_ON_FRONT', true);
		endif;
	}
}
add_action('wp_head', 'fflcommerce_front_page_archive', 0);

/**
 * Content Wrappers
 **/
if (!function_exists('fflcommerce_output_content_wrapper')) {
	function fflcommerce_output_content_wrapper()
	{
		if (get_theme_support('woocommerce') || wp_get_theme()->get('Author') === 'WooThemes') {
			echo '<div id="content" class="col-full woothemes-compatible"><div id="main" class="col-left"><div class="post">';
		} elseif (get_option('template') === 'twentyfourteen') {
			echo '<div id="primary" class="content-area"><div id="content" class="site-content" role="main">';
		} elseif (get_option('template') === 'twentythirteen') {
			echo '<div id="primary" class="content-area"><div id="content" class="site-content" role="main">';
		} elseif (get_option('template') === 'twentytwelve') {
			echo '<div id="primary" class="site-content"><div id="content" role="main">';
		} elseif (get_option('template') === 'twentyeleven') {
			echo '<section id="primary"><div id="content" role="main">';
		} else {
			/* twenty-ten */
			echo '<div id="container"><div id="content" role="main">';
		}
	}
}
if (!function_exists('fflcommerce_output_content_wrapper_end')) {
	function fflcommerce_output_content_wrapper_end()
	{
		if (get_theme_support('woocommerce') || wp_get_theme()->get('Author') === 'WooThemes') {
			echo '</div></div>';
		} elseif (get_option('template') === 'twentyfourteen') {
			echo '</div></div>';
		} elseif (get_option('template') === 'twentythirteen') {
			echo '</div></div>';
		} elseif (get_option('template') === 'twentytwelve') {
			echo '</div></div>';
		} elseif (get_option('template') === 'twentyeleven') {
			echo '</div></section>';
		} else {
			/* twenty-ten */
			echo '</div></div>';
		}
	}
}

/**
 * Sale Flash
 **/
if (!function_exists('fflcommerce_show_product_sale_flash')) {
	function fflcommerce_show_product_sale_flash($post, $_product)
	{
		if ($_product->is_on_sale()) {
			echo '<span class="onsale">'.__('Sale!', 'fflcommerce').'</span>';
		}
	}
}

/**
 * Sidebar
 **/
if (!function_exists('fflcommerce_get_sidebar')) {
	function fflcommerce_get_sidebar()
	{
		get_sidebar('shop');
	}
}
if (!function_exists('fflcommerce_get_sidebar_end')) {
	function fflcommerce_get_sidebar_end()
	{
		if (get_theme_support('woocommerce') || wp_get_theme()->get('Author') == 'WooThemes') {
			echo '</div>';
		}
	}
}

/**
 * Products Loop
 **/
if (!function_exists('fflcommerce_template_loop_add_to_cart')) {
	function fflcommerce_template_loop_add_to_cart($post, $_product)
	{
		do_action('fflcommerce_before_add_to_cart_button');

		// do not show "add to cart" button if product's price isn't announced
		if ($_product->get_price() === '' && !($_product->is_type(array('variable', 'grouped', 'external')))) {
			return;
		}

		if ($_product->is_in_stock() || $_product->is_type('external')) {
			$button_type = FFLCommerce_Base::get_options()->get('fflcommerce_catalog_product_button');
			if ($button_type === false) {
				$button_type = 'add';
			}

			if ($_product->is_type(array('variable', 'grouped'))) {
				if ($button_type != 'none') {
					if ($button_type == 'view') {
						$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'.__('View Product', 'fflcommerce').'</a>';
					} else {
						$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'._x('Select', 'verb', 'fflcommerce').'</a>';
					}
				} else {
					$output = '';
				}
			} else if ($_product->is_type('external')) {
				if ($button_type != 'none') {
					if ($button_type == 'view') {
						$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'.__('View Product', 'fflcommerce').'</a>';
					} else {
						$output = '<a href="'.esc_url(get_post_meta($_product->id, 'external_url', true)).'" class="button" rel="nofollow">'.__('Buy product', 'fflcommerce').'</a>';
					}
				} else {
					$output = '';
				}
			} else if ($button_type == 'add') {
				$output = '<a href="'.esc_url($_product->add_to_cart_url()).'" class="button" rel="nofollow">'.__('Add to cart', 'fflcommerce').'</a>';
			} else if ($button_type == 'view') {
				$output = '<a href="'.esc_url(get_permalink($_product->id)).'" class="button">'.__('View Product', 'fflcommerce').'</a>';
			} else {
				$output = '';
			}
		} else if (($_product->is_type(array('grouped')))) {
			$output = '';
		} else {
			$output = '<span class="nostock">'.__('Out of Stock', 'fflcommerce').'</span>';
		}

		echo apply_filters('fflcommerce_loop_add_to_cart_output', $output, $post, $_product);
		do_action('fflcommerce_after_add_to_cart_button');
	}
}
if (!function_exists('fflcommerce_template_loop_product_thumbnail')) {
	function fflcommerce_template_loop_product_thumbnail($post, $_product)
	{
		echo $_product->get_image('shop_small');
	}
}
if (!function_exists('fflcommerce_template_loop_price')) {
	function fflcommerce_template_loop_price($post, $_product)
	{
		?><span class="price"><?php echo $_product->get_price_html(); ?></span><?php
	}
}

if (!function_exists('fflcommerce_cart_has_post_thumbnail')) {
	/**
	 * Check if product in cart has an image attached. Applies `fflcommerce_cart_has_post_thumbnail` filter.
	 *
	 * @param string $cart_item_key Cart key
	 * @param int $post_id Optional. Post ID.
	 * @return bool Whether post has an image attached.
	 */
	function fflcommerce_cart_has_post_thumbnail($cart_item_key, $post_id)
	{
		return apply_filters('fflcommerce_cart_has_post_thumbnail', has_post_thumbnail($post_id), $cart_item_key, $post_id);
	}
}

if (!function_exists('fflcommerce_cart_get_post_thumbnail')) {
	/**
	 * Retrieve product in cart thumbnail. Applies `fflcommerce_cart_get_post_thumbnail` filter.
	 *
	 * @param string $cart_item_key
	 * @param int $post_id Optional. Post ID.
	 * @param string $size Optional. Image size. Defaults to 'post-thumbnail'.
	 * @param string|array $attr Optional. Query string or array of attributes.
	 * @return mixed|void
	 */
	function fflcommerce_cart_get_post_thumbnail($cart_item_key, $post_id, $size = 'post-thumbnail', $attr = '')
	{
		return apply_filters('fflcommerce_cart_get_post_thumbnail', get_the_post_thumbnail($post_id, $size, $attr), $cart_item_key, $post_id, $size, $attr);
	}
}

/**
 * Before Single Products Summary Div
 **/
if (!function_exists('fflcommerce_show_product_images')) {
	function fflcommerce_show_product_images()
	{
		global $_product, $post;
		echo '<div class="images">';
		do_action('fflcommerce_before_single_product_summary_thumbnails', $post, $_product);

		if (has_post_thumbnail()) :
			$thumb_id = get_post_thumbnail_id();
			$large_thumbnail_size = fflcommerce_get_image_size('shop_large');
			$image_classes = apply_filters('fflcommerce_product_image_classes', array(), $_product);
			array_unshift($image_classes, 'zoom');
			$image_classes = implode(' ', $image_classes);

			$args = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'numberposts' => -1,
				'post_status' => null,
				'post_parent' => $post->ID,
				'orderby' => 'menu_order',
				'order' => 'asc',
				'fields' => 'ids'
			);
			$attachments = get_posts($args);
			$attachment_count = count($attachments);
			if ($attachment_count > 1) {
				$gallery = '[product-gallery]';
			} else {
				$gallery = '';
			}

			echo '<a href="'.wp_get_attachment_url($thumb_id).'" class="'.$image_classes.'" rel="prettyPhoto'.$gallery.'">';
			the_post_thumbnail($large_thumbnail_size);
			echo '</a>';
		else :
			echo fflcommerce_get_image_placeholder('shop_large');
		endif;

		do_action('fflcommerce_product_thumbnails');
		echo '</div>';
	}
}
if (!function_exists('fflcommerce_show_product_thumbnails')) {
	function fflcommerce_show_product_thumbnails()
	{
		global $post;
		$options = \FFLCommerce_Base::get_options();
		echo '<div class="thumbnails" style="width: '.($options->get('fflcommerce_product_thumbnail_columns') * (15 + $options->get('fflcommerce_shop_thumbnail_w'))).'px;">';

		$thumb_id = get_post_thumbnail_id();
		$small_thumbnail_size = fflcommerce_get_image_size('shop_thumbnail');

		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $post->ID,
			'orderby' => 'menu_order',
			'order' => 'asc',
			'fields' => 'ids'
		);

		$attachments = get_posts($args);

		if ($attachments) :

			$loop = 0;
			$attachment_count = count($attachments);
			if ($attachment_count > 1) {
				$gallery = '[product-gallery]';
			} else {
				$gallery = '';
			}

			$columns = FFLCommerce_Base::get_options()->get('fflcommerce_product_thumbnail_columns', '3');
			$columns = apply_filters('single_thumbnail_columns', $columns);

			foreach ($attachments as $attachment_id) :

				if ($thumb_id == $attachment_id) {
					continue;
				} /* ignore the large featured image */

				$loop++;

				$_post = get_post($attachment_id);
				$url = wp_get_attachment_url($_post->ID);
				$post_title = esc_attr($_post->post_title);
				$image = wp_get_attachment_image($attachment_id, $small_thumbnail_size);

				if (!$image || $url == get_post_meta($post->ID, 'file_path', true)) {
					continue;
				}

				echo '<a rel="prettyPhoto'.$gallery.'" href="'.esc_url($url).'" title="'.esc_attr($post_title).'" class="zoom ';
				if ($loop == 1 || ($loop - 1) % $columns == 0) {
					echo 'first';
				}
				if ($loop % $columns == 0) {
					echo 'last';
				}
				echo '">'.$image.'</a>';

			endforeach;
		endif;
		wp_reset_query();

		echo '</div>';
	}
}

/**
 * After Single Products Summary Div
 **/
if (!function_exists('fflcommerce_output_product_data_tabs')) {
	function fflcommerce_output_product_data_tabs()
	{
		if (isset($_COOKIE["current_tab"])) {
			$current_tab = $_COOKIE["current_tab"];
		} else {
			$current_tab = '#tab-description';
		}
		?>
		<div id="tabs">
			<ul class="tabs">
				<?php do_action('fflcommerce_product_tabs', $current_tab); ?>
			</ul>
			<?php do_action('fflcommerce_product_tab_panels'); ?>
		</div>
	<?php
	}
}

/**
 * Product summary box
 **/
if (!function_exists('fflcommerce_template_single_title')) {
	function fflcommerce_template_single_title($post, $_product)
	{
		?><h1 class="product_title page-title"><?php echo apply_filters('fflcommerce_single_product_title', the_title('', '', false)); ?></h1><?php
	}
}

if (!function_exists('fflcommerce_template_single_price')) {
	function fflcommerce_template_single_price($post, $_product)
	{
		?><p class="price"><?php echo apply_filters('fflcommerce_single_product_price', $_product->get_price_html()); ?></p><?php
	}
}

if (!function_exists('fflcommerce_template_single_excerpt')) {
	function fflcommerce_template_single_excerpt($post, $_product)
	{
		if ($post->post_excerpt) {
			echo apply_filters('fflcommerce_single_product_excerpt', wpautop(wptexturize($post->post_excerpt)));
		}
	}
}

if (!function_exists('fflcommerce_template_single_meta')) {
	function fflcommerce_template_single_meta($post, $_product)
	{
		$options = FFLCommerce_Base::get_options();
		echo '<div class="product_meta">';
		if ($options->get('fflcommerce_enable_sku') == 'yes' && !empty($_product->sku)) :
			echo '<div class="sku">'.__('SKU', 'fflcommerce').': '.$_product->sku.'</div>';
		endif;
		if ($options->get('fflcommerce_enable_brand') == 'yes' && !empty($_product->brand)) :
			echo '<div class="brand">'.__('Brand', 'fflcommerce').': '.$_product->brand.'</div>';
		endif;
		if ($options->get('fflcommerce_enable_gtin') == 'yes' && !empty($_product->gtin)) :
			echo '<div class="gtin">'.__('GTIN', 'fflcommerce').': '.$_product->gtin.'</div>';
		endif;
		if ($options->get('fflcommerce_enable_mpn') == 'yes' && !empty($_product->mpn)) :
			echo '<div class="mpn">'.__('MPN', 'fflcommerce').': '.$_product->mpn.'</div>';
		endif;
		echo $_product->get_categories(', ', ' <div class="posted_in">'.__('Posted in ', 'fflcommerce').'', '.</div>');
		echo $_product->get_tags(', ', ' <div class="tagged_as">'.__('Tagged as ', 'fflcommerce').'', '.</div>');
		echo '</div>';

	}
}

if (!function_exists('fflcommerce_template_single_sharing')) {
	function fflcommerce_template_single_sharing($post, $_product)
	{
		$fflcommerce_options = FFLCommerce_Base::get_options();
		if ($fflcommerce_options->get('fflcommerce_sharethis')) :
			echo '<div class="social">
				<iframe src="https://www.facebook.com/plugins/like.php?href='.urlencode(get_permalink($post->ID)).'&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
				<span class="st_twitter"></span><span class="st_email"></span><span class="st_sharethis"></span><span class="st_plusone_button"></span>
			</div>';
		endif;
	}
}

/**
 * Product Add to cart buttons
 **/
if (!function_exists('fflcommerce_template_single_add_to_cart')) {
	function fflcommerce_template_single_add_to_cart($post, $_product)
	{
		$availability = $_product->get_availability();
		if ($availability != '') {
		?>
			<p class="stock <?php echo $availability['class'] ?>"><?php echo $availability['availability']; ?></p>
		<?php
		}
		if ($_product->is_in_stock()) {
			do_action($_product->product_type.'_add_to_cart');
		}

	}
}
if (!function_exists('fflcommerce_simple_add_to_cart')) {
	function fflcommerce_simple_add_to_cart()
	{
		global $_product;

		// do not show "add to cart" button if product's price isn't announced
		if ($_product->get_price() === '') {
			return;
		}
		?>
		<form action="<?php echo esc_url($_product->add_to_cart_url()); ?>" class="cart" method="post">
			<?php do_action('fflcommerce_before_add_to_cart_form_button'); ?>
			<div class="quantity"><input name="quantity" value="1" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>
			<button type="submit" class="button-alt"><?php _e('Add to cart', 'fflcommerce'); ?></button>
			<?php do_action('fflcommerce_add_to_cart_form'); ?>
		</form>
	<?php
	}
}
if (!function_exists('fflcommerce_virtual_add_to_cart')) {
	function fflcommerce_virtual_add_to_cart()
	{
		fflcommerce_simple_add_to_cart();
	}
}
if (!function_exists('fflcommerce_downloadable_add_to_cart')) {
	function fflcommerce_downloadable_add_to_cart()
	{
		global $_product;

		// do not show "add to cart" button if product's price isn't announced
		if ($_product->get_price() === '') {
			return;
		}
		?>
		<form action="<?php echo esc_url($_product->add_to_cart_url()); ?>" class="cart" method="post">
			<?php do_action('fflcommerce_before_add_to_cart_form_button'); ?>
			<button type="submit" class="button-alt"><?php _e('Add to cart', 'fflcommerce'); ?></button>
			<?php do_action('fflcommerce_add_to_cart_form'); ?>
		</form>
	<?php
	}
}
if (!function_exists('fflcommerce_grouped_add_to_cart')) {
	function fflcommerce_grouped_add_to_cart()
	{
		global $_product;
		if (!$_product->get_children()) {
			return;
		}
		?>
		<form action="<?php echo esc_url($_product->add_to_cart_url()); ?>" class="cart" method="post">
			<table>
				<tbody>
				<?php foreach ($_product->get_children() as $child_ID) : $child = $_product->get_child($child_ID);
					$cavailability = $child->get_availability(); ?>
					<tr>
						<td>
							<div class="quantity"><input name="quantity[<?php echo $child->ID; ?>]" value="0" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>
						</td>
						<td><label for="product-<?php echo $child->id; ?>"><?php
								if ($child->is_visible()) {
									echo '<a href="'.get_permalink($child->ID).'">';
								}
								echo $child->get_title();
								if ($child->is_visible()) {
									echo '</a>';
								}
								?></label></td>
						<td class="price"><?php echo $child->get_price_html(); ?>
							<small class="stock <?php echo $cavailability['class'] ?>"><?php echo $cavailability['availability']; ?></small>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php do_action('fflcommerce_before_add_to_cart_form_button'); ?>
			<button type="submit" class="button-alt"><?php _e('Add to cart', 'fflcommerce'); ?></button>
			<?php do_action('fflcommerce_add_to_cart_form'); ?>
		</form>
	<?php
	}
}
if (!function_exists('fflcommerce_variable_add_to_cart')) {
	function fflcommerce_variable_add_to_cart()
	{
		global $post, $_product;
		$fflcommerce_options = FFLCommerce_Base::get_options();
		$attributes = $_product->get_available_attributes_variations();

		//get all variations available as an array for easy usage by javascript
		$variationsAvailable = array();
		$children = $_product->get_children();

		foreach ($children as $child) {
			/* @var $variation fflcommerce_product_variation */
			$variation = $_product->get_child($child);
			if ($variation instanceof fflcommerce_product_variation) {
				$vattrs = $variation->get_variation_attributes();
				$availability = $variation->get_availability();

				//@todo needs to be moved to fflcommerce_product_variation class
				if (has_post_thumbnail($variation->get_variation_id())) {
					$attachment_id = get_post_thumbnail_id($variation->get_variation_id());
					$large_thumbnail_size = apply_filters('single_product_large_thumbnail_size', 'shop_large');
					$image = wp_get_attachment_image_src($attachment_id, $large_thumbnail_size);
					if (!empty($image)) {
						$image = $image[0];
					}
					$image_link = wp_get_attachment_image_src($attachment_id, 'full');
					if (!empty($image_link)) {
						$image_link = $image_link[0];
					}
				} else {
					$image = '';
					$image_link = '';
				}

				$a_weight = $a_length = $a_width = $a_height = '';

				if ($variation->get_weight()) {
					$a_weight = '
            <tr class="weight">
              <th>Weight</th>
              <td>'.$variation->get_weight().$fflcommerce_options->get('fflcommerce_weight_unit').'</td>
            </tr>';
				}

				if ($variation->get_length()) {
					$a_length = '
            <tr class="length">
              <th>Length</th>
              <td>'.$variation->get_length().$fflcommerce_options->get('fflcommerce_dimension_unit').'</td>
            </tr>';
				}

				if ($variation->get_width()) {
					$a_width = '
            <tr class="width">
              <th>Width</th>
              <td>'.$variation->get_width().$fflcommerce_options->get('fflcommerce_dimension_unit').'</td>
            </tr>';
				}

				if ($variation->get_height()) {
					$a_height = '
            <tr class="height">
              <th>Height</th>
              <td>'.$variation->get_height().$fflcommerce_options->get('fflcommerce_dimension_unit').'</td>
            </tr>';
				}

				$variationsAvailable[] = array(
					'variation_id' => $variation->get_variation_id(),
					'sku' => '<div class="sku">'.__('SKU', 'fflcommerce').': '.$variation->get_sku().'</div>',
					'attributes' => $vattrs,
					'in_stock' => $variation->is_in_stock(),
					'image_src' => $image,
					'image_link' => $image_link,
					'price_html' => '<span class="price">'.$variation->get_price_html().'</span>',
					'availability_html' => '<p class="stock '.esc_attr($availability['class']).'">'.$availability['availability'].'</p>',
					'a_weight' => $a_weight,
					'a_length' => $a_length,
					'a_width' => $a_width,
					'a_height' => $a_height,
					'same_prices' => $_product->variations_priced_the_same(),
					'no_price' => $variation->get_price() == null ? true : false,
				);
			}
		}
		?>
		<script type="text/javascript">
			var product_variations = <?php echo json_encode($variationsAvailable) ?>;
		</script>
		<?php $default_attributes = $_product->get_default_attributes() ?>
		<form action="<?php echo esc_url($_product->add_to_cart_url()); ?>" class="variations_form cart"
		      method="post">
			<fieldset class="variations">
				<?php foreach ($attributes as $name => $options): ?>
					<?php $sanitized_name = sanitize_title($name); ?>
					<div>
						<span class="select_label"><?php echo $_product->attribute_label('pa_'.$name); ?></span>
						<select id="<?php echo esc_attr($sanitized_name); ?>" name="tax_<?php echo $sanitized_name; ?>">
							<option value=""><?php echo __('Choose an option ', 'fflcommerce') ?>&hellip;</option>

							<?php if (empty($_POST)): ?>
								<?php $selected_value = (isset($default_attributes[$sanitized_name])) ? $default_attributes[$sanitized_name] : ''; ?>
							<?php else: ?>
								<?php $selected_value = isset($_POST['tax_'.$sanitized_name]) ? $_POST['tax_'.$sanitized_name] : ''; ?>
							<?php endif; ?>

							<?php foreach ($options as $value) : ?>
								<?php if (taxonomy_exists('pa_'.$sanitized_name)) : ?>
									<?php $term = get_term_by('slug', $value, 'pa_'.$sanitized_name); ?>
									<option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selected_value, $term->slug) ?>><?php echo $term->name; ?></option>
								<?php else :
									$display_value = apply_filters('fflcommerce_product_attribute_value_custom', esc_attr(sanitize_text_field($value)), $sanitized_name);
									?>
									<option value="<?php echo $value; ?>"<?php selected($selected_value, $value) ?> ><?php echo $display_value; ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endforeach; ?>
			</fieldset>
			<div class="single_variation"></div>
			<?php do_action('fflcommerce_before_add_to_cart_form_button'); ?>
			<div class="variations_button" style="display:none;">
				<input type="hidden" name="variation_id" value="" />
				<input type="hidden" name="product_id" value="<?php echo esc_attr($post->ID); ?>" />
				<div class="quantity"><input name="quantity" value="1" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div>
				<input type="submit" class="button-alt" value="<?php esc_html_e('Add to cart', 'fflcommerce'); ?>" />
			</div>
			<?php do_action('fflcommerce_add_to_cart_form'); ?>
		</form>
	<?php
	}
}

if (!function_exists('fflcommerce_external_add_to_cart')) {
	function fflcommerce_external_add_to_cart()
	{
		global $_product;
		$external_url = get_post_meta($_product->ID, 'external_url', true);

		if (!$external_url) {
			return false;
		}
		?>
		<form action="#" class="cart" method="post">
			<?php do_action('fflcommerce_before_add_to_cart_form_button'); ?>
			<p>
				<a href="<?php echo esc_url($external_url); ?>" rel="nofollow" class="button"><?php _e('Buy product', 'fflcommerce'); ?></a>
			</p>
			<?php do_action('fflcommerce_add_to_cart_form'); ?>
		</form>
	<?php
	}
}

/**
 * Product Add to Cart forms
 **/
if (!function_exists('fflcommerce_add_to_cart_form_nonce')) {
	function fflcommerce_add_to_cart_form_nonce()
	{
		fflcommerce::nonce_field('add_to_cart');
	}
}

/**
 * Pagination
 **/
if (!function_exists('fflcommerce_pagination')) {
	function fflcommerce_pagination()
	{
		global $wp_query;

		if ($wp_query->max_num_pages > 1) :
			?>
			<div class="navigation">
				<?php if (function_exists('wp_pagenavi')) : ?>
					<?php wp_pagenavi(); ?>
				<?php else : ?>
					<div class="nav-next"><?php next_posts_link(__('Next <span class="meta-nav">&rarr;</span>', 'fflcommerce')); ?></div>
					<div class="nav-previous"><?php previous_posts_link(__('<span class="meta-nav">&larr;</span> Previous', 'fflcommerce')); ?></div>
				<?php endif; ?>
			</div>
		<?php
		endif;
	}
}

/**
 * Product page tabs
 **/
if (!function_exists('fflcommerce_product_description_tab')) {
	function fflcommerce_product_description_tab($current_tab)
	{
		global $post;
		if (!$post->post_content) {
			return false;
		}
		?>
		<li <?php if ($current_tab == '#tab-description') {
			echo 'class="active"';
		} ?>><a href="#tab-description"><?php _e('Description', 'fflcommerce'); ?></a></li>
	<?php
	}
}
if (!function_exists('fflcommerce_product_attributes_tab')) {
	function fflcommerce_product_attributes_tab($current_tab)
	{
		global $_product;
		if (($_product->has_attributes() || $_product->has_dimensions() || $_product->has_weight())):
			?>
		<li <?php if ($current_tab == '#tab-attributes') {
			echo 'class="active"';
		} ?>><a href="#tab-attributes"><?php _e('Additional Information', 'fflcommerce'); ?></a>
			</li>
		<?php endif;
	}
}
if (!function_exists('fflcommerce_product_reviews_tab')) {
	function fflcommerce_product_reviews_tab($current_tab)
	{
		if (comments_open()) : ?>
			<li <?php if ($current_tab == '#tab-reviews') {
				echo 'class="active"';
			} ?>><a
				href="#tab-reviews"><?php _e('Reviews', 'fflcommerce'); ?><?php echo comments_number(' (0)', ' (1)', ' (%)'); ?></a>
			</li>
		<?php endif;
	}
}
if (!function_exists('fflcommerce_product_customize_tab')) {
	function fflcommerce_product_customize_tab($current_tab)
	{
		global $_product;

		if (get_post_meta($_product->ID, 'customizable', true) == 'yes') {
			?>
			<li <?php if ($current_tab == '#tab-customize') {
				echo 'class="active"';
			} ?>><a href="#tab-customize"><?php _e('Personalize', 'fflcommerce'); ?></a></li>
		<?php
		}
	}
}

/**
 * Product page tab panels
 **/
if (!function_exists('fflcommerce_product_description_panel')) {
	function fflcommerce_product_description_panel()
	{
		$content = get_the_content();
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		$content = apply_filters('fflcommerce_single_product_content', $content);
		if ($content <> '') {
			echo '<div class="panel" id="tab-description">';
			$heading = apply_filters('fflcommerce_product_description_heading', '');
			if (!empty($heading)) {
				echo '<h2>'.$heading.'</h2>';
			}
			echo $content;
			echo '</div>';
		}
	}
}
if (!function_exists('fflcommerce_product_attributes_panel')) {
	function fflcommerce_product_attributes_panel()
	{
		global $_product;
		$content = apply_filters('fflcommerce_single_product_attributes', $_product->list_attributes());
		if ($content <> '') {
			echo '<div class="panel" id="tab-attributes">';
			$heading = apply_filters('fflcommerce_product_attributes_heading', '');
			if (!empty($heading)) {
				echo '<h2>'.$heading.'</h2>';
			}
			echo $content;
			echo '</div>';
		}
	}
}
if (!function_exists('fflcommerce_product_reviews_panel')) {
	function fflcommerce_product_reviews_panel()
	{
		echo '<div class="panel" id="tab-reviews">';
		comments_template();
		echo '</div>';
	}
}
if (!function_exists('fflcommerce_product_customize_panel')) {
	function fflcommerce_product_customize_panel()
	{
		global $_product;

		if (isset($_POST['Submit']) && $_POST['Submit'] == __('Save Personalization', 'fflcommerce')) {
			$custom_products = (array)fflcommerce_session::instance()->customized_products;
			$custom_products[$_POST['customized_id']] = sanitize_text_field(stripslashes($_POST['fflcommerce_customized_product']));
			fflcommerce_session::instance()->customized_products = $custom_products;
		}

		if (get_post_meta($_product->ID, 'customizable', true) == 'yes') :
			$custom_products = (array)fflcommerce_session::instance()->customized_products;
			$custom = isset($custom_products[$_product->ID]) ? $custom_products[$_product->ID] : '';
			$custom_length = get_post_meta($_product->ID, 'customized_length', true);
			$length_str = $custom_length == '' ? '' : sprintf(__('You may enter a maximum of %s characters.', 'fflcommerce'), $custom_length);

			echo '<div class="panel" id="tab-customize">';
			echo '<p>'.apply_filters('fflcommerce_product_customize_heading', __('Enter your personal information as you want it to appear on the product.<br />', 'fflcommerce').$length_str).'</p>';
			?>
			<form action="#" method="post">
				<input type="hidden" name="customized_id" value="<?php echo esc_attr($_product->ID); ?>" />
				<?php
				if ($custom_length == '') :
					?>
					<textarea id="fflcommerce_customized_product" name="fflcommerce_customized_product" cols="60" rows="4"><?php echo esc_textarea($custom); ?></textarea>
				<?php else : ?>
					<input type="text" id="fflcommerce_customized_product" name="fflcommerce_customized_product" size="<?php echo $custom_length; ?>" maxlength="<?php echo $custom_length; ?>" value="<?php echo esc_attr($custom); ?>" />
				<?php endif; ?>
				<p class="submit"><input name="Submit" type="submit" class="button-alt add_personalization" value="<?php _e("Save Personalization", 'fflcommerce'); ?>" /></p>
			</form>
			<?php
			echo '</div>';
		endif;
	}
}


/**
 * FFL Commerce Product Thumbnail
 **/
if (!function_exists('fflcommerce_get_product_thumbnail')) {
	function fflcommerce_get_product_thumbnail($size = 'shop_small')
	{
		global $post;

		if (has_post_thumbnail()) {
			return get_the_post_thumbnail($post->ID, $size);
		} else {
			return fflcommerce_get_image_placeholder($size);
		}
	}
}


/**
 * fflcommerce Product Category Image
 **/
if (!function_exists('fflcommerce_product_cat_image')) {
	function fflcommerce_product_cat_image($id)
	{
		if (empty($id)) {
			return false;
		}

		$thumbnail_id = get_metadata('fflcommerce_term', $id, 'thumbnail_id', true);
		$category_image = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : FFLCOMMERCE_URL.'/assets/images/placeholder.png';

		return array('image' => $category_image, 'thumb_id' => $thumbnail_id);
	}
}


/**
 * FFL Commerce Product Image Placeholder
 *
 * @since 0.9.9
 **/
if (!function_exists('fflcommerce_get_image_placeholder')) {
	function fflcommerce_get_image_placeholder($size = 'shop_small')
	{
		$image_size = fflcommerce_get_image_size($size);

		return apply_filters('fflcommerce_image_placeholder_html', '<img src="'.FFLCOMMERCE_URL.'/assets/images/placeholder.png" alt="Placeholder" width="'.$image_size[0].'" height="'.$image_size[1].'" />', $image_size);
	}
}

/**
 * FFL Commerce Related Products
 **/
if (!function_exists('fflcommerce_output_related_products')) {
	function fflcommerce_output_related_products()
	{
		$options = FFLCommerce_Base::get_options();
		if ($options->get('fflcommerce_enable_related_products') != 'no') // 2 Related Products in 2 columns
		{
			fflcommerce_related_products(2, 2);
		}
	}
}

if (!function_exists('fflcommerce_related_products')) {
	function fflcommerce_related_products($posts_per_page = 4, $post_columns = 4, $orderby = 'rand')
	{
		/** @var $_product fflcommerce_product */
		global $_product, $columns, $per_page;

		// Pass vars to loop
		$per_page = $posts_per_page;
		$columns = $post_columns;

		$related = $_product->get_related($posts_per_page);
		if (sizeof($related) > 0) :
			echo '<div class="related products"><h2>'.__('Related Products', 'fflcommerce').'</h2>';
			$args = array(
				'post_type' => 'product',
				'ignore_sticky_posts' => 1,
				'posts_per_page' => $per_page,
				'orderby' => $orderby,
				'post__in' => $related
			);
			$args = apply_filters('fflcommerce_related_products_args', $args);
			query_posts($args);
			fflcommerce_get_template_part('loop', 'shop');
			echo '</div>';
			wp_reset_query();
		endif;
		$per_page = null;   // reset for cross sells if enabled
		$columns = null;
	}
}

/**
 * FFL Commerce Shipping Calculator
 **/
if (!function_exists('fflcommerce_shipping_calculator')) {
	function fflcommerce_shipping_calculator()
	{
		if (fflcommerce_shipping::show_shipping_calculator()) :
			?>
			<form class="shipping_calculator" action="<?php echo esc_url(fflcommerce_cart::get_cart_url()); ?>" method="post">
				<h2><a href="#" class="shipping-calculator-button"><?php _e('Calculate Shipping', 'fflcommerce'); ?><span>&darr;</span></a></h2>
				<section class="shipping-calculator-form">
					<p class="form-row">
						<select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state" rel="calc_shipping_state">
							<?php foreach (fflcommerce_countries::get_allowed_countries() as $key => $value): ?>
								<option value="<?php echo esc_attr($key); ?>" <?php selected(fflcommerce_customer::get_shipping_country(), $key); ?>><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<div class="col2-set">
						<p class="form-row col-1">
							<?php
							$current_cc = fflcommerce_customer::get_shipping_country();
							$current_r = fflcommerce_customer::get_shipping_state();
							$states = fflcommerce_countries::$states;

							if (fflcommerce_countries::country_has_states($current_cc)) :
								// Dropdown
								?>
								<span>
								<select name="calc_shipping_state" id="calc_shipping_state">
									<option value=""><?php _e('Select a state&hellip;', 'fflcommerce'); ?></option><?php
									foreach ($states[$current_cc] as $key => $value) :
										echo '<option value="'.esc_attr($key).'"';
										if ($current_r == $key) {
											echo 'selected="selected"';
										}
										echo '>'.$value.'</option>';
									endforeach;
									?></select>
							</span>
							<?php
							else :
								// Input
								?>
								<input type="text" class="input-text" value="<?php echo esc_attr($current_r); ?>" placeholder="<?php _e('state', 'fflcommerce'); ?>" name="calc_shipping_state" id="calc_shipping_state" />
							<?php
							endif;
							?>
						</p>
						<p class="form-row col-2">
							<input type="text" class="input-text" value="<?php echo esc_attr(fflcommerce_customer::get_shipping_postcode()); ?>" placeholder="<?php _e('Postcode/Zip', 'fflcommerce'); ?>" title="<?php _e('Postcode', 'fflcommerce'); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" />
						</p>
						<?php do_action('fflcommerce_after_shipping_calculator_fields');?>
					</div>
					<p>
						<button type="submit" name="calc_shipping" value="1" class="button"><?php _e('Update Totals', 'fflcommerce'); ?></button>
					</p>
					<p>
						<?php
						$available_methods = fflcommerce_shipping::get_available_shipping_methods();
						foreach ($available_methods as $method) :

						for ($i = 0;
						$i < $method->get_rates_amount();
						$i++) {
						?>
					<div class="col2-set">
						<p class="form-row col-1">
							<?php
							echo '<input type="radio" name="shipping_rates" value="'.esc_attr($method->id.':'.$i).'"'.' class="shipping_select"';
							if ($method->get_cheapest_service() == $method->get_selected_service($i) && $method->is_chosen()) {
								echo ' checked>';
							} else {
								echo '>';
							}
							echo $method->get_selected_service($i);
							?>
						<p class="form-row col-2"><?php
							if ($method->get_selected_price($i) > 0) :
								echo fflcommerce_price($method->get_selected_price($i));
								echo __(' (ex. tax)', 'fflcommerce');
							else :
								echo __('Free', 'fflcommerce');
							endif;
							?>
					</div>
					<?php
					}
					endforeach;
					?>
					<input type="hidden" name="cart-url" value="<?php echo esc_attr(fflcommerce_cart::get_cart_url()); ?>">
					<?php fflcommerce::nonce_field('cart') ?>
				</section>
			</form>
		<?php
		endif;
	}
}

/**
 * FFL Commerce Login Form
 **/
if (!function_exists('fflcommerce_login_form')) {
	function fflcommerce_login_form()
	{
		if (is_user_logged_in()) {
			return;
		}
		?>
		<form method="post" class="login">
			<p class="form-row form-row-first">
				<label for="username"><?php _e('Username', 'fflcommerce'); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="username" id="username" />
			</p>
			<p class="form-row form-row-last">
				<label for="password"><?php _e('Password', 'fflcommerce'); ?> <span class="required">*</span></label>
				<input class="input-text" type="password" name="password" id="password" />
			</p>
			<div class="clear"></div>
			<p class="form-row">
				<?php fflcommerce::nonce_field('login', 'login') ?>
				<input type="submit" class="button" name="login" value="<?php esc_html_e('Login', 'fflcommerce'); ?>" />
				<a class="lost_password" href="<?php echo esc_url(wp_lostpassword_url(get_permalink())); ?>"><?php _e('Lost Password?', 'fflcommerce'); ?></a>
			</p>
		</form>
	<?php
	}
}

/**
 * FFL Commerce Login Form
 **/
if (!function_exists('fflcommerce_checkout_login_form')) {
	function fflcommerce_checkout_login_form()
	{
		$options = FFLCommerce_Base::get_options();
		if (is_user_logged_in() || $options->get('fflcommerce_enable_guest_login') != 'yes') {
			return;
		}

		?><p class="info"><?php _e('Already registered?', 'fflcommerce'); ?> <a href="#" class="showlogin"><?php _e('Click here to login', 'fflcommerce'); ?></a></p><?php
		fflcommerce_login_form();
	}
}

/**
 * FFL Commerce Verify Checkout States Message
 **/
if (!function_exists('fflcommerce_verify_checkout_states_for_countries_message')) {
	function fflcommerce_verify_checkout_states_for_countries_message()
	{
		if (FFLCommerce_Base::get_options()->get('fflcommerce_verify_checkout_info_message') == 'yes') {
			// the following will return true or false if a country requires states
			if (!fflcommerce_customer::has_valid_shipping_state()) {
				echo '<div class="clear"></div><div class="payment_message">'.__('You may have already established your Billing and Shipping state, but please verify it is correctly set for your location as well as all the rest of your information before placing your Order.', 'fflcommerce').'</div>';
			} else {
				echo '<div class="clear"></div><div class="payment_message">'.__('Please verify that all your information is correctly entered before placing your Order.', 'fflcommerce').'</div>';
			}
		}
	}
}

/**
 * FFL Commerce EU B2B VAT Message
 **/
if (!function_exists('fflcommerce_eu_b2b_vat_message')) {
	function fflcommerce_eu_b2b_vat_message()
	{
		if (fflcommerce_countries::is_eu_country(fflcommerce_customer::get_country())
			&& FFLCommerce_Base::get_options()->get('fflcommerce_eu_vat_reduction_message') == 'yes'
		) {
			echo '<div class="clear"></div><div class="payment_message">'.__('If you have entered an EU VAT Number, it will be looked up when you <strong>Place</strong> your Order and verified.  At that time <strong><em>Only</em></strong>, will VAT then be removed from the final Order and totals adjusted.  You may enter your EU VAT Number either with, or without, the 2 character EU country code in front.', 'fflcommerce').'</div>';
		}
	}
}

/**
 * FFL Commerce Breadcrumb
 **/
if (!function_exists('fflcommerce_breadcrumb')) {
	function fflcommerce_breadcrumb($delimiter = ' &rsaquo; ', $wrap_before = '<div id="breadcrumb">', $wrap_after = '</div>', $before = '', $after = '', $home = null)
	{
		global $post, $wp_query, $author;
		$options = FFLCommerce_Base::get_options();

		if (!$home) {
			$home = _x('Home', 'breadcrumb', 'fflcommerce');
		}

		$home_link = home_url();
		$prepend = '';

		if ($options->get('fflcommerce_prepend_shop_page_to_urls') == "yes" && fflcommerce_get_page_id('shop') && get_option('page_on_front') !== fflcommerce_get_page_id('shop')) {
			$prepend = $before.'<a href="'.esc_url(fflcommerce_cart::get_shop_url()).'">'.get_the_title(fflcommerce_get_page_id('shop')).'</a> '.$after.$delimiter;
		}

		if ((!is_home() && !is_front_page() && !(is_post_type_archive() && get_option('page_on_front') == fflcommerce_get_page_id('shop'))) || is_paged()) :
			echo $wrap_before;
			echo $before.'<a class="home" href="'.$home_link.'">'.$home.'</a> '.$after.$delimiter;

			if (is_category()) :
				$cat_obj = $wp_query->get_queried_object();
				$this_category = $cat_obj->term_id;
				$this_category = get_category($this_category);
				if ($this_category->parent != 0) :
					$parent_category = get_category($this_category->parent);
					echo get_category_parents($parent_category->term_id, true, $delimiter);
				endif;
				echo $before.single_cat_title('', false).$after;
			elseif (is_tax('product_cat')) :
				$term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
				$parents = array();
				$parent = $term->parent;
				while ($parent):
					$parents[] = $parent;
					$new_parent = get_term_by('id', $parent, get_query_var('taxonomy'));
					$parent = $new_parent->parent;
				endwhile;
				if (!empty($parents)):
					$parents = array_reverse($parents);
					foreach ($parents as $parent):
						$item = get_term_by('id', $parent, get_query_var('taxonomy'));
						echo $before.'<a href="'.get_term_link($item->slug, 'product_cat').'">'.$item->name.'</a>'.$after.$delimiter;
					endforeach;
				endif;

				$queried_object = $wp_query->get_queried_object();
				echo $prepend.$before.$queried_object->name.$after;
			elseif (is_tax('product_tag')) :
				$queried_object = $wp_query->get_queried_object();
				echo $prepend.$before.__('Products tagged &ldquo;', 'fflcommerce').$queried_object->name.'&rdquo;'.$after;
			elseif (is_day()) :
				echo $before.'<a href="'.get_year_link(get_the_time('Y')).'">'.get_the_time('Y').'</a>'.$after.$delimiter;
				echo $before.'<a href="'.get_month_link(get_the_time('Y'), get_the_time('m')).'">'.get_the_time('F').'</a>'.$after.$delimiter;
				echo $before.get_the_time('d').$after;
			elseif (is_month()) :
				echo $before.'<a href="'.get_year_link(get_the_time('Y')).'">'.get_the_time('Y').'</a>'.$after.$delimiter;
				echo $before.get_the_time('F').$after;
			elseif (is_year()) :
				echo $before.get_the_time('Y').$after;
			elseif (is_post_type_archive('product') && get_option('page_on_front') !== fflcommerce_get_page_id('shop')) :
				$_name = fflcommerce_get_page_id('shop') ? get_the_title(fflcommerce_get_page_id('shop')) : ucwords($options->get('fflcommerce_shop_slug'));

				if (is_search()) :
					echo $before.'<a href="'.get_post_type_archive_link('product').'">'.$_name.'</a>'.$delimiter.__('Search results for &ldquo;', 'fflcommerce').get_search_query().'&rdquo;'.$after;
				else :
					echo $before.'<a href="'.get_post_type_archive_link('product').'">'.$_name.'</a>'.$after;
				endif;
			elseif (is_single() && !is_attachment()) :
				if (get_post_type() == 'product') :
					echo $prepend;

					if ($terms = get_the_terms($post->ID, 'product_cat')) :
						$term = apply_filters('fflcommerce_product_cat_breadcrumb_terms', current($terms), $terms);
						$parents = array();
						$parent = $term->parent;
						while ($parent):
							$parents[] = $parent;
							$new_parent = get_term_by('id', $parent, 'product_cat');
							$parent = $new_parent->parent;
						endwhile;
						if (!empty($parents)):
							$parents = array_reverse($parents);
							foreach ($parents as $parent):
								$item = get_term_by('id', $parent, 'product_cat');
								echo $before.'<a href="'.get_term_link($item->slug, 'product_cat').'">'.$item->name.'</a>'.$after.$delimiter;
							endforeach;
						endif;
						echo $before.'<a href="'.get_term_link($term->slug, 'product_cat').'">'.$term->name.'</a>'.$after.$delimiter;
					endif;

					echo $before.get_the_title().$after;
				elseif (get_post_type() != 'post') :
					$post_type = get_post_type_object(get_post_type());
					echo $before.'<a href="'.get_post_type_archive_link(get_post_type()).'">'.$post_type->labels->singular_name.'</a>'.$after.$delimiter;
					echo $before.get_the_title().$after;
				else :
					$cat = current(get_the_category());
					echo get_category_parents($cat, true, $delimiter);
					echo $before.get_the_title().$after;
				endif;
			elseif (is_404()) :
				echo $before.__('Error 404', 'fflcommerce').$after;
			elseif (!is_single() && !is_page() && get_post_type() != 'post') :
				$post_type = get_post_type_object(get_post_type());
				if ($post_type) : echo $before.$post_type->labels->singular_name.$after; endif;
			elseif (is_attachment()) :
				$parent = get_post($post->post_parent);
				$cat = get_the_category($parent->ID);
				$cat = $cat[0];
				echo get_category_parents($cat, true, ''.$delimiter);
				echo $before.'<a href="'.get_permalink($parent).'">'.$parent->post_title.'</a>'.$after.$delimiter;
				echo $before.get_the_title().$after;
			elseif (is_page() && !$post->post_parent) :
				echo $before.get_the_title().$after;
			elseif (is_page() && $post->post_parent) :
				$parent_id = $post->post_parent;
				$breadcrumbs = array();

				while ($parent_id) {
					$page = get_post($parent_id);
					$breadcrumbs[] = '<a href="'.get_permalink($page->ID).'">'.get_the_title($page->ID).'</a>';
					$parent_id = $page->post_parent;
				}

				$breadcrumbs = array_reverse($breadcrumbs);
				foreach ($breadcrumbs as $crumb) :
					echo $crumb.''.$delimiter;
				endforeach;

				echo $before.get_the_title().$after;
			elseif (is_search()) :
				echo $before.__('Search results for &ldquo;', 'fflcommerce').get_search_query().'&rdquo;'.$after;
			elseif (is_tag()) :
				echo $before.__('Posts tagged &ldquo;', 'fflcommerce').single_tag_title('', false).'&rdquo;'.$after;
			elseif (is_author()) :
				$userdata = get_userdata($author);
				echo $before.__('Author: ', 'fflcommerce').$userdata->display_name.$after;
			endif;

			if (get_query_var('paged')) :
				echo ' ('.__('Page', 'fflcommerce').' '.get_query_var('paged').')';
			endif;

			echo $wrap_after;
		endif;
	}
}

/**
 * Hook to remove the 'singular' class, for the twenty eleven theme, to properly display the sidebar
 *
 * @param array $classes
 * @return array
 */
function fflcommerce_body_classes($classes)
{
	if (!is_content_wrapped()) {
		return $classes;
	}

	$key = array_search('singular', $classes);
	if ($key !== false) {
		unset($classes[$key]);
	}

	return $classes;
}

/**
 * Order review table for checkout
 **/
function fflcommerce_order_review()
{
	fflcommerce_get_template('checkout/review_order.php', false);
}

