<?php
/**
 * Template for displaying Repurchase Packages.
 *
 * Expects $repurchase_packages_query (a WP_Query object) to be available.
 */

if ( ! isset( $repurchase_packages_query ) || ! $repurchase_packages_query instanceof WP_Query ) {
    echo '<p>Error: Repurchase package data is not available.</p>';
    return;
}
?>

<div class="electric-scooter-packages-list electric-scooter-repurchase-packages-list">
    <?php while ( $repurchase_packages_query->have_posts() ) : $repurchase_packages_query->the_post(); ?>
        <?php
        $package_id = get_the_ID();
        $package_name = get_the_title();
        $package_price = get_post_meta( $package_id, '_repurchase_package_price', true );
        $fixed_income = get_post_meta( $package_id, '_repurchase_fixed_income', true );
        $level_percentages_str = get_post_meta( $package_id, '_repurchase_level_income_percentages', true );
        $level_percentages = !empty($level_percentages_str) ? explode(',', $level_percentages_str) : array();

        $package_image_url = get_the_post_thumbnail_url( $package_id, 'medium' );
        $package_description = get_the_content();

        // The "Buy Now" button will need a handler.
        // This could be an AJAX action or a POST to a specific URL/handler.
        // For now, let's make it a link that includes package_id.
        // A real purchase would require user to be logged in.
        $buy_now_url = '#'; // Placeholder
        if ( is_user_logged_in() ) {
            // Example: admin-ajax.php for AJAX handling, or a specific page that handles the purchase.
            // For non-AJAX, you might create a specific page with a slug like 'handle-repurchase'
            // and pass package_id and a nonce to it.
            $buy_now_url = wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => 'electric_scooter_repurchase_package', // Custom action hook for logged-in users
                        'package_id' => $package_id,
                        // 'user_id' => get_current_user_id() // Not needed if using current_user_can checks
                    ),
                    admin_url( 'admin-post.php' ) // admin-post.php is a good way to handle form submissions / actions
                ),
                'electric_scooter_repurchase_' . $package_id
            );
        } else {
            // Maybe link to login page with a redirect back, or just hide button.
            // For now, non-logged in users can't click (link is #) or button is hidden.
             $buy_now_url = add_query_arg('redirect_to', get_permalink(), wp_login_url()); // Redirect to login
        }
        ?>
        <div class="package-item repurchase-package-item" id="repurchase-package-<?php echo esc_attr( $package_id ); ?>">
            <?php if ( $package_image_url ) : ?>
                <div class="package-image">
                    <img src="<?php echo esc_url( $package_image_url ); ?>" alt="<?php echo esc_attr( $package_name ); ?>">
                </div>
            <?php endif; ?>
            <div class="package-details">
                <h3 class="package-name"><?php echo esc_html( $package_name ); ?></h3>

                <?php if ( $package_description ): ?>
                    <div class="package-description">
                        <?php echo wp_kses_post( $package_description ); ?>
                    </div>
                <?php endif; ?>

                <p class="package-meta">
                    <strong>Price:</strong> $<?php echo esc_html( number_format( (float)$package_price, 2 ) ); ?><br>
                    <?php if ( !empty($fixed_income) && is_numeric($fixed_income) && $fixed_income > 0 ): ?>
                        <strong>Fixed Income for You:</strong> $<?php echo esc_html( number_format( (float)$fixed_income, 2 ) ); ?><br>
                    <?php endif; ?>
                    <?php if ( !empty($level_percentages) ): ?>
                        <strong>Level Income:</strong> Up to <?php echo count($level_percentages); ?> levels (<?php echo esc_html(implode('%, ', $level_percentages) . '%'); ?>)
                    <?php endif; ?>
                </p>

                <?php if ( is_user_logged_in() ): ?>
                    <a href="<?php echo esc_url( $buy_now_url ); ?>" class="button package-purchase-button repurchase-buy-now-button">
                        Buy Now
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url( $buy_now_url ); ?>" class="button package-purchase-button repurchase-buy-now-button">
                        Login to Buy
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php
// Add some basic styling specific to repurchase packages, if needed, or use existing .package-item styles.
// The styles from joining-packages-list.php are quite generic and should apply here too.
?>
