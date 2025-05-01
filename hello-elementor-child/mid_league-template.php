<?php

/**
 * The template for displaying all pages
 * 
 * Template Name: Mid League template
 *
 * This is the template that displays all pages by default.
 * Note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package HelloElementor
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header(); ?>

<main id="content" <?php post_class('site-main'); ?>>
    <?php
    while (have_posts()) :
        the_post();
        the_content();
        //    echo add_shortcode('[league_tabs league="npl"]');

        get_template_part('template-parts/content', 'page');
    endwhile; // End of the loop.
    ?>
</main>




<?php
get_footer();
