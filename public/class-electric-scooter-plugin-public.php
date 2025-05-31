<?php

class Electric_Scooter_Plugin_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/css/electric-scooter-plugin-public.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/js/electric-scooter-plugin-public.js', array( 'jquery' ), $this->version, false );
    }

    // Shortcodes
    public function register_shortcode() {
        add_shortcode( 'electric_scooter_register', array( $this, 'render_registration_form' ) );
        add_shortcode( 'electric_scooter_login', array( $this, 'render_login_form' ) );
        add_shortcode( 'electric_scooter_profile', array( $this, 'render_profile_management_page' ) );
        add_shortcode( 'electric_scooter_binary_tree', array( $this, 'render_binary_tree_shortcode' ) );
        add_shortcode( 'electric_scooter_joining_packages', array( $this, 'render_joining_packages_shortcode' ) );
        add_shortcode( 'electric_scooter_repurchase_packages', array( $this, 'render_repurchase_packages_shortcode' ) );
    }

    public function render_registration_form() {
        ob_start();
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/registration-form.php';
        return ob_get_clean();
    }

    public function handle_registration() {
        if ( isset( $_POST['submit_registration'] ) ) {
            // Sanitize and validate input
            $username = sanitize_user( $_POST['username'] );
            $email = sanitize_email( $_POST['email'] );
            $password = $_POST['password']; // WordPress handles password hashing
            // The 'referral_id' from the form is if the user MANUALLY types a code.
            // We also need to check for a 'ref' URL parameter.
            $manual_referral_code = isset($_POST['referral_id']) ? sanitize_text_field( $_POST['referral_id'] ) : '';
            $url_referral_code = isset($_GET['ref']) ? sanitize_text_field( $_GET['ref'] ) : '';
            $active_referral_code = !empty($url_referral_code) ? $url_referral_code : $manual_referral_code;


            $errors = array();

            if ( empty( $username ) ) {
                $errors[] = "Username is required.";
            }
            if ( username_exists( $username ) ) {
                $errors[] = "Username already exists.";
            }
            if ( ! is_email( $email ) ) {
                $errors[] = "Invalid email address.";
            }
            if ( email_exists( $email ) ) {
                $errors[] = "Email address already registered.";
            }
            if ( empty( $password ) ) {
                $errors[] = "Password is required.";
            }
            // Add more password strength validation if needed

            if ( empty( $errors ) ) {
                $user_id = wp_create_user( $username, $password, $email );

                if ( is_wp_error( $user_id ) ) {
                    // Registration failed
                    echo '<div class="error">Registration failed: ' . $user_id->get_error_message() . '</div>';
                } else {
                    // Set custom role
                    $user = new WP_User( $user_id );
                    $user->set_role( 'member' );

                    // Generate and store unique Member ID
                    // Example: ESMEMBER-USERID-RANDOMSUFFIX
                    // For a simpler sequential ID, we might need a global option counter (more complex)
                    // Using user ID with a prefix is common and ensures uniqueness.
                    $member_unique_id = 'ESMEMBER-' . $user_id . '-' . substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
                    update_user_meta( $user_id, 'member_unique_id', $member_unique_id );

                    // Generate and store unique Referral Code for this new user
                    $new_user_referral_code = substr(md5( $user_id . time() . rand() ), 0, 10); // Example generation
                    update_user_meta( $user_id, 'referral_code', $new_user_referral_code );

                    // Check and store referrer information if 'active_referral_code' is present and valid
                    $referred_by_user_id = 0;
                    if ( ! empty( $active_referral_code ) ) {
                        // Find user by their referral_code
                        $referring_users = get_users( array(
                            'meta_key'   => 'referral_code',
                            'meta_value' => $active_referral_code,
                            'number'     => 1,
                            'fields'     => 'ID',
                        ) );
                        if ( ! empty( $referring_users ) ) {
                            $referred_by_user_id = $referring_users[0];
                            update_user_meta( $user_id, 'referred_by_user_id', $referred_by_user_id );
                            // Optional: Store a list of referred users in the referrer's meta
                            // add_user_meta( $referred_by_user_id, 'referred_user_id', $user_id );

                            // --- Binary Placement Logic ---
                            // Place the new user ($user_id) under the referrer ($referred_by_user_id)
                            $left_child_id = get_user_meta( $referred_by_user_id, 'binary_left_child_id', true );
                            $right_child_id = get_user_meta( $referred_by_user_id, 'binary_right_child_id', true );

                            if ( empty( $left_child_id ) ) {
                                update_user_meta( $referred_by_user_id, 'binary_left_child_id', $user_id );
                                update_user_meta( $user_id, 'binary_parent_id', $referred_by_user_id );
                                update_user_meta( $user_id, 'binary_position', 'L' );
                            } elseif ( empty( $right_child_id ) ) {
                                update_user_meta( $referred_by_user_id, 'binary_right_child_id', $user_id );
                                update_user_meta( $user_id, 'binary_parent_id', $referred_by_user_id );
                                update_user_meta( $user_id, 'binary_position', 'R' );
                            } else {
                                // Both direct children spots are full. Mark user as pending placement.
                                // This logic will need to be expanded for spillover or manual placement.
                                update_user_meta( $user_id, 'binary_placement_pending', true );
                                // For now, also set binary_parent_id to the sponsor, even if pending direct placement.
                                // The actual position under this sponsor will be determined by spillover rules later.
                                update_user_meta( $user_id, 'binary_parent_id', $referred_by_user_id );
                                // TODO: Add admin notification or UI for pending placements.
                            }
                            // --- End Binary Placement Logic ---

                        } else {
                            // Optional: Handle invalid referral code (e.g. log it, or add to $errors before user creation)
                            // For now, we'll let registration proceed but not link a referrer.
                            // Users without a referrer (or invalid code) are "orphans" in the binary tree until placed by admin.
                            update_user_meta( $user_id, 'binary_placement_pending', true );
                            // TODO: Add admin notification or UI for pending placements for orphans.
                        }
                    } else {
                         // User registered without a referral code - also pending placement.
                        update_user_meta( $user_id, 'binary_placement_pending', true );
                        // TODO: Add admin notification or UI for pending placements for direct sign-ups.
                    }

                    // Handle Package Purchase for New User
                    $selected_package_id = isset( $_REQUEST['package_id'] ) ? intval( $_REQUEST['package_id'] ) : 0; // Can come from GET (link) or POST (form submission)
                    if ( $selected_package_id && get_post_type( $selected_package_id ) === 'joining_package' ) {
                        update_user_meta( $user_id, 'purchased_joining_package_id', $selected_package_id );
                        update_user_meta( $user_id, 'joining_package_purchase_date', current_time( 'mysql' ) );
                        update_user_meta( $user_id, 'member_status', 'active' ); // Set member status

                        // Income disbursement
                        $this->handle_direct_referral_income( $user_id, $selected_package_id, $referred_by_user_id );
                        // Binary income is dependent on placement, which happens just before this block in handle_registration
                        $this->handle_binary_income( $user_id, $selected_package_id );
                    } else {
                        // No package selected or invalid package ID during registration
                        update_user_meta( $user_id, 'member_status', 'pending_package' );
                    }

                    // Display success message and summary
                    echo '<div class="success">';
                    echo '<h2>Registration Successful!</h2>';
                    echo '<p>Welcome, ' . esc_html( $username ) . '!</p>';
                    echo '<p>Your Member ID is: ' . esc_html( $member_unique_id ) . '</p>';
                    echo '<p>Your personal referral code is: ' . esc_html( $new_user_referral_code ) . '</p>';
                    if ( $referred_by_user_id ) {
                        $referrer_data = get_userdata($referred_by_user_id);
                        echo '<p>You were referred by: ' . esc_html( $referrer_data->display_name ) . '</p>';
                    } elseif ( !empty($active_referral_code) ) {
                        echo '<p>The referral code entered (' . esc_html( $active_referral_code ) . ') was not valid.</p>';
                    }
                    echo '<p>Your email is: ' . esc_html( $email ) . '</p>';
                    echo '<p>You can now log in.</p>';
                    echo '</div>';
                    // Optionally, redirect to login page or dashboard
                    return; // Stop form rendering
                }
            } else {
                // Display errors
                echo '<div class="error">';
                foreach ( $errors as $error ) {
                    echo '<p>' . esc_html( $error ) . '</p>';
                }
                echo '</div>';
            }
        }
    }

    public function render_login_form() {
        ob_start();
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/login-form.php';
        return ob_get_clean();
    }

    public function handle_login() {
        if ( isset( $_POST['submit_login'] ) ) {
            $username = sanitize_user( $_POST['log'] );
            $password = $_POST['pwd'];
            $remember = isset( $_POST['rememberme'] );

            $creds = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => $remember,
            );

            $user = wp_signon( $creds, false );

            if ( is_wp_error( $user ) ) {
                // Redirect back to the login page with an error code
                $login_page_url = home_url( $_POST['_wp_http_referer'] ); // Get URL of the page where form was submitted
                // Remove any existing query params to avoid messy URLs
                $login_page_url = strtok( $login_page_url, '?' );
                $error_code = $user->get_error_code(); // Get a code for the error
                wp_redirect( add_query_arg( 'login_error', $error_code, $login_page_url ) );
                exit;
            } else {
                // Successful login
                $redirect_url = home_url( '/member-dashboard/' ); // TODO: Make redirect URL configurable
                // Allow filtering of the redirect URL
                $redirect_url = apply_filters( 'electric_scooter_login_redirect', $redirect_url, $user );
                wp_redirect( $redirect_url );
                exit;
            }
        }
    }

    public function render_profile_management_page() {
        if ( ! is_user_logged_in() ) {
            // Users must be logged in to see their profile.
            // Optionally, show a login form or a message.
            // For now, redirect to login page, assuming one exists with slug 'login'
            // Or better, use the shortcode for the login form itself.
            $login_page_url = home_url('/login/'); // TODO: Make this URL configurable or use a settings page
            return '<p>You must be logged in to view your profile. <a href="' . esc_url( $login_page_url ) . '?redirect_to=' . urlencode( get_permalink() ) . '">Login</a></p>' . do_shortcode('[electric_scooter_login]');
        }

        ob_start();
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/profile-management-form.php';
        return ob_get_clean();
    }

    public function handle_profile_update() {
        if ( ! is_user_logged_in() ) {
            return; // Should not happen if form is protected
        }

        if ( isset( $_POST['submit_profile_update'] ) ) {
            // Verify nonce for security
            if ( ! isset( $_POST['_wpnonce_profile_update'] ) || ! wp_verify_nonce( $_POST['_wpnonce_profile_update'], 'electric_scooter_profile_update_action' ) ) {
                wp_die( 'Security check failed. Please try again.' );
            }

            $user_id = get_current_user_id();
            $errors = array();
            $success_message = '';

            // Update First Name
            if ( isset( $_POST['first_name'] ) ) {
                update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
            }

            // Update Last Name
            if ( isset( $_POST['last_name'] ) ) {
                update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
            }

            // Update Email
            // Changing email requires more care, potentially a confirmation process.
            // For now, we'll update it directly if it's different and valid.
            if ( isset( $_POST['email'] ) ) {
                $new_email = sanitize_email( $_POST['email'] );
                $current_user = wp_get_current_user();
                if ( $new_email !== $current_user->user_email ) {
                    if ( !is_email( $new_email ) ) {
                        $errors[] = "Invalid email address provided.";
                    } elseif ( email_exists( $new_email ) && $new_email !== $current_user->user_email ) {
                        $errors[] = "This email address is already in use by another account.";
                    } else {
                        // Update the user's email in wp_users table
                        // This is a core field, not just meta.
                        wp_update_user( array( 'ID' => $user_id, 'user_email' => $new_email ) );
                    }
                }
            }

            // Update Phone Number
            if ( isset( $_POST['phone_number'] ) ) {
                // Basic sanitization, can be improved with regex for specific formats
                update_user_meta( $user_id, 'phone_number', sanitize_text_field( $_POST['phone_number'] ) );
            }

            // Update Address (Street, City, Pin Code)
            if ( isset( $_POST['address_street'] ) ) {
                update_user_meta( $user_id, 'address_street', sanitize_text_field( $_POST['address_street'] ) );
            }
            if ( isset( $_POST['address_city'] ) ) {
                update_user_meta( $user_id, 'address_city', sanitize_text_field( $_POST['address_city'] ) );
            }
            if ( isset( $_POST['address_pin_code'] ) ) {
                update_user_meta( $user_id, 'address_pin_code', sanitize_text_field( $_POST['address_pin_code'] ) );
            }

            // Handle KYC Document Upload
            if ( isset( $_FILES['kyc_document'] ) && $_FILES['kyc_document']['error'] === UPLOAD_ERR_OK ) {
                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }

                // WordPress's upload directory data
                $upload_dir = wp_upload_dir();
                // Custom subdirectory for KYC documents, organized by user ID
                $kyc_upload_path = $upload_dir['basedir'] . '/electric_scooter_kyc/' . $user_id;
                wp_mkdir_p( $kyc_upload_path ); // Ensure the directory exists

                $uploaded_file = $_FILES['kyc_document'];

                // Allowed file types
                $allowed_mime_types = array(
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'png'          => 'image/png',
                    'pdf'          => 'application/pdf',
                );

                // Override WordPress's default upload filters
                $upload_overrides = array(
                    'test_form' => false, // Important for security
                    'mimes'     => $allowed_mime_types,
                );

                // Handle the upload
                $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

                if ( $movefile && ! isset( $movefile['error'] ) ) {
                    // File is uploaded successfully.
                    // $movefile contains the path and url of the uploaded file.
                    update_user_meta( $user_id, 'kyc_document_path', $movefile['file'] );
                    update_user_meta( $user_id, 'kyc_document_url', $movefile['url'] ); // Store URL if direct access is needed (be cautious)
                    update_user_meta( $user_id, 'kyc_status', 'Pending' ); // Set status to Pending

                    // Optionally, remove old file if one existed and is being replaced
                    // $old_file_path = get_user_meta($user_id, 'kyc_document_path', true);
                    // if ($old_file_path && $old_file_path !== $movefile['file'] && file_exists($old_file_path)) {
                    //     wp_delete_file($old_file_path);
                    // }

                } else {
                    // File upload failed. $movefile['error'] contains the error message.
                    $errors[] = "KYC Document Upload Failed: " . esc_html( $movefile['error'] );
                }
            } elseif ( isset( $_FILES['kyc_document'] ) && $_FILES['kyc_document']['error'] !== UPLOAD_ERR_NO_FILE ) {
                // Other upload errors (e.g., file too large as per server/PHP settings)
                $upload_error_codes = array(
                    UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                    UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                    UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
                );
                $error_code = $_FILES['kyc_document']['error'];
                $errors[] = "KYC Document Upload Error: " . ( $upload_error_codes[$error_code] ?? 'Unknown upload error.' );
            }


            if ( empty( $errors ) ) {
                $success_message = "Profile updated successfully!";
                // Store success message in a transient to display after redirect (Post/Redirect/Get pattern)
                set_transient( 'electric_scooter_profile_success_' . $user_id, $success_message, 60 ); // Expires in 60 seconds
            } else {
                // Store errors in a transient
                set_transient( 'electric_scooter_profile_errors_' . $user_id, $errors, 60 );
            }

            // Redirect back to the profile page to prevent form resubmission issues
            wp_redirect( get_permalink() );
            exit;
        }
    }

    /**
     * Handles the [electric_scooter_binary_tree] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output for the binary tree.
     */
    public function render_binary_tree_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'user_id' => '', // Allow specifying a user ID for admins
            'max_levels' => 3, // Default levels to show
        ), $atts, 'electric_scooter_binary_tree' );

        $display_user_id = 0;

        if ( ! empty( $atts['user_id'] ) && current_user_can( 'manage_options' ) ) { // manage_options for admin
            $display_user_id = intval( $atts['user_id'] );
        } elseif ( is_user_logged_in() ) {
            $display_user_id = get_current_user_id();
        } else {
            return '<p>You must be logged in to view the binary tree. Or, if you are an admin, specify a user_id.</p>';
        }

        if ( ! $display_user_id || ! get_userdata( $display_user_id ) ) {
            return '<p>Invalid user ID specified or user does not exist.</p>';
        }

        // Check if user is placed
        $is_pending_placement = get_user_meta( $display_user_id, 'binary_placement_pending', true );
        if ( $is_pending_placement && empty(get_user_meta( $display_user_id, 'binary_left_child_id', true )) && empty(get_user_meta( $display_user_id, 'binary_right_child_id', true )) ) {
            // If they are pending and have no children, they are truly not in the tree structure yet.
            // However, if they are pending but HAVE children, it means their upline is full, but they act as a root for their own downline.
            // The definition of "pending" might need refinement. For now, if they are marked pending, we show a message.
             $placement_status_message = 'This user is currently pending placement in the binary tree.';
             // Allow admins to see the tree even if pending, as they might be building it.
             if (!current_user_can('manage_options')) {
                 return '<p>' . $placement_status_message . '</p>';
             }
        }


        $tree_data = $this->get_binary_tree_data( $display_user_id, intval($atts['max_levels']) );

        ob_start();
        // Pass data to a template file
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/binary-tree.php';
        return ob_get_clean();
    }

    /**
     * Recursively fetches binary tree data for a user.
     *
     * @param int $user_id The ID of the user to fetch the tree for.
     * @param int $max_levels The maximum number of levels to fetch.
     * @param int $current_level The current level of recursion.
     * @return array Tree data.
     */
    private function get_binary_tree_data( $user_id, $max_levels, $current_level = 0 ) {
        if ( $current_level >= $max_levels || ! $user_id ) {
            return null;
        }

        $user_data = get_userdata( $user_id );
        if ( ! $user_data ) {
            return null;
        }

        $node = array(
            'user_id'    => $user_id,
            'member_id'  => get_user_meta( $user_id, 'member_unique_id', true ),
            'username'   => $user_data->user_login,
            'display_name' => $user_data->display_name,
            'join_date'  => date( 'Y-m-d', strtotime( $user_data->user_registered ) ),
            'position'   => get_user_meta( $user_id, 'binary_position', true ), // L or R relative to its parent
            // 'package_name' => get_user_meta( $user_id, 'active_package_name', true ), // TODO: Implement package system
            'left_child' => null,
            'right_child'=> null,
            'level'      => $current_level,
            'is_pending_placement' => get_user_meta($user_id, 'binary_placement_pending', true),
        );

        $left_child_id = get_user_meta( $user_id, 'binary_left_child_id', true );
        if ( $left_child_id ) {
            $node['left_child'] = $this->get_binary_tree_data( $left_child_id, $max_levels, $current_level + 1 );
        }

        $right_child_id = get_user_meta( $user_id, 'binary_right_child_id', true );
        if ( $right_child_id ) {
            $node['right_child'] = $this->get_binary_tree_data( $right_child_id, $max_levels, $current_level + 1 );
        }

        // If user has no children yet, but might be a starting point for an admin view
        if (empty($left_child_id) && empty($right_child_id) && $current_level === 0 && $max_levels > 0) {
            // This ensures the root node is always returned even if it has no children.
        }


        return $node;
    }

    /**
     * Handles the [electric_scooter_joining_packages] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output for the list of joining packages.
     */
    public function render_joining_packages_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            // No attributes needed for now, but can be added later (e.g., category, limit)
        ), $atts, 'electric_scooter_joining_packages' );

        $args = array(
            'post_type' => 'joining_package',
            'post_status' => 'publish',
            'posts_per_page' => -1, // Display all packages
            'orderby' => 'menu_order title', // Can order by menu_order then title
            'order' => 'ASC',
        );
        $packages_query = new WP_Query( $args );

        ob_start();
        if ( $packages_query->have_posts() ) {
            // Pass data to a template file
            // Make $packages_query available to the template
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/joining-packages-list.php';
        } else {
            echo '<p>No joining packages available at the moment.</p>';
        }
        wp_reset_postdata(); // Important after custom WP_Query
        return ob_get_clean();
    }

    /**
     * Handles direct referral income disbursement.
     *
     * @param int $new_user_id User ID of the newly registered user.
     * @param int $package_id Package ID purchased by the new user.
     * @param int $sponsor_id User ID of the sponsor.
     */
    private function handle_direct_referral_income( $new_user_id, $package_id, $sponsor_id ) {
        if ( ! $sponsor_id || ! $package_id ) {
            return; // No sponsor or no package, so no direct referral income.
        }

        $direct_income_amount = get_post_meta( $package_id, '_direct_referral_income_fixed', true );

        if ( is_numeric( $direct_income_amount ) && $direct_income_amount > 0 ) {
            $current_sponsor_earnings = get_user_meta( $sponsor_id, 'total_direct_referral_earnings', true );
            if ( empty( $current_sponsor_earnings ) || ! is_numeric( $current_sponsor_earnings ) ) {
                $current_sponsor_earnings = 0;
            }
            $new_total_earnings = $current_sponsor_earnings + $direct_income_amount;
            update_user_meta( $sponsor_id, 'total_direct_referral_earnings', $new_total_earnings );

            // Optional: Log this transaction (e.g., in a custom table or as user meta on the sponsor/new user)
            // add_user_meta( $sponsor_id, 'direct_referral_log', array(
            // 'date' => current_time('mysql'),
            // 'amount' => $direct_income_amount,
            // 'from_user_id' => $new_user_id,
            // 'package_id' => $package_id
            // ));
        }
    }

    // TODO: Implement handle_binary_income() in a subsequent step.

    /**
     * Updates the leg counts for all upline ancestors.
     *
     * @param int $user_id The ID of the user who was just added/activated.
     * @param int $max_depth Maximum levels to traverse upwards.
     */
    private function update_upline_leg_counts( $user_id, $max_depth = 20 ) { // Max depth to prevent infinite loops in case of data error
        $current_user_id = $user_id;
        $count = 0;

        while ( $current_user_id && $count < $max_depth ) {
            $parent_id = get_user_meta( $current_user_id, 'binary_parent_id', true );
            if ( ! $parent_id ) {
                break; // No more parents
            }

            $position_of_current_user = get_user_meta( $current_user_id, 'binary_position', true );
            if ( $position_of_current_user === 'L' ) {
                $current_leg_count = get_user_meta( $parent_id, 'binary_left_leg_active_members', true );
                update_user_meta( $parent_id, 'binary_left_leg_active_members', (int)$current_leg_count + 1 );
            } elseif ( $position_of_current_user === 'R' ) {
                $current_leg_count = get_user_meta( $parent_id, 'binary_right_leg_active_members', true );
                update_user_meta( $parent_id, 'binary_right_leg_active_members', (int)$current_leg_count + 1 );
            }

            $current_user_id = $parent_id; // Move up to the next parent
            $count++;
        }
    }

    /**
     * Handles binary income calculation and disbursement for upline.
     *
     * @param int $new_user_id User ID of the newly registered and package-activated user.
     * @param int $package_id_of_new_user Package ID purchased by the new user.
     * @param int $max_upline_levels Maximum levels to traverse upwards for payout.
     */
    private function handle_binary_income( $new_user_id, $package_id_of_new_user, $max_upline_levels = 20 ) {
        // First, update leg counts for all upline members based on this new user
        $this->update_upline_leg_counts( $new_user_id, $max_upline_levels );

        $current_ancestor_id = get_user_meta( $new_user_id, 'binary_parent_id', true );
        $depth = 0;

        while ( $current_ancestor_id && $depth < $max_upline_levels ) {
            $left_leg_count = (int)get_user_meta( $current_ancestor_id, 'binary_left_leg_active_members', true );
            $right_leg_count = (int)get_user_meta( $current_ancestor_id, 'binary_right_leg_active_members', true );
            $pairs_already_paid = (int)get_user_meta( $current_ancestor_id, 'binary_pairs_paid', true );

            $potential_pairs_now = min( $left_leg_count, $right_leg_count );

            if ( $potential_pairs_now > $pairs_already_paid ) {
                $newly_formed_pairs = $potential_pairs_now - $pairs_already_paid;
                // Income amount is from the package of the NEW USER who triggered this calculation.
                $binary_income_per_pair = get_post_meta( $package_id_of_new_user, '_binary_income_fixed', true );

                if ( is_numeric( $binary_income_per_pair ) && $binary_income_per_pair > 0 ) {
                    $income_to_disburse = $newly_formed_pairs * $binary_income_per_pair;

                    // TODO: Implement Daily Capping Logic here before updating earnings.
                    // For now, direct update:
                    $current_total_binary_earnings = get_user_meta( $current_ancestor_id, 'total_binary_earnings', true );
                    if ( empty( $current_total_binary_earnings ) || ! is_numeric( $current_total_binary_earnings ) ) {
                        $current_total_binary_earnings = 0;
                    }
                    $new_total_binary_earnings = $current_total_binary_earnings + $income_to_disburse;
                    update_user_meta( $current_ancestor_id, 'total_binary_earnings', $new_total_binary_earnings );

                    // Update the count of pairs paid for this ancestor
                    update_user_meta( $current_ancestor_id, 'binary_pairs_paid', $potential_pairs_now );

                    // Check for Fast Track Achievements for this ancestor
                    $this->check_and_process_fast_track_achievements( $current_ancestor_id );
                    // Check for Award Qualifications for this ancestor
                    $this->check_and_process_award_qualifications( $current_ancestor_id );

                    // Optional: Log this transaction
                    // add_user_meta($current_ancestor_id, 'binary_income_log', array(
                    //     'date' => current_time('mysql'),
                    //     'amount' => $income_to_disburse,
                    //     'pairs_formed' => $newly_formed_pairs,
                    //     'triggered_by_user_id' => $new_user_id,
                    //     'package_id' => $package_id_of_new_user
                    // ));
                }
            }

            // Move to the next ancestor
            $current_ancestor_id = get_user_meta( $current_ancestor_id, 'binary_parent_id', true );
            $depth++;
        }
    }

    /**
     * Handles the [electric_scooter_repurchase_packages] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output for the list of repurchase packages.
     */
    public function render_repurchase_packages_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            // Attributes can be added later if needed (e.g., category, limit)
        ), $atts, 'electric_scooter_repurchase_packages' );

        $args = array(
            'post_type' => 'repurchase_package',
            'post_status' => 'publish',
            'posts_per_page' => -1, // Display all packages
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        );
        $repurchase_packages_query = new WP_Query( $args );

        ob_start();
        if ( $repurchase_packages_query->have_posts() ) {
            // Pass data to a template file
            // Make $repurchase_packages_query available to the template
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'templates/repurchase-packages-list.php';
        } else {
            echo '<p>No repurchase packages available at the moment.</p>';
        }
        wp_reset_postdata(); // Important after custom WP_Query
        return ob_get_clean();
    }

    /**
     * Handles the purchase of a repurchase package.
     * Hooked to admin_post_electric_scooter_repurchase_package and admin_post_nopriv_electric_scooter_repurchase_package
     */
    public function handle_repurchase_package_purchase() {
        if ( ! isset( $_GET['package_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
            wp_die( 'Invalid request.' );
        }

        $package_id = intval( $_GET['package_id'] );

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'electric_scooter_repurchase_' . $package_id ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! is_user_logged_in() ) {
            // Should not happen if button is hidden or links to login, but double check.
            $login_url = add_query_arg('redirect_to', get_permalink($package_id), wp_login_url()); // Assuming package is a post
             wp_redirect($login_url);
            exit;
        }

        $user_id = get_current_user_id();
        $package_post = get_post( $package_id );

        if ( ! $package_post || $package_post->post_type !== 'repurchase_package' || $package_post->post_status !== 'publish' ) {
            wp_die( 'Invalid package.' );
        }

        // --- Record the Purchase (Simulated - No actual payment processing) ---
        $package_price = get_post_meta( $package_id, '_repurchase_package_price', true );
        $new_repurchase = array(
            'package_id' => $package_id,
            'package_name' => $package_post->post_title, // Store name for easier display
            'date'       => current_time( 'mysql' ),
            'price'      => $package_price,
        );

        $user_repurchases = get_user_meta( $user_id, '_user_repurchases', true );
        if ( ! is_array( $user_repurchases ) ) {
            $user_repurchases = array();
        }
        $user_repurchases[] = $new_repurchase;
        update_user_meta( $user_id, '_user_repurchases', $user_repurchases );

        // --- Income Disbursement ---
        // 1. Fixed Income for Buyer
        $this->disburse_repurchase_fixed_income( $user_id, $package_id );

        // 2. Percentage-Based Level Income for Upline
        $this->disburse_repurchase_level_income( $user_id, $package_id, (float)$package_price );

        // Redirect back to a page, e.g., profile or a success page.
        // Add a query arg for success message.
        $redirect_url = add_query_arg( 'repurchase_success', 'true', wp_get_referer() ? wp_get_referer() : home_url('/member-dashboard/') );
        // TODO: Create a more robust redirect / success message system.
        // For now, redirecting to where user came from or dashboard.
        wp_redirect( $redirect_url );
        exit;
    }

    private function disburse_repurchase_fixed_income( $buyer_id, $package_id ) {
        $fixed_income = get_post_meta( $package_id, '_repurchase_fixed_income', true );
        if ( is_numeric( $fixed_income ) && $fixed_income > 0 ) {
            $current_earnings = get_user_meta( $buyer_id, 'total_repurchase_fixed_earnings', true );
            $current_earnings = is_numeric( $current_earnings ) ? (float)$current_earnings : 0;
            update_user_meta( $buyer_id, 'total_repurchase_fixed_earnings', $current_earnings + (float)$fixed_income );
        }
    }

    private function disburse_repurchase_level_income( $buyer_id, $package_id, $package_price ) {
        $level_percentages_str = get_post_meta( $package_id, '_repurchase_level_income_percentages', true );
        if ( empty( $level_percentages_str ) ) {
            return;
        }
        $level_percentages = array_map('floatval', explode(',', $level_percentages_str));

        $current_sponsor_id = get_user_meta( $buyer_id, 'referred_by_user_id', true );
        $level = 0; // Level 1 is the direct sponsor

        while ( $current_sponsor_id && $level < count( $level_percentages ) ) {
            if ( !get_userdata($current_sponsor_id) ) break; // Stop if sponsor user doesn't exist

            $percentage_for_this_level = $level_percentages[$level];
            if ( $percentage_for_this_level > 0 ) {
                $income_for_this_level = ( $percentage_for_this_level / 100 ) * $package_price;

                $current_level_earnings = get_user_meta( $current_sponsor_id, 'total_repurchase_level_earnings', true );
                $current_level_earnings = is_numeric( $current_level_earnings ) ? (float)$current_level_earnings : 0;
                update_user_meta( $current_sponsor_id, 'total_repurchase_level_earnings', $current_level_earnings + $income_for_this_level );

                // Optional: Log this transaction
                // add_user_meta($current_sponsor_id, 'repurchase_level_income_log', array(
                // 'date' => current_time('mysql'),
                // 'amount' => $income_for_this_level,
                // 'from_user_id' => $buyer_id,
                // 'package_id' => $package_id,
                // 'level' => $level + 1
                // ));
            }

            // Get next upline sponsor
            $current_sponsor_id = get_user_meta( $current_sponsor_id, 'referred_by_user_id', true );
            $level++;
        }
    }

}
