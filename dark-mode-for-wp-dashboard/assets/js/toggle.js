/**
 * Dark Mode for WP Dashboard — admin bar toggle.
 *
 * Vanilla JavaScript, no jQuery. Configuration arrives as the global
 * darkModeDashboard (wp_localize_script).
 */
( function () {
	'use strict';

	var cfg = window.darkModeDashboard;

	if ( ! cfg ) {
		return;
	}

	var node = document.getElementById( 'wp-admin-bar-dark-mode-dashboard' );
	var toggle = node ? node.querySelector( '.ab-item' ) : null;

	if ( ! node || ! toggle ) {
		return;
	}

	var live = node.querySelector( '.dm-live' );
	var body = document.body;
	var saved = cfg.preference; // What the server currently has stored.
	var initialPref = cfg.preference; // What the server pre-painted this page for.
	var inFlight = false;
	var queued = null;
	var stylesLoaded = ! cfg.lazyStyles || ! Object.keys( cfg.lazyStyles ).length;

	function isDark() {
		return body.classList.contains( 'dark-mode' );
	}

	function announce( message ) {
		if ( live ) {
			live.textContent = message;
		}
	}

	/**
	 * Reflect the current state in the icon and the switch semantics.
	 *
	 * role/aria-checked are applied from here rather than from PHP because the
	 * control does nothing at all without this script running.
	 */
	function paintState() {
		var dark = isDark();

		node.classList.toggle( 'dm-light', ! dark );
		toggle.setAttribute( 'role', 'switch' );
		toggle.setAttribute( 'aria-checked', dark ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', dark ? cfg.i18n.switchToLight : cfg.i18n.switchToDark );
	}

	// ---------------------------------------------------------------------
	// Lazy stylesheet loading
	// ---------------------------------------------------------------------

	/**
	 * Fetch the theme stylesheets a light-mode user never downloaded.
	 *
	 * Resolves once they have loaded, or after a short grace period, so the
	 * class flip does not land on a page with no rules behind it.
	 *
	 * @return {Promise} Resolves when the sheets are in the document.
	 */
	function loadStyles() {
		if ( stylesLoaded ) {
			return Promise.resolve();
		}

		stylesLoaded = true;

		var pending = Object.keys( cfg.lazyStyles ).map( function ( handle ) {
			return new Promise( function ( resolve ) {
				var id = handle + '-css';

				if ( document.getElementById( id ) ) {
					resolve();
					return;
				}

				var link = document.createElement( 'link' );

				link.id = id;
				link.rel = 'stylesheet';
				link.href = cfg.lazyStyles[ handle ];
				link.addEventListener( 'load', resolve );
				link.addEventListener( 'error', resolve );
				document.head.appendChild( link );
			} );
		} );

		return Promise.race( [
			Promise.all( pending ),
			new Promise( function ( resolve ) {
				window.setTimeout( resolve, 600 );
			} ),
		] );
	}

	// ---------------------------------------------------------------------
	// Editor canvases
	// ---------------------------------------------------------------------

	var CANVAS_STYLE_ID = 'dm-canvas-baseline';
	var TINYMCE_STYLE_ID = 'dm-toggle-override';

	function setDocStyle( doc, id, css ) {
		var existing = doc.getElementById( id );

		if ( ! css ) {
			if ( existing ) {
				existing.parentNode.removeChild( existing );
			}
			return;
		}

		if ( existing ) {
			existing.textContent = css;
			return;
		}

		var style = doc.createElement( 'style' );

		style.id = id;
		style.textContent = css;
		( doc.head || doc.documentElement ).appendChild( style );
	}

	/**
	 * Push the current state into every editor canvas on the page.
	 *
	 * Idempotent on purpose: the block editor remounts its iframe while
	 * loading, and a canvas that remounts after the last sync would otherwise
	 * keep whatever it was born with.
	 *
	 * @param {boolean} dark Whether the canvas should be dark.
	 */
	function syncCanvases( dark ) {
		var frames = document.querySelectorAll( 'iframe[name="editor-canvas"]' );

		Array.prototype.forEach.call( frames, function ( frame ) {
			try {
				var doc = frame.contentDocument;
				var canvas = doc && doc.body;

				if ( ! canvas ) {
					return;
				}

				canvas.classList.toggle( 'dark-mode', dark );
				canvas.classList.toggle( 'dark-mode-off', ! dark );

				// Belt and braces for the one case the server could not
				// pre-paint: a user whose stored preference is light, turning
				// dark inside the editor. Without this the canvas would depend
				// entirely on the class landing before the next frame.
				setDocStyle( doc, CANVAS_STYLE_ID, dark && 'disabled' === initialPref ? cfg.canvasDark : '' );
			} catch ( e ) {
				// Cross-origin canvas; nothing we can do, and nothing broken.
			}
		} );

		var mce = document.getElementById( 'content_ifr' );

		if ( mce ) {
			try {
				setDocStyle( mce.contentDocument, TINYMCE_STYLE_ID, dark ? '' : cfg.tinymceLight );
			} catch ( e ) {}
		}
	}

	/**
	 * Watch for canvases appearing or remounting.
	 *
	 * Only ever started on an editor screen, and scoped to the narrowest
	 * container that exists, rather than observing every DOM insertion in the
	 * whole admin for the lifetime of the tab.
	 */
	function watchCanvases() {
		var scheduled = false;

		function schedule() {
			if ( scheduled ) {
				return;
			}

			scheduled = true;
			window.requestAnimationFrame( function () {
				scheduled = false;
				syncCanvases( isDark() );
			} );
		}

		var target = document.querySelector( '.editor-visual-editor' )
			|| document.querySelector( '.interface-interface-skeleton__content' )
			|| document.getElementById( 'editor' )
			|| document.getElementById( 'wpbody-content' )
			|| document.body;

		new MutationObserver( schedule ).observe( target, { childList: true, subtree: true } );

		document.addEventListener( 'load', function ( event ) {
			if ( event.target && 'editor-canvas' === event.target.name ) {
				schedule();
			}
		}, true );

		schedule();

		// Fast while the editor mounts, then a cheap heartbeat: the canvas can
		// still remount an hour into a writing session, and re-applying a class
		// that is already there costs nothing.
		var ticks = 0;
		var timer = window.setInterval( function () {
			schedule();

			if ( ++ticks === 40 ) {
				window.clearInterval( timer );
				window.setInterval( schedule, 2000 );
			}
		}, 150 );
	}

	// ---------------------------------------------------------------------
	// State changes
	// ---------------------------------------------------------------------

	/**
	 * Apply a state to the document without touching the server.
	 *
	 * @param {boolean} dark Target state.
	 */
	function applyState( dark ) {
		body.classList.toggle( 'dark-mode', dark );

		// An explicit click always wins over "auto": drop the marker class so
		// the OS-change listener stops second-guessing the user until reload.
		body.classList.remove( 'dark-mode-auto' );

		paintState();

		if ( cfg.editorCanvas ) {
			syncCanvases( dark );
		}
	}

	/**
	 * Serial number of the most recent intent to change state.
	 *
	 * A click that has to fetch stylesheets first applies its state later, in a
	 * promise callback. If the save has failed by then, that callback would
	 * otherwise reinstate the state the rollback just undid — the page ending up
	 * dark while the message says the previous setting was restored. Bumping
	 * this invalidates any apply still in flight.
	 */
	var applyToken = 0;

	/**
	 * Put the document back to whatever the server still has stored.
	 *
	 * 'auto' is a third state, not a synonym for dark. The old rollback asked
	 * ( 'disabled' !== saved ), which is true for 'auto', so a failed save left
	 * an auto user on a light OS looking at a dark admin their preference never
	 * asked for. Restoring auto also puts the marker class back, which is what
	 * lets the OS-change listener resume speaking for them.
	 */
	function restoreSaved() {
		if ( 'auto' === saved ) {
			var media = window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' );
			var dark = !! ( media && media.matches );

			body.classList.toggle( 'dark-mode', dark );
			body.classList.add( 'dark-mode-auto' );
			paintState();

			if ( cfg.editorCanvas ) {
				syncCanvases( dark );
			}

			return;
		}

		applyState( 'enabled' === saved );
	}

	/**
	 * Persist a preference, putting the UI back if the server refuses it.
	 *
	 * The old handler was fire-and-forget: an expired nonce, a WAF block or a
	 * failed metadata write left the page showing a state that was never
	 * stored, and the next reload silently undid it.
	 *
	 * @param {string} preference One of 'enabled' or 'disabled'.
	 */
	function persist( preference ) {
		if ( inFlight ) {
			queued = preference;
			return;
		}

		inFlight = true;

		var data = new FormData();

		data.append( 'action', 'dark_mode_dashboard_toggle' );
		data.append( 'security', cfg.nonce );
		data.append( 'preference', preference );

		window.fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data,
		} ).then( function ( response ) {
			return response.json().catch( function () {
				return { success: false };
			} );
		} ).then( function ( result ) {
			if ( ! result || ! result.success ) {
				throw new Error( 'rejected' );
			}

			saved = preference;
			announce( 'enabled' === preference ? cfg.i18n.nowDark : cfg.i18n.nowLight );
		} ).catch( function () {
			// A newer click is still the user's intended choice and must be sent
			// after this failed request. Only roll back when that choice agrees
			// with the last saved value, or when no newer click is waiting.
			if ( null === queued || queued === saved ) {
				// Invalidate any apply still waiting on loadStyles(), so it cannot
				// reinstate the failed state after the rollback.
				applyToken++;
				restoreSaved();
				announce( cfg.i18n.saveFailed );
			}
		} ).then( function () {
			inFlight = false;

			// Clicks that arrived while the request was open: send the last one
			// so the stored value matches what the user is looking at, instead
			// of letting responses land out of order.
			if ( null !== queued ) {
				var next = queued;

				queued = null;

				if ( next !== saved ) {
					persist( next );
				}
			}
		} );
	}

	toggle.addEventListener( 'click', function ( event ) {
		event.preventDefault();

		var dark = ! isDark();
		var preference = dark ? 'enabled' : 'disabled';
		var token = ++applyToken;

		if ( dark && ! stylesLoaded ) {
			loadStyles().then( function () {
				// A failed save, or a newer click, has moved on without us. The
				// stylesheets are loaded either way — only the state change is
				// abandoned, so a later click still applies instantly.
				if ( token !== applyToken ) {
					return;
				}

				applyState( true );
			} );
		} else {
			applyState( dark );
		}

		persist( preference );
	} );

	// The resolver script flips the body class when the OS scheme changes under
	// an "auto" user; keep the icon and the switch state with it.
	document.addEventListener( 'dark-mode-dashboard-change', function () {
		paintState();

		if ( cfg.editorCanvas ) {
			syncCanvases( isDark() );
		}
	} );

	paintState();

	if ( cfg.editorCanvas ) {
		watchCanvases();
	}
} )();
