<?php
/**
 * Login form template
 */

// Call the login handler function from the public class
// This ensures errors are displayed above the form if login fails
if ( class_exists( 'Electric_Scooter_Plugin_Public' ) && !is_user_logged_in()) {
    $public_class = new Electric_Scooter_Plugin_Public( 'electric-scooter-plugin', '1.0.0' );
    // Display errors if any (e.g., from a failed login attempt)
    // The actual handle_login() is hooked to 'init' so it processes before headers.
    // We might need a way to pass error messages if not redirecting.
    // For now, wp_signon errors are typically shown on wp-login.php or need custom handling to display here.
    // The current handle_login redirects on success or echoes error *then exits*.
    // Let's adjust handle_login to store errors for display here.
}

if ( is_user_logged_in() ) {
    echo '<p>You are already logged in. <a href="' . wp_logout_url( home_url() ) . '">Logout</a></p>';
    // Optionally, show a link to the dashboard
    echo '<p><a href="' . home_url('/member-dashboard/') . '">Go to Dashboard</a></p>'; // TODO: Make dashboard URL configurable
    return;
}
?>

<div id="electric-scooter-login-form">
    <?php
    // Display login errors if any were stored by handle_login
    if ( isset( $_SESSION['electric_scooter_login_error'] ) ) {
        echo '<div class="error"><p>' . esc_html( $_SESSION['electric_scooter_login_error'] ) . '</p></div>';
        unset( $_SESSION['electric_scooter_login_error'] ); // Clear the error message
    }
    ?>
    <form name="loginform" id="loginform" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
        <p>
            <label for="user_login">Username or Email Address<span class="required">*</span></label>
            <input type="text" name="log" id="user_login" class="input" value="<?php echo isset( $_POST['log'] ) ? esc_attr( $_POST['log'] ) : ''; ?>" size="20" required />
        </p>
        <p>
            <label for="user_pass">Password<span class="required">*</span></label>
            <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" required />
        </p>
        <p class="login-remember">
            <label><input name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked( isset( $_POST['rememberme'] ) ); ?> /> Remember Me</label>
        </p>
        <p class="login-submit">
            <input type="submit" name="submit_login" id="wp-submit" class="button button-primary" value="Log In" />
            <?php // Add a hidden field for the redirect, though wp_signon handles redirect on success if not specified or if using wp_redirect ?>
        </p>
        <?php // Add lost password link? Add registration link? ?>
        <p>
            <a href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a>
        </p>
        <?php
        // Example: Link to registration page if you have one
        // $registration_page_url = home_url('/register/'); // Replace with your registration page slug
        // echo '<p>Don\'t have an account? <a href="' . esc_url( $registration_page_url ) . '">Register</a></p>';
        ?>
    </form>
</div>
