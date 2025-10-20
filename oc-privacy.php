<?php
/**
 * Plugin Name: Cookie Consent Bar
 * Plugin URI: https://yourwebsite.com/
 * Description: תוסף פשוט וידידותי להצגת באנר הסכמה לעוגיות עם אפשרויות התאמה אישית
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 * License: GPL v2 or later
 * Text Domain: cookie-consent-bar
 * Domain Path: /languages
 */

// מניעת גישה ישירה
if (!defined('ABSPATH')) {
    exit;
}

/**
 * המחלקה הראשית של התוסף
 */
class CookieConsentBar {

    private $options;
    private $option_name = 'ccb_settings';

    /**
     * אתחול התוסף
     */
    public function __construct() {
        $this->options = get_option($this->option_name);
        $this->init_hooks();
    }

    /**
     * רישום כל ה-Hooks
     */
    private function init_hooks() {
        // הוספת תפריט אדמין
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));

        // הוספת הסקריפטים והסגנונות לאתר
        add_action('wp_head', array($this, 'add_styles'));
        add_action('wp_footer', array($this, 'add_html'));
        add_action('wp_footer', array($this, 'add_scripts'));

        // הוספת לינק להגדרות בדף התוספים
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    /**
     * הוספת לינק להגדרות בדף התוספים
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=cookie-consent-bar') . '">' . __('הגדרות', 'cookie-consent-bar') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * הוספת עמוד להגדרות
     */
    public function add_admin_menu() {
        add_options_page(
            __('הגדרות Cookie Consent Bar', 'cookie-consent-bar'),
            __('Cookie Consent', 'cookie-consent-bar'),
            'manage_options',
            'cookie-consent-bar',
            array($this, 'settings_page')
        );
    }

    /**
     * אתחול ההגדרות
     */
    public function settings_init() {
        register_setting('ccb_settings_group', $this->option_name, array($this, 'sanitize_settings'));

        // הוספת סקשן להגדרות טקסט
        add_settings_section(
            'ccb_text_section',
            __('הגדרות תוכן', 'cookie-consent-bar'),
            array($this, 'text_section_callback'),
            'cookie-consent-bar'
        );

        // כותרת
        add_settings_field(
            'ccb_title',
            __('כותרת', 'cookie-consent-bar'),
            array($this, 'title_field_callback'),
            'cookie-consent-bar',
            'ccb_text_section'
        );

        // טקסט
        add_settings_field(
            'ccb_text',
            __('טקסט', 'cookie-consent-bar'),
            array($this, 'text_field_callback'),
            'cookie-consent-bar',
            'ccb_text_section'
        );

        // טקסט כפתור
        add_settings_field(
            'ccb_button_text',
            __('טקסט בכפתור אישור', 'cookie-consent-bar'),
            array($this, 'button_text_field_callback'),
            'cookie-consent-bar',
            'ccb_text_section'
        );

        // הוספת סקשן להגדרות עיצוב
        add_settings_section(
            'ccb_style_section',
            __('הגדרות עיצוב', 'cookie-consent-bar'),
            array($this, 'style_section_callback'),
            'cookie-consent-bar'
        );

        // צבע רקע
        add_settings_field(
            'ccb_bg_color',
            __('צבע רקע', 'cookie-consent-bar'),
            array($this, 'bg_color_field_callback'),
            'cookie-consent-bar',
            'ccb_style_section'
        );

        // צבע טקסט
        add_settings_field(
            'ccb_text_color',
            __('צבע טקסט', 'cookie-consent-bar'),
            array($this, 'text_color_field_callback'),
            'cookie-consent-bar',
            'ccb_style_section'
        );

        // צבע כפתור
        add_settings_field(
            'ccb_button_bg_color',
            __('צבע רקע כפתור', 'cookie-consent-bar'),
            array($this, 'button_bg_color_field_callback'),
            'cookie-consent-bar',
            'ccb_style_section'
        );

        // צבע טקסט בכפתור
        add_settings_field(
            'ccb_button_text_color',
            __('צבע טקסט בכפתור', 'cookie-consent-bar'),
            array($this, 'button_text_color_field_callback'),
            'cookie-consent-bar',
            'ccb_style_section'
        );
    }

    /**
     * קבלת ערך ברירת מחדל או מההגדרות
     */
    private function get_option($key, $default = '') {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * תיאור סקשן התוכן
     */
    public function text_section_callback() {
        echo '<p>' . __('הגדר את התוכן שיופיע בבאנר העוגיות', 'cookie-consent-bar') . '</p>';
    }

    /**
     * תיאור סקשן העיצוב
     */
    public function style_section_callback() {
        echo '<p>' . __('התאם את העיצוב של באנר העוגיות', 'cookie-consent-bar') . '</p>';
    }

    /**
     * שדה כותרת
     */
    public function title_field_callback() {
        $value = $this->get_option('title', '🍪 אוהבים עוגיות? גם אנחנו!');
        echo '<input type="text" name="' . $this->option_name . '[title]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * שדה טקסט
     */
    public function text_field_callback() {
        $value = $this->get_option('text', 'אנחנו משתמשים בעוגיות (cookies) לשיפור חוויית הגלישה שלך, הצגת הצעות מותאמות ועוד.');
        echo '<textarea name="' . $this->option_name . '[text]" rows="3" cols="50" class="large-text">' . esc_textarea($value) . '</textarea>';
    }

    /**
     * שדה טקסט כפתור
     */
    public function button_text_field_callback() {
        $value = $this->get_option('button_text', 'הבנתי');
        echo '<input type="text" name="' . $this->option_name . '[button_text]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * שדה צבע רקע
     */
    public function bg_color_field_callback() {
        $value = $this->get_option('bg_color', '#ffffff');
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="color" id="ccb_bg_color_picker" value="<?php echo esc_attr($value); ?>" />
            <input type="text"
                   id="ccb_bg_color"
                   name="<?php echo $this->option_name; ?>[bg_color]"
                   value="<?php echo esc_attr($value); ?>"
                   class="regular-text color-field"
                   placeholder="#ffffff"
                   pattern="^#[0-9A-Fa-f]{6}$" />
            <span class="description">הזן קוד צבע HEX</span>
        </div>
        <?php
    }

    /**
     * שדה צבע טקסט
     */
    public function text_color_field_callback() {
        $value = $this->get_option('text_color', '#333333');
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="color" id="ccb_text_color_picker" value="<?php echo esc_attr($value); ?>" />
            <input type="text"
                   id="ccb_text_color"
                   name="<?php echo $this->option_name; ?>[text_color]"
                   value="<?php echo esc_attr($value); ?>"
                   class="regular-text color-field"
                   placeholder="#333333"
                   pattern="^#[0-9A-Fa-f]{6}$" />
            <span class="description">הזן קוד צבע HEX</span>
        </div>
        <?php
    }

    /**
     * שדה צבע רקע כפתור
     */
    public function button_bg_color_field_callback() {
        $value = $this->get_option('button_bg_color', '#ff8a00');
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="color" id="ccb_button_bg_color_picker" value="<?php echo esc_attr($value); ?>" />
            <input type="text"
                   id="ccb_button_bg_color"
                   name="<?php echo $this->option_name; ?>[button_bg_color]"
                   value="<?php echo esc_attr($value); ?>"
                   class="regular-text color-field"
                   placeholder="#ff8a00"
                   pattern="^#[0-9A-Fa-f]{6}$" />
            <span class="description">הזן קוד צבע HEX</span>
        </div>
        <?php
    }

    /**
     * שדה צבע טקסט בכפתור
     */
    public function button_text_color_field_callback() {
        $value = $this->get_option('button_text_color', '#ffffff');
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="color" id="ccb_button_text_color_picker" value="<?php echo esc_attr($value); ?>" />
            <input type="text"
                   id="ccb_button_text_color"
                   name="<?php echo $this->option_name; ?>[button_text_color]"
                   value="<?php echo esc_attr($value); ?>"
                   class="regular-text color-field"
                   placeholder="#ffffff"
                   pattern="^#[0-9A-Fa-f]{6}$" />
            <span class="description">הזן קוד צבע HEX</span>
        </div>
        <?php
    }

    /**
     * ניקוי וולידציה של ההגדרות
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        if (isset($input['title'])) {
            $sanitized['title'] = sanitize_text_field($input['title']);
        }

        if (isset($input['text'])) {
            $sanitized['text'] = sanitize_textarea_field($input['text']);
        }

        if (isset($input['button_text'])) {
            $sanitized['button_text'] = sanitize_text_field($input['button_text']);
        }

        if (isset($input['bg_color'])) {
            $sanitized['bg_color'] = sanitize_hex_color($input['bg_color']);
        }

        if (isset($input['text_color'])) {
            $sanitized['text_color'] = sanitize_hex_color($input['text_color']);
        }

        if (isset($input['button_bg_color'])) {
            $sanitized['button_bg_color'] = sanitize_hex_color($input['button_bg_color']);
        }

        if (isset($input['button_text_color'])) {
            $sanitized['button_text_color'] = sanitize_hex_color($input['button_text_color']);
        }

        return $sanitized;
    }

    /**
     * עמוד ההגדרות
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('ccb_settings_group');
                do_settings_sections('cookie-consent-bar');
                submit_button();
                ?>
            </form>

            <hr>

            <h2><?php _e('תצוגה מקדימה', 'cookie-consent-bar'); ?></h2>
            <p><?php _e('כך ייראה הבאנר באתר שלך:', 'cookie-consent-bar'); ?></p>

            <div style="position: relative; background: #f0f0f0; padding: 40px; border-radius: 8px; margin-top: 20px;">
                <div id="ccb-preview" style="
                    max-width: 350px;
                    background: <?php echo esc_attr($this->get_option('bg_color', '#ffffff')); ?>;
                    color: <?php echo esc_attr($this->get_option('text_color', '#333333')); ?>;
                    text-align: right;
                    padding: 20px;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                    font-size: 15px;
                    direction: rtl;
                    margin: 0 auto;
                    ">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="line-height:1.7;">
                            <strong id="ccb-preview-title"><?php echo esc_html($this->get_option('title', '🍪 אוהבים עוגיות? גם אנחנו!')); ?></strong>
                            <p style="margin: 5px 0 0 0;">
                                <span id="ccb-preview-text"><?php echo esc_html($this->get_option('text', 'אנחנו משתמשים בעוגיות (cookies) לשיפור חוויית הגלישה שלך, הצגת הצעות מותאמות ועוד.')); ?></span>
                                <?php if (get_privacy_policy_url()): ?>
                                    עיין ב<a href="#" style="color:#144456; text-decoration: underline;">מדיניות הפרטיות</a> שלנו.
                                <?php endif; ?>
                            </p>
                        </div>
                        <button id="ccb-preview-button" style="
                            background: <?php echo esc_attr($this->get_option('button_bg_color', '#ff8a00')); ?>;
                            color: <?php echo esc_attr($this->get_option('button_text_color', '#ffffff')); ?>;
                            border: none;
                            padding: 10px 20px;
                            border-radius: 6px;
                            font-weight: bold;
                            cursor: pointer;
                            align-self: flex-end;
                            "><?php echo esc_html($this->get_option('button_text', 'הבנתי')); ?></button>
                    </div>
                </div>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    // פונקציה לבדיקת תקינות קוד HEX
                    function isValidHex(hex) {
                        return /^#[0-9A-Fa-f]{6}$/.test(hex);
                    }

                    // סנכרון בין color picker לשדה טקסט
                    function syncColorFields(pickerId, textId) {
                        var picker = $('#' + pickerId);
                        var textField = $('#' + textId);

                        // עדכון מ-picker לטקסט
                        picker.on('input change', function() {
                            textField.val(this.value);
                            textField.trigger('input');
                        });

                        // עדכון מטקסט ל-picker
                        textField.on('input change', function() {
                            var value = this.value;
                            if (isValidHex(value)) {
                                picker.val(value);
                            }
                        });
                    }

                    // הגדרת סנכרון לכל שדות הצבע
                    syncColorFields('ccb_bg_color_picker', 'ccb_bg_color');
                    syncColorFields('ccb_text_color_picker', 'ccb_text_color');
                    syncColorFields('ccb_button_bg_color_picker', 'ccb_button_bg_color');
                    syncColorFields('ccb_button_text_color_picker', 'ccb_button_text_color');

                    // עדכון תצוגה מקדימה בזמן אמת
                    function updatePreview() {
                        var preview = $('#ccb-preview');
                        var previewButton = $('#ccb-preview-button');

                        // עדכון טקסטים
                        $('#ccb-preview-title').text($('input[name="ccb_settings[title]"]').val());
                        $('#ccb-preview-text').text($('textarea[name="ccb_settings[text]"]').val());
                        previewButton.text($('input[name="ccb_settings[button_text]"]').val());

                        // עדכון צבעים
                        var bgColor = $('#ccb_bg_color').val();
                        var textColor = $('#ccb_text_color').val();
                        var buttonBgColor = $('#ccb_button_bg_color').val();
                        var buttonTextColor = $('#ccb_button_text_color').val();

                        if (isValidHex(bgColor)) {
                            preview.css('background-color', bgColor);
                        }
                        if (isValidHex(textColor)) {
                            preview.css('color', textColor);
                        }
                        if (isValidHex(buttonBgColor)) {
                            previewButton.css('background-color', buttonBgColor);
                        }
                        if (isValidHex(buttonTextColor)) {
                            previewButton.css('color', buttonTextColor);
                        }
                    }

                    // האזנה לשינויים בכל השדות
                    $('input[name="ccb_settings[title]"], textarea[name="ccb_settings[text]"], input[name="ccb_settings[button_text]"]').on('input', updatePreview);
                    $('.color-field').on('input change', updatePreview);

                    // אנימציה לכפתור בתצוגה המקדימה
                    $('#ccb-preview-button').on('click', function(e) {
                        e.preventDefault();
                        var button = $(this);
                        button.css('transform', 'scale(0.95)');
                        setTimeout(function() {
                            button.css('transform', 'scale(1)');
                        }, 100);
                    });
                });
            </script>

            <style>
                .color-field {
                    width: 100px !important;
                    font-family: monospace;
                    text-transform: uppercase;
                }
                .color-field:invalid {
                    border-color: #dc3545;
                }
                #ccb-preview-button {
                    transition: transform 0.1s ease;
                }
            </style>

            <hr style="margin-top: 30px;">

            <h2><?php _e('מידע נוסף', 'cookie-consent-bar'); ?></h2>
            <p>
                <?php _e('התוסף משתמש ב-localStorage כדי לזכור את בחירת המשתמש.', 'cookie-consent-bar'); ?><br>
                <?php _e('הבאנר יוצג למשתמש רק פעם אחת עד שינקה את ה-localStorage של הדפדפן.', 'cookie-consent-bar'); ?>
            </p>

            <?php if (!get_privacy_policy_url()): ?>
                <div class="notice notice-warning">
                    <p>
                        <?php
                        printf(
                            __('שים לב: לא הוגדר עמוד מדיניות פרטיות באתר. %sהגדר עמוד מדיניות פרטיות%s', 'cookie-consent-bar'),
                            '<a href="' . admin_url('options-privacy.php') . '">',
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * הוספת הסגנונות
     */
    public function add_styles() {
        ?>
        <style>
            #cookie-consent-bar {
                position: fixed;
                bottom: 20px;
                right: 20px;
                max-width: 350px;
                background: <?php echo esc_attr($this->get_option('bg_color', '#ffffff')); ?>;
                color: <?php echo esc_attr($this->get_option('text_color', '#333333')); ?>;
                text-align: right;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                font-size: 15px;
                z-index: 99999;
                display: none;
                direction: rtl;
                animation: slideUp 0.3s ease-out;
            }

            @keyframes slideUp {
                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            #cookie-consent-bar .ccb-content {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            #cookie-consent-bar .ccb-text {
                line-height: 1.7;
            }

            #cookie-consent-bar .ccb-text strong {
                display: block;
                margin-bottom: 5px;
            }

            #cookie-consent-bar .ccb-text p {
                margin: 0;
            }

            #cookie-consent-bar .ccb-text a {
                color: #144456;
                text-decoration: underline;
            }

            #cookie-consent-bar .ccb-text a:hover {
                text-decoration: none;
            }

            #cookie-accept-btn {
                background: <?php echo esc_attr($this->get_option('button_bg_color', '#ff8a00')); ?>;
                color: <?php echo esc_attr($this->get_option('button_text_color', '#ffffff')); ?>;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: bold;
                cursor: pointer;
                align-self: flex-end;
                transition: all 0.3s ease;
            }

            #cookie-accept-btn:hover {
                opacity: 0.9;
                transform: translateY(-2px);
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            }

            @media (max-width: 768px) {
                #cookie-consent-bar {
                    max-width: 100%;
                    right: 0;
                    left: 0;
                    bottom: 0;
                    border-radius: 0;
                    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
                }
            }
        </style>
        <?php
    }

    /**
     * הוספת ה-HTML
     */
    public function add_html() {
        ?>
        <div id="cookie-consent-bar" role="alert" aria-live="polite" aria-label="הודעת עוגיות">
            <div class="ccb-content">
                <div class="ccb-text">
                    <strong><?php echo esc_html($this->get_option('title', '🍪 אוהבים עוגיות? גם אנחנו!')); ?></strong>
                    <p>
                        <?php echo esc_html($this->get_option('text', 'אנחנו משתמשים בעוגיות (cookies) לשיפור חוויית הגלישה שלך, הצגת הצעות מותאמות ועוד.')); ?>
                        <?php if (get_privacy_policy_url()): ?>
                            עיין ב<a href="<?php echo esc_url(get_privacy_policy_url()); ?>">מדיניות הפרטיות</a> שלנו.
                        <?php endif; ?>
                    </p>
                </div>
                <button id="cookie-accept-btn" aria-label="אשר שימוש בעוגיות">
                    <?php echo esc_html($this->get_option('button_text', 'הבנתי')); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * הוספת הסקריפטים
     */
    public function add_scripts() {
        ?>
        <script>
            (function() {
                document.addEventListener('DOMContentLoaded', function() {
                    var bar = document.getElementById('cookie-consent-bar');
                    var btn = document.getElementById('cookie-accept-btn');

                    if (!bar || !btn) return;

                    // בדיקה אם localStorage זמין
                    var storageAvailable = false;
                    try {
                        var test = '__storage_test__';
                        localStorage.setItem(test, test);
                        localStorage.removeItem(test);
                        storageAvailable = true;
                    } catch(e) {
                        console.warn('localStorage is not available');
                    }

                    // פונקציה לבדיקת מובייל
                    function isMobile() {
                        return window.matchMedia("(max-width: 768px)").matches;
                    }

                    // הצגת הבאנר אם אין אישור
                    function shouldShowBar() {
                        if (!storageAvailable) return true;
                        return !localStorage.getItem('cookieConsentAccepted');
                    }

                    if (shouldShowBar()) {
                        bar.style.display = 'block';
                    }

                    // התאמת העיצוב למובייל/דסקטופ
                    function adjustLayout() {
                        if (isMobile()) {
                            bar.style.maxWidth = '100%';
                            bar.style.right = '0';
                            bar.style.left = '0';
                            bar.style.bottom = '0';
                            bar.style.borderRadius = '0';
                            bar.style.boxShadow = '0 -2px 10px rgba(0,0,0,0.1)';
                        } else {
                            bar.style.maxWidth = '350px';
                            bar.style.right = '20px';
                            bar.style.left = 'auto';
                            bar.style.bottom = '20px';
                            bar.style.borderRadius = '12px';
                            bar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
                        }
                    }

                    adjustLayout();
                    window.addEventListener('resize', adjustLayout);

                    // טיפול בלחיצה על כפתור האישור
                    btn.addEventListener('click', function() {
                        if (storageAvailable) {
                            localStorage.setItem('cookieConsentAccepted', 'true');
                        }

                        // אנימציה של סגירה
                        bar.style.animation = 'slideDown 0.3s ease-out forwards';

                        setTimeout(function() {
                            bar.style.display = 'none';
                        }, 300);

                        // שליחת אירוע מותאם אישית
                        if (typeof CustomEvent === 'function') {
                            var event = new CustomEvent('cookieConsentAccepted', {
                                detail: { timestamp: new Date() }
                            });
                            document.dispatchEvent(event);
                        }
                    });
                });
            })();
        </script>

        <style>
            @keyframes slideDown {
                from {
                    transform: translateY(0);
                    opacity: 1;
                }
                to {
                    transform: translateY(100px);
                    opacity: 0;
                }
            }
        </style>
        <?php
    }
}

