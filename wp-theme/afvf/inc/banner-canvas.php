<?php
/**
 * Bannière à calques : position libre (glisser-déposer dans l’éditeur).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

/** Blocs pouvant être positionnés sur une bannière. */
function afvf_banner_layer_block_types() {
	return array(
		'core/heading',
		'core/paragraph',
		'core/buttons',
		'core/button',
		'core/image',
		'core/video',
		'core/group',
	);
}

/**
 * Attribut afvfLayerPos sur les blocs core + carte des calques sur le Cover.
 *
 * @param array  $args       Block type args.
 * @param string $block_type Block name.
 * @return array
 */
function afvf_register_banner_layer_attribute( $args, $block_type ) {
	if ( in_array( $block_type, afvf_banner_layer_block_types(), true ) ) {
		$args['attributes']['afvfLayerPos'] = array(
			'type'    => 'object',
			'default' => null,
		);
	}
	if ( 'core/cover' === $block_type ) {
		$args['attributes']['afvfBannerLayers'] = array(
			'type'    => 'object',
			'default' => null,
		);
	}
	return $args;
}
add_filter( 'register_block_type_args', 'afvf_register_banner_layer_attribute', 10, 2 );

/**
 * @param string|null $value Position CSS.
 * @return string|null
 */
function afvf_sanitize_banner_layer_axis( $value ) {
	if ( ! is_string( $value ) ) {
		return null;
	}
	$value = trim( $value );
	if ( preg_match( '/^[0-9.]+\s*(%|px|rem|em|vw|vh)$/i', $value ) ) {
		return $value;
	}
	return null;
}

/**
 * @param array<string, mixed>|null $pos Raw position.
 * @return array<string, string>|null
 */
function afvf_normalize_banner_layer_pos( $pos ) {
	if ( ! is_array( $pos ) ) {
		return null;
	}
	$top   = afvf_sanitize_banner_layer_axis( $pos['top'] ?? '' );
	$left  = afvf_sanitize_banner_layer_axis( $pos['left'] ?? '' );
	$right = afvf_sanitize_banner_layer_axis( $pos['right'] ?? '' );
	if ( ! $top && ! $left && ! $right ) {
		return null;
	}
	$out = array( 'top' => $top ?: '10%' );
	if ( $right ) {
		$out['right'] = $right;
	} else {
		$out['left'] = $left ?: '10%';
	}
	return $out;
}

/**
 * Clé de stockage (className afvf-*).
 *
 * @param array $attrs Block attrs.
 * @return string|null
 */
function afvf_banner_layer_storage_key( $attrs ) {
	if ( ! empty( $attrs['anchor'] ) && is_string( $attrs['anchor'] ) ) {
		$anchor = trim( $attrs['anchor'] );
		if ( preg_match( '/^afvf-layer-[a-z0-9]+$/i', $anchor ) || preg_match( '/^afvf-[a-z0-9_-]+$/i', $anchor ) ) {
			return $anchor;
		}
	}
	$class = isset( $attrs['className'] ) ? trim( (string) $attrs['className'] ) : '';
	if ( '' === $class ) {
		return null;
	}
	foreach ( preg_split( '/\s+/', $class ) as $cn ) {
		if ( str_starts_with( $cn, 'afvf-pos-' ) ) {
			continue;
		}
		if ( str_starts_with( $cn, 'afvf-layer-' ) ) {
			return $cn;
		}
		if ( str_starts_with( $cn, 'afvf-' ) ) {
			return $cn;
		}
	}
	return null;
}

/**
 * @param array<string, string> $pos Position normalisée.
 * @param string                $source Source debug.
 * @return string
 */
function afvf_banner_layer_inline_style( $pos, $source = '' ) {
	$top   = $pos['top'];
	$left  = $pos['left'] ?? '';
	$right = $pos['right'] ?? '';
	/*
	 * Site public : pas de !important en ligne, sinon impossible de re-empiler les calques
	 * sur téléphone depuis la feuille de style (banner-canvas.css). Le bureau reste verrouillé
	 * par les règles !important de banner-canvas.css ; l’éditeur garde !important.
	 */
	$imp   = ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ? '!important' : '';
	$style = 'position:absolute' . $imp . ';top:' . $top . $imp . ';z-index:3;width:max-content' . $imp . ';max-width:min(92%,720px)' . $imp . ';box-sizing:border-box;transform:none' . $imp . ';';
	if ( $right ) {
		$style .= 'right:' . $right . $imp . ';left:auto' . $imp . ';';
	} else {
		$style .= 'left:' . $left . $imp . ';right:auto' . $imp . ';';
	}
	return $style;
}

/**
 * @param array<string, array<string, string>> $layers Carte clé => position.
 * @param array                                $block  Bloc cover optionnel.
 * @return array<string, array<string, string>>
 */
