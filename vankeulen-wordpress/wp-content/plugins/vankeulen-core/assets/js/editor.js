/**
 * Van Keulen – editor-integratie (zonder build-stap).
 *
 * - Voorbeeldweergave + instellingen voor de dynamische "Van Keulen"-blokken.
 * - Zijbalkpaneel "Zoekmachines" voor SEO-titel, meta-omschrijving en noindex.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var be = wp.blockEditor;
	var c = wp.components;
	var SSR = wp.serverSideRender;
	var cfg = window.vkEditor || {};

	var iconen = {
		logo: 'store',
		knop: 'button',
		contactgegevens: 'location',
		openingstijden: 'clock',
		beschikbaarheid: 'yes-alt',
		mededeling: 'megaphone',
		prijzen: 'money-alt',
		faq: 'editor-help',
		aanvraagformulier: 'feedback',
		kaart: 'location-alt',
		kruimelpad: 'arrow-right-alt',
		'mobiele-balk': 'smartphone',
		copyright: 'editor-code',
	};

	var uitleg = {
		contactgegevens: 'Wijzig adres, telefoon en e-mail via Van Keulen → Bedrijfsgegevens.',
		openingstijden: 'Wijzig openingstijden via Van Keulen → Bedrijfsgegevens.',
		beschikbaarheid: 'Wijzig de beschikbaarheid via Van Keulen → Bedrijfsgegevens. Staat deze op "Niet tonen", dan is dit blok leeg.',
		mededeling: 'Zet de mededeling aan/uit via Van Keulen → Bedrijfsgegevens.',
		prijzen: 'Tarieven publiceren of "prijs aanvragen" tonen: Van Keulen → Bedrijfsgegevens → Prijzen.',
		faq: 'Vragen toevoegen of wijzigen: Van Keulen → Veelgestelde vragen. Volgorde via het veld "Volgorde".',
		aanvraagformulier: 'Welke objecten en periodes getoond worden stelt u in via Van Keulen → Bedrijfsgegevens → Aanvraagformulier.',
		'mobiele-balk': 'Alleen zichtbaar op telefoons. Bellen/WhatsApp verschijnen zodra de nummers zijn ingevuld.',
	};

	function opties( obj ) {
		return Object.keys( obj ).map( function ( k ) {
			return { value: k, label: obj[ k ] };
		} );
	}

	function instellingen( naam, props ) {
		var a = props.attributes;
		var set = props.setAttributes;
		var velden = [];

		if ( naam === 'knop' ) {
			velden.push(
				el( c.SelectControl, {
					key: 'soort',
					label: 'Soort knop',
					value: a.soort,
					options: [
						{ value: 'aanvragen', label: 'Stallingsplaats aanvragen' },
						{ value: 'prijs', label: 'Prijs aanvragen' },
						{ value: 'bellen', label: 'Bellen' },
						{ value: 'whatsapp', label: 'WhatsApp' },
						{ value: 'route', label: 'Route plannen' },
						{ value: 'locatie', label: 'Bekijk locatie (Google Maps)' },
						{ value: 'email', label: 'E-mail' },
						{ value: 'link', label: 'Eigen link' },
					],
					onChange: function ( v ) {
						set( { soort: v } );
					},
				} ),
				el( c.SelectControl, {
					key: 'stijl',
					label: 'Stijl',
					value: a.stijl,
					options: [
						{ value: 'primair', label: 'Primair (groen)' },
						{ value: 'secundair', label: 'Secundair (omlijnd)' },
						{ value: 'licht', label: 'Licht (op donkere achtergrond)' },
						{ value: 'tekstlink', label: 'Tekstlink met pijl' },
					],
					onChange: function ( v ) {
						set( { stijl: v } );
					},
				} ),
				el( c.TextControl, {
					key: 'label',
					label: 'Tekst op de knop (leeg = standaard)',
					value: a.label,
					onChange: function ( v ) {
						set( { label: v } );
					},
				} )
			);
			if ( a.soort === 'link' ) {
				velden.push(
					el( c.TextControl, {
						key: 'url',
						label: 'Link (bijv. /bootstalling/)',
						value: a.url,
						onChange: function ( v ) {
							set( { url: v } );
						},
					} )
				);
			}
			if ( a.soort === 'aanvragen' || a.soort === 'prijs' ) {
				velden.push(
					el( c.SelectControl, {
						key: 'type',
						label: 'Object vooraf selecteren in het formulier',
						value: a.type,
						options: [ { value: '', label: '— geen —' } ].concat( opties( cfg.objecten || {} ) ),
						onChange: function ( v ) {
							set( { type: v } );
						},
					} )
				);
			}
		}

		if ( naam === 'logo' ) {
			velden.push(
				el( c.SelectControl, {
					key: 'variant',
					label: 'Variant',
					value: a.variant,
					options: [
						{ value: 'donker', label: 'Voor lichte achtergrond' },
						{ value: 'licht', label: 'Voor donkere achtergrond' },
					],
					onChange: function ( v ) {
						set( { variant: v } );
					},
				} )
			);
		}

		if ( naam === 'beschikbaarheid' ) {
			velden.push(
				el( c.SelectControl, {
					key: 'stijl',
					label: 'Weergave',
					value: a.stijl,
					options: [
						{ value: 'badge', label: 'Compact label' },
						{ value: 'blok', label: 'Blok' },
					],
					onChange: function ( v ) {
						set( { stijl: v } );
					},
				} )
			);
		}

		if ( naam === 'contactgegevens' ) {
			velden.push(
				el( c.ToggleControl, {
					key: 'metNaam',
					label: 'Bedrijfsnaam tonen',
					checked: !! a.metNaam,
					onChange: function ( v ) {
						set( { metNaam: v } );
					},
				} )
			);
		}

		if ( naam === 'kaart' ) {
			velden.push(
				el( c.ToggleControl, {
					key: 'knoppen',
					label: 'Knoppen "Plan uw route" en "Bekijk locatie" tonen',
					checked: !! a.knoppen,
					onChange: function ( v ) {
						set( { knoppen: v } );
					},
				} )
			);
		}

		if ( naam === 'faq' ) {
			velden.push(
				el( c.RangeControl, {
					key: 'aantal',
					label: 'Aantal vragen (0 = alle)',
					value: a.aantal,
					min: 0,
					max: 30,
					onChange: function ( v ) {
						set( { aantal: v || 0 } );
					},
				} )
			);
		}

		if ( uitleg[ naam ] ) {
			velden.push( el( 'p', { key: 'uitleg', style: { color: '#555' } }, uitleg[ naam ] ) );
		}

		if ( ! velden.length ) {
			return null;
		}
		return el( be.InspectorControls, null, el( c.PanelBody, { title: 'Instellingen' }, velden ) );
	}

	Object.keys( iconen ).forEach( function ( naam ) {
		var blok = 'vankeulen/' + naam;
		wp.blocks.registerBlockType( blok, {
			icon: iconen[ naam ],
			edit: function ( props ) {
				var blockProps = be.useBlockProps( { className: 'vk-editor-voorbeeld' } );
				return el(
					Fragment,
					null,
					instellingen( naam, props ),
					el(
						'div',
						blockProps,
						el(
							c.Disabled,
							null,
							el( SSR, {
								block: blok,
								attributes: props.attributes,
								EmptyResponsePlaceholder: function () {
									return el( 'p', { className: 'vk-editor-leeg' }, ( uitleg[ naam ] || 'Dit blok is nu leeg.' ) );
								},
							} )
						)
					)
				);
			},
			save: function () {
				return null;
			},
		} );
	} );

	/* ---- Zijbalk: Zoekmachines --------------------------------------- */
	var Paneel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	if ( ! cfg.seoActief || ! Paneel || ! wp.plugins ) {
		return;
	}

	function SeoPaneel() {
		var postType = wp.data.useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var entity = wp.coreData.useEntityProp( 'postType', postType, 'meta' );
		var meta = entity[ 0 ] || {};
		var setMeta = entity[ 1 ];
		if ( postType !== 'page' ) {
			return null;
		}
		var titel = meta._vk_seo_titel || '';
		var oms = meta._vk_seo_omschrijving || '';

		function teller( tekst, max ) {
			var n = tekst.length;
			var kleur = n === 0 ? '#555' : n > max ? '#b32d2e' : '#2D6A43';
			return el( 'span', { style: { color: kleur } }, n + ' / ' + max + ' tekens' );
		}

		return el(
			Paneel,
			{ name: 'vk-seo', title: 'Zoekmachines (Google)', icon: 'search' },
			el( c.TextControl, {
				label: 'SEO-titel',
				help: el( 'span', null, 'Leeg = paginatitel | ' + ( cfg.siteNaam || '' ) + '. ', teller( titel, 60 ) ),
				value: titel,
				onChange: function ( v ) {
					setMeta( Object.assign( {}, meta, { _vk_seo_titel: v } ) );
				},
			} ),
			el( c.TextareaControl, {
				label: 'Meta-omschrijving',
				help: el( 'span', null, 'Leeg = samenvatting van de pagina. ', teller( oms, 155 ) ),
				value: oms,
				rows: 4,
				onChange: function ( v ) {
					setMeta( Object.assign( {}, meta, { _vk_seo_omschrijving: v } ) );
				},
			} ),
			el( c.ToggleControl, {
				label: 'Niet tonen in Google (noindex)',
				checked: !! meta._vk_noindex,
				onChange: function ( v ) {
					setMeta( Object.assign( {}, meta, { _vk_noindex: v } ) );
				},
			} )
		);
	}

	wp.plugins.registerPlugin( 'vk-seo', { render: SeoPaneel } );
} )( window.wp );
