
<!-- snr-specific change: -->
	<!--  	<div id="product_home_link"><a href="##bcHomePageUrl">Back to Products List</a></div> -->
<!-- end snr-specific change: -->
		    <div id="productDetail_txt">
	 	 	<h2 id="product_name">##pName</h2></div>
 	 <div class="productDetail ##userClass"> 
 		<div id="product_images">##pImages</div>
 	 	
 		<div id="productDetail_txt"> 
			<div id="product_description"><p> 
				##pDescription	 
		 	</p></div>
		 	<h3 class="product_price"><b>Price:</b> <span class="currency_sign">$</span>##pPrice</h3> 
			
			<!--<p class="product-onsale"><b>On sale:</b> <span>##pOnSale</span></p> 
			<p class="product-url"><b>Url:</b> <span>##pUrl</span></p> 
			<p class="product-id"><b>ID:</b> <span>##pId</span></p> 
			<p class="product-position"><b>Position:</b> <span>##pPosition</span></p> 
			-->
			<div id="product_form_wrap"> 
				<form id="product_form" class="clearfix" method="post" action="##bcCartUrl"> 
					<div id="product_options" class="options">
					##pOptions
					</div> 
					<button id="btn_product_buy" class="button" name="submit" type="submit">Add to cart</button>    <br /> 
					<button id="btn_product_view" class="button" name="view" type="button" onclick="document.location='##bcCartUrl'">View cart</button>       <br /> 
					<button id="btn_product_checkout" class="button" name="checkout" type="button" onclick="document.location='##bcCartUrl'">Checkout</button><br />    
				</form> 
			</div> 
			<!--<div id="product_categories"><p> 
				##pCategory	 
		 	</p></div>-->
		 	 
		 	<div style="clear:both;"></div>
		 </div> 
		 <div style="clear:both;"></div>		
	</div>	
