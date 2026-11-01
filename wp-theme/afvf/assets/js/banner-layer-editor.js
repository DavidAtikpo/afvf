/**
 * Glisser-déposer X+Y sur bannière afvf-banner-canvas (poignée « Déplacer »).
 * Positions : afvfLayerPos sur chaque bloc + afvfBannerLayers sur le Cover (sauvegarde fiable).
 */
( function () {
	if ( typeof wp === 'undefined' || ! wp.domReady || ! wp.data ) {
		return;
	}

	function isAfvfBlockEditorContext() {
		const body = document.body;
		if ( ! body ) {
			return false;
		}
		if ( body.classList.contains( 'block-editor-page' ) || body.classList.contains( 'block-editor-iframe__body' ) ) {
			return true;
		}
		if ( document.querySelector( '.editor-styles-wrapper' ) ) {
			return true;
		}
		try {
			if ( window.frameElement && window.parent && window.parent.document && window.parent.document.body.classList.contains( 'block-editor-page' ) ) {
				return true;
			}
		} catch ( err ) {
			// ignore
		}
		return false;
	}

	if ( ! isAfvfBlockEditorContext() ) {
		return;
	}

	const config = window.afvfBannerLayer || { coverClass: 'afvf-banner-canvas', blockTypes: [] };

	const COVER_LAYER_HOST_SEL =
		'.wp-block-cover.' +
		config.coverClass +
		', .wp-block-cover.is-style-afvf-banner-canvas, .wp-block-cover.afvf-adhere-banner, .wp-block-cover.afvf-adhere-cover, .wp-block-cover.afvf-mission-banner, .wp-block-cover.afvf-former-hero';

	if ( wp.hooks && wp.hooks.addFilter ) {
		wp.hooks.addFilter( 'blocks.registerBlockType', 'afvf/banner-layer-pos', function ( settings, name ) {
			if ( config.blockTypes && config.blockTypes.indexOf( name ) !== -1 ) {
				settings.attributes = Object.assign( {}, settings.attributes, {
					afvfLayerPos: {
						type: 'object',
						default: null,
					},
				} );
			}
			if ( name === 'core/cover' ) {
				settings.attributes = Object.assign( {}, settings.attributes, {
					afvfBannerLayers: {
						type: 'object',
						default: null,
					},
				} );
			}
			return settings;
		} );
	}

	function coverIsCanvas( attrs ) {
		if ( ! attrs ) {
			return false;
		}
		const cn = attrs.className ? String( attrs.className ) : '';
		return cn.indexOf( config.coverClass ) !== -1 || cn.indexOf( 'is-style-afvf-banner-canvas' ) !== -1;
	}

	/** Couverture avec calques (Assises ou bannière Adhérer) */
	function coverIsLayerHost( attrs ) {
		if ( ! attrs || coverIsPhotoHeroCard( attrs ) ) {
			return false;
		}
		const cn = attrs.className ? String( attrs.className ) : '';
		return (
			coverIsCanvas( attrs ) ||
			cn.indexOf( 'afvf-adhere-banner' ) !== -1 ||
			cn.indexOf( 'afvf-adhere-cover' ) !== -1 ||
			cn.indexOf( 'afvf-mission-banner' ) !== -1 ||
			cn.indexOf( 'afvf-former-hero' ) !== -1
		);
	}

	function coverElIsAdhereBanner( coverEl ) {
		return (
			coverEl &&
			( coverEl.classList.contains( 'afvf-adhere-banner' ) ||
				coverEl.classList.contains( 'afvf-adhere-cover' ) ||
				coverEl.classList.contains( 'afvf-mission-banner' ) ||
				coverEl.classList.contains( 'afvf-former-hero' ) )
		);
	}

	function coverElIsLayerHost( coverEl ) {
		if ( ! coverEl || coverElIsPhotoHero( coverEl ) ) {
			return false;
		}
		return (
			coverEl.classList.contains( 'afvf-banner-canvas' ) ||
			coverEl.classList.contains( 'is-style-afvf-banner-canvas' ) ||
			coverEl.classList.contains( 'afvf-former-hero' ) ||
			coverElIsAdhereBanner( coverEl )
		);
	}

	function blockElIsMovableLayer( blockEl ) {
		if ( ! blockEl ) {
			return false;
		}
		if ( blockEl.querySelector( '.afvf-pattern-hint' ) ) {
			return false;
		}
		if (
			blockEl.querySelector(
				'.afvf-former-hero__title, .afvf-former-hero__disc, .afvf-mission-banner__title, .afvf-adhere-banner__poster, .afvf-adhere-cover__poster'
			)
		) {
			return true;
		}
		return (
			blockEl.classList.contains( 'wp-block-image' ) ||
			blockEl.classList.contains( 'wp-block-buttons' ) ||
			blockEl.classList.contains( 'wp-block-heading' ) ||
			blockEl.classList.contains( 'wp-block-group' ) ||
			!! blockEl.querySelector( ':scope > .wp-block-image' )
		);
	}

	/** Hero carte blanche (Entreprises…) : pas de mode « placement libre » */
	function coverIsPhotoHeroCard( attrs ) {
		if ( ! attrs ) {
			return false;
		}
		const cn = attrs.className ? String( attrs.className ) : '';
		return cn.indexOf( 'afvf-wix-photo-card' ) !== -1 || cn.indexOf( 'afvf-hero-entreprises' ) !== -1;
	}

	function coverElIsPhotoHero( coverEl ) {
		return (
			coverEl &&
			( coverEl.classList.contains( 'afvf-wix-photo-card' ) || coverEl.classList.contains( 'afvf-hero-entreprises' ) )
		);
	}

	function stripCanvasClassesFromPlacementBanner( coverBlock ) {
		if ( ! coverBlock || coverBlock.name !== 'core/cover' || ! coverIsLayerHost( coverBlock.attributes ) ) {
			return;
		}
		const cn = coverBlock.attributes && coverBlock.attributes.className ? String( coverBlock.attributes.className ) : '';
		const isPlacement =
			cn.indexOf( 'afvf-adhere-banner' ) !== -1 ||
			cn.indexOf( 'afvf-adhere-cover' ) !== -1 ||
			cn.indexOf( 'afvf-mission-banner' ) !== -1 ||
			cn.indexOf( 'afvf-former-hero' ) !== -1;
		if ( ! isPlacement || ( cn.indexOf( 'afvf-banner-canvas' ) === -1 && cn.indexOf( 'is-style-afvf-banner-canvas' ) === -1 ) ) {
			return;
		}
		const cleaned = cn
			.split( /\s+/ )
			.filter( function ( c ) {
				return c && c !== 'afvf-banner-canvas' && c !== 'is-style-afvf-banner-canvas';
			} )
			.join( ' ' );
		wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( coverBlock.clientId, {
			className: cleaned,
		} );
	}

	function stripCanvasClassesFromPhotoHero( coverBlock ) {
		if ( ! coverBlock || coverBlock.name !== 'core/cover' || ! coverIsPhotoHeroCard( coverBlock.attributes ) ) {
			return;
		}
		const cn = coverBlock.attributes && coverBlock.attributes.className ? String( coverBlock.attributes.className ) : '';
		if ( cn.indexOf( 'afvf-banner-canvas' ) === -1 && cn.indexOf( 'is-style-afvf-banner-canvas' ) === -1 ) {
			return;
		}
		const cleaned = cn
			.split( /\s+/ )
			.filter( function ( c ) {
				return c && c !== 'afvf-banner-canvas' && c !== 'is-style-afvf-banner-canvas';
			} )
			.join( ' ' );
		wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( coverBlock.clientId, {
			className: cleaned,
		} );
	}

	function cleanupPhotoHeroLayerUi( root ) {
		root.querySelectorAll( '.wp-block-cover.afvf-wix-photo-card, .wp-block-cover.afvf-hero-entreprises' ).forEach( function ( cover ) {
			cover.querySelectorAll( '.wp-block-cover__inner-container [data-block]' ).forEach( function ( blockEl ) {
				const handle = blockEl.querySelector( ':scope > .afvf-drag-handle' );
				if ( handle ) {
					handle.remove();
				}
				blockEl.classList.remove( 'afvf-banner-layer', 'is-afvf-dragging' );
			} );
		} );
	}

	/* Méta de page (JSON) : source de vérité lue aussi par le site public */
	const META_KEY = 'afvf_banner_layers';

	function readMetaLayers() {
		try {
			const ed = wp.data.select( 'core/editor' );
			if ( ! ed || ! ed.getEditedPostAttribute ) {
				return {};
			}
			const meta = ed.getEditedPostAttribute( 'meta' ) || {};
			const raw = meta[ META_KEY ];
			return raw ? JSON.parse( raw ) || {} : {};
		} catch ( err ) {
			return {};
		}
	}

	function writeAllMetaLayers( map ) {
		try {
			const ed = wp.data.select( 'core/editor' );
			if ( ! ed || ! ed.getEditedPostAttribute ) {
				return;
			}
			const prevMeta = ed.getEditedPostAttribute( 'meta' ) || {};
			const nextMeta = Object.assign( {}, prevMeta );
			nextMeta[ META_KEY ] = JSON.stringify( map );
			wp.data.dispatch( 'core/editor' ).editPost( { meta: nextMeta } );
		} catch ( err ) {
			// ignore
		}
	}

	function writeMetaLayer( key, layer ) {
		const map = readMetaLayers();
		if ( JSON.stringify( map[ key ] ) === JSON.stringify( layer ) ) {
			return;
		}
		map[ key ] = layer;
		writeAllMetaLayers( map );
	}

	function flushAllLayersToPostMeta() {
		const map = Object.assign( {}, readMetaLayers() );
		const blocks = wp.data.select( 'core/block-editor' ).getBlocks();

		function collectFromCover( cover ) {
			function walk( list ) {
				if ( ! list || ! list.length ) {
					return;
				}
				list.forEach( function ( block ) {
					if ( isInsideSyncedComposition( block.clientId ) ) {
						return;
					}
					const pos = posFromBlock( block, cover );
					if ( pos ) {
						const raw = layerFromPos( pos );
						const key = layerStorageKey( block.attributes, block.clientId );
						map[ key ] = raw;
						const cn = classWithPos( block.attributes.className || '', raw );
						const tokenMatch = /(afvf-pos-t\d+_\d+-[lr]\d+_\d+)/.exec( cn );
						if ( tokenMatch ) {
							map[ tokenMatch[ 1 ] ] = raw;
						}
					}
					if ( block.innerBlocks && block.innerBlocks.length ) {
						walk( block.innerBlocks );
					}
				} );
			}
			walk( cover.innerBlocks || [] );
			const fromCover = cover.attributes && cover.attributes.afvfBannerLayers;
			if ( fromCover && typeof fromCover === 'object' ) {
				Object.assign( map, fromCover );
			}
		}

		function walkCovers( list ) {
			if ( ! list || ! list.length ) {
				return;
			}
			list.forEach( function ( block ) {
				if ( block.name === 'core/cover' && coverIsLayerHost( block.attributes ) ) {
					collectFromCover( block );
				}
				if ( block.innerBlocks && block.innerBlocks.length ) {
					walkCovers( block.innerBlocks );
				}
			} );
		}
		walkCovers( blocks );
		if ( Object.keys( map ).length ) {
			writeAllMetaLayers( map );
		}
	}

	function layerFromPos( pos ) {
		if ( ! pos ) {
			return null;
		}
		return pos.useRight ? { top: pos.top, right: pos.right } : { top: pos.top, left: pos.left };
	}

	function stableLayerClass( clientId ) {
		return 'afvf-layer-' + String( clientId ).replace( /-/g, '' ).slice( 0, 8 );
	}

	function ensureStableLayerClass( className, clientId ) {
		const cn = String( className || '' ).trim();
		if ( /\bafvf-layer-[a-f0-9]+\b/i.test( cn ) ) {
			return cn;
		}
		for ( let i = 0; i < cn.split( /\s+/ ).length; i++ ) {
			const part = cn.split( /\s+/ )[ i ];
			if ( part && part.indexOf( 'afvf-' ) === 0 && part.indexOf( 'afvf-pos-' ) !== 0 && part.indexOf( 'afvf-layer-' ) !== 0 ) {
				return cn;
			}
		}
		return ( cn + ' ' + stableLayerClass( clientId ) ).trim();
	}

	/** Clé lue depuis le contenu enregistré (pas le clientId, qui change à chaque chargement). */
	function layerKeyFromSavedAttrs( attrs ) {
		if ( attrs && attrs.anchor && String( attrs.anchor ).indexOf( 'afvf-' ) === 0 ) {
			return String( attrs.anchor );
		}
		const cn = attrs && attrs.className ? String( attrs.className ).trim().split( /\s+/ ) : [];
		for ( let i = 0; i < cn.length; i++ ) {
			if ( cn[ i ].indexOf( 'afvf-layer-' ) === 0 ) {
				return cn[ i ];
			}
		}
		for ( let j = 0; j < cn.length; j++ ) {
			if ( cn[ j ].indexOf( 'afvf-' ) === 0 && cn[ j ].indexOf( 'afvf-pos-' ) !== 0 ) {
				return cn[ j ];
			}
		}
		return null;
	}

	function layerStorageKey( attrs, clientId ) {
		const saved = layerKeyFromSavedAttrs( attrs );
		if ( saved ) {
			return saved;
		}
		return stableLayerClass( clientId );
	}

	function mergedLayerMap( coverBlock ) {
		const map = Object.assign( {}, readMetaLayers() );
		const fromCover = coverBlock && coverBlock.attributes && coverBlock.attributes.afvfBannerLayers;
		if ( fromCover && typeof fromCover === 'object' ) {
			Object.assign( map, fromCover );
		}
		return map;
	}

	function findLayerKeyForBlock( block, merged ) {
		const saved = layerKeyFromSavedAttrs( block.attributes || {} );
		if ( saved && merged[ saved ] ) {
			return saved;
		}
		const cn = block.attributes && block.attributes.className ? String( block.attributes.className ) : '';
		const parts = cn.trim() ? cn.trim().split( /\s+/ ) : [];
		for ( let i = 0; i < parts.length; i++ ) {
			if ( merged[ parts[ i ] ] ) {
				return parts[ i ];
			}
		}
		return saved;
	}

	function isLayerBlockType( name ) {
		return config.blockTypes && config.blockTypes.indexOf( name ) !== -1;
	}

	function isInsideSyncedComposition( clientId ) {
		const select = wp.data.select( 'core/block-editor' );
		if ( ! select || ! select.getBlockParents || ! clientId ) {
			return false;
		}
		const parents = select.getBlockParents( clientId ) || [];
		for ( let i = 0; i < parents.length; i++ ) {
			const p = select.getBlock( parents[ i ] );
			if ( p && p.name === 'core/block' && p.attributes && p.attributes.ref ) {
				return true;
			}
		}
		return false;
	}

	let syncedNoticeShown = false;
	function maybeWarnSyncedComposition( clientId ) {
		if ( syncedNoticeShown || ! isInsideSyncedComposition( clientId ) ) {
			return;
		}
		syncedNoticeShown = true;
		const msg =
			( config.i18n && config.i18n.syncedComposition ) ||
			'Cette bannière est une composition synchronisée : les positions ne peuvent pas être enregistrées sur la page. Cliquez la bannière puis « Sortir de la composition », ou réinsérez le motif AFVF « Bannière page — affiche ».';
		if ( wp.data.dispatch( 'core/notices' ) ) {
			wp.data.dispatch( 'core/notices' ).createWarningNotice( msg, {
				id: 'afvf-banner-synced-composition',
				isDismissible: true,
			} );
		}
	}

	function collectLayerBlocks( list, out ) {
		if ( ! list || ! list.length ) {
			return;
		}
		list.forEach( function ( block ) {
			if ( isLayerBlockType( block.name ) ) {
				out.push( block );
			}
			if ( block.innerBlocks && block.innerBlocks.length ) {
				collectLayerBlocks( block.innerBlocks, out );
			}
		} );
	}

	/** Position dans className (toujours sauvegardée par WordPress) : afvf-pos-t12_5-l48_0 / -r6_0 */
	function numToken( value ) {
		const n = parseFloat( value );
		return ( isNaN( n ) ? 0 : n ).toFixed( 1 ).replace( '.', '_' );
	}

	function classWithPos( className, layer ) {
		const base = String( className || '' )
			.split( /\s+/ )
			.filter( function ( c ) {
				return c && c.indexOf( 'afvf-pos-' ) !== 0;
			} );
		const side = layer.right ? 'r' + numToken( layer.right ) : 'l' + numToken( layer.left );
		base.push( 'afvf-pos-t' + numToken( layer.top ) + '-' + side );
		return base.join( ' ' );
	}

	function posFromClassName( className ) {
		const m = /afvf-pos-t(\d+_\d+)-([lr])(\d+_\d+)/.exec( String( className || '' ) );
		if ( ! m ) {
			return null;
		}
		const top = m[ 1 ].replace( '_', '.' ) + '%';
		const val = m[ 3 ].replace( '_', '.' ) + '%';
		return m[ 2 ] === 'r'
			? { top: top, right: val, useRight: true }
			: { top: top, left: val, useRight: false };
	}

	function findCoverFromEl( el ) {
		return el && el.closest ? el.closest( COVER_LAYER_HOST_SEL ) : null;
	}

	function innerContainer( cover ) {
		return cover ? cover.querySelector( '.wp-block-cover__inner-container' ) : null;
	}

	function positionRoot( cover ) {
		const inner = innerContainer( cover );
		if ( inner && ( coverElIsAdhereBanner( cover ) || coverElIsLayerHost( cover ) ) ) {
			return inner;
		}
		if ( inner ) {
			const rect = inner.getBoundingClientRect();
			const coverRect = cover.getBoundingClientRect();
			if ( rect.height >= coverRect.height * 0.85 && rect.width >= coverRect.width * 0.85 ) {
				return inner;
			}
		}
		return cover;
	}

	function findBlockEl( root, clientId ) {
		const sel = '[data-block="' + clientId + '"]';
		const covers = root.querySelectorAll(
			'.wp-block-cover.' +
				config.coverClass +
				' ' +
				sel +
				', .wp-block-cover.is-style-afvf-banner-canvas ' +
				sel +
				', .wp-block-cover.afvf-adhere-banner ' +
				sel +
				', .wp-block-cover.afvf-adhere-cover ' +
				sel +
				', .wp-block-cover.afvf-mission-banner ' +
				sel +
				', .wp-block-cover.afvf-former-hero ' +
				sel
		);
		if ( covers.length ) {
			return covers[ 0 ];
		}
		return root.querySelector( sel );
	}

	function pctFromPointer( container, clientX, clientY ) {
		const rect = container.getBoundingClientRect();
		const x = Math.min( Math.max( clientX - rect.left, 0 ), rect.width );
		const y = Math.min( Math.max( clientY - rect.top, 0 ), rect.height );
		const leftPct = ( x / rect.width ) * 100;
		const topPct = ( y / rect.height ) * 100;
		return {
			top: topPct.toFixed( 1 ) + '%',
			left: leftPct.toFixed( 1 ) + '%',
			right: ( 100 - leftPct ).toFixed( 1 ) + '%',
		};
	}

	function applyPosToEl( blockEl, pos ) {
		if ( ! blockEl || ! pos ) {
			return;
		}
		blockEl.classList.add( 'afvf-banner-layer' );
		blockEl.style.setProperty( 'position', 'absolute', 'important' );
		blockEl.style.setProperty( 'top', pos.top || '10%', 'important' );
		blockEl.style.setProperty( 'margin', '0', 'important' );
		blockEl.style.setProperty( 'width', 'max-content', 'important' );
		const discInside = blockEl.querySelector( '.afvf-former-hero__disc' );
		blockEl.style.setProperty(
			'max-width',
			discInside ? 'min(300px, 72vw)' : 'min(92%, 720px)',
			'important'
		);
		blockEl.style.setProperty( 'transform', 'none', 'important' );
		if ( pos.useRight ) {
			blockEl.style.setProperty( 'right', pos.right || '6%', 'important' );
			blockEl.style.setProperty( 'left', 'auto', 'important' );
		} else {
			blockEl.style.setProperty( 'left', pos.left || '10%', 'important' );
			blockEl.style.setProperty( 'right', 'auto', 'important' );
		}
	}

	function preferRightAnchor( attrs ) {
		if ( ! attrs ) {
			return false;
		}
		const fromClass = posFromClassName( attrs.className );
		if ( fromClass ) {
			return fromClass.useRight;
		}
		if ( attrs.afvfLayerPos && attrs.afvfLayerPos.right ) {
			return true;
		}
		const cn = attrs.className ? String( attrs.className ) : '';
		return (
			cn.indexOf( 'afvf-banner-entrez' ) !== -1 ||
			cn.indexOf( 'afvf-adhere-banner__poster' ) !== -1 ||
			cn.indexOf( 'afvf-former-hero__disc' ) !== -1
		);
	}

	function posFromLayerObj( raw ) {
		if ( ! raw ) {
			return null;
		}
		if ( raw.right ) {
			return { top: raw.top, right: raw.right, useRight: true };
		}
		return { top: raw.top, left: raw.left, useRight: false };
	}

	function posFromBlock( block, coverBlock ) {
		const merged = coverBlock ? mergedLayerMap( coverBlock ) : readMetaLayers();
		const key = findLayerKeyForBlock( block, merged );
		if ( key && merged[ key ] ) {
			return posFromLayerObj( merged[ key ] );
		}
		const cn = block.attributes && block.attributes.className ? String( block.attributes.className ) : '';
		const fromClass = posFromClassName( cn );
		if ( fromClass ) {
			return fromClass;
		}
		const tokenMatch = /(afvf-pos-t\d+_\d+-[lr]\d+_\d+)/.exec( cn );
		if ( tokenMatch && merged[ tokenMatch[ 1 ] ] ) {
			return posFromLayerObj( merged[ tokenMatch[ 1 ] ] );
		}
		const fromAttr = posFromLayerObj( block.attributes && block.attributes.afvfLayerPos );
		if ( fromAttr ) {
			return fromAttr;
		}
		if ( coverBlock ) {
			const layerBlocks = [];
			collectLayerBlocks( coverBlock.innerBlocks || [], layerBlocks );
			const keys = Object.keys( merged );
			if ( layerBlocks.length === 1 && keys.length === 1 && merged[ keys[ 0 ] ] ) {
				return posFromLayerObj( merged[ keys[ 0 ] ] );
			}
		}
		return null;
	}

	const hydratedCovers = {};

	function hydrateLayerAttrsFromMaps( coverBlock ) {
		const merged = mergedLayerMap( coverBlock );
		if ( ! Object.keys( merged ).length ) {
			return;
		}
		const sig = coverBlock.clientId + ':' + JSON.stringify( merged );
		if ( hydratedCovers[ sig ] ) {
			return;
		}
		hydratedCovers[ sig ] = true;
		const dispatch = wp.data.dispatch( 'core/block-editor' );
		const layerBlocks = [];
		collectLayerBlocks( coverBlock.innerBlocks || [], layerBlocks );
		const keys = Object.keys( merged );

		function walk( list ) {
			list.forEach( function ( block ) {
				let key = findLayerKeyForBlock( block, merged );
				let layer = key && merged[ key ] ? merged[ key ] : null;
				if ( ! layer && layerBlocks.length === 1 && keys.length === 1 ) {
					key = keys[ 0 ];
					layer = merged[ key ];
				}
				if ( layer && isLayerBlockType( block.name ) ) {
					const hasClassPos = posFromClassName( block.attributes && block.attributes.className );
					const hasAttr = block.attributes && block.attributes.afvfLayerPos;
					if ( ! hasClassPos && ! hasAttr && ! isInsideSyncedComposition( block.clientId ) ) {
						const base = ensureStableLayerClass( block.attributes.className || '', block.clientId );
						const storeKey = key || layerStorageKey( { className: base }, block.clientId );
						dispatch.updateBlockAttributes( block.clientId, {
							afvfLayerPos: layer,
							className: classWithPos( base, layer ),
							anchor: storeKey,
						} );
					}
				}
				if ( block.innerBlocks && block.innerBlocks.length ) {
					walk( block.innerBlocks );
				}
			} );
		}
		walk( coverBlock.innerBlocks || [] );

		if ( ! isInsideSyncedComposition( coverBlock.clientId ) ) {
			const coverMap = Object.assign( {}, coverBlock.attributes.afvfBannerLayers || {}, merged );
			if ( JSON.stringify( coverBlock.attributes.afvfBannerLayers || {} ) !== JSON.stringify( coverMap ) ) {
				dispatch.updateBlockAttributes( coverBlock.clientId, {
					afvfBannerLayers: coverMap,
				} );
			}
		}
	}

	function ensureCoverCanvasAttrs( coverBlock ) {
		if ( ! coverBlock || coverBlock.name !== 'core/cover' ) {
			return coverBlock;
		}
		if ( coverIsLayerHost( coverBlock.attributes ) ) {
			return coverBlock;
		}
		if ( coverIsCanvas( coverBlock.attributes ) ) {
			return coverBlock;
		}
		if ( coverIsPhotoHeroCard( coverBlock.attributes ) ) {
			return coverBlock;
		}
		if ( isInsideSyncedComposition( coverBlock.clientId ) ) {
			return coverBlock;
		}
		const dispatch = wp.data.dispatch( 'core/block-editor' );
		const cn = coverBlock.attributes && coverBlock.attributes.className ? String( coverBlock.attributes.className ) : '';
		dispatch.updateBlockAttributes( coverBlock.clientId, {
			className: ( cn + ' afvf-banner-canvas is-style-afvf-banner-canvas' ).trim(),
		} );
		return wp.data.select( 'core/block-editor' ).getBlock( coverBlock.clientId ) || coverBlock;
	}

	function findBannerCoverBlock( clientId ) {
		const select = wp.data.select( 'core/block-editor' );
		if ( ! select || ! select.getBlockParents ) {
			return null;
		}
		const parents = select.getBlockParents( clientId ) || [];
		for ( let i = 0; i < parents.length; i++ ) {
			const b = select.getBlock( parents[ i ] );
			if ( b && b.name === 'core/cover' ) {
				return ensureCoverCanvasAttrs( b );
			}
		}
		return null;
	}

	function persistLayerPosition( clientId, layer ) {
		const synced = isInsideSyncedComposition( clientId );
		if ( synced ) {
			maybeWarnSyncedComposition( clientId );
		}
		const dispatch = wp.data.dispatch( 'core/block-editor' );
		const current = wp.data.select( 'core/block-editor' ).getBlockAttributes( clientId ) || {};
		const baseClass = ensureStableLayerClass( current.className || '', clientId );
		const nextClass = classWithPos( baseClass, layer );
		const keyAfter = layerStorageKey( { className: nextClass, anchor: current.anchor }, clientId );
		if ( ! synced ) {
			dispatch.updateBlockAttributes( clientId, {
				afvfLayerPos: layer,
				className: nextClass,
				anchor: keyAfter,
			} );
		}
		writeMetaLayer( keyAfter, layer );
		const tokenMatch = /(afvf-pos-t\d+_\d+-[lr]\d+_\d+)/.exec( nextClass );
		if ( tokenMatch ) {
			writeMetaLayer( tokenMatch[ 1 ], layer );
		}
		flushAllLayersToPostMeta();
		const cover = findBannerCoverBlock( clientId );
		if ( ! cover || synced ) {
			return;
		}
		const key = keyAfter;
		const map = Object.assign( {}, cover.attributes.afvfBannerLayers || {} );
		map[ key ] = layer;
		dispatch.updateBlockAttributes( cover.clientId, {
			afvfBannerLayers: map,
		} );
	}

	function syncCoverLayersFromTree( coverBlock ) {
		if ( ! coverBlock || ! coverBlock.innerBlocks ) {
			return;
		}
		const map = Object.assign( {}, coverBlock.attributes.afvfBannerLayers || {} );
		let changed = false;
		function walk( list ) {
			list.forEach( function ( block ) {
				const pos = posFromBlock( block, coverBlock );
				const raw = pos
					? layerFromPos( pos )
					: block.attributes && block.attributes.afvfLayerPos;
				if ( raw && ( raw.top || raw.left || raw.right ) ) {
					const key = layerStorageKey( block.attributes, block.clientId );
					writeMetaLayer( key, raw );
					if ( JSON.stringify( map[ key ] ) !== JSON.stringify( raw ) ) {
						map[ key ] = raw;
						changed = true;
					}
				}
				if ( block.innerBlocks && block.innerBlocks.length ) {
					walk( block.innerBlocks );
				}
			} );
		}
		walk( coverBlock.innerBlocks );
		if ( changed ) {
			wp.data.dispatch( 'core/block-editor' ).updateBlockAttributes( coverBlock.clientId, {
				afvfBannerLayers: map,
			} );
		}
	}

	function ensureDragHandle( blockEl ) {
		if ( ! blockEl || blockEl.querySelector( ':scope > .afvf-drag-handle' ) ) {
			return;
		}
		const btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.contentEditable = 'false';
		btn.draggable = false;
		btn.className = 'afvf-drag-handle';
		btn.setAttribute( 'aria-label', 'Déplacer sur la bannière' );
		btn.textContent = 'Déplacer';
		blockEl.insertBefore( btn, blockEl.firstChild );
	}

	function injectHandles( root ) {
		root.querySelectorAll( COVER_LAYER_HOST_SEL ).forEach( function ( cover ) {
			if ( ! coverElIsLayerHost( cover ) ) {
				return;
			}
			cover
				.querySelectorAll(
					'.wp-block-image, .wp-block-buttons, .wp-block-heading, .wp-block-group, .afvf-mission-banner__title, .afvf-former-hero__title, .afvf-former-hero__disc'
				)
				.forEach( function ( node ) {
					const blockEl = node.closest( '[data-block]' );
					if ( ! blockEl || ! cover.contains( blockEl ) ) {
						return;
					}
					if ( ! blockElIsMovableLayer( blockEl ) ) {
						return;
					}
					ensureDragHandle( blockEl );
				} );
		} );
	}

	function walkBlocksInCover( coverBlock, root ) {
		hydrateLayerAttrsFromMaps( coverBlock );
		function walk( list ) {
			if ( ! list || ! list.length ) {
				return;
			}
			list.forEach( function ( block ) {
				if ( isInsideSyncedComposition( block.clientId ) ) {
					maybeWarnSyncedComposition( block.clientId );
				}
				const pos = posFromBlock( block, coverBlock );
				const el = findBlockEl( root, block.clientId );
				if ( pos && el && ! el.classList.contains( 'is-afvf-dragging' ) ) {
					applyPosToEl( el, pos );
				}
				if ( el && isLayerBlockType( block.name ) && coverIsLayerHost( coverBlock.attributes ) ) {
					ensureDragHandle( el );
				}
				if ( block.innerBlocks && block.innerBlocks.length ) {
					walk( block.innerBlocks );
				}
			} );
		}
		walk( coverBlock.innerBlocks );
	}

	function walkAllCovers( list, root ) {
		if ( ! list || ! list.length ) {
			return;
		}
		list.forEach( function ( block ) {
			if ( block.name === 'core/cover' ) {
				if ( coverIsPhotoHeroCard( block.attributes ) ) {
					stripCanvasClassesFromPhotoHero( block );
					if ( block.innerBlocks && block.innerBlocks.length ) {
						walkAllCovers( block.innerBlocks, root );
					}
					return;
				}
				stripCanvasClassesFromPlacementBanner( block );
				let cover = block;
				if ( ! coverIsLayerHost( cover.attributes ) ) {
					cover = ensureCoverCanvasAttrs( block );
				}
				if ( cover && coverIsLayerHost( cover.attributes ) ) {
					syncCoverLayersFromTree( cover );
					walkBlocksInCover( cover, root );
				}
			}
			if ( block.innerBlocks && block.innerBlocks.length ) {
				walkAllCovers( block.innerBlocks, root );
			}
		} );
	}

	function setupDragRoot( wrapper ) {
		if ( ! wrapper || wrapper.dataset.afvfDragReady === '1' ) {
			return;
		}
		wrapper.dataset.afvfDragReady = '1';

		let drag = null;

		function beginDrag( e, blockEl, container ) {
			const clientId = blockEl.getAttribute( 'data-block' );
			if ( ! clientId ) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			blockEl.classList.add( 'is-afvf-dragging' );
			wrapper.dataset.afvfDragging = '1';
			try {
				blockEl.setPointerCapture( e.pointerId );
			} catch ( err ) {
				if ( e.target && e.target.setPointerCapture ) {
					e.target.setPointerCapture( e.pointerId );
				}
			}
			const attrs = wp.data.select( 'core/block-editor' ).getBlockAttributes( clientId );
			const useRight = preferRightAnchor( attrs );
			drag = { clientId, container, blockEl, pointerId: e.pointerId, useRight };
		}

		wrapper.addEventListener(
			'pointerdown',
			function ( e ) {
				if ( e.button !== 0 ) {
					return;
				}
				const handle = e.target.closest( '.afvf-drag-handle' );
				if ( handle ) {
					e.preventDefault();
					e.stopPropagation();
					e.stopImmediatePropagation();
					const blockEl = handle.closest( '[data-block]' );
					const cover = findCoverFromEl( blockEl );
					const container = positionRoot( cover );
					if ( blockEl && container ) {
						beginDrag( e, blockEl, container );
					}
					return;
				}
				if ( e.altKey || e.shiftKey ) {
					const cover = findCoverFromEl( e.target );
					if ( ! cover ) {
						return;
					}
					const container = positionRoot( cover );
					let node = e.target;
					while ( node && node !== container ) {
						if ( node.getAttribute && node.getAttribute( 'data-block' ) ) {
							beginDrag( e, node, container );
							return;
						}
						node = node.parentElement;
					}
				}
			},
			true
		);

		/* Titre éditable : empêche le curseur de texte / la sélection de voler le geste */
		wrapper.addEventListener(
			'mousedown',
			function ( e ) {
				if ( e.target && e.target.closest && e.target.closest( '.afvf-drag-handle' ) ) {
					e.preventDefault();
				}
			},
			true
		);
		wrapper.addEventListener(
			'dragstart',
			function ( e ) {
				if ( drag || ( e.target && e.target.closest && e.target.closest( '.afvf-drag-handle' ) ) ) {
					e.preventDefault();
				}
			},
			true
		);

		wrapper.addEventListener(
			'pointermove',
			function ( e ) {
				if ( ! drag || e.pointerId !== drag.pointerId ) {
					return;
				}
				const pct = pctFromPointer( drag.container, e.clientX, e.clientY );
				if ( drag.useRight ) {
					applyPosToEl( drag.blockEl, { top: pct.top, right: pct.right, useRight: true } );
				} else {
					applyPosToEl( drag.blockEl, { top: pct.top, left: pct.left, useRight: false } );
				}
				drag.blockEl.style.setProperty( 'z-index', '20', 'important' );
			},
			true
		);

		function endDrag( e ) {
			if ( ! drag || ( e.pointerId !== undefined && e.pointerId !== drag.pointerId ) ) {
				return;
			}
			const { clientId, container, useRight, blockEl } = drag;
			blockEl.classList.remove( 'is-afvf-dragging' );
			delete wrapper.dataset.afvfDragging;
			try {
				blockEl.releasePointerCapture( drag.pointerId );
			} catch ( err ) {
				// ignore
			}
			const pct = pctFromPointer( container, e.clientX, e.clientY );
			const layer = useRight
				? { top: pct.top, right: pct.right }
				: { top: pct.top, left: pct.left };
			persistLayerPosition( clientId, layer );
			applyPosToEl( blockEl, useRight ? { top: layer.top, right: layer.right, useRight: true } : { top: layer.top, left: layer.left, useRight: false } );
			drag = null;
		}

		wrapper.addEventListener( 'pointerup', endDrag, true );
		wrapper.addEventListener( 'pointercancel', endDrag, true );

		let syncTimer = null;
		let lastMetaSig = '';
		wp.data.subscribe( function () {
			if ( syncTimer ) {
				return;
			}
			syncTimer = window.setTimeout( function () {
				syncTimer = null;
				if ( wrapper.dataset.afvfDragging === '1' ) {
					return;
				}
				if ( ! wp.data.select( 'core/block-editor' ) ) {
					return;
				}
				const metaSig = JSON.stringify( readMetaLayers() );
				if ( metaSig !== lastMetaSig ) {
					lastMetaSig = metaSig;
					Object.keys( hydratedCovers ).forEach( function ( k ) {
						delete hydratedCovers[ k ];
					} );
				}
				const blocks = wp.data.select( 'core/block-editor' ).getBlocks();
				walkAllCovers( blocks, wrapper );
				injectHandles( wrapper );
				cleanupPhotoHeroLayerUi( wrapper );
			}, 120 );
		} );

		/* Avant enregistrement : recopier toutes les positions sur le Cover */
		if ( wp.data.subscribe ) {
			let wasSaving = false;
			wp.data.subscribe( function () {
				const select = wp.data.select( 'core/editor' );
				if ( ! select || ! select.isSavingPost ) {
					return;
				}
				const saving = select.isSavingPost();
				if ( saving && ! wasSaving ) {
					flushAllLayersToPostMeta();
					const blocks = wp.data.select( 'core/block-editor' ).getBlocks();
					walkAllCovers( blocks, wrapper );
				}
				wasSaving = saving;
			} );
		}
	}

	function findEditorWrappers( doc ) {
		if ( ! doc ) {
			return [];
		}
		return Array.from( doc.querySelectorAll( '.editor-styles-wrapper' ) );
	}

	function bootAll() {
		findEditorWrappers( document ).forEach( setupDragRoot );
		document.querySelectorAll( 'iframe' ).forEach( function ( frame ) {
			try {
				if ( frame.contentDocument ) {
					findEditorWrappers( frame.contentDocument ).forEach( setupDragRoot );
				}
			} catch ( err ) {
				// ignore
			}
		} );
	}

	wp.domReady( function () {
		bootAll();
		const obs = new MutationObserver( bootAll );
		obs.observe( document.documentElement, { childList: true, subtree: true } );
		setTimeout( bootAll, 500 );
		setTimeout( bootAll, 1500 );
	} );
} )();
