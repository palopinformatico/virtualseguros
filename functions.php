<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'theme_start_session' ) ) {
	function theme_start_session() {
		if ( ! is_admin() && ! headers_sent() && session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}
	}
}
add_action( 'init', 'theme_start_session', 1 );

define( 'HELLO_ELEMENTOR_VERSION', '3.4.7' );
define( 'EHP_THEME_SLUG', 'hello-elementor' );

define( 'HELLO_THEME_PATH', get_template_directory() );
define( 'HELLO_THEME_URL', get_template_directory_uri() );
define( 'HELLO_THEME_ASSETS_PATH', HELLO_THEME_PATH . '/assets/' );
define( 'HELLO_THEME_ASSETS_URL', HELLO_THEME_URL . '/assets/' );
define( 'HELLO_THEME_SCRIPTS_PATH', HELLO_THEME_ASSETS_PATH . 'js/' );
define( 'HELLO_THEME_SCRIPTS_URL', HELLO_THEME_ASSETS_URL . 'js/' );
define( 'HELLO_THEME_STYLE_PATH', HELLO_THEME_ASSETS_PATH . 'css/' );
define( 'HELLO_THEME_STYLE_URL', HELLO_THEME_ASSETS_URL . 'css/' );
define( 'HELLO_THEME_IMAGES_PATH', HELLO_THEME_ASSETS_PATH . 'images/' );
define( 'HELLO_THEME_IMAGES_URL', HELLO_THEME_ASSETS_URL . 'images/' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
					'navigation-widgets',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);
			add_theme_support( 'align-wide' );
			add_theme_support( 'responsive-embeds' );

			/*
			 * Editor Styles
			 */
			add_theme_support( 'editor-styles' );
			add_editor_style( 'assets/css/editor-styles.css' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				HELLO_THEME_STYLE_URL . 'reset.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				HELLO_THEME_STYLE_URL . 'theme.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				HELLO_THEME_STYLE_URL . 'header-footer.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}

require HELLO_THEME_PATH . '/theme.php';

HelloTheme\Theme::instance();

function agregar_bootstrap() {
    wp_enqueue_style(
        'bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
        array(),
        '5.3.8'
    );
}
add_action('wp_enqueue_scripts', 'agregar_bootstrap');

function recuperarToken(){
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://segurossurab2cdev.b2clogin.com/segurossurab2cdev.onmicrosoft.com/B2C_1_APIM_External/oauth2/v2.0/token?api-version=1.0&scope=https%3A%2F%2Fsegurossurab2cdev.onmicrosoft.com%2F4a8bbadd-02c8-4c43-b451-59b1740ccbe5%2F.default',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query([
			'grant_type' => 'client_credentials',
			'client_id' => 'd37c8bf6-69e9-4b84-a5c2-eafedf50f133',
			'client_secret' => 'oGe8Q~UyPSljI__ThJH68FSqwILdGkBt7Aw-fasq'
		]),
		CURLOPT_HTTPHEADER => [
			'Content-Type: application/x-www-form-urlencoded',
			'Cookie: fpc=Aj3UPwGQz6pJuWp0UsWR-u_mn7k0AQAAABVywt8OAAAA; fpc=Aj3UPwGQz6pJuWp0UsWR-u892oSoAQAAAGlvxt8OAAAA'
		],
	]);

	$response = curl_exec($curl);
	curl_close($curl);

	// Decodificamos el JSON
	$data = json_decode($response, true);

	// Verificamos y obtenemos el token
	if (isset($data['access_token'])) {
		$accessToken = $data['access_token'];
		//echo "Token: " . $accessToken;
	} else {
		echo "No se pudo obtener el token.";
	}
	
	return $accessToken;
}

add_action('wp_enqueue_scripts', 'cargar_librerias_select2');
function cargar_librerias_select2() {
    // jQuery (ya viene con WP, pero por si acaso)
    wp_enqueue_script('jquery');

    // Select2 CSS
    wp_enqueue_style(
        'select2-css',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
        [],
        null
    );

    // Select2 JS
    wp_enqueue_script(
        'select2-js',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
        ['jquery'],
        null,
        true
    );
}

