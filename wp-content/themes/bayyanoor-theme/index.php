<?php
/**
 * Main Template file
 */

get_header(); ?>

<div class="index-content-wrapper">
    <?php
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            ?>
            <article class="index-content">
                <h1><?php echo esc_html( get_the_title() ); ?></h1>
                <?php the_content(); ?>
            </article>
            <?php
        }
    } else {
        echo '<p>' . esc_html__( 'No content found.', 'bayyanoor-theme' ) . '</p>';
    }
    ?>
</div>

<?php get_footer(); ?>