function afvf_get_merged_banner_layers( $block = null ) {
	$merged = array();
	if ( is_array( $block ) && ! empty( $block['attrs']['afvfBannerLayers'] ) && is_array( $block['attrs']['afvfBannerLayers'] ) ) {
		foreach ( $block['attrs']['afvfBannerLayers'] as $key => $pos ) {
			$norm = afvf_normalize_banner_layer_pos( $pos );
			if ( $norm && is_string( $key ) ) {
				$merged[ $key ] = $norm;
			}
		}
	}
	if ( ! empty( $GLOBALS['afvf_cover_banner_layers'] ) && is_array( $GLOBALS['afvf_cover_banner_layers'] ) ) {
		foreach ( $GLOBALS['afvf_cover_banner_layers'] as $key => $pos ) {
			$norm = afvf_normalize_banner_layer_pos( $pos );
			if ( $norm && is_string( $key ) ) {
				$merged[ $key ] = $norm;
			}
		}
	}
	foreach ( afvf_banner_meta_layers() as $key => $pos ) {
		$norm = afvf_normalize_banner_layer_pos( $pos );
		if ( $norm && is_string( $key ) ) {
			$merged[ $key ] = $norm;
		}
	}
	return $merged;
}

/**
 * @param string $class_attr Classes HTML.
 * @param string $needle     Classe recherchée.
 * @return bool
 */
function afvf_html_class_contains( $class_attr, $needle ) {
	if ( ! is_string( $class_attr ) || '' === $needle ) {
		return false;
	}
	return in_array( $needle, preg_split( '/\s+/', trim( $class_attr ) ), true );
}

/**
 * Applique les calques manquants sur le HTML complet du Cover (composition / ref).
 *
 * @param string $content HTML cover.
 * @param array  $block   Bloc cover.
 * @return string
 */