function renderizarTarjetaSeguro($seguro) {
	echo '<div class="card">';
	echo '    <div class="card-header">';
	echo '        <h3>' . htmlspecialchars($seguro['titulo_seguro']) . '</h3>';
	echo '    </div>';
	echo '    <div class="card-body">';
	echo '        <div class="logo-section">';
	echo '            <img src="' . htmlspecialchars($seguro['logo']) . '" alt="' . htmlspecialchars($seguro['nombre_aseguradora']) . ' logo">';
	echo '        </div>';
	echo '        <div class="details-section">';
	echo '            <h4>¿Qué cubre?</h4>';
	echo '            <ul class="coverage-list">';
	foreach ($seguro['cubiertas'] as $cubierta) {
		echo '                <li><span class="check-icon">&#10003;</span> ' . htmlspecialchars($cubierta) . '</li>';
	}
	echo '            </ul>';
	echo '            <a href="#" class="more-details" onclick="togglePopup(event, \'' . $seguro['nombre_plan'] . '\')">Más detalles y coberturas</a>';
	echo '            <div id="coverage-popup-' . $seguro['nombre_plan'] . '" class="coverage-popup">';
	echo '                <div class="popup-content">';
	echo '                    <span class="close-popup" onclick="togglePopup(event, \'' . $seguro['nombre_plan'] . '\')">&times;</span>';
	echo '                    <h3>Coberturas Completas</h3>';
	echo '                    <ul class="popup-coverage-list">';
	foreach ($seguro['detalle_coberturas'] as $detalle_coberturas) {
		echo '                <li>' . htmlspecialchars($detalle_coberturas) . '</li>';
	}
	echo '                    </ul>';
	echo '                </div>';
	echo '            </div>';
	echo '        </div>';
	echo '        <div class="price-section">';
	echo '            <span class="price-clp">$ ' . number_format(htmlspecialchars($seguro['precio_clp_aprox']) / 12, 0, ',', '.') . ' aprox</span>';
	echo '            <span class="price-uf">UF ' . number_format($seguro['uf_anual'] / 12, 2, ',', '') . ' mensual</span>';
		// Aquí el formulario POST
	echo '            <form method="POST" action="/datos-bien">';
	echo '                <input type="hidden" name="nombrePlan" value="' . htmlspecialchars($seguro['nombre_plan']) . '">';
	echo '                <input type="hidden" name="precio_clp" value="' . htmlspecialchars($seguro['precio_clp_aprox'] / 12) . '">';
	echo '                <input type="hidden" name="cotizacionServicioID" value="' . htmlspecialchars($seguro['cotizacionServicioID']) . '">';
	echo '                <input type="hidden" name="csrf_token" value="fla857"/>';
	echo '                <button type="submit" class="btn-comprar">Contratar</button>';
	echo '            </form>';
	echo '        </div>';
	echo '    </div>';
	echo '</div>';
}

add_action('wp_ajax_get_marcas_sura', 'get_marcas_sura');
add_action('wp_ajax_nopriv_get_marcas_sura', 'get_marcas_sura');
function get_marcas_sura() {
    $estado     = false;
    $respuesta  = 'Ocurrió un error';
    $data       = null;

    try {
        $api_url = 'https://hub-qa.segurossura.cl/seguros-generales-vehiculo/api/marcas';

		$token = recuperarToken();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Ocp-Apim-Subscription-Key: 712c61e9edff4ad4842111902b1dcf18',
                'Accept: application/json'
            ),
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false
        ));

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			$respuesta = 'cURL error: ' . curl_error($curl);
		} else {
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($http_code === 200) {
				$estado = true;
				$respuesta = 'Datos obtenidos correctamente';
				$data = json_decode($response, true);
			} else {
				$respuesta = 'Error HTTP: ' . $http_code . ' - ' . $response;
			}
		}
		curl_close($curl);

    } catch (Exception $ex) {
        $respuesta = 'Excepción: ' . $ex->getMessage();
    }

    echo json_encode([
        'estado' => $estado,
        'respuesta' => $respuesta,
        'data' => $data
    ]);
    exit;
}
// Obtener modelos por código de marca
add_action('wp_ajax_get_modelos_sura', 'get_modelos_sura');
add_action('wp_ajax_nopriv_get_modelos_sura', 'get_modelos_sura');
function get_modelos_sura() {
    $codigoMarca = isset($_GET['marca']) ? $_GET['marca'] : '';
    if (!$codigoMarca) {
        echo json_encode(['estado' => false, 'respuesta' => 'Código de marca no válido', 'data' => null]);
        exit;
    }
	
    $url = "https://hub-qa.segurossura.cl/seguros-generales-vehiculo/api/marca/".urlencode($codigoMarca)."/modelos";
	$token = recuperarToken();

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Ocp-Apim-Subscription-Key: 712c61e9edff4ad4842111902b1dcf18',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
	
	if (curl_errno($curl)) {
		$respuesta = 'cURL error: ' . curl_error($curl);
	} else {
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($http_code === 200) {
			$estado = true;
			$respuesta = 'Datos obtenidos correctamente';
			$data = json_decode($response, true);
		} else {
			$respuesta = 'Error HTTP: ' . $http_code . ' - ' . $response;
		}
	}
	curl_close($curl);
	
    echo json_encode([
        'estado' => $estado,
        'respuesta' => $respuesta,
        'data' => $data
    ]);
	
    exit;
}

