<?php
/**
 * Bayyanoor AI Integration (Gemini API)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Bayyanoor_AI {

    public static function init() {
        add_action( 'wp_ajax_bayyanoor_ai_query', array( __CLASS__, 'handle_ai_query' ) );
        
        // Add shortcode for the AI interface
        add_shortcode( 'bayyanoor_ai_interface', array( __CLASS__, 'render_ai_interface' ) );
    }

    public static function handle_ai_query() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'You must be logged in to use the AI Tutor.' );
        }

        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';

        if ( empty( $query ) ) {
            wp_send_json_error( 'Query is empty.' );
        }

        $user_id = get_current_user_id();

        // Integrate with Gemini API (Enhanced mock for now)
        $ai_response = self::call_gemini_api( $query );

        if ( is_wp_error( $ai_response ) ) {
            wp_send_json_error( $ai_response->get_error_message() );
        }

        // Log the AI Query to the DB
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_ai_logs';
        $wpdb->insert(
            $table_name,
            array(
                'user_id'          => $user_id,
                'query'            => $query,
                'response_summary' => wp_trim_words( $ai_response, 20 ),
            ),
            array( '%d', '%s', '%s' )
        );

        // Record activity for streak
        if ( class_exists( 'Bayyanoor_Streaks' ) ) {
            Bayyanoor_Streaks::record_activity( $user_id );
        }

        wp_send_json_success( array( 'response' => $ai_response ) );
    }

    private static function call_gemini_api( $user_query ) {
        $query_lower = strtolower( $user_query );

        // Tajweed rules
        if ( strpos( $query_lower, 'tajweed' ) !== false || strpos( $query_lower, 'noon' ) !== false || strpos( $query_lower, 'meem' ) !== false ) {
            $responses = array(
                "Excellent question about Tajweed! The rules of Noon Sakin and Tanween include four categories:\n\n1. **Izhar** (إظهار) — Clear pronunciation when followed by throat letters (ء ه ع ح غ خ)\n2. **Idgham** (إدغام) — Merging, when followed by ي ر م ل و ن\n3. **Iqlab** (إقلاب) — Changing Noon to Meem when followed by ب\n4. **Ikhfa** (إخفاء) — Hiding, with the remaining 15 letters\n\nKeep practicing with Surah Al-Baqarah for abundant examples!",
                "Great to see your interest in Tajweed! The rules of Meem Sakin are:\n\n1. **Ikhfa Shafawi** — When Meem Sakin is followed by ب, pronounce with a slight nasalization\n2. **Idgham Shafawi** — When followed by another م, merge them with ghunna\n3. **Izhar Shafawi** — With all other letters, pronounce the Meem clearly\n\nPractice slowly and listen to Sheikh Husary's recitation for perfect examples.",
            );
            return $responses[ array_rand( $responses ) ];
        }

        // Tafseer
        if ( strpos( $query_lower, 'tafseer' ) !== false || strpos( $query_lower, 'tafsir' ) !== false || strpos( $query_lower, 'meaning' ) !== false ) {
            return "Looking into Tafseer Ibn Kathir, we find deep layers of meaning in every ayah. Ibn Kathir's methodology combines hadith-based interpretation with linguistic analysis.\n\nFor specific verse interpretations, tell me which Surah and Ayah number you'd like to explore, and I'll provide the relevant commentary, historical context, and lessons we can derive from it.\n\nMay Allah increase your knowledge.";
        }

        // Arabic grammar
        if ( strpos( $query_lower, 'grammar' ) !== false || strpos( $query_lower, 'nahw' ) !== false || strpos( $query_lower, 'sarf' ) !== false || strpos( $query_lower, 'arabic' ) !== false ) {
            return "Arabic Grammar (النحو والصرف) is the key to understanding the Quran directly.\n\n**Essential concepts to master:**\n• **Ism** (اسم) — Nouns: Learn the signs (tanween, al-, preposition before it)\n• **Fi'l** (فعل) — Verbs: Past, present, command forms\n• **Harf** (حرف) — Particles: Connecting words that change meaning\n\nThe beauty of Quranic Arabic is its precision — every word form carries meaning. Would you like to practice identifying word types in a specific ayah?";
        }

        // Memorization / Hifz
        if ( strpos( $query_lower, 'memoriz' ) !== false || strpos( $query_lower, 'hifz' ) !== false || strpos( $query_lower, 'hafiz' ) !== false ) {
            return "Memorizing the Quran is one of the greatest acts of worship! Here are proven techniques:\n\n🔹 **The 3×3 Method**: Read 3 ayahs, repeat each 3 times looking, then 3 times from memory\n🔹 **Connection**: Link the meaning to the words — understand what you memorize\n🔹 **Consistency**: Better to memorize 3 ayahs daily than 20 once a week\n🔹 **Review**: The Prophet ﷺ said: 'Keep refreshing your knowledge of the Quran'\n🔹 **Best time**: After Fajr when the mind is fresh\n\nWhich Surah are you currently working on? I can help with a memorization plan!";
        }

        // Seerah / Islamic history
        if ( strpos( $query_lower, 'seerah' ) !== false || strpos( $query_lower, 'prophet' ) !== false || strpos( $query_lower, 'history' ) !== false ) {
            return "The Seerah (biography of Prophet Muhammad ﷺ) is essential for every Muslim.\n\n**Key periods to study:**\n1. Pre-prophethood — His character and upbringing\n2. Meccan period — The early struggles and perseverance\n3. Hijrah — The migration and its lessons in sacrifice\n4. Medinan period — Building a community and governance\n5. Conquest of Makkah — Mercy and forgiveness in action\n\nEach period has profound lessons for our daily lives. Which period interests you most?";
        }

        // Prayer / Salah
        if ( strpos( $query_lower, 'salah' ) !== false || strpos( $query_lower, 'prayer' ) !== false || strpos( $query_lower, 'salat' ) !== false ) {
            return "Salah is the pillar of our deen and the first thing we'll be asked about on the Day of Judgment.\n\n**Tips for improving your Salah:**\n• Understand what you're reciting — learn the meanings of Al-Fatiha and your surahs\n• Take your time in each position — the Prophet ﷺ would stay until his bones settled\n• Make dua in sujood — it's the closest you are to Allah\n• Pray as if it's your last prayer\n\nWould you like help learning the meanings of specific prayers?";
        }

        // Default warm response (NO system prompt leaked)
        return "As-salamu alaykum! I'm your Bayyanoor AI Tutor, here to help you with:\n\n- **Tajweed** - rules of Quranic recitation\n- **Tafseer** - understanding the meanings of the Quran\n- **Arabic Grammar** - Nahw and Sarf fundamentals\n- **Islamic Studies** - Seerah, Fiqh, and more\n- **Hifz Tips** - memorization techniques and plans\n\nTry asking me something specific like: 'What are the rules of Noon Sakin?' or 'Help me understand Surah Al-Fatiha'.\n\nI'm ready to help you on your journey.";
    }

    public static function render_ai_interface() {
        if ( ! is_user_logged_in() ) {
            return '<div class="bayyanoor-card"><h2>AI Tutor</h2><p>Please <a href="' . esc_url( home_url( '/bayyanoor-login' ) ) . '">log in</a> to access the AI Tutor.</p></div>';
        }

        ob_start();
        ?>
        <div class="bayyanoor-card b-ai-container">
            <div class="b-ai-header">
                <div class="b-ai-icon">AI</div>
                <div>
                    <h2>Bayyanoor AI Tutor</h2>
                    <p class="b-ai-subtitle">Ask questions about Tajweed, Tafseer, Arabic Grammar & more</p>
                </div>
            </div>
            <div class="bayyanoor-ai-chat-box">
                <div class="chat-history">
                    <div class="chat-msg ai">As-salamu alaykum! How can I help you with your Quranic studies today?</div>
                </div>
                <form id="bayyanoor-ai-form" class="chat-input-area">
                    <input type="text" id="ai-query" placeholder="Ask about Tajweed, Tafseer, Arabic, Hifz..." required autocomplete="off" />
                    <button type="submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
Bayyanoor_AI::init();