function afvf_patch_cover_banner_layers_html( $content, $block ) {
	$attrs = $block['attrs'] ?? array();
	if ( afvf_cover_is_photo_hero( $attrs ) || str_contains( $content, 'afvf-wix-photo-card' ) || str_contains( $content, 'afvf-hero-entreprises' ) ) {
		return $content;
	}
	$layer_host = afvf_cover_supports_banner_layers( $attrs )
		|| str_contains( $content, 'afvf-adhere-banner' )
		|| str_contains( $content, 'afvf-mission-banner' )
		|| str_contains( $content, 'afvf-former-hero' )
		|| str_contains( $content, 'afvf-banner-canvas' )
		|| str_contains( $content, 'is-style-afvf-banner-canvas' );
	if ( ! $layer_host ) {
		return $content;
	}
	$layers = afvf_get_merged_banner_layers( $block );
	if ( empty( $layers ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}
	$processor = new WP_HTML_Tag_Processor( $content );
	$applied   = 0;
	while ( $processor->next_tag() ) {
		$class_attr = (string) $processor->get_attribute( 'class' );
		if ( '' === $class_attr && ! $processor->get_attribute( 'id' ) ) {
			continue;
		}
		if ( str_contains( (string) $processor->get_attribute( 'data-afvf-pos' ), ':' ) ) {
			continue;
		}
		$id_attr = (string) $processor->get_attribute( 'id' );
		$pos     = null;
		$source  = 'patch';

		foreach ( $layers as $key => $layer_pos ) {
			$match = afvf_html_class_contains( $class_attr, $key )
				|| ( $id_attr && $id_attr === $key );
			if ( $match ) {
				$pos = $layer_pos;
				break;
			}
		}
		if ( ! $pos ) {
			$pos = afvf_banner_layer_pos_from_class( $class_attr );
			if ( $pos ) {
				$source = 'patch-class';
			}
		}
		if ( ! $pos ) {
			continue;
		}
		$existing = (string) $processor->get_attribute( 'style' );
		$extra    = afvf_banner_layer_inline_style( $pos, $source );
		$merged   = trim( $existing . ';' . $extra, '; ' );
		$processor->set_attribute( 'style', $merged );
		$processor->add_class( 'afvf-banner-layer' );
		$left  = $pos['left'] ?? '';
		$right = $pos['right'] ?? '';
		$processor->set_attribute(
			'data-afvf-pos',
			$source . ':' . $pos['top'] . '/' . ( $right ? 'r' . $right : 'l' . $left )
		);
		++$applied;
	}

	$html = $processor->get_updated_html();

	// Filet : 1 calque en méta / cover, 1 image ou paragraphe sans position dans la bannière.
	if ( 0 === $applied && 1 === count( $layers ) ) {
		$only_pos = afvf_normalize_banner_layer_pos( reset( $layers ) );
		if ( $only_pos && class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$proc2 = new WP_HTML_Tag_Processor( $html );
			while ( $proc2->next_tag() ) {
				$cn = (string) $proc2->get_attribute( 'class' );
				if ( ! preg_match( '/wp-block-(image|paragraph|heading|buttons|group)\b/', $cn ) ) {
					continue;
				}
				if ( str_contains( (string) $proc2->get_attribute( 'data-afvf-pos' ), ':' ) ) {
					continue;
				}
				$proc2->set_attribute( 'style', trim( (string) $proc2->get_attribute( 'style' ) . ';' . afvf_banner_layer_inline_style( $only_pos, 'patch-single' ), '; ' ) );
				$proc2->add_class( 'afvf-banner-layer' );
				$left  = $only_pos['left'] ?? '';
				$right = $only_pos['right'] ?? '';
				$proc2->set_attribute(
					'data-afvf-pos',
					'patch-single:' . $only_pos['top'] . '/' . ( $right ? 'r' . $right : 'l' . $left )
				);
				return $proc2->get_updated_html();
			}
		}
	}

	return $html;
}

/**
 * @param array<int, array<string, mixed>> $blocks Blocs parsés.
 * @param array<string, array<string, string>> $out  Sortie.
 */
function afvf_collect_layers_from_parsed_blocks( $blocks, &$out ) {
	foreach ( $blocks as $block ) {
		$attrs = $block['attrs'] ?? array();
		$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
		$key   = afvf_banner_layer_storage_key(
			array(
				'className' => $class,
				'anchor'    => isset( $attrs['anchor'] ) ? (string) $attrs['anchor'] : '',
			)
		);
		$from_class = afvf_banner_layer_pos_from_class( $class );
		if ( $key ) {
			$from_attr = afvf_normalize_banner_layer_pos( $attrs['afvfLayerPos'] ?? null );
			if ( $from_attr ) {
				$out[ $key ] = $from_attr;
			}
			if ( $from_class ) {
				$out[ $key ] = $from_class;
			}
		} elseif ( $from_class && preg_match( '/(afvf-pos-t\d+_\d+-[lr]\d+_\d+)/', $class, $pos_key ) ) {
			$out[ $pos_key[1] ] = $from_class;
		}
		if ( ( $block['blockName'] ?? '' ) === 'core/cover' && ! empty( $attrs['afvfBannerLayers'] ) && is_array( $attrs['afvfBannerLayers'] ) ) {
			foreach ( $attrs['afvfBannerLayers'] as $k => $pos ) {
				$norm = afvf_normalize_banner_layer_pos( $pos );
				if ( $norm && is_string( $k ) ) {
					$out[ $k ] = $norm;
				}
			}
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			afvf_collect_layers_from_parsed_blocks( $block['innerBlocks'], $out );
		}
	}
}

/**
 * À chaque enregistrement : copie toutes les positions dans la méta de page.
 *
 * @param int $post_id ID post.
 */
function afvf_sync_banner_layers_on_save( $post_id ) {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	$post = get_post( $post_id );
	if ( ! $post || ! in_array( $post->post_type, array( 'page', 'post', 'wp_block' ), true ) ) {
		return;
	}
	$layers = array();
	afvf_collect_layers_from_parsed_blocks( parse_blocks( $post->post_content ), $layers );
	if ( empty( $layers ) ) {
		delete_post_meta( $post_id, AFVF_BANNER_META_KEY );
		return;
	}
	update_post_meta( $post_id, AFVF_BANNER_META_KEY, wp_json_encode( $layers ) );
}
add_action( 'save_post', 'afvf_sync_banner_layers_on_save', 25 );

/**
 * Position encodée dans une classe : afvf-pos-t12_5-l48_0 ou afvf-pos-t12_5-r6_0.
 *
 * @param string $class_attr Valeur de l’attribut class.
 * @return array<string, string>|null
 */
/**
 * @param string               $class_attr Classes existantes.
 * @param array<string,string> $pos        Position normalisée.
 * @return string
 */
function afvf_banner_merge_pos_class( $class_attr, $pos ) {
	$base = array_filter(
		preg_split( '/\s+/', trim( (string) $class_attr ) ) ?: array(),
		static function ( $c ) {
			return $c && ! str_starts_with( $c, 'afvf-pos-' );
		}
	);
	$top_n = str_replace( '.', '_', number_format( (float) str_replace( '%', '', $pos['top'] ?? '0' ), 1, '.', '' ) );
	if ( ! empty( $pos['right'] ) ) {
		$side = 'r' . str_replace( '.', '_', number_format( (float) str_replace( '%', '', $pos['right'] ), 1, '.', '' ) );
	} else {
		$side = 'l' . str_replace( '.', '_', number_format( (float) str_replace( '%', '', $pos['left'] ?? '0' ), 1, '.', '' ) );
	}
	$base[] = 'afvf-pos-t' . $top_n . '-' . $side;
	return implode( ' ', $base );
}

/**
 * @param string               $selector Sélecteur CSS.
 * @param array<string,string> $pos      Position.
 * @return string
 */
function afvf_banner_css_rules_for_selector( $selector, $pos ) {
	$top   = esc_attr( $pos['top'] ?? '10%' );
	$left  = esc_attr( $pos['left'] ?? '10%' );
	$right = esc_attr( $pos['right'] ?? '' );
	$css   = $selector . '{position:absolute!important;top:' . $top . '!important;z-index:3!important;width:max-content!important;max-width:min(92%,720px)!important;box-sizing:border-box;}';
	if ( $right ) {
		$css .= $selector . '{right:' . $right . '!important;left:auto!important;}';
	} else {
		$css .= $selector . '{left:' . $left . '!important;right:auto!important;}';
	}
	return $css;
}

function afvf_banner_layer_pos_from_class( $class_attr ) {
	if ( ! is_string( $class_attr ) || ! preg_match( '/afvf-pos-t(\d+_\d+)-([lr])(\d+_\d+)/', $class_attr, $m ) ) {
		return null;
	}
	$top = str_replace( '_', '.', $m[1] ) . '%';
	$val = str_replace( '_', '.', $m[3] ) . '%';
	return 'r' === $m[2]
		? array(
			'top'   => $top,
			'right' => $val,
		)
		: array(
			'top'  => $top,
			'left' => $val,
		);
}

/**
 * Position enregistrée : classe afvf-pos-* (prioritaire), attribut du bloc, ou carte sur le Cover.
 *
 * @param array  $block   Parsed block.
 * @param string $content HTML du bloc.
 * @return array<string, string>|null
 */
function afvf_banner_layer_pos_for_block( $block, $content = '' ) {
	$attrs = $block['attrs'] ?? array();

	$class_attr = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	if ( $content && class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$probe = new WP_HTML_Tag_Processor( $content );
		if ( $probe->next_tag() ) {
			$class_attr .= ' ' . (string) $probe->get_attribute( 'class' );
		}
	}
	$key = afvf_banner_layer_storage_key(
		array(
			'className' => $class_attr,
			'anchor'    => isset( $attrs['anchor'] ) ? (string) $attrs['anchor'] : '',
		)
	);

	// 1) Classe afvf-pos-* dans le contenu enregistré (prioritaire — évite méta obsolète).
	$pos = afvf_banner_layer_pos_from_class( $class_attr );
	if ( $pos ) {
		$pos['_source'] = 'class';
		return $pos;
	}

	// 2) Attribut du bloc.
	$pos = afvf_normalize_banner_layer_pos( $attrs['afvfLayerPos'] ?? null );
	if ( $pos ) {
		$pos['_source'] = 'attr';
		return $pos;
	}

	// 3) Méta de page (secours si pas de classe dans le HTML).
	$meta = afvf_banner_meta_layers();
	if ( $key && isset( $meta[ $key ] ) ) {
		$pos = afvf_normalize_banner_layer_pos( $meta[ $key ] );
		if ( $pos ) {
			$pos['_source'] = 'meta';
			return $pos;
		}
	}

	// 4) Carte sur le Cover (parent).
	if ( $key && ! empty( $GLOBALS['afvf_cover_banner_layers'] ) && is_array( $GLOBALS['afvf_cover_banner_layers'] ) && isset( $GLOBALS['afvf_cover_banner_layers'][ $key ] ) ) {
		$pos = afvf_normalize_banner_layer_pos( $GLOBALS['afvf_cover_banner_layers'][ $key ] );
		if ( $pos ) {
			$pos['_source'] = 'cover';
			return $pos;
		}
	}

	// 5) Clé = token afvf-pos-* (bloc sans classe afvf dédiée).
	if ( preg_match( '/(afvf-pos-t\d+_\d+-[lr]\d+_\d+)/', $class_attr, $pos_token ) ) {
		$token = $pos_token[1];
		foreach ( array( afvf_banner_meta_layers(), $GLOBALS['afvf_cover_banner_layers'] ?? array() ) as $source_map ) {
			if ( ! is_array( $source_map ) || ! isset( $source_map[ $token ] ) ) {
				continue;
			}
			$pos = afvf_normalize_banner_layer_pos( $source_map[ $token ] );
			if ( $pos ) {
				$pos['_source'] = 'token';
				return $pos;
			}
		}
	}

	// 6) Un seul calque enregistré, un seul bloc positionnable dans le cover (pages simples type Assises).
	if ( ! empty( $GLOBALS['afvf_cover_banner_layers'] ) && is_array( $GLOBALS['afvf_cover_banner_layers'] ) ) {
		$cover_layers = $GLOBALS['afvf_cover_banner_layers'];
		if ( 1 === count( $cover_layers ) && in_array( $block['blockName'] ?? '', afvf_banner_layer_block_types(), true ) ) {
			$only = afvf_normalize_banner_layer_pos( reset( $cover_layers ) );
			if ( $only ) {
				$only['_source'] = 'cover-single';
				return $only;
			}
		}
	}
	$meta_only = afvf_banner_meta_layers();
	if ( 1 === count( $meta_only ) && in_array( $block['blockName'] ?? '', afvf_banner_layer_block_types(), true ) ) {
		$only = afvf_normalize_banner_layer_pos( reset( $meta_only ) );
		if ( $only ) {
			$only['_source'] = 'meta-single';
			return $only;
		}
	}
	return null;
}

/**
 * Méta de page : { "afvf-banner-wix-lines": {"top":"12%","left":"40%"}, ... }
 */
define( 'AFVF_BANNER_META_KEY', 'afvf_banner_layers' );

function afvf_register_banner_layers_meta() {
	foreach ( array( 'page', 'post', 'wp_block' ) as $type ) {
		add_post_type_support( $type, 'custom-fields' );
		register_post_meta(
			$type,
			AFVF_BANNER_META_KEY,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'afvf_sanitize_banner_layers_meta',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'afvf_register_banner_layers_meta' );

/**
 * @param mixed $value JSON string.
 * @return string
 */
function afvf_sanitize_banner_layers_meta( $value ) {
	$data = is_string( $value ) ? json_decode( wp_unslash( $value ), true ) : null;
	if ( ! is_array( $data ) ) {
		return '';
	}
	$clean = array();
	foreach ( $data as $key => $pos ) {
		if ( ! is_string( $key ) || ! preg_match( '/^[a-z0-9_-]{1,80}$/i', $key ) ) {
			continue;
		}
		$norm = afvf_normalize_banner_layer_pos( $pos );
		if ( $norm ) {
			$clean[ $key ] = $norm;
		}
	}
	return wp_slash( wp_json_encode( $clean ) );
}

/**
 * @return array<string, array<string, string>>
 */
function afvf_banner_meta_post_id() {
	if ( ! empty( $GLOBALS['afvf_banner_meta_post_id'] ) ) {
		return (int) $GLOBALS['afvf_banner_meta_post_id'];
	}
	if ( is_singular() ) {
		return (int) get_queried_object_id();
	}
	return (int) get_the_ID();
}

function afvf_banner_bind_meta_post_id( $post ) {
	if ( $post instanceof WP_Post ) {
		$GLOBALS['afvf_banner_meta_post_id'] = (int) $post->ID;
	}
}
add_action( 'the_post', 'afvf_banner_bind_meta_post_id' );

function afvf_banner_meta_layers() {
	static $cache = array();
	$post_id = afvf_banner_meta_post_id();
	if ( ! $post_id ) {
		return array();
	}
	if ( ! isset( $cache[ $post_id ] ) ) {
		$raw   = get_post_meta( $post_id, AFVF_BANNER_META_KEY, true );
		$data  = is_string( $raw ) && '' !== $raw ? json_decode( $raw, true ) : array();
		$cache[ $post_id ] = is_array( $data ) ? $data : array();
	}
	return $cache[ $post_id ];
}

/**
 * @param array $attrs Cover attrs.
 * @return bool
 */
function afvf_cover_is_adhere_banner( $attrs ) {
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	return str_contains( $class, 'afvf-adhere-banner' )
		|| str_contains( $class, 'afvf-adhere-cover' )
		|| str_contains( $class, 'afvf-mission-banner' )
		|| str_contains( $class, 'afvf-former-hero' );
}

/**
 * Couverture avec calques déplaçables (Assises ou bannière Adhérer).
 *
 * @param array $attrs Attributs bloc core/cover.
 * @return bool
 */
function afvf_cover_supports_banner_layers( $attrs ) {
	if ( afvf_cover_is_photo_hero( $attrs ) ) {
		return false;
	}
	if ( function_exists( 'afvf_cover_is_ker_aicha_section' ) && afvf_cover_is_ker_aicha_section( $attrs ) ) {
		return false;
	}
	if ( afvf_cover_is_adhere_banner( $attrs ) ) {
		return true;
	}
	return afvf_cover_is_banner_canvas( $attrs );
}

function afvf_cover_is_banner_canvas( $attrs ) {
	if ( function_exists( 'afvf_cover_is_ker_aicha_section' ) && afvf_cover_is_ker_aicha_section( $attrs ) ) {
		return false;
	}
	if ( afvf_cover_is_adhere_banner( $attrs ) ) {
		return false;
	}
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	return str_contains( $class, 'afvf-banner-canvas' )
		|| str_contains( $class, 'is-style-afvf-banner-canvas' );
}

/**
 * Hero « photo + carte blanche » (Entreprises…) — pas de calques placement libre.
 *
 * @param array $attrs Cover attrs.
 * @return bool
 */
function afvf_cover_is_photo_hero( $attrs ) {
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	if ( str_contains( $class, 'afvf-ker-aicha-page' ) || str_contains( $class, 'afvf-ker-aicha-band' ) ) {
		return false;
	}
	return str_contains( $class, 'afvf-wix-photo-card' )
		|| str_contains( $class, 'afvf-hero-entreprises' );
}

/**
 * Avant rendu Cover : expose afvfBannerLayers aux calques enfants.
 *
 * @param string|null $pre_render   Pre-render short-circuit.
 * @param array       $parsed_block Block.
 * @return string|null
 */
function afvf_pre_render_banner_cover_layers( $pre_render, $parsed_block ) {
	if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/cover' ) {
		return $pre_render;
	}
	$attrs = $parsed_block['attrs'] ?? array();
	if ( afvf_cover_is_photo_hero( $attrs ) || ! afvf_cover_supports_banner_layers( $attrs ) ) {
		return $pre_render;
	}
	$GLOBALS['afvf_banner_cover_depth']   = (int) ( $GLOBALS['afvf_banner_cover_depth'] ?? 0 ) + 1;
	$GLOBALS['afvf_cover_banner_layers'] = afvf_get_merged_banner_layers( $parsed_block );
	return $pre_render;
}
add_filter( 'pre_render_block', 'afvf_pre_render_banner_cover_layers', 10, 2 );

/**
 * Hero photo + carte : pas de calques absolus sur les blocs enfants.
 *
 * @param string|null $pre_render   Pre-render.
 * @param array       $parsed_block Block.
 * @return string|null
 */
function afvf_pre_render_photo_hero_scope( $pre_render, $parsed_block ) {
	if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/cover' ) {
		return $pre_render;
	}
	if ( afvf_cover_is_photo_hero( $parsed_block['attrs'] ?? array() ) ) {
		$GLOBALS['afvf_photo_hero_depth'] = (int) ( $GLOBALS['afvf_photo_hero_depth'] ?? 0 ) + 1;
	}
	return $pre_render;
}
add_filter( 'pre_render_block', 'afvf_pre_render_photo_hero_scope', 9, 2 );

/**
 * @param string $content HTML.
 * @param array  $block   Block.
 * @return string
 */
function afvf_after_render_photo_hero_scope( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/cover' ) {
		return $content;
	}
	if ( afvf_cover_is_photo_hero( $block['attrs'] ?? array() ) ) {
		$GLOBALS['afvf_photo_hero_depth'] = max( 0, (int) ( $GLOBALS['afvf_photo_hero_depth'] ?? 0 ) - 1 );
	}
	return $content;
}
add_filter( 'render_block', 'afvf_after_render_photo_hero_scope', 98, 2 );