add_action('wp_ajax_get_regiones_sura', 'get_regiones_sura');
add_action('wp_ajax_nopriv_get_regiones_sura', 'get_regiones_sura');
function get_regiones_sura() {
    $estado     = false;
    $respuesta  = 'Ocurrió un error';
    $data       = null;

    try {
        $api_url = 'https://hub-qa.segurossura.cl/seguros-generales-localizacion/api/regiones';

		$token = recuperarToken();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Ocp-Apim-Subscription-Key: 6866acda868341dd9ab261965122a0e0',
                'Accept: application/json'
            ),
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false
        ));

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			$respuesta = 'cURL error: ' . curl_error($curl);
		} else {
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($http_code === 200) {
				$estado = true;
				$respuesta = 'Datos obtenidos correctamente';
				$data = json_decode($response, true);
			} else {
				$respuesta = 'Error HTTP: ' . $http_code . ' - ' . $response;
			}
		}
		curl_close($curl);

    } catch (Exception $ex) {
        $respuesta = 'Excepción: ' . $ex->getMessage();
    }

    echo json_encode([
        'estado' => $estado,
        'respuesta' => $respuesta,
        'data' => $data
    ]);
    exit;
}
// Obtener comunas por region
add_action('wp_ajax_get_comunas_sura', 'get_comunas_sura');
add_action('wp_ajax_nopriv_get_comunas_sura', 'get_comunas_sura');
function get_comunas_sura() {
    $codigoRegion = isset($_GET['region']) ? $_GET['region'] : '';
    if (!$codigoRegion) {
        echo json_encode(['estado' => false, 'respuesta' => 'Código de region no válido', 'data' => null]);
        exit;
    }
	
    $url = "https://hub-qa.segurossura.cl/seguros-generales-localizacion/api/regiones/".urlencode($codigoRegion)."/comunas";
	$token = recuperarToken();

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Ocp-Apim-Subscription-Key: 6866acda868341dd9ab261965122a0e0',
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
	
	if (curl_errno($curl)) {
		$respuesta = 'cURL error: ' . curl_error($curl);
	} else {
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($http_code === 200) {
			$estado = true;
			$respuesta = 'Datos obtenidos correctamente';
			$data = json_decode($response, true);
		} else {
			$respuesta = 'Error HTTP: ' . $http_code . ' - ' . $response;
		}
	}
	curl_close($curl);
	
    echo json_encode([
        'estado' => $estado,
        'respuesta' => $respuesta,
        'data' => $data
    ]);
	
    exit;
}

function manejar_redirects_template() {
    $request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/');
    wp_custom_log('en funcion template_redirect ' . $request_path);

    if (preg_match('#^subir-documento/([a-f0-9\-]{8}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{12})/?$#i', $request_path, $matches)) {
        $uuid = $matches[1];
        $redirect_url = add_query_arg(['uuid' => $uuid], home_url('/subir-documento'));
        wp_safe_redirect($redirect_url, 302);
        exit;
    }

    if (preg_match('#^lap-solicitud-usado/([a-f0-9\-]{8}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{4}-[a-f0-9\-]{12})/?$#i', $request_path, $matches)) {
        $uuid = $matches[1];
        $redirect_url = add_query_arg(['uuid' => $uuid], home_url('/lap-solicitud-usado'));
        wp_safe_redirect($redirect_url, 302);
        exit;
    }

    if (preg_match('#^oneclick-callback/?$#', $request_path)) {
		wp_custom_log('en funcion oneclick-callback ' . $request_path);
        $target = isset($_SESSION['oneclick_target']) && !empty($_SESSION['oneclick_target'])
            ? sanitize_text_field($_SESSION['oneclick_target'])
            : (isset($_GET['target']) ? sanitize_text_field($_GET['target']) : 'subir-documento');
        $allowed_targets = ['subir-documento', 'lap-solicitud-usado'];

        if (!in_array($target, $allowed_targets, true)) {
            $target = 'subir-documento';
        }

        wp_custom_log('target resuelto en oneclick-callback: ' . $target);

        $redirect_url = home_url('/' . $target);
        $query_args = array_filter($_GET, function ($key) {
            return $key !== 'target';
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($query_args)) {
            $redirect_url = add_query_arg($query_args, $redirect_url);
        }

        if (!empty($_GET['uuid'])) {
            $redirect_url = add_query_arg(['uuid' => sanitize_text_field($_GET['uuid'])], $redirect_url);
        }

        wp_safe_redirect($redirect_url, 302);
        exit;
    }

    if (is_404()) {
        wp_safe_redirect(home_url('/'), 302);
        exit;
    }
}
add_action('template_redirect', 'manejar_redirects_template');

function cotizacion_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/page-cotizador.php';
    return ob_get_clean();
}
add_shortcode('cotizacion_php', 'cotizacion_php_shortcode');

