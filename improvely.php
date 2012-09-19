<?php

/***************************************************************************

Plugin Name:  Improvely for WooCommerce
Plugin URI:   http://www.improvely.com
Description:  Integrates <a href="http://www.improvely.com">Improvely</a> with your WooCommerce store &mdash; allowing you to identify the exact traffic source of every purchase, split test ads, monitor your PPC ads for fraudulent clicks and more.
Version:      1.0
Author:       Improvely
Author URI:   http://www.improvely.com

**************************************************************************/

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	add_action('wp_head', 'improvely_init');
	add_action('woocommerce_thankyou', 'improvely_goal');
	add_action('admin_menu', 'improvely_pages');
	add_action('admin_init', 'improvely_admin_init');

}

function improvely_admin_init() {

    if (function_exists('get_plugin_page_hook'))
        $hook = get_plugin_page_hook('improvely-display', 'index.php');
    else
        $hook = 'dashboard_page_improvely-display';
    add_action('admin_head-'.$hook, 'improvely_stats_script');

    $subdomain = get_option('improvely_id');
    if (empty($subdomain))
    	add_action('admin_notices', 'improvely_warning');

}

function improvely_pages() {
	if (function_exists('add_submenu_page')) {
    	add_submenu_page('options-general.php', 'Improvely', 'Improvely', 'manage_options', 'improvely-config', 'improvely_config');
    	add_submenu_page('index.php', 'Improvely', 'Improvely', 'manage_options', 'improvely-display', 'improvely_display');    
    }
}

function improvely_warning() {
	echo "<div id='improvely-warning' class='updated fade'><p><strong>Improvely is almost ready.</strong> ".sprintf('<a href="%1$s">Click here to configure the plugin</a>.', "admin.php?page=improvely-config")."</p></div>";
}

function improvely_init() {
	$improvely_subdomain = get_option('improvely_subdomain');
	$improvely_id = get_option('improvely_id');
?>
<script type="text/javascript" src="https://<?php echo $improvely_subdomain; ?>.iljmp.com/improvely.js"></script>
<script type="text/javascript">
  improvely.init('<?php echo $improvely_subdomain; ?>', <?php echo $improvely_id; ?>);
</script>
<?php 
}

function improvely_goal($order_id) {
	$improvely_subdomain = get_option('improvely_subdomain');
	$improvely_id = get_option('improvely_id');	
	$order = new WC_Order($order_id);
	?>
<script type="text/javascript" src="https://<?php echo $improvely_subdomain; ?>.iljmp.com/improvely.js"></script>
<script type="text/javascript">
  improvely.init('<?php echo $improvely_subdomain; ?>', <?php echo $improvely_id; ?>);
  improvely.goal({
    type: 'sale',
    amount: <?php echo $order->order_total; ?>,
    reference: '<?php echo $order_id; ?>'
  });
</script>
<noscript>
<img src="https://<?php echo $improvely_subdomain; ?>.iljmp.com/track/conversion?product=<?php echo $improvely_id; ?>&type=sale&amount=<?php echo $order->order_total; ?>&reference=<?php echo $order_id; ?>" width="1" height="1" />
</noscript>
	<?php
}

function improvely_stats_script() { ?>
	<script type="text/javascript">
		function resizeImprovely() {
		    var height = document.documentElement.clientHeight;
		    height -= document.getElementById('improvely-stats-frame').offsetTop;
		    height += 100; // magic padding
		    document.getElementById('improvely-stats-frame').style.height = height +"px";
		};
		function resizeImprovelyInit() {
	        document.getElementById('improvely-stats-frame').onload = resizeImprovely;
	        window.onresize = resizeImprovely;
		}
		addLoadEvent(resizeImprovelyInit);
	</script>
	<?php
}


function improvely_display() {
	
	$subdomain = get_option('improvely_subdomain');

	if (!empty($subdomain)) {

		$url = 'https://' . $subdomain . '.improvely.com/';
    	?>
   		<div class="wrap">
    		<iframe src="<?php echo $url; ?>" width="100%" height="100%" frameborder="0" id="improvely-stats-frame"></iframe>
    	</div>
    	<?php

	}
}

function improvely_config() {

	if (!empty($_POST)) {
		update_option('improvely_subdomain', $_POST['improvely_subdomain']);
		update_option('improvely_id', $_POST['improvely_id']);
	}
	$improvely_subdomain = get_option('improvely_subdomain');
	$improvely_id = get_option('improvely_id');

?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>

    <h2>Improvely Settings</h2>

    <form method="post" action="admin.php?page=improvely-config">

	<?php if (!empty($improvely_subdomain) && !empty($improvely_id)): ?>
		<div id="message" class="updated fade"><p><strong><?php _e('Your settings have been saved. Improvely is now tracking the visits and purchases on your website.') ?></strong></p></div>
	<?php else: ?>
		<div style="font-size: 15px; line-height: 2em; border: 1px solid #ccc; border-radius: 8px; background: #f5f5f5; padding: 10px 20px; width: 706px">

			<p>
				To install this plugin, you need an Improvely account. If you do not already have one, 
				you can <a href="http://www.improvely.com/">sign up for a free 30-day trial here</a>.

				After creating your project in Improvely, copy the site name and the tracking ID from 
				your Improvely Code into the boxes below to install this plugin.
			</p>
			<p>
				<img src="<?php echo plugins_url( 'install.png' , __FILE__ ) ?>" alt="" style="border: 1px solid #ccc" />
			</p>

		</div>
	<?php endif; ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label>Improvely Site Name:</label></th>
            <td>
                <input type="text" name="improvely_subdomain" value="<?php echo $improvely_subdomain; ?>" />.improvely.com
            </td>
        </tr>
		<tr>
			<th scope="row"><label>Tracking ID:</label></th>
			<td>
				<input type="text" name="improvely_id" value="<?php echo $improvely_id; ?>" />
            </td>
	    </tr>
	</table>

	<br /><br />

	<input type="submit" value="Save Changes" class="button" />

	</form>
</div>

<?php } ?>