<?php
/**
 * Bayyanoor Theme: Default Page Template
 */
get_header(); ?>

<div class="page-header">
    <h1><?php the_title(); ?></h1>
</div>

<div class="page-content-wrapper">
    <?php
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            ?>
            <div class="page-content">
                <?php the_content(); ?>
            </div>
            <?php
        }
    }
    ?>
</div>

<?php get_footer(); ?>