function consultas_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/consultas.php';
    return ob_get_clean();
}
add_shortcode('consultas_php', 'consultas_php_shortcode');

function page_consultas_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/page-consultas.php';
    return ob_get_clean();
}
add_shortcode('page_consultas_php', 'page_consultas_php_shortcode');

function cotizacion_res_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/page-cotizador-resultados.php';
    return ob_get_clean();
}
add_shortcode('cotizacion_res_php', 'cotizacion_res_php_shortcode');

function datos_bien_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/datos-bien.php';
    return ob_get_clean();
}
add_shortcode('datos_bien_php', 'datos_bien_php_shortcode');

function finaliza_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/finaliza.php';
    return ob_get_clean();
}
add_shortcode('finaliza_php', 'finaliza_php_shortcode');

function lap_solicitud_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/lap-solicitud.php';
    return ob_get_clean();
}
add_shortcode('lap_solicitud_php', 'lap_solicitud_php_shortcode');

function lap_solicitud_usado_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/lap-solicitud-usado.php';
    return ob_get_clean();
}
add_shortcode('lap_solicitud_usado_php', 'lap_solicitud_usado_php_shortcode');

function subir_documento_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/subir-documento.php';
    return ob_get_clean();
}
add_shortcode('subir_documento_php', 'subir_documento_php_shortcode');

function ver_datos_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/ver-datos.php';
    return ob_get_clean();
}
add_shortcode('ver_datos_php', 'ver_datos_php_shortcode');

function ver_consultas_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/ver-consultas.php';
    return ob_get_clean();
}
add_shortcode('ver_consultas_php', 'ver_consultas_php_shortcode');

function exportar_excel_php_shortcode() {
    ob_start();
    include ABSPATH . 'wp-content/themes/hello-elementor/exportar_excel.php';
    return ob_get_clean();
}
add_shortcode('exportar_excel_php', 'exportar_excel_php_shortcode');

function wp_custom_log($data) {
    $log_file = ABSPATH . 'virtual-seguros-log.log';
    $time = date("Y-m-d H:i:s");
    $line = "[" . $time . "] " . print_r($data, true) . "\n";
    error_log($line, 3, $log_file);
}

