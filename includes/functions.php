<?php

namespace OES\Monadic;

/**
 * Include assets
 */
function enqueue_scripts(): void
{
    if (!is_monadic_exploration()) {
        return;
    }

    wp_register_style('oes-monadic-exploration', OES_MONADIC_PLUGIN_URL . 'assets/css/me.css');
    wp_enqueue_style('oes-monadic-exploration');

    wp_register_script('oes-monadic-exploration', OES_MONADIC_PLUGIN_URL . 'assets/js/oes-me.js');
    wp_enqueue_script('oes-monadic-exploration');
}

/**
 * Add monadic exploration class to body tag.
 *
 * @param array $classes The body classes.
 * @return array Return body classes.
 */
function body_class(array $classes): array
{
    if (is_monadic_exploration()) $classes[] = 'oes-me-page';
    return $classes;
}

/**
 * Determine if page include monadic exploration shortcode.
 *
 * @return bool Return true if page includes shortcode.
 */
function is_monadic_exploration(): bool
{
    global $post;
    return $post && has_shortcode($post->post_content, 'oes_monadic_exploration');
}

/**
 * Get the HTML representation of the monadic exploration.
 *
 * @param string|array $args Shortcode attributes.
 *
 * @return string
 */
function html($args): string
{
    global $oes_me_args;
    $oes_me_args = $args;
    $class = oes_get_project_class_name('\OES\Monadic\Explorer');
    $explorer = new $class($args);
    return $explorer->render();
}