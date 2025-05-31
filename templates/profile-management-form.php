<?php
/**
 * Profile Management Form template
 */

if ( ! is_user_logged_in() ) {
    echo '<p>You must be logged in to view this page.</p>';
    return;
}

$user_id = get_current_user_id();
$current_user = wp_get_current_user();

// Retrieve success messages
$success_message = get_transient( 'electric_scooter_profile_success_' . $user_id );
if ( $success_message ) {
    echo '<div class="success"><p>' . esc_html( $success_message ) . '</p></div>';
    delete_transient( 'electric_scooter_profile_success_' . $user_id );
}

// Retrieve error messages
$errors = get_transient( 'electric_scooter_profile_errors_' . $user_id );
if ( $errors ) {
    echo '<div class="error">';
    foreach ( $errors as $error ) {
        echo '<p>' . esc_html( $error ) . '</p>';
    }
    echo '</div>';
    delete_transient( 'electric_scooter_profile_errors_' . $user_id );
}

// Get current user meta
$first_name = get_user_meta( $user_id, 'first_name', true );
$last_name = get_user_meta( $user_id, 'last_name', true );
$phone_number = get_user_meta( $user_id, 'phone_number', true );
$address_street = get_user_meta( $user_id, 'address_street', true );
$address_city = get_user_meta( $user_id, 'address_city', true );
$address_pin_code = get_user_meta( $user_id, 'address_pin_code', true );

// KYC Status
$kyc_status = get_user_meta( $user_id, 'kyc_status', true );
if(empty($kyc_status)){
    $kyc_status = "Not Submitted";
}

// Member ID
$member_unique_id = get_user_meta( $user_id, 'member_unique_id', true );

// Referral Code & Link
$user_referral_code = get_user_meta( $user_id, 'referral_code', true );
$referral_link = '';
if ( $user_referral_code ) {
    // TODO: Make the registration page slug ('/register/') configurable via plugin settings
    $registration_page_slug = '/register/';
    $referral_link = esc_url( add_query_arg( 'ref', $user_referral_code, home_url( $registration_page_slug ) ) );
}

// Member Status & Package Info
$member_status = get_user_meta( $user_id, 'member_status', true );
$purchased_package_id = get_user_meta( $user_id, 'purchased_joining_package_id', true );
$package_name_display = 'N/A';
$package_purchase_date_display = '';

if ( $purchased_package_id && get_post_type( $purchased_package_id ) === 'joining_package' ) {
    $package_post_display = get_post( $purchased_package_id );
    if($package_post_display){
        $package_name_display = $package_post_display->post_title;
        $purchase_date_meta = get_user_meta( $user_id, 'joining_package_purchase_date', true );
        if($purchase_date_meta) {
            $package_purchase_date_display = date( 'Y-m-d', strtotime( $purchase_date_meta ) );
        }
    }
}

?>