function crearPrimeraCotizacion($amaterno, $apaterno, $comuna, $fecha_nacimiento, $nombre, $rut, $anio, $marca, $modelo, $patente, $uso){
	
    $url = 'https://hub-qa.segurossura.cl/seguros-generales/vechiculos-cotizacion';
	$token = recuperarToken();

    $headers = [
        'Content-Type' => 'text/xml; charset=utf-8',
        'SOAPAction'   => '"http://ws.rsa.cl/WsIndustrializacion/CotizaOfertaBase"',
        'Authorization'=> 'Bearer ' . $token,
        'Ocp-Apim-Subscription-Key' => '712c61e9edff4ad4842111902b1dcf18',
    ];

    $body = <<<XML
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.rsa.cl/" xmlns:epv="http://schemas.datacontract.org/2004/07/EPV.Servicios.Services">
   <soapenv:Header>
      <ws:DatosTransaccion>
         <epv:contrasena>Sura2025_06_R</epv:contrasena>
         <epv:fechaHora>2021-12-28</epv:fechaHora>
         <epv:idContexto></epv:idContexto>
         <epv:idTransaccion></epv:idTransaccion>
         <epv:sistema></epv:sistema>
         <epv:usuario>77496140@ProductoraDiaz.cl</epv:usuario>
      </ws:DatosTransaccion>
   </soapenv:Header>
   <soapenv:Body>
      <ws:CotizaOfertaRequest>
         <ws:CotizacionOferta>
            <ws:DatosBase>
               <ws:Asegurado>
                  <ws:apellidoMaterno>$amaterno</ws:apellidoMaterno>
                  <ws:apellidoPaterno>$apaterno</ws:apellidoPaterno>
                  <ws:codigoComuna>$comuna</ws:codigoComuna>
                  <ws:fechaNacimiento>$fecha_nacimiento</ws:fechaNacimiento>
                  <ws:nombres>$nombre</ws:nombres>
                  <ws:razonSocial>$nombre $apaterno $amaterno</ws:razonSocial>
                  <ws:rut>$rut</ws:rut>
                  <ws:tipoPersona>Natural</ws:tipoPersona>
               </ws:Asegurado>
               <ws:declaraConductorMenor30Anios>true</ws:declaraConductorMenor30Anios>
               <ws:formaPago>Oneclick</ws:formaPago>
               <ws:recargoDescuento>Recargo</ws:recargoDescuento>
               <ws:valorRecargo>0,00</ws:valorRecargo>
            </ws:DatosBase>
            <ws:DatosBien>
               <ws:anio>$anio</ws:anio>
               <ws:codigoMarca>$marca</ws:codigoMarca>
               <ws:codigoModelo>$modelo</ws:codigoModelo>
               <ws:esNuevo>true</ws:esNuevo>
               <ws:patente>$patente</ws:patente>
               <ws:usoBien>$uso</ws:usoBien>
            </ws:DatosBien>
            <ws:Opcionales>
               <ws:asistencias>SinOpcional</ws:asistencias>
               <ws:autoReemplazo>SinOpcional</ws:autoReemplazo>
               <ws:rCExceso>SinOpcional</ws:rCExceso>
               <ws:taller>SinOpcional</ws:taller>
            </ws:Opcionales>
         </ws:CotizacionOferta>
      </ws:CotizaOfertaRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;

    $response = wp_remote_post($url, [
        'headers' => $headers,
        'body'    => $body,
        'timeout' => 30, // Opcional: aumenta el tiempo de espera si tarda
		'sslverify' => false
    ]);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
		error_log("❌ Error HTTP: $error_message");
		//echo "Error: $error_message";
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
		
        // Guardar resultado en sesión para usar en otra pantalla
        if (!session_id()) session_start();
		
		$respuestaApi = htmlspecialchars($response_body);
		
    }

    if (isset($respuestaApi)) {

        // Formatea lo que quieras mostrar
        //ob_start();
		$codigoError = "";
		
		preg_match('/<a:codigoError>(.*?)<\/a:codigoError>/', html_entity_decode($respuestaApi), $match);

		if (isset($match[1])) {
			$codigoError = $match[1];
		}
		
		preg_match('/<a:detalleError>(.*?)<\/a:detalleError>/', html_entity_decode($respuestaApi), $match);

		if (isset($match[1])) {
			$detalleError = $match[1];
		}

		// Validar si todo está OK
		if ($codigoError !== '0' || strtoupper($detalleError) !== 'OK') {
			//Hay un error al llamar API de ofertas
			echo 'Hay un error en la pagina, contactese con el administrador<br>';
			echo 'codigoError: '.$codigoError.'<br>';
			echo 'detalleError: '.$detalleError.'<br>';
			echo 'respuestaApi: '.$respuestaApi.'<br>';
		} else {
			preg_match_all('/<cotizacionServicioID[^>]*>(.*?)<\/cotizacionServicioID>/', html_entity_decode($respuestaApi), $matches);

			if (!empty($matches[1])) {
				$cotizacionServicioID = $matches[1][0];
				//echo "ID encontrado: " . $cotizacionServicioID;
			} else {
				echo "No se encontró ID";
			}
			
			preg_match_all('/<nombrePlan>(.*?)<\/nombrePlan>/', html_entity_decode($respuestaApi), $matches);

			if (!empty($matches[1])) {
				foreach ($matches[1] as $index => $valor) {
					$nombrePlan[$index] = $valor;
				}
			}
			
			$nombrePrimerPlan = $nombrePlan[0];
			
			//API cotizacion
			$fecha = date('Y-m-d');
			$url = 'https://hub-qa.segurossura.cl/seguros-generales/vechiculos-cotizacion';
			//$token = recuperarToken();

			$headers = [
				'Content-Type' => 'text/xml; charset=utf-8',
				'SOAPAction'   => '"http://ws.rsa.cl/WsIndustrializacion/GenerarCotizacion"',
				'Authorization'=> 'Bearer ' . $token,
				'Ocp-Apim-Subscription-Key' => '712c61e9edff4ad4842111902b1dcf18',
			];

			$body = <<<XML
			<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.rsa.cl/" xmlns:epv="http://schemas.datacontract.org/2004/07/EPV.Servicios.Services">
				<soapenv:Header>
					<ws:DatosTransaccion>
						<epv:contrasena>Sura2025_06_R</epv:contrasena>
						<epv:fechaHora>$fecha</epv:fechaHora>
						<epv:idContexto></epv:idContexto>
						<epv:idTransaccion></epv:idTransaccion>
						<epv:sistema></epv:sistema>
						<epv:usuario>77496140@ProductoraDiaz.cl</epv:usuario>
					</ws:DatosTransaccion>
				</soapenv:Header>
				<soapenv:Body>
					<ws:GeneraCotizacionRequest>
						<ws:Oferta>
							<epv:cotizacionServicioID>$cotizacionServicioID</epv:cotizacionServicioID>
							<epv:nombrePlan>$nombrePrimerPlan</epv:nombrePlan>
						</ws:Oferta>
					</ws:GeneraCotizacionRequest>
				</soapenv:Body>
			</soapenv:Envelope>
			XML;

			wp_custom_log("body en funcion crearPrimeraCotizacion".$body);

			$response = wp_remote_post($url, [
				'headers' => $headers,
				'body'    => $body,
				'timeout' => 30, // Opcional: aumenta el tiempo de espera si tarda
				'sslverify' => false
			]);

			//echo "response ".$response;

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				error_log("❌ Error HTTP: $error_message");
				//echo "Error: $error_message";
			} else {
				$status_code = wp_remote_retrieve_response_code($response);
				$response_body = wp_remote_retrieve_body($response);
				
				// Guardar resultado en sesión para usar en otra pantalla
				if (!session_id()) session_start();
				
				$respuestaApi2 = htmlspecialchars($response_body);
				
			}

			if (isset($respuestaApi2)) {

				// Formatea lo que quieras mostrar
				//ob_start();
				$codigoError = "";
				preg_match('/<a:codigoError>(.*?)<\/a:codigoError>/', html_entity_decode($respuestaApi2), $match);

				if (isset($match[1])) {
					$codigoError = $match[1];
				}
				
				preg_match('/<a:detalleError>(.*?)<\/a:detalleError>/', html_entity_decode($respuestaApi2), $match);

				if (isset($match[1])) {
					$detalleError = $match[1];
				}
				
				preg_match('/<NumeroCotizacion>(.*?)<\/NumeroCotizacion>/', html_entity_decode($respuestaApi2), $match);

				if (isset($match[1])) {
					$NumeroCotizacion = $match[1];
				}

				wp_custom_log("crearPrimeraCotizacion respuesta ".$respuestaApi2);
				wp_custom_log("crearPrimeraCotizacion NumeroCotizacion ".$NumeroCotizacion);
				wp_custom_log("crearPrimeraCotizacion codigoError ".$codigoError);
				wp_custom_log("crearPrimeraCotizacion detalleError ".$detalleError);
				
				// Validar si todo está OK
				if ($codigoError !== '0' /*|| strtoupper($detalleError) !== 'OK'*/) {
					//Hay un error al llamar API de ofertas
					echo 'Hay un error en la pagina, contactese con el administrador<br>';
					echo 'codigoError: '.$codigoError.'<br>';
					echo 'detalleError: '.$detalleError.'<br>';
				} else {
					//echo "NumeroCotizacion obtenida en funcion ".$NumeroCotizacion;
					//si entra aca se genera cotizacion ok
				}
			}
        }
		
    }
	else{
		echo "No hay resultados para mostrar.";
	}
}

function ocultar_errores_login() {
    return 'Usuario o contraseña incorrectos';
}
add_filter('login_errors', 'ocultar_errores_login');

add_filter('rest_authentication_errors', function($result) {
    if (!is_user_logged_in()) {
        return new WP_Error('forbidden', 'No autorizado', array('status' => 403));
    }
    return $result;
});

function eliminar_cookie_frontend() {
    if (!is_admin() && isset($_COOKIE['wordpress_test_cookie'])) {
        setcookie('wordpress_test_cookie', '', time() - 3600, '/');
    }
}
add_action('init', 'eliminar_cookie_frontend');

add_action('template_redirect', function () {

    if (is_404()) {
        wp_redirect(home_url('/'));
        exit;
    }

});