/**
 * Injecte les classes afvf-pos-* avant génération HTML (méta / cover).
 *
 * @param array $parsed_block Bloc.
 * @return array
 */
function afvf_inject_banner_layer_class_before_render( $parsed_block ) {
	if ( ( $GLOBALS['afvf_banner_cover_depth'] ?? 0 ) < 1 ) {
		return $parsed_block;
	}
	if ( ! in_array( $parsed_block['blockName'] ?? '', afvf_banner_layer_block_types(), true ) ) {
		return $parsed_block;
	}
	$pos = afvf_banner_layer_pos_for_block( $parsed_block, '' );
	if ( ! $pos ) {
		return $parsed_block;
	}
	$attrs = $parsed_block['attrs'] ?? array();
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	if ( ! preg_match( '/afvf-pos-t\d+_\d+-[lr]\d+_\d+/', $class ) ) {
		$attrs['className'] = afvf_banner_merge_pos_class( $class, $pos );
		$parsed_block['attrs'] = $attrs;
	}
	return $parsed_block;
}
add_filter( 'render_block_data', 'afvf_inject_banner_layer_class_before_render', 12 );

/**
 * @param string $content HTML.
 * @param array  $block   Block.
 * @return string
 */
function afvf_after_render_banner_cover_clear_layers( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/cover' ) {
		return $content;
	}
	$attrs = $block['attrs'] ?? array();
	if ( afvf_cover_supports_banner_layers( $attrs ) ) {
		$depth = (int) ( $GLOBALS['afvf_banner_cover_depth'] ?? 0 );
		$GLOBALS['afvf_banner_cover_depth'] = max( 0, $depth - 1 );
		if ( 0 === (int) ( $GLOBALS['afvf_banner_cover_depth'] ?? 0 ) ) {
			unset( $GLOBALS['afvf_cover_banner_layers'] );
		}
	}
	return $content;
}
add_filter( 'render_block', 'afvf_after_render_banner_cover_clear_layers', 99, 2 );

