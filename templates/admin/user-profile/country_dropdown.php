<?php
/**
 * @var $name string Name of the widget
 * @var $country string Currently selected country
 * @var $state string Currently selected state
 */
?>
<select id="fflcommerce_<?php echo $name; ?>" class="single_select_country" name="fflcommerce[<?php echo $name; ?>]">
<?php echo fflcommerce_countries::country_dropdown_options($country, $state, true, false, false, false); ?>
</select>
<script type="text/javascript">
	/*<![CDATA[*/
	jQuery(function($){
		$("#fflcommerce_<?php echo $name; ?>").select2({ width: '25em' });
	});
	/*]]>*/
</script>
