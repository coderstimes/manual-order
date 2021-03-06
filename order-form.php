<?php 
defined( 'ABSPATH' ) || exit;
?>

<div class="main">
    <div class="container">
        <div class="row">
            <div class="col-md-8 m-auto">

                <div class="mofw-form-title text-center bg-secondary clearfix py-2 mt-5 text-white rounded">
                    <h4><?php _e('Woocommerce Manual Order', 'mofw'); ?></h4>
                </div>

                <form action='<?php echo esc_url(admin_url('admin-post.php')); ?>' class="mt-4" method='POST'>

                    <div class="mb-3">
                        <?php $label = __('Email Address', 'mofw'); ?>
                        <label for="email" class="form-label"> <?php echo $label; ?> </label>
                        <input type="email" name="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="<?php echo $label; ?>">
                        <div id="emailHelp" class="form-text"> <?php _e('Your customer email address', 'mofw'); ?> </div>
                    </div>

                    <div class="mb-3">
                        <?php $label = __('First Name', 'mofw'); ?>
                        <label for="first_name" class="form-label"> <?php echo $label; ?> </label>
                        <input type="text" name="first_name" class="form-control" id="first_name" aria-describedby="first_nameHelp" placeholder="<?php echo $label; ?>">
                        <div id="first_nameHelp" class="form-text"> <?php _e('Your customer first name address', 'mofw'); ?> </div>
                    </div>

                    <div class="mb-3">
                        <?php $label = __('Last Name', 'mofw'); ?>
                        <label for="last_name" class="form-label"> <?php echo $label; ?> </label>
                        <input type="text" name="last_name" class="form-control" id="last_name" aria-describedby="last_nameHelp" placeholder="<?php echo $label; ?>">
                        <div id="last_nameHelp" class="form-text"> <?php _e('Your customer last name address', 'mofw'); ?> </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8 position-relative">
                            <?php $label = __('Password', 'mofw'); ?>
                            <label for="password" class="form-label"> <?php echo $label; ?> </label>
                            <input type="text" class="form-control" id="password" placeholder='<?php echo $label; ?>' aria-describedby="passwordHelp">
                            <div id="passwordHelp" class="form-text"> <?php _e('Customer password', 'mofw'); ?> </div>
                        </div>                        
                        <div class="col-md-4 position-relative">
                            <div class="d-grid gap-2">
                                <?php $label = __('Generate', 'mofw'); ?>
                                <button class="btn btn-primary button-hero mt-4 py-2" id='mofw_genpw' type="button"> <?php echo $label; ?> </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?php $label = __('Phone Number', 'mofw'); ?>
                        <label for="phone" class="form-label"> <?php echo $label; ?> </label>
                        <input type="text" name="phone" class="form-control" id="phone" aria-describedby="phoneHelp" placeholder="<?php echo $label; ?>">
                        <div id="phoneHelp" class="form-text"> <?php _e('Your customer phone number', 'mofw'); ?> </div>
                    </div>

                    <div class="mb-3">
                        <?php $label = __('Discount in ', 'mofw') . get_option( 'woocommerce_currency' ) . ' ('.get_woocommerce_currency_symbol() . ')' ;  ?>
                        <label id="discount-label" for="discount" class="form-label"> <?php echo $label; ?> </label>
                        <input type="text" name="discount" class="form-control" id="discount" aria-describedby="discountHelp" placeholder="<?php echo $label; ?>">
                        <div id="discountHelp" class="form-text"> <?php _e('product discount', 'mofw'); ?> </div>
                    </div>

                    <div class="mb-3">
                        <?php $label = __('I want to input coupon code', 'mofw'); ?>
                        <input class="form-check-input" type='checkbox' name='coupon' id='coupon' value="" > 
                        <label class="form-check-label" for='coupon'> <?php echo $label; ?> </label>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 position-relative">
                            <?php $label = __('Product List', 'mofw'); ?>
                            <label for='item'><?php echo $label; ?></label>
                            <select class='select_product mt-1' name='item' id='item'>
                                <option value="0"><?php _e('Select One', 'mofw'); ?></option>
                                <?php
                                $products = wc_get_products(array('post_status' => 'published', 'posts_per_page' => -1));
                                foreach ($products as $product) { ?>
                                    <option data-thumbnail='<?php echo get_the_post_thumbnail_url($product->get_ID()) ?>' value='<?php echo $product->get_ID(); ?>'><?php echo $product->get_Name(); ?></option>
                                <?php } ?>
                            </select>
                        </div>                        
                    </div>

                    <div class="mb-3">
                        <?php $label = __('Order Note', 'mofw'); ?>
                        <label for="note" class="form-label"> <?php echo $label; ?> </label>
                        <input type="text" name="note" class="form-control" id="note" placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class="row mb-3 mt-4">
                        <div class="d-grid gap-2 col-6 mx-auto">
                          <button class="btn btn-primary py-2" name='submit' type="submit"> <?php _e('Create Order', 'mofw'); ?> </button>
                        </div>
                    </div>

                    <input type="hidden" name="action" value="mofw_form">
                    <input type="hidden" name="mofw_identifier" value="<?php echo md5(time()); ?>">
                    <?php wp_nonce_field('mofw_form', 'mofw_form_nonce'); ?>                  

                </form>
                
            </div>
        </div>
    </div>
</div>

<div id="mofw-modal">
    <div class="mofw-modal-content">
        <?php
        if (isset($_GET['order_id'])) {
            do_action('mofw_order_processing_complete', sanitize_text_field($_GET['order_id']));
        }
        ?>
    </div>
</div>