/**
 * Applique top/left en absolute sur le front (attributs + carte Cover).
 *
 * @param string $content Block HTML.
 * @param array  $block   Block data.
 * @return string
 */
function afvf_render_banner_layer_position( $content, $block ) {
	if ( ( $GLOBALS['afvf_photo_hero_depth'] ?? 0 ) > 0 ) {
		return $content;
	}
	$class_attr = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( str_contains( $class_attr, 'afvf-wix-overlay-card' ) ) {
		return $content;
	}
	$pos = afvf_banner_layer_pos_for_block( $block, $content );
	if ( ! $pos ) {
		return $content;
	}
	$attrs  = $block['attrs'] ?? array();
	$class  = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	$layer_key = afvf_banner_layer_storage_key(
		array(
			'className' => $class,
			'anchor'    => isset( $attrs['anchor'] ) ? (string) $attrs['anchor'] : '',
		)
	);
	$source = $pos['_source'] ?? '';
	$top    = $pos['top'];
	$left   = $pos['left'] ?? '';
	$right  = $pos['right'] ?? '';

	$style = afvf_banner_layer_inline_style( $pos, $source );

	if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$processor = new WP_HTML_Tag_Processor( $content );
		if ( $processor->next_tag() ) {
			$existing = (string) $processor->get_attribute( 'style' );
			$merged   = trim( $existing . ';' . $style, '; ' );
			$processor->set_attribute( 'style', $merged );
			$processor->set_attribute( 'data-afvf-pos', $source . ':' . $top . '/' . ( $right ? 'r' . $right : 'l' . $left ) );
			$processor->add_class( 'afvf-banner-layer' );
			if ( $layer_key && ! $processor->get_attribute( 'id' ) ) {
				$processor->set_attribute( 'id', $layer_key );
			}
			return $processor->get_updated_html();
		}
	}

	return $content;
}
add_filter( 'render_block', 'afvf_render_banner_layer_position', 12, 2 );

