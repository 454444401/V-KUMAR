<?php

class Electric_Scooter_Plugin_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    // Add custom columns to the users list table
    public function add_custom_user_columns( $columns ) {
        $columns['member_unique_id'] = __( 'Member ID', 'electric-scooter-plugin' );
        $columns['referral_code'] = __( 'Referral Code', 'electric-scooter-plugin' );
        $columns['referred_by_user_id'] = __( 'Referred By', 'electric-scooter-plugin' );
        return $columns;
    }

    // Display data in custom columns
    public function display_custom_user_column_data( $value, $column_name, $user_id ) {
        switch ( $column_name ) {
            case 'member_unique_id':
                return get_user_meta( $user_id, 'member_unique_id', true );
            case 'referral_code':
                return get_user_meta( $user_id, 'referral_code', true );
            case 'referred_by_user_id':
                $referrer_id = get_user_meta( $user_id, 'referred_by_user_id', true );
                if ( $referrer_id ) {
                    $referrer_data = get_userdata( $referrer_id );
                    if ( $referrer_data ) {
                        // You can choose to display username, email, or Member ID of the referrer
                        return esc_html( $referrer_data->user_login . ' (ID: ' . $referrer_id . ')' );
                    } else {
                        return 'User ID ' . esc_html( $referrer_id ) . ' (not found)';
                    }
                }
                return 'N/A';
            default:
                break;
        }
        return $value;
    }

    // Make custom columns sortable (optional, but good for usability)
    public function make_custom_user_columns_sortable( $columns ) {
        $columns['member_unique_id'] = 'member_unique_id';
        $columns['referral_code'] = 'referral_code';
        // Sorting by 'referred_by_user_id' would require custom query modification,
        // so we'll skip making it sortable for now to keep it simple.
        return $columns;
    }

    // Handle sorting for custom columns (if made sortable and needs custom query)
    // public function custom_user_column_orderby( $query ) {
    //     if ( ! is_admin() ) {
    //         return $query;
    //     }
    //     $orderby = $query->get( 'orderby');
    //     if ( 'member_unique_id' == $orderby ) {
    //         $query->set('meta_key', 'member_unique_id');
    //         $query->set('orderby', 'meta_value');
    //     } elseif ( 'referral_code' == $orderby ) {
    //         $query->set('meta_key', 'referral_code');
    //         $query->set('orderby', 'meta_value');
    //     }
    //     return $query;
    // }

    public function enqueue_styles() {
        global $post_type;
        if ( 'joining_package' == $post_type ) {
            wp_enqueue_media(); // Needed for image uploads in meta box
            // Enqueue admin-specific stylesheets here if needed
            // wp_enqueue_style( $this->plugin_name .'-admin-cpt', plugin_dir_url( __FILE__ ) . 'css/electric-scooter-plugin-admin-cpt.css', array(), $this->version, 'all' );
        }
    }

    public function enqueue_scripts() {
        global $post_type;
        if ( 'joining_package' == $post_type ) {
            // Enqueue admin-specific JavaScript here if needed for meta boxes
            wp_enqueue_script( $this->plugin_name .'-admin-cpt', plugin_dir_url( __FILE__ ) . 'js/electric-scooter-admin-cpt.js', array( 'jquery' ), $this->version, false );
        }
    }

    // Register Custom Post Type "Joining Package"
    public function register_joining_package_cpt() {
        $labels = array(
            'name'                  => _x( 'Joining Packages', 'Post Type General Name', 'electric-scooter-plugin' ),
            'singular_name'         => _x( 'Joining Package', 'Post Type Singular Name', 'electric-scooter-plugin' ),
            'menu_name'             => __( 'Joining Packages', 'electric-scooter-plugin' ),
            'name_admin_bar'        => __( 'Joining Package', 'electric-scooter-plugin' ),
            'archives'              => __( 'Package Archives', 'electric-scooter-plugin' ),
            'attributes'            => __( 'Package Attributes', 'electric-scooter-plugin' ),
            'parent_item_colon'     => __( 'Parent Package:', 'electric-scooter-plugin' ),
            'all_items'             => __( 'All Packages', 'electric-scooter-plugin' ),
            'add_new_item'          => __( 'Add New Package', 'electric-scooter-plugin' ),
            'add_new'               => __( 'Add New', 'electric-scooter-plugin' ),
            'new_item'              => __( 'New Package', 'electric-scooter-plugin' ),
            'edit_item'             => __( 'Edit Package', 'electric-scooter-plugin' ),
            'update_item'           => __( 'Update Package', 'electric-scooter-plugin' ),
            'view_item'             => __( 'View Package', 'electric-scooter-plugin' ),
            'view_items'            => __( 'View Packages', 'electric-scooter-plugin' ),
            'search_items'          => __( 'Search Package', 'electric-scooter-plugin' ),
            'not_found'             => __( 'Not found', 'electric-scooter-plugin' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'electric-scooter-plugin' ),
            'featured_image'        => __( 'Package Image', 'electric-scooter-plugin' ),
            'set_featured_image'    => __( 'Set package image', 'electric-scooter-plugin' ),
            'remove_featured_image' => __( 'Remove package image', 'electric-scooter-plugin' ),
            'use_featured_image'    => __( 'Use as package image', 'electric-scooter-plugin' ),
            'insert_into_item'      => __( 'Insert into package', 'electric-scooter-plugin' ),
            'uploaded_to_this_item' => __( 'Uploaded to this package', 'electric-scooter-plugin' ),
            'items_list'            => __( 'Packages list', 'electric-scooter-plugin' ),
            'items_list_navigation' => __( 'Packages list navigation', 'electric-scooter-plugin' ),
            'filter_items_list'     => __( 'Filter packages list', 'electric-scooter-plugin' ),
        );
        $args = array(
            'label'                 => __( 'Joining Package', 'electric-scooter-plugin' ),
            'description'           => __( 'Custom Post Type for Joining Packages', 'electric-scooter-plugin' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail' ), // title = Package Name, editor for description, thumbnail for Package Image
            'hierarchical'          => false,
            'public'                => true, // Make it public to be queryable on frontend
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5, // Below Posts
            'menu_icon'             => 'dashicons-products', // Example icon
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true, // Enable archive page if needed e.g. example.com/joining_package/
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'rewrite'               => array( 'slug' => 'joining-packages' ), // URL slug
            'show_in_rest'          => true, // Enable Gutenberg editor / REST API support
        );
        register_post_type( 'joining_package', $args );
    }

    // Add Meta Boxes for Joining Package CPT
    public function add_joining_package_meta_boxes() {
        add_meta_box(
            'joining_package_details', // ID
            __( 'Package Details', 'electric-scooter-plugin' ), // Title
            array( $this, 'render_joining_package_meta_box' ), // Callback
            'joining_package', // Post type
            'normal', // Context (normal, side, advanced)
            'high' // Priority (high, core, default, low)
        );
    }

    // Render Meta Box Content
    public function render_joining_package_meta_box( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'joining_package_meta_save', 'joining_package_meta_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $package_amount = get_post_meta( $post->ID, '_package_amount', true );
        $binary_income_fixed = get_post_meta( $post->ID, '_binary_income_fixed', true );
        $direct_referral_income_fixed = get_post_meta( $post->ID, '_direct_referral_income_fixed', true );
        // package_image_id is handled by featured image, but if you need a second image:
        // $package_image_id = get_post_meta( $post->ID, '_package_image_id', true );

        ?>
        <p>
            <label for="package_amount"><?php _e( 'Package Amount ($)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="package_amount" name="package_amount" value="<?php echo esc_attr( $package_amount ); ?>" class="widefat" step="0.01" min="0" />
        </p>
        <p>
            <label for="binary_income_fixed"><?php _e( 'Binary Income (Fixed Amount $)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="binary_income_fixed" name="binary_income_fixed" value="<?php echo esc_attr( $binary_income_fixed ); ?>" class="widefat" step="0.01" min="0" />
        </p>
        <p>
            <label for="direct_referral_income_fixed"><?php _e( 'Direct Referral Income (Fixed Amount $)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="direct_referral_income_fixed" name="direct_referral_income_fixed" value="<?php echo esc_attr( $direct_referral_income_fixed ); ?>" class="widefat" step="0.01" min="0" />
        </p>
        <?php
        // If using a separate image field (instead of/in addition to featured image):
        // echo '<p><label for="package_image_id">Package Image ID:</label>';
        // echo '<input type="number" id="package_image_id" name="package_image_id" value="' . esc_attr( $package_image_id ) . '" class="widefat" />';
        // echo '<button type="button" class="button" id="upload_package_image_button">Upload Image</button></p>';
        // echo '<div id="package_image_preview_wrapper">';
        // if ($package_image_id) { echo wp_get_attachment_image($package_image_id, 'thumbnail'); }
        // echo '</div>';
    }

    // Save Meta Box Data
    public function save_joining_package_meta( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['joining_package_meta_nonce'] ) ) {
            return;
        }
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['joining_package_meta_nonce'], 'joining_package_meta_save' ) ) {
            return;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'joining_package' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // Sanitize user input and update meta fields.
        if ( isset( $_POST['package_amount'] ) ) {
            update_post_meta( $post_id, '_package_amount', sanitize_text_field( $_POST['package_amount'] ) );
        }
        if ( isset( $_POST['binary_income_fixed'] ) ) {
            update_post_meta( $post_id, '_binary_income_fixed', sanitize_text_field( $_POST['binary_income_fixed'] ) );
        }
        if ( isset( $_POST['direct_referral_income_fixed'] ) ) {
            update_post_meta( $post_id, '_direct_referral_income_fixed', sanitize_text_field( $_POST['direct_referral_income_fixed'] ) );
        }
        // if ( isset( $_POST['package_image_id'] ) ) {
        //     update_post_meta( $post_id, '_package_image_id', intval( $_POST['package_image_id'] ) );
        // }
    }

    // Register Custom Post Type "Repurchase Package"
    public function register_repurchase_package_cpt() {
        $labels = array(
            'name'                  => _x( 'Repurchase Packages', 'Post Type General Name', 'electric-scooter-plugin' ),
            'singular_name'         => _x( 'Repurchase Package', 'Post Type Singular Name', 'electric-scooter-plugin' ),
            'menu_name'             => __( 'Repurchase Packages', 'electric-scooter-plugin' ),
            // ... other labels similar to joining_package ...
            'all_items'             => __( 'All Repurchase Packages', 'electric-scooter-plugin' ),
            'add_new_item'          => __( 'Add New Repurchase Package', 'electric-scooter-plugin' ),
            'featured_image'        => __( 'Package Image', 'electric-scooter-plugin' ),
            'set_featured_image'    => __( 'Set package image', 'electric-scooter-plugin' ),
            'remove_featured_image' => __( 'Remove package image', 'electric-scooter-plugin' ),
            'use_featured_image'    => __( 'Use as package image', 'electric-scooter-plugin' ),
        );
        $args = array(
            'label'                 => __( 'Repurchase Package', 'electric-scooter-plugin' ),
            'description'           => __( 'Custom Post Type for Repurchase Packages', 'electric-scooter-plugin' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=joining_package', // Show under "Joining Packages" menu
            'menu_icon'             => 'dashicons-cart', // Example icon
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'rewrite'               => array( 'slug' => 'repurchase-packages' ),
            'show_in_rest'          => true,
        );
        register_post_type( 'repurchase_package', $args );
    }

    // Add Meta Boxes for Repurchase Package CPT
    public function add_repurchase_package_meta_boxes() {
        add_meta_box(
            'repurchase_package_details',
            __( 'Repurchase Package Details', 'electric-scooter-plugin' ),
            array( $this, 'render_repurchase_package_meta_box' ),
            'repurchase_package',
            'normal',
            'high'
        );
    }

    // Render Repurchase Package Meta Box Content
    public function render_repurchase_package_meta_box( $post ) {
        wp_nonce_field( 'repurchase_package_meta_save', 'repurchase_package_meta_nonce' );

        $price = get_post_meta( $post->ID, '_repurchase_package_price', true );
        $fixed_income = get_post_meta( $post->ID, '_repurchase_fixed_income', true );
        $level_percentages_raw = get_post_meta( $post->ID, '_repurchase_level_income_percentages', true );
        // Ensure it's an array for easier handling in the form; convert if string
        $level_percentages = is_array($level_percentages_raw) ? $level_percentages_raw : (empty($level_percentages_raw) ? array() : explode(',', $level_percentages_raw));

        ?>
        <p>
            <label for="repurchase_package_price"><?php _e( 'Package Price ($)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="repurchase_package_price" name="repurchase_package_price" value="<?php echo esc_attr( $price ); ?>" class="widefat" step="0.01" min="0" />
        </p>
        <p>
            <label for="repurchase_fixed_income"><?php _e( 'Fixed Income for Buyer ($) (Optional)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="repurchase_fixed_income" name="repurchase_fixed_income" value="<?php echo esc_attr( $fixed_income ); ?>" class="widefat" step="0.01" min="0" />
        </p>
        <div id="repurchase_level_income_percentages_wrapper">
            <label><?php _e( 'Level Income Percentages (%)', 'electric-scooter-plugin' ); ?></label>
            <p><em><?php _e('Enter percentages for each level, comma-separated (e.g., 10,5,2.5 for Level 1: 10%, Level 2: 5%, etc.). Leave empty if no level income.', 'electric-scooter-plugin'); ?></em></p>
            <input type="text" id="repurchase_level_income_percentages" name="repurchase_level_income_percentages"
                   value="<?php echo esc_attr( implode(',', array_map('floatval', $level_percentages)) ); // Store and display as comma-separated string ?>"
                   class="widefat" placeholder="e.g., 10,5,2.5"/>
            <!-- TODO: Add JS to dynamically add/remove level fields for better UX if desired -->
        </div>
        <?php
    }

    // Save Repurchase Package Meta Box Data
    public function save_repurchase_package_meta( $post_id ) {
        if ( ! isset( $_POST['repurchase_package_meta_nonce'] ) || ! wp_verify_nonce( $_POST['repurchase_package_meta_nonce'], 'repurchase_package_meta_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'repurchase_package' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        if ( isset( $_POST['repurchase_package_price'] ) ) {
            update_post_meta( $post_id, '_repurchase_package_price', sanitize_text_field( $_POST['repurchase_package_price'] ) );
        }
        if ( isset( $_POST['repurchase_fixed_income'] ) ) {
            update_post_meta( $post_id, '_repurchase_fixed_income', sanitize_text_field( $_POST['repurchase_fixed_income'] ) );
        }
        if ( isset( $_POST['repurchase_level_income_percentages'] ) ) {
            // Sanitize and store as comma-separated string or serialized array.
            // Here, storing as comma-separated string of numbers.
            $percentages_str = sanitize_text_field( $_POST['repurchase_level_income_percentages'] );
            $percentages_arr = array_map( 'floatval', explode( ',', $percentages_str ) );
            // Filter out non-numeric or zero values if necessary, though floatval handles many cases.
            $valid_percentages = array_filter($percentages_arr, function($value) { return is_numeric($value) && $value > 0; });
            update_post_meta( $post_id, '_repurchase_level_income_percentages', implode(',', $valid_percentages) );
        } else {
             // If empty or not set, ensure it's stored as an empty string or removed
            update_post_meta( $post_id, '_repurchase_level_income_percentages', '' );
        }
    }

    // Register Custom Post Type "Fast Track Tier"
    public function register_fast_track_tier_cpt() {
        $labels = array(
            'name'                  => _x( 'Fast Track Tiers', 'Post Type General Name', 'electric-scooter-plugin' ),
            'singular_name'         => _x( 'Fast Track Tier', 'Post Type Singular Name', 'electric-scooter-plugin' ),
            'menu_name'             => __( 'Fast Track Tiers', 'electric-scooter-plugin' ),
            'all_items'             => __( 'All Fast Track Tiers', 'electric-scooter-plugin' ),
            'add_new_item'          => __( 'Add New Fast Track Tier', 'electric-scooter-plugin' ),
            // ... other labels ...
        );
        $args = array(
            'label'                 => __( 'Fast Track Tier', 'electric-scooter-plugin' ),
            'description'           => __( 'Custom Post Type for Fast Track Bonus Tiers', 'electric-scooter-plugin' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ), // Only title needed, other fields are meta
            'hierarchical'          => false,
            'public'                => false, // Not publicly queryable unless needed for display
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=joining_package', // Show under "Joining Packages" menu
            'menu_icon'             => 'dashicons-star-filled',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false, // Tiers are usually internal logic, not for public pages
            'capability_type'       => 'post',
            'rewrite'               => false, // No frontend slug needed
            'show_in_rest'          => true, // For potential future admin interactions via REST
        );
        register_post_type( 'fast_track_tier', $args );
    }

    // Add Meta Boxes for Fast Track Tier CPT
    public function add_fast_track_tier_meta_boxes() {
        add_meta_box(
            'fast_track_tier_details',
            __( 'Fast Track Tier Details', 'electric-scooter-plugin' ),
            array( $this, 'render_fast_track_tier_meta_box' ),
            'fast_track_tier',
            'normal',
            'high'
        );
    }

    // Render Fast Track Tier Meta Box Content
    public function render_fast_track_tier_meta_box( $post ) {
        wp_nonce_field( 'fast_track_tier_meta_save', 'fast_track_tier_meta_nonce' );

        $pairs_required = get_post_meta( $post->ID, '_binary_pairs_required', true );
        $duration_days = get_post_meta( $post->ID, '_time_duration_days', true );
        $income_amount = get_post_meta( $post->ID, '_income_amount', true );
        $is_active_raw = get_post_meta( $post->ID, '_is_active', true );
        $is_active = $is_active_raw === 'yes' ? true : false; // Store as 'yes'/'no', handle as boolean

        ?>
        <p>
            <label for="binary_pairs_required"><?php _e( 'Binary Pairs Required', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="binary_pairs_required" name="binary_pairs_required" value="<?php echo esc_attr( $pairs_required ); ?>" class="widefat" step="1" min="1" />
        </p>
        <p>
            <label for="time_duration_days"><?php _e( 'Time Duration (Days from Joining)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="time_duration_days" name="time_duration_days" value="<?php echo esc_attr( $duration_days ); ?>" class="widefat" step="1" min="1" />
        </p>
        <p>
            <label for="income_amount"><?php _e( 'Income Amount ($) (Fixed Bonus)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="income_amount" name="income_amount" value="<?php echo esc_attr( $income_amount ); ?>" class="widefat" step="0.01" min="0" />
        </p>
        <p>
            <label for="is_active"><?php _e( 'Is Active?', 'electric-scooter-plugin' ); ?></label>
            <select name="is_active" id="is_active">
                <option value="yes" <?php selected( $is_active, true ); ?>><?php _e( 'Yes', 'electric-scooter-plugin' ); ?></option>
                <option value="no" <?php selected( $is_active, false ); ?>><?php _e( 'No', 'electric-scooter-plugin' ); ?></option>
            </select>
        </p>
        <?php
    }

    // Save Fast Track Tier Meta Box Data
    public function save_fast_track_tier_meta( $post_id ) {
        if ( ! isset( $_POST['fast_track_tier_meta_nonce'] ) || ! wp_verify_nonce( $_POST['fast_track_tier_meta_nonce'], 'fast_track_tier_meta_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'fast_track_tier' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        if ( isset( $_POST['binary_pairs_required'] ) ) {
            update_post_meta( $post_id, '_binary_pairs_required', intval( $_POST['binary_pairs_required'] ) );
        }
        if ( isset( $_POST['time_duration_days'] ) ) {
            update_post_meta( $post_id, '_time_duration_days', intval( $_POST['time_duration_days'] ) );
        }
        if ( isset( $_POST['income_amount'] ) ) {
            update_post_meta( $post_id, '_income_amount', sanitize_text_field( $_POST['income_amount'] ) );
        }
        if ( isset( $_POST['is_active'] ) ) {
            update_post_meta( $post_id, '_is_active', sanitize_text_field( $_POST['is_active'] ) === 'yes' ? 'yes' : 'no' );
        }
    }

    // Register Custom Post Type "Award / Reward"
    public function register_award_reward_cpt() {
        $labels = array(
            'name'                  => _x( 'Awards & Rewards', 'Post Type General Name', 'electric-scooter-plugin' ),
            'singular_name'         => _x( 'Award/Reward', 'Post Type Singular Name', 'electric-scooter-plugin' ),
            'menu_name'             => __( 'Awards & Rewards', 'electric-scooter-plugin' ),
            'all_items'             => __( 'All Awards', 'electric-scooter-plugin' ),
            'add_new_item'          => __( 'Add New Award', 'electric-scooter-plugin' ),
            // ... other labels ...
        );
        $args = array(
            'label'                 => __( 'Award/Reward', 'electric-scooter-plugin' ),
            'description'           => __( 'Custom Post Type for Awards and Rewards Tiers', 'electric-scooter-plugin' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'page-attributes' ), // title for Rank Title, editor for description (optional), page-attributes for menu_order
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=joining_package', // Show under "Joining Packages" menu
            'menu_icon'             => 'dashicons-awards',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'rewrite'               => false,
            'show_in_rest'          => true,
        );
        register_post_type( 'award_reward', $args );
    }

    // Add Meta Boxes for Award/Reward CPT
    public function add_award_reward_meta_boxes() {
        add_meta_box(
            'award_reward_details',
            __( 'Award/Reward Details', 'electric-scooter-plugin' ),
            array( $this, 'render_award_reward_meta_box' ),
            'award_reward',
            'normal',
            'high'
        );
    }

    // Render Award/Reward Meta Box Content
    public function render_award_reward_meta_box( $post ) {
        wp_nonce_field( 'award_reward_meta_save', 'award_reward_meta_nonce' );

        $pairs_required = get_post_meta( $post->ID, '_pairs_required', true );
        // award_description is main content editor
        $award_value = get_post_meta( $post->ID, '_award_value', true );
        $is_active_raw = get_post_meta( $post->ID, '_is_active', true );
        $is_active = $is_active_raw === 'yes' ? true : false;
        // menu_order (award_order) is handled by WordPress 'page-attributes' support

        ?>
        <p>
            <label for="pairs_required"><?php _e( 'Total Binary Pairs Required', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="pairs_required" name="pairs_required" value="<?php echo esc_attr( $pairs_required ); ?>" class="widefat" step="1" min="1" />
        </p>
        <p>
            <label for="award_value"><?php _e( 'Award Value / Reward Income ($) (Optional)', 'electric-scooter-plugin' ); ?></label>
            <input type="number" id="award_value" name="award_value" value="<?php echo esc_attr( $award_value ); ?>" class="widefat" step="0.01" min="0" />
        </p>
         <p>
            <label for="is_active"><?php _e( 'Is Active?', 'electric-scooter-plugin' ); ?></label>
            <select name="is_active" id="is_active">
                <option value="yes" <?php selected( $is_active, true ); ?>><?php _e( 'Yes', 'electric-scooter-plugin' ); ?></option>
                <option value="no" <?php selected( $is_active, false ); ?>><?php _e( 'No', 'electric-scooter-plugin' ); ?></option>
            </select>
        </p>
        <p>
            <em><?php _e('Use the "Order" field in the "Page Attributes" box (usually on the right) to set the sequence of awards.', 'electric-scooter-plugin'); ?></em>
        </p>
        <?php
    }

    // Save Award/Reward Meta Box Data
    public function save_award_reward_meta( $post_id ) {
        if ( ! isset( $_POST['award_reward_meta_nonce'] ) || ! wp_verify_nonce( $_POST['award_reward_meta_nonce'], 'award_reward_meta_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['post_type'] ) && 'award_reward' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        if ( isset( $_POST['pairs_required'] ) ) {
            update_post_meta( $post_id, '_pairs_required', intval( $_POST['pairs_required'] ) );
        }
        if ( isset( $_POST['award_value'] ) ) {
            update_post_meta( $post_id, '_award_value', sanitize_text_field( $_POST['award_value'] ) );
        }
        if ( isset( $_POST['is_active'] ) ) {
            update_post_meta( $post_id, '_is_active', sanitize_text_field( $_POST['is_active'] ) === 'yes' ? 'yes' : 'no' );
        }
        // menu_order is saved automatically by WordPress due to 'page-attributes' support
    }

    // Add Admin Menu for Award Approvals
    public function add_award_approval_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=joining_package', // Parent slug (main plugin menu or under a CPT)
            __( 'Approve Awards', 'electric-scooter-plugin' ), // Page title
            __( 'Approve Awards', 'electric-scooter-plugin' ), // Menu title
            'manage_options', // Capability required
            'electric-scooter-approve-awards', // Menu slug
            array( $this, 'render_award_approval_page' ) // Callback function
        );
    }

    // Render Award Approval Page
    public function render_award_approval_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        // Handle approval/rejection actions
        if ( isset( $_GET['action'], $_GET['user_id'], $_GET['award_id'], $_GET['_wpnonce'] ) ) {
            $user_id = intval( $_GET['user_id'] );
            $award_id = intval( $_GET['award_id'] );
            $action = sanitize_key( $_GET['action'] );

            if ( wp_verify_nonce( $_GET['_wpnonce'], 'award_approval_action_' . $user_id . '_' . $award_id ) ) {
                $achieved_awards_meta = get_user_meta( $user_id, '_achieved_awards', true );
                if ( is_array( $achieved_awards_meta ) && isset( $achieved_awards_meta[$award_id] ) ) {

                    if ( $action === 'approve' ) {
                        $achieved_awards_meta[$award_id]['status'] = 'approved';
                        $achieved_awards_meta[$award_id]['date_approved'] = current_time( 'mysql' );
                        update_user_meta( $user_id, '_achieved_awards', $achieved_awards_meta );

                        // Disburse award_value if any
                        $award_value = (float) $achieved_awards_meta[$award_id]['award_value'];
                        if ( $award_value > 0 ) {
                            $current_reward_earnings = get_user_meta( $user_id, 'total_reward_income_earnings', true );
                            $current_reward_earnings = is_numeric( $current_reward_earnings ) ? (float)$current_reward_earnings : 0;
                            update_user_meta( $user_id, 'total_reward_income_earnings', $current_reward_earnings + $award_value );
                        }

                        // Re-check award qualifications for this user in case this approval unlocks further awards.
                        // Need to ensure Electric_Scooter_Plugin_Public is available or this logic is part of a shared helper.
                        // For now, assuming the public class can be instantiated if needed, or this logic moved.
                        // This is a simplified call; direct instantiation might not be ideal.
                        // A better way would be to have this check as part of a core plugin class or a dedicated awards manager.
                        if (class_exists('Electric_Scooter_Plugin_Public')) {
                            $public_class_instance = new Electric_Scooter_Plugin_Public( $this->plugin_name, $this->version );
                            // The method check_and_process_award_qualifications is private,
                            // so we'd need a public wrapper or make it callable in a different way.
                            // For now, this is a placeholder for the re-check logic.
                            // $public_class_instance->check_and_process_award_qualifications($user_id);
                            // Actual call might need to be: do_action('electric_scooter_check_user_awards', $user_id);
                            // And then Electric_Scooter_Plugin_Public hooks into that action.
                            // For the purpose of this subtask, we'll note this re-check is important.
                            // The qualification check will run again when the user next earns a binary pair.
                        }


                        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __('Award "%s" approved for user ID %d.', 'electric-scooter-plugin'), $achieved_awards_meta[$award_id]['award_title'], $user_id ) . '</p></div>';
                    } elseif ( $action === 'reject' ) {
                        $achieved_awards_meta[$award_id]['status'] = 'rejected';
                        // Optionally add a reason: $achieved_awards_meta[$award_id]['rejection_reason'] = sanitize_text_field($_POST['rejection_reason']);
                        $achieved_awards_meta[$award_id]['date_rejected'] = current_time( 'mysql' );
                        update_user_meta( $user_id, '_achieved_awards', $achieved_awards_meta );
                        echo '<div class="notice notice-warning is-dismissible"><p>' . sprintf( __('Award "%s" rejected for user ID %d.', 'electric-scooter-plugin'), $achieved_awards_meta[$award_id]['award_title'], $user_id ) . '</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>' . __('Error: Award or user meta not found.', 'electric-scooter-plugin') . '</p></div>';
                }
            } else {
                 echo '<div class="notice notice-error is-dismissible"><p>' . __('Nonce verification failed.', 'electric-scooter-plugin') . '</p></div>';
            }
        }

        // Query users with pending awards
        $args = array(
            'meta_query' => array(
                array(
                    'key' => '_achieved_awards',
                    'value' => '"status";s:18:"pending_approval"', // Search for the string indicating pending status within the serialized array
                    'compare' => 'LIKE'
                )
            ),
            'fields' => 'all_with_meta'
        );
        $pending_users_query = new WP_User_Query( $args );
        $pending_users = $pending_users_query->get_results();
        ?>
        <div class="wrap">
            <h1><?php _e( 'Approve Awards & Rewards', 'electric-scooter-plugin' ); ?></h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"><?php _e( 'User ID', 'electric-scooter-plugin' ); ?></th>
                        <th scope="col"><?php _e( 'User Name', 'electric-scooter-plugin' ); ?></th>
                        <th scope="col"><?php _e( 'Award Title', 'electric-scooter-plugin' ); ?></th>
                        <th scope="col"><?php _e( 'Date Qualified', 'electric-scooter-plugin' ); ?></th>
                        <th scope="col"><?php _e( 'Pairs at Qual.', 'electric-scooter-plugin'); ?></th>
                        <th scope="col"><?php _e( 'Award Value', 'electric-scooter-plugin'); ?></th>
                        <th scope="col"><?php _e( 'Actions', 'electric-scooter-plugin' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $pending_users ) ) : ?>
                        <?php foreach ( $pending_users as $user ) : ?>
                            <?php
                            $achieved_awards = get_user_meta( $user->ID, '_achieved_awards', true );
                            if (is_array($achieved_awards)) {
                                foreach ($achieved_awards as $award_id => $award_data) {
                                    if ($award_data['status'] === 'pending_approval') {
                                        $approve_url = wp_nonce_url(admin_url('admin.php?page=electric-scooter-approve-awards&action=approve&user_id=' . $user->ID . '&award_id=' . $award_id), 'award_approval_action_' . $user->ID . '_' . $award_id);
                                        $reject_url = wp_nonce_url(admin_url('admin.php?page=electric-scooter-approve-awards&action=reject&user_id=' . $user->ID . '&award_id=' . $award_id), 'award_approval_action_' . $user->ID . '_' . $award_id);
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html( $user->ID ); ?></td>
                                            <td><a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>"><?php echo esc_html( $user->user_login ); ?></a></td>
                                            <td><?php echo esc_html( $award_data['award_title'] ); ?></td>
                                            <td><?php echo esc_html( date( 'Y-m-d H:i', strtotime($award_data['date_qualified']) ) ); ?></td>
                                            <td><?php echo esc_html( $award_data['pairs_at_qualification'] ); ?></td>
                                            <td>$<?php echo esc_html( number_format((float)$award_data['award_value'], 2) ); ?></td>
                                            <td>
                                                <a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary"><?php _e( 'Approve', 'electric-scooter-plugin' ); ?></a>
                                                <a href="<?php echo esc_url( $reject_url ); ?>" class="button button-secondary"><?php _e( 'Reject', 'electric-scooter-plugin' ); ?></a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7"><?php _e( 'No awards pending approval.', 'electric-scooter-plugin' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
