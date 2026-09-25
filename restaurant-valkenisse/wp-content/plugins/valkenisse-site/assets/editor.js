/**
 * Editorweergave van de Valkenisse-blokken. Geen build-stap nodig:
 * de blokken worden op de server opgebouwd en hier live getoond.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck } = wp.blockEditor;
	const { PanelBody, SelectControl, ToggleControl, CheckboxControl, RangeControl, TextControl, TextareaControl, Notice, Button } = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const data = window.valkenisseData || { menu: [], fotos: [], admin: '' };

	const preview = ( name, props ) =>
		el( 'div', useBlockProps(), el( ServerSideRender, { block: name, attributes: props.attributes, skipBlockSupportAttributes: true } ) );

	const termChecklist = ( label, terms, props, key, help ) =>
		el(
			PanelBody,
			{ title: label, initialOpen: true },
			el( 'p', { className: 'components-base-control__help' }, help ),
			terms.map( ( term ) =>
				el( CheckboxControl, {
					key: term.value,
					label: term.label,
					checked: props.attributes[ key ].includes( term.value ),
					onChange: ( on ) => {
						const list = props.attributes[ key ].filter( ( v ) => v !== term.value );
						if ( on ) list.push( term.value );
						props.setAttributes( { [ key ]: list } );
					},
				} )
			)
		);

	const settingsNotice = ( text ) =>
		el( Notice, { status: 'info', isDismissible: false }, text, ' ', el( 'a', { href: data.admin, target: '_blank' }, 'Openen' ) );

	const controls = {
		openingstijden: ( props ) => [
			el( PanelBody, { title: 'Weergave', key: 'w' },
				el( SelectControl, {
					label: 'Wat tonen?',
					value: props.attributes.weergave,
					options: [
						{ label: 'Vandaag geopend (kort)', value: 'vandaag' },
						{ label: 'Huidig seizoen per dag', value: 'seizoen' },
						{ label: 'Volledig overzicht alle seizoenen', value: 'overzicht' },
					],
					onChange: ( weergave ) => props.setAttributes( { weergave } ),
				} ),
				el( ToggleControl, {
					label: 'Link "Bekijk alle openingstijden"',
					checked: props.attributes.toonLink,
					onChange: ( toonLink ) => props.setAttributes( { toonLink } ),
				} ),
				settingsNotice( 'De tijden zelf wijzigt u via het menu Openingstijden.' )
			),
		],
		contact: ( props ) => [
			el( PanelBody, { title: 'Weergave', key: 'w' },
				el( SelectControl, {
					label: 'Wat tonen?',
					value: props.attributes.weergave,
					options: [
						{ label: 'Adres, telefoon, e-mail en knoppen', value: 'volledig' },
						{ label: 'Alleen adres en gegevens', value: 'adres' },
						{ label: 'Alleen knoppen (bellen, route, reserveren)', value: 'knoppen' },
					],
					onChange: ( weergave ) => props.setAttributes( { weergave } ),
				} ),
				settingsNotice( 'Adres en telefoon wijzigt u via Openingstijden → Contact & reserveren.' )
			),
		],
		menukaart: ( props ) => [
			termChecklist( 'Categorieën', data.menu, props, 'categorieen', 'Niets aangevinkt = de hele kaart.' ),
			el( PanelBody, { title: 'Weergave', key: 'w' },
				el( ToggleControl, { label: 'Categorie-navigatie tonen', checked: props.attributes.navigatie, onChange: ( navigatie ) => props.setAttributes( { navigatie } ) } ),
				el( ToggleControl, { label: "Foto's bij gerechten tonen", checked: props.attributes.fotos, onChange: ( fotos ) => props.setAttributes( { fotos } ) } )
			),
		],
		galerij: ( props ) => [
			termChecklist( 'Fotocategorieën', data.fotos, props, 'categorieen', "Niets aangevinkt = alle foto's met een categorie." ),
			el( PanelBody, { title: 'Weergave', key: 'w' },
				el( ToggleControl, { label: 'Filterknoppen tonen', checked: props.attributes.filter, onChange: ( filter ) => props.setAttributes( { filter } ) } ),
				el( RangeControl, { label: "Maximaal aantal foto's", min: 3, max: 200, value: props.attributes.maximum, onChange: ( maximum ) => props.setAttributes( { maximum } ) } )
			),
		],
		'menukaart-pdf': ( props ) => [
			el( PanelBody, { title: 'Menukaart-PDF', key: 'p' },
				el( 'p', null, props.attributes.pdfId ? 'Er is een PDF gekozen. Nieuwe kaart? Kies hieronder de nieuwe PDF.' : 'Kies de PDF van de menukaart.' ),
				el( MediaUploadCheck, null,
					el( MediaUpload, {
						allowedTypes: [ 'application/pdf' ],
						value: props.attributes.pdfId,
						onSelect: ( media ) => props.setAttributes( { pdfId: media.id } ),
						render: ( { open } ) => el( Button, { variant: 'primary', onClick: open }, props.attributes.pdfId ? 'Andere PDF kiezen' : 'PDF kiezen' ),
					} )
				),
				el( ToggleControl, {
					label: 'Tekstversie tonen (uitklapbaar)',
					help: 'Aanbevolen: zo kunnen Google en schermlezers de gerechten ook lezen. De tekst komt uit het menu Menukaart.',
					checked: props.attributes.tekstversie,
					onChange: ( tekstversie ) => props.setAttributes( { tekstversie } ),
				} )
			),
		],
		buffetten: ( props ) => [
			el( PanelBody, { title: 'Tekst boven de buffetten', key: 't' },
				el( TextControl, { label: 'Kop', value: props.attributes.titel, onChange: ( titel ) => props.setAttributes( { titel } ) } ),
				el( TextareaControl, { label: 'Introductie', value: props.attributes.intro, onChange: ( intro ) => props.setAttributes( { intro } ) } ),
				el( 'p', { className: 'components-base-control__help' }, 'Kop en tekst verschijnen alleen als er gepubliceerde buffetten zijn.' )
			),
		],
		formulier: ( props ) => [
			el( PanelBody, { title: 'Formulier', key: 'f' },
				el( SelectControl, {
					label: 'Soort formulier',
					value: props.attributes.soort,
					options: [
						{ label: 'Tafel reserveren', value: 'reservering' },
						{ label: 'Studio aanvragen', value: 'studio' },
						{ label: 'Feest of partij', value: 'feest' },
						{ label: 'Contact', value: 'contact' },
					],
					onChange: ( soort ) => props.setAttributes( { soort } ),
				} )
			),
		],
	};

	[ 'openingstijden', 'contact', 'menukaart', 'menukaart-pdf', 'studios', 'buffetten', 'galerij', 'formulier', 'actiebalk', 'mededeling' ].forEach( ( slug ) => {
		const name = 'valkenisse/' + slug;
		registerBlockType( name, {
			edit( props ) {
				return el( Fragment, null,
					controls[ slug ] ? el( InspectorControls, null, controls[ slug ]( props ) ) : null,
					preview( name, props )
				);
			},
			save: () => null,
		} );
	} );
} )( window.wp );
