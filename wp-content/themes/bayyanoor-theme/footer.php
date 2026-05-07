<?php
/**
 * Bayyanoor Theme: Footer
 */
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-widgets">
            <div class="footer-widget">
                <h3>About Bayyanoor</h3>
                <p>Live Quran, Arabic, and Islamic Studies for families who want structured learning and visible progress.</p>
            </div>
            
            <div class="footer-widget">
                <h3>Programs</h3>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/quran-classes' ) ); ?>">Quran Classes</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/arabic-language-classes' ) ); ?>">Arabic Language</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/islamic-studies' ) ); ?>">Islamic Studies</a></li>
                </ul>
            </div>
            
            <div class="footer-widget">
                <h3>Resources</h3>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>">Pricing</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/faq' ) ); ?>">FAQ</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">Blog</a></li>
                </ul>
            </div>
            
            <div class="footer-widget">
                <h3>Connect With Us</h3>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/contact-us' ) ); ?>">Contact Us</a></li>
                    <li>Tel: +1 800 555 0199</li>
                </ul>
            </div>
        </div>
        
        <div class="site-info">
            <p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Bayyanoor Academy. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
