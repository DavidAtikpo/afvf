/**
 * Bloc Couverture : ajout d’une vidéo de fond (bouton dans la barre du bloc + panneau latéral).
 */
( function ( wp ) {
	if ( ! wp || ! wp.hooks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.compose ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;

	wp.hooks.addFilter( 'blocks.registerBlockType', 'afvf/cover-video-attrs', function ( settings, name ) {
		if ( name !== 'core/cover' ) {
			return settings;
		}
		settings.attributes = Object.assign( {}, settings.attributes, {
			afvfVideoUrl: { type: 'string', default: '' },
			afvfVideoId: { type: 'number', default: 0 }
		} );
		return settings;
	} );

	var withVideo = wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( props.name !== 'core/cover' ) {
				return el( BlockEdit, props );
			}

			var attrs = props.attributes;
			var setAttributes = props.setAttributes;
			var url = attrs.afvfVideoUrl || '';

			function onSelect( media ) {
				if ( ! media || ! media.url ) {
					return;
				}
				setAttributes( { afvfVideoUrl: media.url, afvfVideoId: media.id || 0 } );
			}

			var picker = function ( render ) {
				return el(
					be.MediaUploadCheck,
					null,
					el( be.MediaUpload, {
						onSelect: onSelect,
						allowedTypes: [ 'video' ],
						value: attrs.afvfVideoId,
						render: render
					} )
				);
			};

			var toolbar = el(
				be.BlockControls,
				{ group: 'other' },
				picker( function ( obj ) {
					return el( c.ToolbarButton, {
						icon: 'video-alt3',
						label: url ? __( 'Changer la vidéo de fond', 'afvf' ) : __( 'Ajouter une vidéo de fond', 'afvf' ),
						text: url ? __( 'Vidéo ✓', 'afvf' ) : __( 'Vidéo', 'afvf' ),
						onClick: obj.open
					} );
				} )
			);

			var panel = el(
				be.InspectorControls,
				null,
				el(
					c.PanelBody,
					{ title: __( 'Vidéo de fond (AFVF)', 'afvf' ), initialOpen: true },
					url
						? el( 'video', { src: url, muted: true, controls: true, preload: 'metadata', style: { width: '100%', marginBottom: '12px', background: '#000' } } )
						: el( 'p', { style: { marginTop: 0 } }, __( 'Aucune vidéo. La vidéo se lit en boucle, sans son, derrière le contenu du bloc.', 'afvf' ) ),
					picker( function ( obj ) {
						return el( c.Button, { variant: 'primary', onClick: obj.open, style: { marginRight: '8px' } }, url ? __( 'Changer la vidéo', 'afvf' ) : __( 'Choisir / téléverser une vidéo', 'afvf' ) );
					} ),
					url
						? el( c.Button, { variant: 'secondary', isDestructive: true, onClick: function () {
							setAttributes( { afvfVideoUrl: '', afvfVideoId: 0 } );
						} }, __( 'Retirer', 'afvf' ) )
						: null,
					el( 'p', { style: { fontSize: '12px', color: '#757575', marginBottom: 0 } }, __( 'Format conseillé : MP4 (H.264), moins de 10 Mo. La vidéo apparaît sur le site publié ; l’éditeur n’affiche que l’aperçu ci-dessus.', 'afvf' ) )
				)
			);

			return el( Fragment, null, el( BlockEdit, props ), toolbar, panel );
		};
	}, 'withAfvfCoverVideo' );

	wp.hooks.addFilter( 'editor.BlockEdit', 'afvf/cover-video-edit', withVideo );
} )( window.wp );
