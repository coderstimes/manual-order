<?php 
defined( 'ABSPATH' ) || exit;
?>
<div class="mofw-form-wrapper">
    <div class="mofw-form-title">
        <h4><?php _e('Woocommerce Manual Order', 'mofw'); ?></h4>
    </div>
    <div class='mofw-form-container'>
        <div class="mofw-form">
            <form action='<?php echo esc_url(admin_url('admin-post.php')); ?>' class='pure-form pure-form-aligned' method='POST'>
                <fieldset>
                    <input type='hidden' name='customer_id' id='customer_id' value='0'>
                    <div class='pure-control-group'>
                        <?php $label = __('Email Address', 'mofw'); ?>
                        <label for='name'><?php echo $label; ?></label>
                        <input class='mofw-control' required name='email' id='email' type='email' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('First Name', 'mofw'); ?>
                        <label for='first_name'><?php echo $label; ?></label>
                        <input class='mofw-control' required name='first_name' id='first_name' type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Last Name', 'mofw'); ?>
                        <label for='last_name'><?php echo $label; ?></label>
                        <input class='mofw-control' required name='last_name' id='last_name' type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group' id='password_container'>
                        <?php $label = __('Password', 'mofw'); ?>
                        <label for='password'><?php echo $label; ?></label>
                        <input class='mofw-control-right-gap' name='password' id='password' type='text' placeholder='<?php echo $label; ?>'>
                        <button type='button' id='mofw_genpw' class="button button-primary button-hero">
                            <?php _e('Generate', 'mofw'); ?>
                        </button>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Phone Number', 'mofw'); ?>
                        <label for='phone'><?php echo $label; ?></label>
                        <input class='mofw-control' name='phone' id='phone' type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Discount in ', 'mofw') . get_option( 'woocommerce_currency' ) . ' ('.get_woocommerce_currency_symbol() . ')' ;  ?>
                        <label id="discount-label" for="discount"><?php echo $label; ?></label>
                        <input class='mofw-control' name="discount" id="discount" type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group' style="margin-top:20px;margin-bottom:20px;">
                        <?php $label = __('I want to input coupon code', 'mofw'); ?>
                        <label for='coupon'></label>
                        <input type='checkbox' name='coupon' id='coupon' value='1' /><?php echo $label; ?>
                    </div>

                    <div class='pure-control-group'>
                        <?php $label = __('Product Name', 'mofw'); ?>
                        <label for='item'><?php echo $label; ?></label>
                        <select class='mofw-control select_product' name='item' id='item'>
                            <option value="0"><?php _e('Select One', 'mofw'); ?></option>
                            <?php
                            $products = wc_get_products(array('post_status' => 'published', 'posts_per_page' => -1));
                            foreach ($products as $product) {
                            ?>
                                <option data-thumbnail='<?=get_the_post_thumbnail_url($product->get_ID()) ?>' value='<?php echo $product->get_ID(); ?>'><?php echo $product->get_Name(); ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <div class='pure-control-group note_area'>
                        <?php $label = __('Order Note', 'mofw'); ?>
                        <label for='note'><?php echo $label; ?></label>
                        <input class='mofw-control' name='note' id="note" type='text' placeholder='<?php echo $label; ?>'>
                    </div>

                    <div class='pure-control-group' style='margin-top:20px;'>
                        <label></label>
                        <button type='submit' name='submit' class='button button-primary button-hero' value="submit">
                            <?php _e('Create Order', 'mofw'); ?>
                        </button>
                    </div>
                </fieldset>
                <input type="hidden" name="action" value="mofw_form">
                <input type="hidden" name="mofw_identifier" value="<?php echo md5(time()); ?>">
                <?php wp_nonce_field('mofw_form', 'mofw_form_nonce'); ?>
            </form>
        </div>
        <div class="mofw-info"> </div>
        <div class="mofw-clearfix"></div>
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

