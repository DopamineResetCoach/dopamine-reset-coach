/**
 * Editor-registratie van de Valkenisse-blokken. Geen build-stap nodig:
 * elk blok toont in de editor een live voorbeeld (ServerSideRender) en
 * eenvoudige instellingen in de zijbalk.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { PanelBody, ToggleControl, SelectControl, TextControl, Disabled } = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const { __ } = wp.i18n;

	( window.vkBlocks || [] ).forEach( function ( def ) {
		const attributes = {};
		Object.keys( def.attributes ).forEach( function ( key ) {
			const a = def.attributes[ key ];
			attributes[ key ] = { type: a.type, default: a.default };
		} );

		registerBlockType( def.name, {
			apiVersion: 3,
			title: def.title,
			icon: def.icon,
			category: 'valkenisse',
			attributes: attributes,
			supports: { html: false, align: [ 'wide', 'full' ] },
			edit: function ( props ) {
				const controls = Object.keys( def.attributes ).map( function ( key ) {
					const a = def.attributes[ key ];
					const value = props.attributes[ key ];
					const onChange = function ( v ) {
						const next = {};
						next[ key ] = a.type === 'number' ? parseInt( v, 10 ) || 0 : v;
						props.setAttributes( next );
					};
					if ( a.type === 'boolean' ) {
						return el( ToggleControl, { key, label: a.label, checked: !! value, onChange } );
					}
					if ( a.enum ) {
						return el( SelectControl, {
							key,
							label: a.label,
							value,
							options: a.enum.map( ( v ) => ( { label: v, value: v } ) ),
							onChange,
						} );
					}
					return el( TextControl, { key, label: a.label, value: value === undefined ? '' : String( value ), onChange } );
				} );

				return el(
					Fragment,
					null,
					controls.length
						? el( InspectorControls, null, el( PanelBody, { title: __( 'Instellingen' ) }, controls ) )
						: null,
					el(
						'div',
						useBlockProps(),
						el(
							Disabled,
							null,
							el( ServerSideRender, { block: def.name, attributes: props.attributes } )
						),
						el(
							'p',
							{ className: 'vk-editor-hint' },
							__( 'Inhoud komt automatisch uit het menu "Strandpaviljoen".' )
						)
					)
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp );
