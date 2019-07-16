<?php
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();
$context['parent'] = $context['post'];
while($context['parent']->parent())
{
    $context['parent'] = $context['parent']->parent();
}

if($context['post']->id == $context['parent']->id)
{
    $redirect_post = get_field('default_page');
    if($redirect_post)
    {
        $redirect_url = get_permalink($redirect_post);
        wp_safe_redirect($redirect_url);
    }
}

get_header();
echo Timber::compile_string(file_get_contents(__DIR__.'/../views/single-guide.twig'), $context);
get_footer();
?>