<div id="electric-scooter-profile-form">

    <div class="profile-section">
        <h2>Membership Details</h2>
        <p>
            <label>Member ID:</label>
            <span><?php echo esc_html( $member_unique_id ? $member_unique_id : 'N/A' ); ?></span>
        </p>
        <p>
            <label>Status:</label>
            <span><?php echo esc_html( ucfirst( $member_status ? $member_status : 'N/A' ) ); ?></span>
        </p>
        <p>
            <label>Current Package:</label>
            <span>
                <?php echo esc_html( $package_name_display ); ?>
                <?php if ( $package_purchase_date_display ) : ?>
                    (Purchased: <?php echo esc_html( $package_purchase_date_display ); ?>)
                <?php endif; ?>
            </span>
        </p>
        <?php if ( $member_status === 'pending_package' && !$purchased_package_id ) : ?>
            <p>
                <a href="<?php echo esc_url( home_url('/joining-packages/') ); // TODO: Make package listing page slug configurable ?>" class="button">
                    Select Your Joining Package
                </a>
            </p>
        <?php endif; ?>

        <?php if ( $referral_link ): ?>
        <p>
            <label>Your Referral Link:</label>
            <input type="text" readonly value="<?php echo esc_attr( $referral_link ); ?>" onclick="this.select();" style="width:100%; cursor:pointer; background:#f0f0f0;">
            <em>Share this link to refer new members!</em>
        </p>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h2>Earnings Summary</h2>
        <?php
        $total_direct_referral_earnings = get_user_meta( $user_id, 'total_direct_referral_earnings', true );
        $total_binary_earnings = get_user_meta( $user_id, 'total_binary_earnings', true );
        $total_repurchase_fixed_earnings = get_user_meta( $user_id, 'total_repurchase_fixed_earnings', true );
        $total_repurchase_level_earnings = get_user_meta( $user_id, 'total_repurchase_level_earnings', true );
        ?>
        <p><label>Total Direct Referral Earnings:</label> $<?php echo esc_html( number_format( (float)($total_direct_referral_earnings ?: 0), 2 ) ); ?></p>
        <p><label>Total Binary Earnings:</label> $<?php echo esc_html( number_format( (float)($total_binary_earnings ?: 0), 2 ) ); ?></p>
        <p><label>Total Repurchase Fixed Earnings:</label> $<?php echo esc_html( number_format( (float)($total_repurchase_fixed_earnings ?: 0), 2 ) ); ?></p>
        <p><label>Total Repurchase Level Earnings:</label> $<?php echo esc_html( number_format( (float)($total_repurchase_level_earnings ?: 0), 2 ) ); ?></p>
        <?php
        $total_fast_track_earnings = get_user_meta( $user_id, 'total_fast_track_earnings', true );
        $total_reward_income_earnings = get_user_meta( $user_id, 'total_reward_income_earnings', true );
        ?>
        <p><label>Total Fast Track Earnings:</label> $<?php echo esc_html( number_format( (float)($total_fast_track_earnings ?: 0), 2 ) ); ?></p>
        <p><label>Total Reward Income Earnings:</label> $<?php echo esc_html( number_format( (float)($total_reward_income_earnings ?: 0), 2 ) ); ?></p>
    </div>

    <?php
    $achieved_fast_track_tiers_ids = get_user_meta( $user_id, '_achieved_fast_track_tiers', true );
    if ( !empty($achieved_fast_track_tiers_ids) && is_array($achieved_fast_track_tiers_ids) ) :
    ?>
    <div class="profile-section">
        <h2>Achieved Fast Track Bonuses</h2>
        <ul class="electric-scooter-list">
            <?php
            foreach ( $achieved_fast_track_tiers_ids as $tier_id ) {
                $tier_post = get_post($tier_id);
                if ($tier_post && $tier_post->post_type === 'fast_track_tier') {
                    $tier_name = $tier_post->post_title;
                    $tier_income = get_post_meta($tier_id, '_income_amount', true);
                    // Date of achievement is not explicitly stored per tier,
                    // but could be added to the '_achieved_fast_track_tiers' meta array if needed.
                    echo '<li>' . esc_html($tier_name) . ' - Bonus: $' . esc_html(number_format((float)$tier_income, 2)) . '</li>';
                }
            }
            ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php
    $achieved_awards_display = get_user_meta( $user_id, '_achieved_awards', true );
    if ( !empty($achieved_awards_display) && is_array($achieved_awards_display) ) :
    ?>
    <div class="profile-section">
        <h2>Awards & Rewards Status</h2>
        <table class="electric-scooter-table" width="100%">
            <thead>
                <tr>
                    <th>Award/Rank</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date Qualified</th>
                    <th>Date Approved/Rejected</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // To display in configured order, we'd need to fetch all awards CPTs ordered by menu_order
                // and then check against $achieved_awards_display. For simplicity, just iterating meta for now.
                // This won't show them in the defined sequence if achieved out of order (which current logic prevents for 'pending').
                foreach ($achieved_awards_display as $award_id => $award_data) :
                    $award_post_title = isset($award_data['award_title']) ? $award_data['award_title'] : 'Award ID: ' . $award_id;
                    $award_cpt = get_post($award_id);
                    $award_description_display = $award_cpt ? $award_cpt->post_content : 'N/A'; // Get description from CPT post_content
                ?>
                    <tr>
                        <td><?php echo esc_html($award_post_title); ?></td>
                        <td><?php echo wp_kses_post($award_description_display); ?></td>
                        <td>
                            <span class="award-status-<?php echo esc_attr($award_data['status']); ?>">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $award_data['status']))); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(isset($award_data['date_qualified']) ? date('Y-m-d', strtotime($award_data['date_qualified'])) : 'N/A'); ?></td>
                        <td>
                            <?php
                            if (isset($award_data['date_approved'])) echo esc_html(date('Y-m-d', strtotime($award_data['date_approved'])));
                            elseif (isset($award_data['date_rejected'])) echo esc_html(date('Y-m-d', strtotime($award_data['date_rejected'])));
                            else echo 'N/A';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>


    <?php
    $user_repurchases = get_user_meta( $user_id, '_user_repurchases', true );
    if ( !empty($user_repurchases) && is_array($user_repurchases) ) :
    ?>
    <div class="profile-section">
        <h2>Repurchase History</h2>
        <table class="electric-scooter-table" width="100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Package Name</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( array_reverse($user_repurchases) as $repurchase ) : // Show newest first ?>
                    <tr>
                        <td><?php echo esc_html( date( 'Y-m-d H:i', strtotime( $repurchase['date'] ) ) ); ?></td>
                        <td><?php echo esc_html( $repurchase['package_name'] ); ?></td>
                        <td>$<?php echo esc_html( number_format( (float)$repurchase['price'], 2 ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( remove_query_arg(array('profile_updated', 'error', 'login_error', 'repurchase_success')) ); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="electric_scooter_profile_update">
        <?php wp_nonce_field( 'electric_scooter_profile_update_action', '_wpnonce_profile_update' ); ?>

        <h2>Personal Information</h2>
        <div class="profile-section">
            <p>
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $first_name ); ?>">
            </p>
            <p>
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $last_name ); ?>">
            </p>
            <p>
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" name="email" id="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
            </p>
            <p>
                <label for="phone_number">Phone Number</label>
                <input type="tel" name="phone_number" id="phone_number" value="<?php echo esc_attr( $phone_number ); ?>">
            </p>
        </div>

        <h2>Address</h2>
        <div class="profile-section">
            <p>
                <label for="address_street">Street Address</label>
                <input type="text" name="address_street" id="address_street" value="<?php echo esc_attr( $address_street ); ?>">
            </p>
            <p>
                <label for="address_city">City</label>
                <input type="text" name="address_city" id="address_city" value="<?php echo esc_attr( $address_city ); ?>">
            </p>
            <p>
                <label for="address_pin_code">Pin Code</label>
                <input type="text" name="address_pin_code" id="address_pin_code" value="<?php echo esc_attr( $address_pin_code ); ?>">
            </p>
        </div>

        <h2>KYC Verification</h2>
        <div class="profile-section">
            <p>
                <label>Current KYC Status: </label>
                <span class="kyc-status <?php echo esc_attr( strtolower( $kyc_status ) ); ?>"><?php echo esc_html( ucfirst( $kyc_status ) ); ?></span>
            </p>
            <?php if ( $kyc_status !== 'Approved' ) : ?>
            <p>
                <label for="kyc_document">Upload Identity Document (JPG, PNG, PDF)</label>
                <input type="file" name="kyc_document" id="kyc_document" accept=".jpg,.jpeg,.png,.pdf">
            </p>
            <?php else: ?>
            <p>Your KYC documents have been approved.</p>
            <?php endif; ?>
        </div>

        <p>
            <input type="submit" name="submit_profile_update" value="Update Profile">
        </p>
    </form>
</div>
