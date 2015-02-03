<?php

/***************************************************************************

Plugin Name:  Improvely for WooCommerce
Plugin URI:   http://www.improvely.com
Description:  Integrates <a href="http://www.improvely.com">Improvely</a> with your WooCommerce store &mdash; allowing you to identify the exact traffic source of every purchase, split test ads, monitor your PPC ads for fraudulent clicks and more.
Version:      1.7
Author:       Improvely
Author URI:   http://www.improvely.com

**************************************************************************/

add_action('wp_head', 'improvely_init');
add_action('woocommerce_thankyou', 'improvely_goal');
add_action('admin_menu', 'improvely_pages');
add_action('admin_init', 'improvely_admin_init');

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
	echo "<div id='improvely-warning' class='updated fade'><p><strong>Improvely for WooCommerce is almost ready.</strong> ".sprintf('<a href="%1$s">Click here to configure the plugin</a>.', "admin.php?page=improvely-config")."</p></div>";
}

function improvely_init() {
	$improvely_subdomain = get_option('improvely_subdomain');
	$improvely_id = get_option('improvely_id');
	if (!empty($improvely_subdomain)) {
?>
<script type="text/javascript">
var im_domain = '<?php echo $improvely_subdomain; ?>';
var im_project_id = <?php echo $improvely_id; ?>;
(function(e,t){window._improvely=[];var n=e.getElementsByTagName("script")[0];var r=e.createElement("script");r.type="text/javascript";r.src="https://"+im_domain+".iljmp.com/improvely.js";r.async=true;n.parentNode.insertBefore(r,n);if(typeof t.init=="undefined"){t.init=function(e,t){window._improvely.push(["init",e,t])};t.goal=function(e){window._improvely.push(["goal",e])};t.conversion=function(e){window._improvely.push(["conversion",e])};t.label=function(e){window._improvely.push(["label",e])}}window.improvely=t;t.init(im_domain,im_project_id)})(document,window.improvely||[])
</script>
<?php 
	}
}

function improvely_goal($order_id) {
	$improvely_subdomain = get_option('improvely_subdomain');
	$improvely_id = get_option('improvely_id');	
	$order = new WC_Order($order_id);
	?>
<script type="text/javascript" src="https://<?php echo $improvely_subdomain; ?>.iljmp.com/improvely.js"></script>
<script type="text/javascript">
improvely.init('<?php echo $improvely_subdomain; ?>', <?php echo $improvely_id; ?>);
improvely.conversion({
  goal: 'sale',
  revenue: <?php echo $order->get_total(); ?>,
  reference: '<?php echo $order_id; ?>'
});
<?php
if (!empty($order->billing_first_name)):
?>
improvely.label({
	label: '<?php echo $order->billing_email; ?>',
	name: '<?php echo $order->billing_first_name . ' ' . $order->billing_last_name; ?>',
	email: '<?php echo $order->billing_email; ?>'
});
<?php elseif (!empty($order->billing_email)): ?>
improvely.label('<?php echo $order->billing_email; ?>');
<?php endif; ?>
</script>
<noscript>
<img src="https://<?php echo $improvely_subdomain; ?>.iljmp.com/track/conversion?project=<?php echo $improvely_id; ?>&goal=sale&revenue=<?php echo $order->order_total; ?>&reference=<?php echo $order_id; ?>" width="1" height="1" />
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

    <div style="width: 772px">		

		<div style="background: #eee">
			<img src="<?php echo plugins_url( 'banner.png' , __FILE__ ) ?>" alt="Banner" />

			<div style="padding: 10px; font-size: 13px; line-height: 1.5em">
				<?php if (!empty($improvely_subdomain) && !empty($improvely_id)): ?>
				<b>Your settings have been saved. Improvely is now tracking the visits and purchases on your website.</b>
				<?php else: ?>
				This plugin requires an <a href="http://www.improvely.com" target="_blank">Improvely</a>
				account. If you do not already have an account, you can 
				<a href="https://www.improvely.com/signup" target="_blank">sign up for a 14-day free trial</a>
				right now. 
				<?php endif; ?>
			</div>
		</div>

		<p>
			Improvely for WooCommerce will send information about your website visits and purchases
			to your Improvely account. You can view your reports and set up tracking links for your 
			ads by clicking on Improvely under your Dashboard menu.
		</p>

		<div style="background: #eee">
			<div style="background: #ccc; padding: 10px; font-weight: bold">Configuration</div>

			<form method="post" action="admin.php?page=improvely-config">

		    <table class="form-table" style="margin: 10px">
		        <tr valign="top">
		            <th scope="row" style="white-space: nowrap"><label>Improvely Site Name:</label></th>
		            <td style="width: 100%">
		                <input type="text" name="improvely_subdomain" value="<?php echo $improvely_subdomain; ?>" />
		                .improvely.com
		            </td>
		        </tr>
				<tr>
					<th scope="row" style="white-space: nowrap"><label>Project ID:</label></th>
					<td style="width: 100%">
						<input type="text" name="improvely_id" value="<?php echo $improvely_id; ?>" />
		            </td>
			    </tr>
			    <tr>
			    	<td></td>
			    	<td>
			    		<input type="submit" value="Save Changes" class="button" />
		    		</td>
	    		</tr>
			</table>

			</form>

		</div>

		<div style="background: #eee; margin-top: 10px">
			<div style="background: #ccc; padding: 10px; font-weight: bold">Where do I find my site name and project ID?</div>
			<div style="padding: 10px">
				Your site name is the subdomain you chose when you signed up, and where you 
				log in to access your account. You can retrieve both your domain and project ID 
				from the <b>Website Code</b> page in your account. You can return to the 
				Website Code page by clicking <b>Click here if you need to retrieve your Improvely 
				Code again</b> on the Getting Started screen, or by clicking on your project at the 
				top left of your account and choosing <b>Project Settings</b>.

				<br /><br />

				<b>Example only, use the values from your own account:</b>
		
				<img src="<?php echo plugins_url( 'install.png' , __FILE__ ) ?>" alt="Banner" style="border: 1px solid #000" />

			</div>
		</div>

	</div>

</div>

<?php } ?>