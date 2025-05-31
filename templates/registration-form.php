<?php
/**
 * Registration form template
 */

// Call the registration handler function from the public class
// This ensures errors or success messages are displayed above the form
if ( class_exists( 'Electric_Scooter_Plugin_Public' ) ) {
    // Note: The actual handle_registration() is hooked to 'init', so it runs before this template is loaded.
    // If there were errors stored by handle_registration (e.g., in a transient or session), they could be displayed here.
    // For now, handle_registration echoes errors directly if it doesn't return early on success.
}

// Check if a package_id is passed via URL (e.g., from a package listing page)
$pre_selected_package_id = isset( $_GET['package_id'] ) ? intval( $_GET['package_id'] ) : 0;
$package_name_display = null;
$package_amount_display = null;

if ( $pre_selected_package_id && get_post_type( $pre_selected_package_id ) === 'joining_package' ) {
    $package_post_display = get_post( $pre_selected_package_id );
    if($package_post_display) {
        $package_name_display = $package_post_display->post_title;
        $package_amount_display = get_post_meta( $pre_selected_package_id, '_package_amount', true );
    } else {
        $pre_selected_package_id = 0; // Reset if post not found
    }
}
?>

<div id="electric-scooter-registration-form">
    <form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); // Post to current URL to process registration ?>">
        <p>
            <label for="username">Username <span class="required">*</span></label>
            <input type="text" name="username" id="username" value="<?php echo isset( $_POST['username'] ) ? esc_attr( $_POST['username'] ) : ''; ?>" required>
        </p>
        <p>
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" name="email" id="email" value="<?php echo isset( $_POST['email'] ) ? esc_attr( $_POST['email'] ) : ''; ?>" required>
        </p>
        <p>
            <label for="password">Password <span class="required">*</span></label>
            <input type="password" name="password" id="password" required>
        </p>
        <p>
            <label for="referral_id">Referral ID (Optional)</label>
            <input type="text" name="referral_id" id="referral_id" value="<?php echo isset( $_POST['referral_id'] ) ? esc_attr( $_POST['referral_id'] ) : (isset($_GET['ref']) ? esc_attr($_GET['ref']) : ''); ?>">
        </p>

        <?php if ( $pre_selected_package_id && $package_name_display ) : ?>
            <div class="selected-package-info" style="padding: 10px; border: 1px solid #ddd; margin-bottom: 15px; background-color: #f9f9f9;">
                <h3>Selected Package</h3>
                <p><strong><?php echo esc_html( $package_name_display ); ?></strong></p>
                <p>Amount: $<?php echo esc_html( number_format( (float)$package_amount_display, 2) ); ?></p>
                <input type="hidden" name="package_id" value="<?php echo esc_attr( $pre_selected_package_id ); ?>">
            </div>
        <?php else : ?>
            <?php
                // Optional: If no package is pre-selected, you could offer a dropdown list of available packages.
                // $all_packages_args = array(
                //     'post_type' => 'joining_package',
                //     'post_status' => 'publish',
                //     'posts_per_page' => -1,
                // );
                // $all_packages = new WP_Query( $all_packages_args );
                // if ( $all_packages->have_posts() ) :
            ?>
                <!-- <p>
                    <label for="package_id_select">Select a Package (Optional)</label>
                    <select name="package_id" id="package_id_select">
                        <option value="">-- None --</option>
                        <?php //while ( $all_packages->have_posts() ) : $all_packages->the_post(); ?>
                            <?php //$pkg_id = get_the_ID(); $pkg_name = get_the_title(); $pkg_amount = get_post_meta( $pkg_id, '_package_amount', true ); ?>
                            //<option value="<?php //echo esc_attr($pkg_id); ?>"><?php //echo esc_html($pkg_name . ' ($' . number_format((float)$pkg_amount, 2) . ')'); ?></option>
                        <?php //endwhile; ?>
                    </select>
                </p> -->
            <?php
                // wp_reset_postdata();
                // endif;
                // For now, if no package is in URL, it means user registers without one or needs to select from a list.
                // The current logic in handle_registration sets status to 'pending_package'.
            ?>
             <p><em>If no package is selected, you can choose one from your dashboard after registration.</em></p>
        <?php endif; ?>

        <p>
            <input type="submit" name="submit_registration" value="Register">
        </p>
    </form>
</div>
