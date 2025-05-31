<?php
/**
 * Template for displaying Joining Packages.
 *
 * Expects $packages_query (a WP_Query object) to be available.
 */

if ( ! isset( $packages_query ) || ! $packages_query instanceof WP_Query ) {
    echo '<p>Error: Package data is not available.</p>';
    return;
}
?>

<div class="electric-scooter-packages-list">
    <?php while ( $packages_query->have_posts() ) : $packages_query->the_post(); ?>
        <?php
        $package_id = get_the_ID();
        $package_name = get_the_title();
        $package_amount = get_post_meta( $package_id, '_package_amount', true );
        $binary_income = get_post_meta( $package_id, '_binary_income_fixed', true );
        $direct_referral_income = get_post_meta( $package_id, '_direct_referral_income_fixed', true );
        $package_image_url = get_the_post_thumbnail_url( $package_id, 'medium' ); // 'medium' or other appropriate size
        $package_description = get_the_content(); // Using the main editor content for description

        // Construct purchase URL. This will redirect to registration with package_id pre-selected.
        // TODO: If user is logged in and has no package, this URL should go to a different purchase handler.
        $purchase_url_args = array( 'package_id' => $package_id );
        if (is_user_logged_in()) {
            // If user is logged in, maybe point to a profile/dashboard page for package selection
            // For now, let's assume they might be re-selecting or it's for new users primarily.
            // A more complex scenario would check if the logged-in user already has a package.
            // $purchase_url = add_query_arg( $purchase_url_args, home_url('/member-dashboard/select-package/') ); // Example URL
        }
        // Default to registration page for now.
        $purchase_url = add_query_arg( $purchase_url_args, home_url('/register/') ); // Assumes '/register/' is the registration page slug
                                                                                      // TODO: Make registration page slug configurable
        ?>
        <div class="package-item" id="package-<?php echo esc_attr( $package_id ); ?>">
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
                    <strong>Amount:</strong> $<?php echo esc_html( number_format( (float)$package_amount, 2 ) ); ?><br>
                    <strong>Binary Income:</strong> $<?php echo esc_html( number_format( (float)$binary_income, 2 ) ); ?><br>
                    <strong>Direct Referral Income:</strong> $<?php echo esc_html( number_format( (float)$direct_referral_income, 2 ) ); ?>
                </p>
                <a href="<?php echo esc_url( $purchase_url ); ?>" class="button package-purchase-button">
                    <?php echo is_user_logged_in() ? 'Select Package' : 'Purchase & Register'; ?>
                </a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<style>
/* Basic styling for package list - can be moved to the main CSS file */
.electric-scooter-packages-list {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.package-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    width: calc(33.333% - 20px); /* Adjust for gap, for 3 columns */
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden; /* Ensures image border radius is respected */
    display: flex;
    flex-direction: column;
}
@media (max-width: 992px) {
    .package-item {
        width: calc(50% - 10px); /* 2 columns on tablets */
    }
}
@media (max-width: 767px) {
    .package-item {
        width: 100%; /* 1 column on mobile */
    }
}
.package-item .package-image img {
    width: 100%;
    height: auto;
    display: block;
}
.package-item .package-details {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1; /* Allows button to stick to bottom */
}
.package-item .package-name {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.5em;
}
.package-item .package-description {
    font-size: 0.9em;
    color: #555;
    margin-bottom: 15px;
    flex-grow: 1; /* Pushes meta and button down */
}
.package-item .package-meta {
    font-size: 0.95em;
    margin-bottom: 15px;
    line-height: 1.6;
}
.package-item .package-purchase-button {
    display: inline-block;
    background-color: #0073aa;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
    text-align: center;
    margin-top: auto; /* Pushes button to bottom if description is short */
}
.package-item .package-purchase-button:hover {
    background-color: #005177;
}
</style>