// אתחול התוסף
function ccb_init() {
    new CookieConsentBar();
}
add_action('plugins_loaded', 'ccb_init');

// פעולות בעת הפעלת התוסף
register_activation_hook(__FILE__, 'ccb_activate');
function ccb_activate() {
    // הגדרת ערכי ברירת מחדל
    $default_options = array(
        'title' => '🍪 אוהבים עוגיות? גם אנחנו!',
        'text' => 'אנחנו משתמשים בעוגיות (cookies) לשיפור חוויית הגלישה שלך, הצגת הצעות מותאמות ועוד.',
        'button_text' => 'הבנתי',
        'bg_color' => '#ffffff',
        'text_color' => '#333333',
        'button_bg_color' => '#ff8a00',
        'button_text_color' => '#ffffff'
    );

    if (!get_option('ccb_settings')) {
        add_option('ccb_settings', $default_options);
    }
}

// פעולות בעת כיבוי התוסף
register_deactivation_hook(__FILE__, 'ccb_deactivate');
function ccb_deactivate() {
    // אפשר להוסיף פעולות ניקוי אם צריך
}

// פעולות בעת מחיקת התוסף
register_uninstall_hook(__FILE__, 'ccb_uninstall');
function ccb_uninstall() {
    // מחיקת ההגדרות מהדאטהבייס
    delete_option('ccb_settings');
}