/**
 * Retire toute poignée « Déplacer » du HTML public (ne doit jamais être enregistrée).
 *
 * @param string $content HTML bloc.
 * @param array  $block   Bloc.
 * @return string
 */
function afvf_strip_drag_handles_from_public_html( $content, $block ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $content;
	}
	if ( ! is_string( $content ) || '' === $content || ! str_contains( $content, 'afvf-drag-handle' ) ) {
		return $content;
	}
	return preg_replace( '/<button[^>]*\bafvf-drag-handle\b[^>]*>[\s\S]*?<\/button>/i', '', $content );
}
add_filter( 'render_block', 'afvf_strip_drag_handles_from_public_html', 999, 2 );

/**
 * Filet de sécurité : repositionne tous les calques dans le HTML du Cover.
 *
 * @param string $content HTML.
 * @param array  $block   Bloc.
 * @return string
 */
function afvf_render_patch_cover_layers( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/cover' ) {
		return $content;
	}
	return afvf_patch_cover_banner_layers_html( $content, $block );
}
add_filter( 'render_block', 'afvf_render_patch_cover_layers', 20, 2 );

/**
 * Hero « photo + carte » : retirer le style calques du HTML public (ajouté par erreur en édition).
 *
 * @param string $content HTML.
 * @param array  $block   Bloc.
 * @return string
 */
