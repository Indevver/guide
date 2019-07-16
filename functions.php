<?php
/**
 * Plugin Name: Guide
 * Plugin URI: http://indevver
 * Description: Create wiki styled guides
 * Version: 1.0
 * Author: Robert Parker
 * Author URI: http://www.indevver.com
 */

require_once __DIR__.'/vendor/autoload.php';

function codex_custom_init() {
    $args = array(
        'public' => true,
        'label'  => 'Guide',
        'rewrite'   => ['slug' => 'guides'],
        'hierarchical' => true,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => [
            'title',
            'editor',
            'page-attributes'
        ]
    );
    register_post_type( 'guide', $args );
}
add_action('init', 'codex_custom_init');

function guide_rewrite_flush() {
    codex_custom_init();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'guide_rewrite_flush' );

function guide_acf()
{
    $default_page = new StoutLogic\AcfBuilder\FieldsBuilder('First Page');
    $default_page
        ->setGroupConfig('position', 'acf_after_title')
        ->setGroupConfig('hide_on_screen', [
            'the_content',
        ])
        ->addMessage('Instructions', 'After creating this page, create the child pages. Then come back and set a child page as the default page people will see when trying to view this page.')
        ->addSelect('default_page', ['label' => 'Default Page'])
        ->setLocation('post_type', '==', 'guide')
        ->and('page_type', '==', 'parent')
    ;
//    var_dump($default_page->build());exit;
    acf_add_local_field_group($default_page->build());
}
add_action('acf/init', 'guide_acf');

function guide_acf_parent($field)
{
    $args = array(
        'post_type'      => 'guide',
        'posts_per_page' => -1,
        'post_parent'    => get_the_ID(),
        'order'          => 'ASC',
        'orderby'        => 'menu_order'
    );
    $parent = new WP_Query( $args );
    if ($parent->have_posts()) {
        while ($parent->have_posts()) {
            $parent->the_post();
            $field['choices'][get_the_ID()] = get_the_title();
        }
    }
    wp_reset_postdata();

    return $field;
}
add_filter('acf/load_field/key=field_first_page_default_page', 'guide_acf_parent');

function guide_template($template)
{
    global $post;

    if ('guide' === $post->post_type && locate_template(['single-guide.php']) !== $template)
    {
        return __DIR__.'/templates/single-guide.php';
    }

    return $template;
}
add_filter('single_template', 'guide_template');

function guide_css() {
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style('style1', $plugin_url.'styles/style.css');
}
add_action( 'wp_enqueue_scripts', 'guide_css' );

