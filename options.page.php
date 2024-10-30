<style type="text/css" media="screen">
    div#wrap{width:600px;}
    div#clearer{clear:both; height:10px;}
    div#clearer2{clear:both; height:5px}
    div#Setup{background: #F1F1F1; padding:4px;}
    .bcint_options{padding:4px;}
    div#confirm{margin:0; padding:0; border:3px #000 solid; width:50%; padding:4px;}
    div#confirm #success{border: 2px #ACA solid; background-color:#AACCAA; -webkit-border-bottom-left-radius: 5px 5px; -webkit-border-bottom-right-radius: 5px 5px; -webkit-border-top-left-radius: 5px 5px; -webkit-border-top-right-radius: 5px 5px;}
</style>
<div class="wrap">
    <div> 
	<?php screen_icon( 'options-general' ); ?><h2>Big Cartel Integration - <?php print BCINT_VERSION ?></h2>
    </div>
    <div id="Setup">
	<h2>Setup</h2> 
	<ol> 
	    <li>Fill out the Options below</li>
	    <li>Confirm Your Data <a href="#confirm">Below</a></li>
	    <li>After you create a Home Page for your store, eg: a Page called "Shop".<br />
	  		Place this code into the page
		<pre> 	 [bcint show='home'/] </pre> This displays the Big Cartel home page, which by default, displays all of the products on your store.
		<br />You could try: <pre> [bcint show='home' categories='caps'/]  </pre> 
			if you only want to show the 'caps' category of your products.
	    </li>
	    <li>After creating the home page create a Product Page for your store, eg: a Page called "Products" and make sure that it is hidden!<br />
	  		Put this code into the page,
		<pre> [bcint show='aproduct'/] </pre> 
			This is the page that will display products in detail.
	    </li> 
	</ol>
    </div>
    <h2>Options</h2> 
    <form method="post" action="options.php" id="optionsform"><?php wp_nonce_field('update-options'); ?>
	<fieldset name="general_options" class="bcint_options">

	    <li>
		<label>Big Cartel Sub-domain</label>
		<input type="text" name="bc_subdomain" value="<?php echo get_option('bc_subdomain'); ?>" size="20" />
		<p class="inline-hints">http://{subdomain}.bigcartel.com - The subdomain of you BigCartel store </p>
	    </li>
	    <li>
		<label>Big Cartel Store URL</label>
		<input type="text" name="bc_store_url" value="<?php echo get_option('bc_store_url'); ?>" size="60" />
		<p class="inline-hints">http://store.yourdomain.com - This will be used to link to your store.</p>
	    </li>
	    <li>
		<label>Product Home Page</label>
		<input type="text" name="bc_wp_homepage" value="<?php echo get_option('bc_wp_homepage'); ?>" /><br>
		<p class="">Slug of a WordPress page you've created eg: "store".</p>
	    </li>
	    <li>
		<label>Product Page</label> 
		<input type="text" name="bc_wp_productpage" value="<?php echo get_option('bc_wp_productpage'); ?>" /><br>
		<p class="">Slug of a WordPress page you've created eg: "product".</p>
	    </li>
	    <div id="clearer"> </div>
	    <div><b>Image Sizes to Use:</b> 
		<div>You should adjust these and find what suits your needs best.</div>
		<?php $s = get_option('bigcarteldetailimagesize'); ?> 
		<ul>
		    <li>	
					    Product Details Page  
			<select name="bigcarteldetailimagesize">
			    <option value="SMALL" <?php if ($s == "SMALL") { ?> selected <?php } ?>> SMALL </option>
			    <option value="MEDIUM" <?php if ($s == "MEDIUM") { ?> selected <?php } ?>> MEDIUM </option>
			    <option value="LARGE" <?php if ($s == "LARGE") { ?> selected <?php } ?>> LARGE </option>
			    <option value="ORIGINAL" <?php if ($s == "ORIGINAL") { ?> selected <?php } ?>> ORIGINAL </option>
			</select>
		    </li>
		    <div id="clearer2"> </div>
		    <?php $s2 = get_option('bigcartelhomeimagesize'); ?> 
		    <li>
					    Home/Store Page  
			<select name="bigcartelhomeimagesize">
			    <option value="SMALL" <?php if ($s2 == "SMALL") { ?> selected <?php } ?>> SMALL </option>
			    <option value="MEDIUM" <?php if ($s2 == "MEDIUM") { ?> selected <?php } ?>> MEDIUM </option>
			    <option value="LARGE" <?php if ($s2 == "LARGE") { ?> selected <?php } ?>> LARGE </option>
			    <option value="ORIGINAL" <?php if ($s2 == "ORIGINAL") { ?> selected <?php } ?>> ORIGINAL </option>
			</select>
		    </li>
		</ul>
	    </div>
	    <input type="hidden" name="action" value="update" />
	    <input type="hidden" name="page_options" value="bc_subdomain,bc_store_url,bc_wp_productpage,bc_wp_homepage,bigcarteldetailimagesize,bigcartelhomeimagesize" />
	</fieldset>
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </form>      
    <h2>Check your Details</h2>
    <div id="confirm"><a name="confirm">&nbsp;</a>
	<?php if (bcint_testConnection()) { ?>
    	<p id="success"> If there's no errors, you're Good to Go! <a href="<?php print bcint_getPageUrl("homepage") ?>">Store Home</a> </p>
	<?php } else { ?>
        	 		Something is preventing Big Cartel Integration from working correctly, please check that you have filled out all the necessary fields above.
	<?php } ?>
	<br/>&nbsp;<br/>
    </div>
    <br/>&nbsp;<br/>
</div>