function afvf_strip_canvas_class_from_photo_hero( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/cover' ) {
		return $content;
	}
	if ( ! is_string( $content ) || '' === $content ) {
		return $content;
	}
	$is_photo_hero = str_contains( $content, 'afvf-wix-photo-card' ) || str_contains( $content, 'afvf-hero-entreprises' );
	$is_ker_aicha  = str_contains( $content, 'afvf-ker-aicha-band--section' )
		|| ( function_exists( 'afvf_cover_is_ker_aicha_section' ) && afvf_cover_is_ker_aicha_section( $block['attrs'] ?? array() ) );
	if ( ! $is_photo_hero && ! $is_ker_aicha ) {
		return $content;
	}
	return preg_replace_callback(
		'/class="([^"]*)"/',
		static function ( $m ) {
			$classes = preg_split( '/\s+/', trim( $m[1] ) );
			$classes = array_values(
				array_filter(
					$classes,
					static function ( $c ) {
						return $c !== '' && $c !== 'afvf-banner-canvas' && $c !== 'is-style-afvf-banner-canvas';
					}
				)
			);
			return 'class="' . implode( ' ', $classes ) . '"';
		},
		$content,
		1
	);
}
add_filter( 'render_block', 'afvf_strip_canvas_class_from_photo_hero', 8, 2 );

/**
 * Retire marges « preset » WP du style inline (var(--wp--preset--*) → flash au chargement).
 *
 * @param string $content HTML.
 * @param array  $block   Bloc.
 * @return string
 */
function afvf_normalize_photo_hero_cover_markup( $content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'core/cover' ) {
		return $content;
	}
	if ( ! afvf_cover_is_photo_hero( $block['attrs'] ?? array() ) ) {
		return $content;
	}
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ! is_string( $content ) || '' === $content ) {
		return $content;
	}
	$processor = new WP_HTML_Tag_Processor( $content );
	if ( ! $processor->next_tag() ) {
		return $content;
	}
	$style = (string) $processor->get_attribute( 'style' );
	if ( '' === $style ) {
		return $content;
	}
	$style = preg_replace( '/\bmargin-top\s*:[^;]+;?/i', '', $style );
	$style = preg_replace( '/\bmargin-bottom\s*:[^;]+;?/i', '', $style );
	$style = trim( preg_replace( '/\s{2,}/', ' ', $style ), '; ' );
	if ( '' !== $style ) {
		$processor->set_attribute( 'style', $style );
	} else {
		$processor->remove_attribute( 'style' );
	}
	return $processor->get_updated_html();
}
add_filter( 'render_block', 'afvf_normalize_photo_hero_cover_markup', 9, 2 );

/**
 * Dans un hero carte blanche : pas de position absolute héritée des calques.
 *
 * @param string $content HTML.
 * @param array  $block   Bloc.
 * @return string
 */
function afvf_strip_layer_styles_in_photo_hero( $content, $block ) {
	if ( ( $GLOBALS['afvf_photo_hero_depth'] ?? 0 ) <= 0 ) {
		return $content;
	}
	if ( ! is_string( $content ) || '' === $content || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}
	$processor = new WP_HTML_Tag_Processor( $content );
	if ( ! $processor->next_tag() ) {
		return $content;
	}
	$processor->remove_class( 'afvf-banner-layer' );
	$style = (string) $processor->get_attribute( 'style' );
	if ( '' !== $style && ( str_contains( $style, 'position' ) || str_contains( $style, 'top:' ) || str_contains( $style, 'left:' ) || str_contains( $style, 'right:' ) ) ) {
		$style = preg_replace( '/\bposition\s*:[^;]+;?/i', '', $style );
		$style = preg_replace( '/\btop\s*:[^;]+;?/i', '', $style );
		$style = preg_replace( '/\bleft\s*:[^;]+;?/i', '', $style );
		$style = preg_replace( '/\bright\s*:[^;]+;?/i', '', $style );
		$style = preg_replace( '/\bz-index\s*:[^;]+;?/i', '', $style );
		$style = trim( preg_replace( '/\s{2,}/', ' ', $style ), '; ' );
		if ( '' !== $style ) {
			$processor->set_attribute( 'style', $style );
		} else {
			$processor->remove_attribute( 'style' );
		}
	}
	return $processor->get_updated_html();
}
add_filter( 'render_block', 'afvf_strip_layer_styles_in_photo_hero', 15, 2 );

