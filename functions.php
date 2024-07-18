<?php
function pietergoosen_theme_setup() {
  register_nav_menus( array( 
    'header' => 'Header menu', 
    'footer' => 'Footer menu' 
  ) );
 }

add_action( 'after_setup_theme', 'pietergoosen_theme_setup' );

function custom_user_form_shortcod() {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
    } else {
        $current_user = null;
    }

    ob_start();
    ?>
    <form id="custom_user_form" method="POST" action="">
        <?php wp_nonce_field('custom_user_form', 'custom_user_form_nonce'); ?>
        
        <p>
            <label for="user_login">Username</label>
            <input type="text" name="user_login" id="user_login" value="<?php echo $current_user ? esc_attr($current_user->user_login) : ''; ?>" <?php echo $current_user ? 'readonly' : ''; ?>>
        </p>
        
        <p>
            <label for="user_email">Email</label>
            <input type="email" name="user_email" id="user_email" value="<?php echo $current_user ? esc_attr($current_user->user_email) : ''; ?>">
        </p>
        
        <p>
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo $current_user ? esc_attr($current_user->first_name) : ''; ?>">
        </p>
        
        <p>
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo $current_user ? esc_attr($current_user->last_name) : ''; ?>">
        </p>

        <?php if (!$current_user) : ?>
            <p>
                <label for="user_password">Password</label>
                <input type="password" name="user_password" id="user_password">
            </p>
            <p>
                <label for="user_password_confirm">Confirm Password</label>
                <input type="password" name="user_password_confirm" id="user_password_confirm">
            </p>
        <?php endif; ?>

        <p>
            <input type="submit" name="custom_user_form_submit" value="<?php echo $current_user ? 'Update' : 'Register'; ?>">
        </p>
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('custom_user_forms', 'custom_user_form_shortcod');
function handle_custom_user_forms() {
    if (isset($_POST['custom_user_form_submit']) && wp_verify_nonce($_POST['custom_user_form_nonce'], 'custom_user_form')) {
        $user_login = sanitize_text_field($_POST['user_login']);
        $user_email = sanitize_email($_POST['user_email']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $userdata = array(
                'ID' => $current_user->ID,
                'user_email' => $user_email,
                'first_name' => $first_name,
                'last_name' => $last_name
            );
            wp_update_user($userdata);
        } else {
            $user_password = $_POST['user_password'];
            $user_password_confirm = $_POST['user_password_confirm'];

            if ($user_password != $user_password_confirm) {
                // Passwords do not match
                return;
            }

            $userdata = array(
                'user_login' => $user_login,
                'user_email' => $user_email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'user_pass' => $user_password,
            );
            wp_insert_user($userdata);
        }
    }
}

add_action('template_redirect', 'handle_custom_user_forms');

add_action('init', 'toys_categories_register');

function toys_categories_register() {
$labels = array(
    'name'                          => 'Toys Categories',
    'singular_name'                 => 'Toys Category',
    'search_items'                  => 'Search Toys Categories',
    'popular_items'                 => 'Popular Toys Categories',
    'all_items'                     => 'All Toys Categories',
    'parent_item'                   => 'Parent Toy Category',
    'edit_item'                     => 'Edit Toy Category',
    'update_item'                   => 'Update Toy Category',
    'add_new_item'                  => 'Add New Toy Category',
    'new_item_name'                 => 'New Toy Category',
    'separate_items_with_commas'    => 'Separate toys categories with commas',
    'add_or_remove_items'           => 'Add or remove toys categories',
    'choose_from_most_used'         => 'Choose from most used toys categories'
    );

$args = array(
    'label'                         => 'Toys Categories',
    'labels'                        => $labels,
    'public'                        => true,
    'hierarchical'                  => true,
    'show_ui'                       => true,
    'show_in_nav_menus'             => true,
    'args'                          => array( 'orderby' => 'term_order' ),
    'rewrite'                       => array( 'slug' => 'toys', 'with_front' => true, 'hierarchical' => true ),
    'query_var'                     => true
);

register_taxonomy( 'toys_categories', 'toys', $args );
}

add_action('init', 'toys_register');

function toys_register() {

    $labels = array(
        'name' => 'Toys',
        'singular_name' => 'Toy',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Toy',
        'edit_item' => 'Edit Toy',
        'new_item' => 'New Toy',
        'view_item' => 'View Toy',
        'search_items' => 'Search Toys',
        'not_found' =>  'Nothing found',
        'not_found_in_trash' => 'Nothing found in Trash',
        'parent_item_colon' => ''
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'has_archive' => true,
        'rewrite' => array( 'slug' => 'toys', 'with_front' => true ),
        'capability_type' => 'post',
        'menu_position' => 6,
        'supports' => array('title', 'excerpt', 'editor','thumbnail') //here you can specify what type of inputs will be accessible in the admin area
      );

    register_post_type( 'toys' , $args );
}

function get_toys_data() {
    $args = array(
        'post_type' => 'toys',
        'posts_per_page' => 5
    );
    $postdata = new WP_Query($args);
    if ($postdata->have_posts()) {
        while ($postdata->have_posts()) {
            $postdata->the_post();
            echo '<div class="toy-post">';
            echo '<h1>' . the_title() . '</h1>';
            echo '<div class="toy-content">' . the_content() . '</div>';
            echo '</div>'; 
        }
        wp_reset_postdata();
    } else {
        echo '<p>No toys found.</p>';
    }
}
add_shortcode('get_posts', 'get_toys_data');

