<?php
namespace EventEspresso\core\libraries\iframe_display;

if ( ! defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}



/**
 * Class Iframe
 *
 * @package       Event Espresso
 * @subpackage    core
 * @author        Brent Christensen
 * @since         4.9
 */
class Iframe
{

    /*
    * HTML for notices and ajax gif
    * @var string $title
    */
    protected $title = '';

    /*
    * HTML for the content being displayed
    * @var string $content
    */
    protected $content = '';

    /*
    * whether or not to call wp_head() and wp_footer()
    * @var boolean $enqueue_wp_assets
    */
    protected $enqueue_wp_assets = false;

    /*
    * an array of CSS URLs
    * @var array $css
    */
    protected $css = array();

    /*
    * an array of JS URLs to be set in the HTML header.
    * @var array $header_js
    */
    protected $header_js = array();

    /*
    * an array of JS URLs to be displayed before the HTML </body> tag
    * @var array $footer_js
    */
    protected $footer_js = array();

    /*
    * an array of JSON vars to be set in the HTML header.
    * @var array $localized_vars
    */
    protected $localized_vars = array();



    /**
     * Iframe constructor
     *
     * @param string $title
     * @param string $content
     * @throws \EE_Error
     */
    public function __construct($title, $content)
    {
        global $wp_version;
        if ( ! defined('EE_IFRAME_DIR_URL')) {
            define('EE_IFRAME_DIR_URL', plugin_dir_url(__FILE__));
        }
        $this->setContent($content);
        $this->setTitle($title);
        $this->addStylesheets(
            apply_filters(
                'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__construct__default_css',
                array(
                    'dashicons'        => includes_url('css/dashicons.min.css?ver=' . $wp_version),
                    'espresso_default' => EE_GLOBAL_ASSETS_URL
                                          . 'css/espresso_default.css?ver='
                                          . EVENT_ESPRESSO_VERSION,
                )
            )
        );
        $this->addScripts(
            apply_filters(
                'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__construct__default_js',
                array(
                    'jquery'        => includes_url('js/jquery/jquery.js?ver=' . $wp_version),
                    'espresso_core' => EE_GLOBAL_ASSETS_URL
                                       . 'scripts/espresso_core.js?ver='
                                       . EVENT_ESPRESSO_VERSION,
                )
            )
        );
    }



    /**
     * @param string $title
     * @throws \EE_Error
     */
    public function setTitle($title)
    {
        if (empty($title)) {
            throw new \EE_Error(
                esc_html__('You must provide a page title in order to create an iframe.', 'event_espresso')
            );
        }
        $this->title = $title;
    }



    /**
     * @param string $content
     * @throws \EE_Error
     */
    public function setContent($content)
    {
        if (empty($content)) {
            throw new \EE_Error(
                esc_html__('You must provide content in order to create an iframe.', 'event_espresso')
            );
        }
        $this->content = $content;
    }



    /**
     * @param boolean $enqueue_wp_assets
     */
    public function setEnqueueWpAssets($enqueue_wp_assets)
    {
        $this->enqueue_wp_assets = filter_var($enqueue_wp_assets, FILTER_VALIDATE_BOOLEAN);
    }



    /**
     * @param array $stylesheets
     * @throws \EE_Error
     */
    public function addStylesheets(array $stylesheets)
    {
        if (empty($stylesheets)) {
            throw new \EE_Error(
                esc_html__('A non-empty array of URLs, is required to add a CSS stylesheet to an iframe.', 'event_espresso')
            );
        }
        foreach ($stylesheets as $handle => $stylesheet) {
            $this->css[$handle] = $stylesheet;
        }
    }



    /**
     * @param array $scripts
     * @param bool  $add_to_header
     * @throws \EE_Error
     */
    public function addScripts(array $scripts, $add_to_header = false)
    {
        if (empty($scripts)) {
            throw new \EE_Error(
                esc_html__('A non-empty array of URLs, is required to add Javascript to an iframe.', 'event_espresso')
            );
        }
        foreach ($scripts as $handle => $script) {
            if ($add_to_header) {
                $this->header_js[$handle] = $script;
            } else {
                $this->footer_js[$handle] = $script;
            }
        }
    }



    /**
     * @param array  $vars
     * @param string $var_name
     * @throws \EE_Error
     */
    public function addLocalizedVars(array $vars, $var_name = 'eei18n')
    {
        if (empty($vars)) {
            throw new \EE_Error(
                esc_html__('A non-empty array of vars, is required to add localized Javascript vars to an iframe.',
                    'event_espresso')
            );
        }
        foreach ($vars as $handle => $var) {
            if ($var_name === 'eei18n') {
                \EE_Registry::$i18n_js_strings[$handle] = $var;
            } else {
                if ( ! isset($this->localized_vars[$var_name])) {
                    $this->localized_vars[$var_name] = array();
                }
                $this->localized_vars[$var_name][$handle] = $var;
            }
        }
    }



    /**
     * @return void
     */
    public function display()
    {
        echo $this->getTemplate();
        exit;
    }



    /**
     * @return string
     */
    public function getTemplate()
    {
	    return \EEH_Template::display_template(
            __DIR__ . '\iframe_wrapper.template.php',
            array(
                'title'             => apply_filters(
                    'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__getTemplate__title',
                    $this->title
                ),
                'content'           => apply_filters(
                    'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__getTemplate__content',
                    $this->content
                ),
                'enqueue_wp_assets' => apply_filters(
                    'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__getTemplate__enqueue_wp_assets',
                    $this->enqueue_wp_assets
                ),
                'css'               => (array)apply_filters(
                    'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__getTemplate__css_urls',
                    $this->css
                ),
                'header_js'         => (array)apply_filters(
                    'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__getTemplate__header_js_urls',
                    $this->header_js
                ),
                'footer_js'         => (array)apply_filters(
                    'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__getTemplate__footer_js_urls',
                    $this->footer_js
                ),
                'eei18n'            => apply_filters(
                    'FHEE__\EventEspresso\core\libraries\iframe_display\Iframe__getTemplate__eei18n_js_strings',
                    \EE_Registry::localize_i18n_js_strings() . $this->localizeJsonVars()
                ),
                'notices'           => \EEH_Template::display_template(
                    EE_TEMPLATES . 'espresso-ajax-notices.template.php',
                    array(),
                    true
                ),
            ),
            true
        );
    }



    /**
     * localizeJsonVars
     *
     * @return string
     */
    public function localizeJsonVars()
    {
        $JSON = '';
        foreach ( (array) $this->localized_vars as $var_name => $vars) {
            foreach ( (array) $vars as $key => $value) {
	            $this->localized_vars[ $var_name ] = $this->encodeJsonVars( $value );
            }
	        $JSON .= "/* <![CDATA[ */ var {$var_name} = " . wp_json_encode( $this->localized_vars[ $var_name ] ) . '; /* ]]> */';
        }
        return $JSON;
    }



	/**
	 * @param bool|int|float|string|array $var
	 * @return array
	 */
	public function encodeJsonVars( $var ) {
		if ( is_array( $var ) ) {
			$localized_vars = array();
			foreach( (array) $var as $key => $value ) {
				$localized_vars[ $key ] = $this->encodeJsonVars( $value );
			}
			return $localized_vars;
		} else if ( is_scalar( $var ) ) {
			return html_entity_decode( (string) $var, ENT_QUOTES, 'UTF-8' );
		}
		return null;
	}



}
// End of file Iframe.php
// Location: /Iframe.php