function afvf_register_banner_cover_style() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}
	register_block_style(
		'core/cover',
		array(
			'name'  => 'afvf-banner-canvas',
			'label' => __( 'Bannière — placement libre (glisser)', 'afvf' ),
		)
	);
}
add_action( 'init', 'afvf_register_banner_cover_style' );

/**
 * Une liste de blocs contient-elle une bannière « placement libre » (y compris dans une composition synchronisée) ?
 *
 * @param array<int, array<string, mixed>> $blocks Blocs parsés.
 * @param int                              $depth  Garde-fou récursion.
 * @return bool
 */
function afvf_blocks_need_banner_canvas( array $blocks, $depth = 0 ) {
	if ( $depth > 5 ) {
		return false;
	}
	foreach ( $blocks as $block ) {
		$name  = $block['blockName'] ?? '';
		$attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		if ( 'core/cover' === $name && afvf_cover_supports_banner_layers( $attrs ) ) {
			return true;
		}
		if ( 'core/block' === $name && ! empty( $attrs['ref'] ) ) {
			$ref = get_post( (int) $attrs['ref'] );
			if ( $ref instanceof WP_Post && afvf_blocks_need_banner_canvas( parse_blocks( (string) $ref->post_content ), $depth + 1 ) ) {
				return true;
			}
		}
		if ( ! empty( $block['innerBlocks'] ) && afvf_blocks_need_banner_canvas( $block['innerBlocks'], $depth + 1 ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Charger banner-canvas.css seulement si la page contient une bannière « placement libre ».
 *
 * @return bool
 */
function afvf_page_needs_banner_canvas_css() {
	if ( is_admin() ) {
		return false;
	}
	$post = get_post( get_queried_object_id() );
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return afvf_blocks_need_banner_canvas( parse_blocks( (string) $post->post_content ) );
}

function afvf_enqueue_banner_canvas_assets() {
	if ( ! afvf_page_needs_banner_canvas_css() ) {
		return;
	}
	wp_enqueue_style(
		'afvf-banner-canvas',
		get_template_directory_uri() . '/assets/css/banner-canvas.css',
		array( 'afvf-main' ),
		'1.9.154'
	);
}
add_action( 'wp_enqueue_scripts', 'afvf_enqueue_banner_canvas_assets' );

function afvf_enqueue_banner_canvas_editor_script() {
	wp_enqueue_script(
		'afvf-banner-layer-editor',
		get_template_directory_uri() . '/assets/js/banner-layer-editor.js',
		array( 'wp-hooks', 'wp-blocks', 'wp-dom-ready', 'wp-data', 'wp-block-editor', 'wp-edit-post' ),
		'1.9.154',
		true
	);
	wp_localize_script(
		'afvf-banner-layer-editor',
		'afvfBannerLayer',
		array(
			'coverClass' => 'afvf-banner-canvas',
			'blockTypes' => afvf_banner_layer_block_types(),
			'i18n'       => array(
				'dragHint'           => __( 'Déplacer : poignée ⋮⋮ ou clic hors texte + glisser. Texte/bouton : maintenez Alt ou Maj.', 'afvf' ),
				'syncedComposition'  => __( 'Bannière AFVF en composition synchronisée : WordPress n’enregistre pas les déplacements sur cette page. Cliquez la bannière → « Sortir de la composition », ou supprimez-la et insérez le motif « Bannière page — affiche » (blocs réguliers).', 'afvf' ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'afvf_enqueue_banner_canvas_editor_script' );

function afvf_enqueue_banner_canvas_editor_style() {
	wp_enqueue_style(
		'afvf-banner-canvas-editor',
		get_template_directory_uri() . '/assets/css/banner-canvas.css',
		array(),
		'1.9.154'
	);
}
add_action( 'enqueue_block_editor_assets', 'afvf_enqueue_banner_canvas_editor_style' );

/**
 * Après sauvegarde REST : conserver la méta calques si présente dans la requête.
 *
 * @param WP_Post         $post     Post.
 * @param WP_REST_Request $request  Requête.
 * @param bool            $creating Création.
 */
function afvf_rest_persist_banner_layers_meta( $post, $request, $creating ) {
	if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
		return;
	}
	$params = $request->get_param( 'meta' );
	if ( ! is_array( $params ) || ! isset( $params[ AFVF_BANNER_META_KEY ] ) ) {
		return;
	}
	$clean = afvf_sanitize_banner_layers_meta( $params[ AFVF_BANNER_META_KEY ] );
	if ( '' !== $clean ) {
		update_post_meta( $post->ID, AFVF_BANNER_META_KEY, wp_unslash( $clean ) );
	}
}
add_action( 'rest_after_insert_page', 'afvf_rest_persist_banner_layers_meta', 10, 3 );
add_action( 'rest_after_insert_post', 'afvf_rest_persist_banner_layers_meta', 10, 3 );
