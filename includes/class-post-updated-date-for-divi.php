<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @see         https://www.linknacional.com/
 * @since       1.0.0
 */

/*
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 *
 * @author     Link Nacional
 */
if ( ! class_exists('Lkn_Post_Updated_Date_For_Divi') ) {
    final class Lkn_Post_Updated_Date_For_Divi {
        /**
         * The loader that's responsible for maintaining and registering all hooks that power
         * the plugin.
         *
         * @since    1.0.0
         *
         * @var Lkn_Payment_Banking_Slip_Pix_For_Lifterlms_Loader maintains and registers all hooks for the plugin
         */
        private $loader;

        /**
         * The unique identifier of this plugin.
         *
         * @since    1.0.0
         *
         * @var string the string used to uniquely identify this plugin
         */
        private $plugin_name;

        /**
         * The current version of the plugin.
         *
         * @since    1.0.0
         *
         * @var string the current version of the plugin
         */
        private $version;

        // For singleton pattern.
        private static $instance = false;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function __construct() {
            if ( defined( 'LKN_PUDD_VERSION' ) ) {
                $this->version = LKN_PUDD_VERSION;
            } else {
                $this->version = '1.0.2';
            }
            $this->plugin_name = 'post-updated-date-for-divi';

            $this->load_dependencies();
            $this->set_locale();

            add_action('init', array($this, 'init'));
        }

        public static function get_instance() {
            if ( ! self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Call the functions for change the data and time text, and the filter to do this.
         *
         * @see         https://www.linknacional.com/
         * @since       1.0.0
         * @version     1.0.2
         */
        public function init(): void {
            add_filter( 'get_the_time', array($this, 'et_last_modified_date_blog'), 10, 1);
            add_filter('post_date_column_status', array($this, 'change_post_status_text'), 10, 4);
            add_filter('post_date_column_time', array($this, 'change_post_date_text'), 10, 2);
            add_filter('wp_insert_post_data', array($this, 'change_post_time_text'), 10, 2);
        }

        /**
         * When get_the_time or get_the_date is used, this function verify if it has updated or only published
         * and return the correct time.
         *
         * @see         https://www.linknacional.com/
         * @since       1.0.0
         * @version     1.0.2
         *
         * @return int date
         *
         */
        public function et_last_modified_date_blog($param) {
            // Verify post type.
            if ('post' === get_post_type()) {
                // Get time format.
                $time_format = get_option('time_format');

                // Get date format.
                $date_format = get_option('date_format');

                // Flag.
                $divi_dformat = null;
                $ex_ddate = null;

                // If Divi loaded, get Divi date format.
                if (function_exists('et_divi_post_meta')) {
                    $divi_dformat = et_get_option( 'divi_date_format' );
                }

                // If time format is empty, set a default value.
                if (empty($time_format)) {
                    $time_format = update_option('time_format', 'H:i');
                }

                // If date format is empty, set a default value.
                if (empty($date_format)) {
                    $date_format = update_option('date_format', 'd/m/Y');
                }

                // If divi date format is empty, set default value equal .
                if (function_exists('et_divi_post_meta') && empty($divi_dformat)) {
                    $divi_dformat = et_update_option( 'divi_date_format', $date_format) ?? 'M j, Y';
                }

                // Get Post time, and Post modified time.
                $the_time = get_post_time( 'Y-m-d H:i', false, null, true );
                $the_modified = get_post_modified_time( 'Y-m-d H:i', false, null, true );

                // Date instances to suit the format.
                $the_published = new DateTime($the_time);
                $the_updated = new DateTime($the_modified);

                // For length comparations. Ex: $param = 21/08/2023, $date_format = d/m/Y, strlen will be different.
                $ex_time = gmdate($time_format);
                $ex_date = gmdate($date_format);

                if (function_exists('et_divi_post_meta')) {
                    $ex_ddate = gmdate($divi_dformat);
                }

                if ( ! empty($param)) {
                    if (strlen($param) === 10 && preg_match('/^\d+$/', $param)) {// Only numbers in $param, $param = Unix
                        // Time convert to Unix timestamp for get_the_time('U').
                        $the_time = get_post_time( 'U' );
                        $the_modified = get_post_modified_time( 'U' );

                        return $the_modified <= $the_time ? $the_time : $the_modified;
                    }
                    if (strpos($param, ':') !== false && strlen($param) === strlen($ex_time)) {// Verification of parameter in the get_the_time() call, equals than time_format:
                        return $the_modified <= $the_time ? date_i18n($time_format, $the_published->getTimestamp()) : date_i18n($time_format, $the_updated->getTimestamp());
                    }
                    if (function_exists('et_divi_post_meta') && strlen($param) >= strlen($ex_ddate)) {// Verification of parameter in the get_the_time() call, equals than date_format or divi_date_format:
                        return $the_modified <= $the_time ? date_i18n($divi_dformat, $the_published->getTimestamp()) : date_i18n($divi_dformat, $the_updated->getTimestamp());
                    }
                    if (strlen($param) >= strlen($ex_date)) {
                        return $the_modified <= $the_time ? date_i18n($date_format, $the_published->getTimestamp()) : date_i18n($date_format, $the_updated->getTimestamp());
                    }
                }
            }
        }

        /**
         * Verify the published time and the update time of an post, and update the text show to user.
         *
         * @see         https://www.linknacional.com/
         * @since       1.0.0
         * @version     1.0.2
         *
         * @return string text to updated post time text
         *
         */
        public function change_post_time_text($data, $postarr) {
            // Verifique se $postarr é um array e não está vazio
            if ( ! is_array($postarr) || empty($postarr)) {
                return $data;
            }

            // Verifique se as chaves 'post_date' e 'post_modified' existem no array antes de criar o DateTime
            $post_date = isset($data["post_date"]) ? $data["post_date"] : '';
            $post_modified = isset($data["post_modified"]) ? $data["post_modified"] : '';

            // Verifique se os valores não são vazios antes de criar o DateTime
            $the_time = ! empty($post_date) ? new DateTime($post_date) : new DateTime();
            $the_modified = ! empty($post_modified) ? new DateTime($post_modified) : new DateTime();

            // Formatar as datas
            $text_time_published = $the_time->format("Y-m-d H:i:s");
            $text_time_updated = $the_modified->format("Y-m-d H:i:s");

            // Atualizar a data do post com base no status do post
            $data["post_date"] = ('future' === $data["post_status"]) ? $text_time_published : ($the_modified <= $the_time ? $text_time_published : $text_time_updated);

            return $data;
        }

        /**
         * Verify the published time and the update time of an post, and update the status text show to user.
         *
         * @see         https://www.linknacional.com/
         * @since       1.0.0
         * @version     1.0.2
         *
         * @return string text to updated status text
         *
         */
        public function change_post_status_text($status, $post, $column_name, $mode) {
            // Verify post type, and define the new status text show to user.
            if ($post) {
                // Post times to compare.
                $the_time = new DateTime( $post->post_date );
                $the_modified = new DateTime( $post->post_modified);
                $the_time = $the_time->format("Y-m-d H:i");
                $the_modified = $the_modified->format("Y/m/d H:i");

                // Set the new status texts.
                $text_updated = __( 'Updated:', 'post-updated-date-for-divi' );

                $text_published = __( 'Published:', 'post-updated-date-for-divi' );

                $text_scheduled = __( 'Scheduled:', 'post-updated-date-for-divi' );
                // // To keep the scheduled status text shown.
                if ('future' === $post->post_status) {
                    return $text_scheduled;
                } else {
                    return $the_modified <= $the_time ? $text_published  : $text_updated;
                }
            }
        }

        public function change_post_date_text($date, $post) {
            if ($post) {
                $format = get_option("date_format");

                $post_date = new DateTime($post->post_date);
                $post_modified = new DateTime($post->post_modified);

                if ($format) {
                    $formatted_post_date = $post_date->format($format);
                    $formatted_post_modified = $post_modified->format($format);
                } else {
                    $formatted_post_date = $post_date->format("Y-m-d H:i");
                    $formatted_post_modified = $post_modified->format("Y-m-d H:i");
                }

                if ('future' === $post->post_status) {
                    return $formatted_post_date;
                } else {
                    return $formatted_post_modified <= $formatted_post_date ? $formatted_post_date : $formatted_post_modified;
                }
            }

            // Se o post não existir, retorna a data original
            return $date;
        }

        /**
         * Run the loader to execute all of the hooks with WordPress.
         *
         * @since    1.0.0
         */
        public function run(): void {
            $this->loader->run();
        }

        /**
         * The name of the plugin used tso uniquely identify it within the context of
         * WordPress and to define internationalization functionality.
         *
         * @since     1.0.0
         *
         * @return string the name of the plugin
         */
        public function get_plugin_name() {
            return $this->plugin_name;
        }

        /**
         * The reference to the class that orchestrates the hooks with the plugin.
         *
         * @since     1.0.0
         *
         * @return Lkn_Post_Updated_Date_For_Divi_Loader orchestrates the hooks of the plugin
         */
        public function get_loader() {
            return $this->loader;
        }

        /**
         * Retrieve the version number of the plugin.
         *
         * @since     1.0.0
         *
         * @return string the version number of the plugin
         */
        public function get_version() {
            return $this->version;
        }

        /**
         * Load the required dependencies for this plugin.
         *
         * Include the following files that make up the plugin:
         *
         * - Lkn_Post_Updated_Date_For_Divi_Loader. Orchestrates the hooks of the plugin.
         * - Lkn_Post_Updated_Date_For_Divi_i18n. Defines internationalization functionality.
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @since    1.0.0
         */
        private function load_dependencies(): void {
            /**
             * The class responsible for orchestrating the actions and filters of the
             * core plugin.
             */
            require_once plugin_dir_path( __DIR__ ) . 'includes/class-post-updated-date-for-divi-loader.php';

            /**
             * The class responsible for defining internationalization functionality
             * of the plugin.
             */
            require_once plugin_dir_path( __DIR__ ) . 'includes/class-post-updated-date-for-divi-i18n.php';

            $this->loader = new Lkn_Post_Updated_Date_For_Divi_Loader();
        }

        /**
         * Define the locale for this plugin for internationalization.
         *
         * Uses the Lkn_Post_Updated_Date_For_Divi_i18n class in order to set the domain and to register the hook
         * with WordPress.
         *
         * @since    1.0.0
         */
        private function set_locale(): void {
            $plugin_i18n = new Lkn_Post_Updated_Date_For_Divi_i18n();

            $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
        }
    }

    Lkn_Post_Updated_Date_For_Divi::get_instance();
}
