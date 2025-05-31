<?php
/**
 * Binary Tree template
 *
 * Expects $tree_data to be available, which is the root node of the tree to display.
 * Expects $placement_status_message (optional) if the root user is pending placement.
 */

if ( ! isset( $tree_data ) || $tree_data === null ) {
    echo '<p>No tree data available for this user, or user not found.</p>';
    return;
}

// If admin is viewing and the user is pending, show the message from the shortcode handler
if ( isset($placement_status_message) && current_user_can('manage_options') && $tree_data['is_pending_placement'] ) {
    echo '<p class="notice notice-warning">' . esc_html($placement_status_message) . ' (Admin view)</p>';
}

/**
 * Recursive function to render a tree node and its children.
 *
 * @param array $node The node data.
 * @param string $class Optional CSS class for the node container.
 * @param int $max_levels Max levels to render.
 */
function electric_scooter_render_tree_node( $node, $max_levels, $class = 'root-node' ) {
    if ( ! $node ) {
        echo '<div class="tree-node empty-node ' . esc_attr($class) . '"><div class="node-content">Empty</div></div>';
        return;
    }

    $has_children = ( $node['left_child'] || $node['right_child'] );
    $node_id_attr = 'node-' . esc_attr( $node['user_id'] ) . '-level-' . esc_attr($node['level']);
    ?>
    <div class="tree-node <?php echo esc_attr($class); ?>" id="<?php echo $node_id_attr; ?>">
        <div class="node-content">
            <strong class="node-name"><?php echo esc_html( $node['display_name'] ); ?></strong>
            <div class="node-details">
                User ID: <?php echo esc_html( $node['user_id'] ); ?><br>
                Member ID: <?php echo esc_html( $node['member_id'] ); ?><br>
                Join Date: <?php echo esc_html( $node['join_date'] ); ?><br>
                Position: <?php echo esc_html( $node['position'] ? $node['position'] : 'Root' ); ?>
                <?php if ( $node['is_pending_placement'] ): ?>
                    <br><span class="pending-placement-notice">(Pending Placement)</span>
                <?php endif; ?>
                <?php // Package Name: echo esc_html( $node['package_name'] ? $node['package_name'] : 'N/A' ); ?>
            </div>
        </div>
        <?php
        // We compare node level against $max_levels passed to the function.
        // The shortcode's max_levels is $atts['max_levels'], which is passed to get_binary_tree_data.
        // The get_binary_tree_data function itself caps recursion at $max_levels, so $node['level'] should not exceed it.
        // The check here is more about *displaying* children. If $node['level'] is already $max_levels-1, its children would be at $max_levels, so we don't show them.
        ?>
        <?php if ( $has_children && $node['level'] < ($max_levels - 1) ): ?>
            <div class="node-children">
                <?php electric_scooter_render_tree_node( $node['left_child'], $max_levels, 'left-child' ); ?>
                <?php electric_scooter_render_tree_node( $node['right_child'], $max_levels, 'right-child' ); ?>
            </div>
        <?php elseif ( $has_children && $node['level'] >= ($max_levels - 1) ): ?>
             <div class="node-children-limit-reached">(Max display levels reached)</div>
        <?php endif; ?>
    </div>
    <?php
}

// $atts is passed from the shortcode handler (render_binary_tree_shortcode) when including this template.
$current_max_levels = isset($atts['max_levels']) ? intval($atts['max_levels']) : 3;

?>

<div class="electric-scooter-binary-tree">
    <?php electric_scooter_render_tree_node( $tree_data, $current_max_levels ); ?>
</div>
