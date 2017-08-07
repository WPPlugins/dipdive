<?php
/**
 * classes/Dipdive.php
 *
 * This class handles hooks to insert a TinyMCE button for Dipdive.com. Code is based on the
 * Vipers Video Quicktags plugin code.
 * (http://www.viper007bond.com/wordpress-plugins/vipers-video-quicktags/)
 *
 * @author Nick de Groot <ngroot@brothersinart.com>
 *
 */


/**
 * Dipdive Embed class
 *
 * @author Nick de Groot <ngroot@dipdive.com>
 *
 */
class Dipdive
{

    var $version = '1.1.1';
    var $url = 'http://play.dipdive.com';


    /**
     * Constructor
     *
     */
    function Dipdive()
    {
        // Register editor button hooks
        add_filter('tiny_mce_version', array(&$this, 'tiny_mce_version'));
        add_filter('mce_external_plugins', array(&$this, 'mce_external_plugins'));
        add_filter('mce_buttons', array(&$this, 'mce_buttons') );

        if ( is_admin() ) {

            // Editor pages only
            if (in_array(basename($_SERVER['PHP_SELF']), apply_filters('dd_editor_pages', array('post-new.php', 'page-new.php', 'post.php', 'page.php')))) {
                wp_enqueue_script('tinymce_dipdive', plugins_url('/dipdive/resource/tinymce_dipdive.js'), array(), $this->version);

                add_action('admin_footer', array(&$this, 'printDialogScreen'));

                wp_enqueue_script('jquery-ui-dialog');
                wp_enqueue_style('dd-jquery-ui', plugins_url('/dipdive/resource/dd-jquery-ui.css'), array(), $this->version, 'screen' );
            }
        }


        // add our shortcode dipdive filter
        add_shortcode('dipdive', array(&$this, 'shortcode_dipdive'));

    }


    /**
     * Change the version line of the tinyMCE to break the cache
     *
     * @param string $version TinyMCE version
     *
     * @return string
     */
    function tiny_mce_version($version) {
        return $version . '-dipdive' . $this->version . 'line' . $this->settings['tinymceline'];
    }


    /**
     * Let TinyMCE load our plugin code
     *
     * @param array $plugins
     */
    function mce_external_plugins($plugins) {
        $plugins['dipdive'] = plugins_url('/dipdive/resource/tinymce/editor_plugin.js');
        return $plugins;
    }


    /**
     * Add our Dipdive button to the TinyMCE list
     *
     * @param array $buttons TinyMCE buttons
     *
     * @return array
     */
    function mce_buttons($buttons) {
        array_push($buttons, 'DipdiveBtn');
        return $buttons;
    }


    /**
     * Print the Dialog HTML
     *
     * @return void;
     */
    function printDialogScreen()
    {
        echo file_get_contents(WP_PLUGIN_DIR . '/dipdive/resource/tinymce/dialog.html');
    }


    /**
     * Dipdive shortcode handler
     *
     * @param array  $attr    Shortcode attributes
     * @param string $content Shortcode contents
     * @param string $code    Shortcode code
     *
     * @return string Embed code replacement
     */
    function shortcode_dipdive($attr, $content = null, $code = "") {
        $output = '';
        $url = '';

        $embed_width = 425;
        $embed_height = 385;


        if ('http://' == substr($content, 0, 7)) {
            // we recieved a complete url
            $urlParts = parse_url($content);

            // check first if it's really dipdive
            if (!strstr($urlParts['host'], 'dipdive.com'))
                return false;

            // split the path to determine what kind of embed we have
            $pathParts = explode('/', $urlParts['path']);
            array_shift($pathParts);

            if (strtolower($pathParts[0]) == 'media' && is_numeric($pathParts[1])) {
                $url = '/i/' . $pathParts[1];
            } else if (strtolower($pathParts[0]) == 'poll' && is_numeric($pathParts[1])) {
                // this is a poll.. fake the attr array
                $attr['poll'] = $pathParts[1];
            } else if (
                strtolower($pathParts[0]) == 'member' &&
                (isset($pathParts[2]) && $pathParts[2] == 'media') &&
                is_numeric($pathParts[3])
            ) {
                // member media
                $url = '/i/' . $pathParts[3];
            } else if (is_numeric($pathParts[0])) {
                // assume only id is given
                $url = '/i/' . $pathParts[0];
            }

        } else if (is_array($attr)) {
            // assume we have some attributes
            if (array_key_exists('video', $attr)) {
                $url = '/i/' . $attr['video'];
            } else if (array_key_exists('item', $attr)) {
                $url = '/i/' . $attr['item'];
            } else if (array_key_exists('playlist', $attr)) {
                $url = '/p/' . $attr['playlist'];
            } else if (array_key_exists('audio', $attr)) {
                $url = '/s/i/' . $attr['audio'];
                $embed_height = 50;
            } else if (array_key_exists(0, $attr)) {
                $url = '/i/' . $attr[0];
            }
        }

        if ($url) {
            $output = '<object width="' . $embed_width . '" height="' . $embed_height . '" data="' . $this->url . $url . '">' .
                '<param name="movie" value="' . $this->url . $url . '"></param>' .
                '<param name="allowfullscreen" value="true"></param>' .
                '<embed src="' . $this->url . $url . '" type="application/x-shockwave-flash" allowFullScreen="true" width="' . $embed_width . '" height="' . $embed_height . '" />' .
                '</object>';
        } elseif (is_array($attr) && array_key_exists('poll', $attr)) {
            // Embed a poll. Include Dipdive poll javascripts
            $output = '<div id="dd-poll-embed-' . $attr['poll'] . '">' .
                '<p>Visit <a href="http://dipdive.com/">Dipdive</a> to take our poll.</p></div>' .
                '<script type="text/javascript" src="' . $this->url . '/poll/' . $attr['poll'] . '"></script>';
        }

        // return with <p> tags surrounding code
        return wpautop($output);
    }


}

