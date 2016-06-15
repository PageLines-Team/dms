!function ($) {

	$.optPanel = {

		defaults: {
			mode: 'section-options'
			, sid: ''
			, sobj: ''
			, clone: 'settings'
			, uniqueID: 'settings'
			, panel: ''
			, settings: {}
			, objectID: ''
			, scope: 'global'

		}

		, cascade: ['local', 'type', 'global', 'template', 'section']

		, render: function( config ) {

			// another test

			var that = this
			,	opts
			, 	config = config || store.get('lastSectionConfig')

			that.config = $.extend({}, that.defaults, typeof config == 'object' && config)

			var mode = that.config.mode
			,	panel = (that.config.panel != '') ? that.config.panel : mode

			store.set('lastSectionConfig', config)

			if(mode == 'object')
				store.set('lastAreaConfig', that.config.objectID)

			that.sobj = that.config.sobj
			that.sid = that.config.sid
			that.uniqueID = that.config.clone
			that.optConfig = $.pl.config.opts
			that.data = $.pl.data
			that.scope = that.config.scope || 'global'

			that.panel = $( '.panel-' + panel )

			// On tab load, the activation hasn't been fired yet
			// so its hard to tell if panel is active. This is used as a workaround
			that.load = that.config.load || false

			if( mode == 'section-options' )
				that.sectionOptionRender()
			else if ( mode == 'settings' )
				that.settingsRender( that.config.settings )
			else if ( mode == 'panel' )
				that.panelRender( that.config.tab, that.config.settings.opts )


			that.onceOffScripts()

			that.setPanel()

			that.setBinding()

			$('.ui-tabs li').on('click.options-tab', $.proxy(that.setPanel, that))

		}

		, panelRender: function( index, theOptions ){
			var that = this

			tab = $("[data-panel='"+index+"']")

			opts = that.runEngine( theOptions, index )

			tab.find('.panel-tab-content').html( opts )

			that.runScriptEngine( index, theOptions )

		}

		, settingsRender: function( settings ) {
			var that = this

			$.each( settings , function(index, o) {

				tab = $("[data-panel='"+index+"']")

				opts = that.runEngine( o.opts, index )

				tab.find('.panel-tab-content').html( opts )

				that.runScriptEngine( index, o.opts )

			})



		}

		, sectionOptionRender: function() {

			var that = this
			, 	cascade = ['local', 'type', 'global', 'template', 'section']
			, 	sid = that.config.sid
			,	uniqueID = that.config.clone
			, 	scope = that.scope
			,	theKey = ''

			if( that.optConfig[ uniqueID ] && !$.isEmptyObject( that.optConfig[ uniqueID ].opts ) )
				opt_array = that.optConfig[ uniqueID ].opts
			else{

				opt_array = [{
					help: $.pl.lang("There are no options available.")
					, key: "no-opts"
					, label: $.pl.lang("No Options")
					, title: $.pl.lang("No Options")
					, type: "help"

				}]
			}

			$.each(cascade, function( i, scope ){

				var sel = sprintf("[data-panel='%s']", scope)
				, 	clone_text = sprintf('<i class="icon icon-screenshot"></i> %s <i class="icon icon-map-marker"></i> %s %s', uniqueID, scope, $.pl.lang( "scope" ) )
				, 	clone_desc = sprintf(' <span class="clip-desc"> &rarr; %s</span>', clone_text)

				tab = $(sel)

				tab.attr('data-clone', uniqueID)

				opts = that.runEngine( opt_array, scope )

				if(that.optConfig[ uniqueID ] && that.optConfig[ uniqueID ].name)
					tab.find('legend').html( that.optConfig[ uniqueID ].name + clone_desc)

				tab.find('.panel-tab-content').html( opts )

				that.runScriptEngine( 0, opt_array )

			})

			var theTabs = $('[data-key="section-options"]')

			var section = $('section[data-clone="'+uniqueID+'"]')
			,	panelScope

			if( section.hasClass('custom-section') ){
				panelScope = 'section'
				theKey = section.data('custom-section')
			} else if ( section.closest('.pl-area.custom-section').length ){
				panelScope = 'section'
				theKey = section.closest('.pl-area.custom-section').data('custom-section')
			} else if( section.closest('[data-region="template"]').length && $('[data-region="template"]').hasClass('custom-template') ){
				panelScope = 'template'
				theKey = $('[data-region="template"]').data('custom-template')
			} else if( section.closest('[data-region="header"]').length || section.closest('[data-region="footer"]').length )
				panelScope = 'global'
			else
				panelScope = $.pl.config.templateMode



			$('.panel-section-options [data-tab-action]').hide()
			$('[data-tab-action="'+panelScope+'"]').show()

			$('[data-panel="'+panelScope+'"]').data('key', theKey).attr('data-key', theKey)

			if( panelScope == 'section' ){

				theTabs.tabs( "option", "active", 4 )

			} else if( panelScope == 'template' ){

				theTabs.tabs( "option", "active", 3 )

			} else if(panelScope == 'global'){

				theTabs.tabs("option", {
				    "disabled": [1]
				})
				theTabs.tabs( "option", "active", 0 )


			} else if(panelScope == 'local'){

				theTabs.tabs("option", {
				    "disabled": [0, 1]
				})
				theTabs.tabs( "option", "active", 2 )

			} else if(panelScope == 'type'){
				theTabs.tabs("option", {
				    "disabled": [0]
				})
				theTabs.tabs( "option", "active", 1 )

			}


		}

		, checkboxDisplay: function( checkgroup ){

			var	globalSet = ( $('.scope-global.checkgroup-'+checkgroup).find('.check-standard .checkbox-input').is(':checked') ) ? true : false
			,	typeSet = ( $('.scope-type.checkgroup-'+checkgroup).find('.check-standard .checkbox-input').is(':checked') ) ? true : false
			,	typeFlipSet = ( $('.scope-type.checkgroup-'+checkgroup).find('.check-flip .checkbox-input').is(':checked') ) ? true : false

			$.each( this.cascade , function(index, currentScope) {

				var showFlip = false

				if( currentScope != 'global' && globalSet )
					showFlip = true

				if( !showFlip && currentScope == 'local' && typeSet )
					showFlip = true

				if( currentScope == 'local' && showFlip && typeFlipSet && globalSet )
					showFlip = false

				var theSelector = sprintf('.scope-%s.checkgroup-%s ', currentScope, checkgroup)

				if(showFlip){
					$( theSelector + '.check-flip').show()
					$( theSelector + '.check-standard').hide()
				} else {
					$( theSelector + '.check-flip').hide()
					$( theSelector + '.check-standard').show()
				}


			})

		}

		, setBinding: function(){
			var that = this

			$('.lstn').on('keyup.optlstn blur.optlstn change.optlstn paste.optlstn', function( e ){

				// FORM PREP
				// First do checkbox switching...
				if($(this).hasClass('checkbox-input')){

					var checkToggle = $(this).prev()
					,	checkGroup = $(this).closest('.checkbox-group').data('checkgroup')

					if( $(this).is(':checked') )
					    checkToggle.val(1)
					else
					    checkToggle.val(0)



				}

				// FORM SAVING
				// Form is ready, set up saving vars
				var theInput = $(this)
				, 	iType = theInput.getInputType()
				,	thePanel = theInput.closest('.tab-panel')
				, 	panelScope = thePanel.data('scope')
				,	scope = (panelScope) ? panelScope : that.scope
				,	key = thePanel.data('key')
				,	uniqueID = (thePanel.attr('data-clone')) ? thePanel.attr('data-clone') : false
				,	formData = that.activeForm.formParams()
				,	myValue = pl_do_shortcode(theInput.val())


				// need to extend list data on page for consistency
				$.pl.data.list = $.extend(true, $.pl.data.list, formData)

				// this comes from old scope system, should reconcile between these two
				$.pl.data[scope] = $.extend(true, $.pl.data[scope], formData)

				// for array option types, the extend is not allowing deletion, this corrects
				// Also stop formdata from containing empty values and sending them. This clutters things up.
				$.each( formData, function(i, o){
					if( typeof(o) == 'object' ){
					$.each( o, function(i2, o2){

						if( ! plIsset(o2) )	// added by simon
							return			// fixes null being passed as an array by multi_select

						if( typeof(o2) == 'object' ){

							$.pl.data[scope][i][i2] = o2

							if( typeof(o2) == 'object' ){

								$.each( o2, function(i3, o3){

									if( typeof(o3) == 'object' ){

										$.each( o3, function(i4, o4){

											if( o4 == '' )
												delete formData[i][i2][i3][i4]

										})
									}
								})
							}
						}

					})
				}
					if( o == '' )
						delete formData[i]
				})

				if(uniqueID)
					var sel = sprintf('[data-clone="%s"] [data-sync="%s"]', uniqueID, theInput.attr('id'))
				else
					var sel = sprintf('[data-sync="%s"]', theInput.attr('id'))



				if( $( sel ).length > 0 ){

					$( sel ).each(function(i){
						var el = $(this)
						,	syncMode = el.data('sync-mode') || ''
						,	syncPrepend = el.data('sync-pre') || ''
						,	syncPost = el.data('sync-post') || ''
						,	syncTarget = el.data('sync-target') || ''
						,	tagName = el.prop('tagName')


						if( tagName == 'IMG'){

							el.attr('src', myValue)

						} else if(syncMode == 'css') {
							el.css( syncTarget, myValue + syncPost)
						} else {
							el.html(myValue)
						}

					})

				} else {

					$.pl.flags.refreshOnSave = true
					$('.li-refresh').show()
				}

				if( e.type == 'blur' || ( e.type == 'change' && ( iType == 'checkbox' || iType == 'select') ) ){

					$.plSave.save({
						run: 'form'
						, store: formData
						, scope: scope
						, key: key
						, uid: uniqueID

					})
				}



			})
		}

		, updateAccordion: function( theAccordion ){
				theAccordion.find('.opt-group').each( function(indx, el) {

					var $that = $( this )
					,	itemNum = indx + 1
					,	itemNumber = $that.attr('data-item-num')

					$that.find('.lstn').each( function(inputIndex, inputElement){

						var optName = $( this ).attr('name')
						,	optID = $( this ).attr('id')

						if(optName)
							optName = optName.replace('item'+itemNumber, 'item'+itemNum )

						if(optID)
							optID = optID.replace('item'+itemNumber, 'item'+itemNum )

						$( this )
							.attr('name', optName)
							.attr('id', optID)

					})

					$that.attr('data-item-num', itemNum)


				})

				theAccordion.find('.lstn').first().trigger('blur')
		}

		, setPanel: function(){
			var that = this

			$('.opt-form.isotope').isotope( 'destroy' )

			that.panel.find('.tab-panel').each(function(){

				if( $(this).is(":visible") || that.load == $(this).data('panel') ){

					that.activeForm = $(this).find('.opt-form')
					that.optScope = that.activeForm.data('scope')
					that.optSID = that.activeForm.data('sid')



					$(this).find('.opt-tabs').tabs()

					that.accordionArea = $(this).find('.opt-accordion')

					that.activeForm.imagesLoaded( function(){

						that.accordionArea
							.accordion({
								header: ".opt-name"
								,	collapsible: true
								,	active: false
							})
							.sortable({
								axis: "y"
								,	containment: "parent"
								,	handle: ".opt-name"
								,	cursor: "move"
								,	stop: function(){

										that.updateAccordion( that.accordionArea )
									}
								})


					})

				}

			})
		}

		, setTabData: function(){
			var that = this

			$tab = that.panel
				.find('.tabs-nav li')
				.attr('data-sid', that.sid)
				.attr('data-clone', that.uniqueID)


		}

		, runEngine: function( opts, tabKey ){

			var that = this
			, 	optionHTML
			, 	optsOut = ''
			,	optCols = {}
			,	colOut = ''

			$.each( opts , function(index, o) {

				var specialClass = ''
				,	theTitle = o.title || o.label || ''
				, 	uniqueKey = ( o.key ) ? o.key : 'no-key-'+plUniqueID()
				, 	colNum = ( o.col ) ? o.col : 1
				,	optionName = ( theTitle != '' ) ? sprintf('<div class="opt-name">%s</div>', theTitle) : ''

				if( o.span )
					specialClass += 'opt-span-'+o.span

				optionHTML = that.optEngine( tabKey, o )

				if( typeof optCols[ colNum ] == 'undefined' )
					optCols[ colNum ] = ''

				optCols[ colNum ] += sprintf( '<div id="%s" class="opt opt-%s opt-type-%s %s" data-number="%s">%s<div class="opt-box">%s</div></div>', uniqueKey, uniqueKey, o.type, specialClass, index, optionName, optionHTML )


			})


			var colSpan = 12 / ( Object.keys(optCols).length )

			$.each( optCols , function(index, o) {

				colOut += sprintf( '<div class="span%s">%s</div>', colSpan, o )

			} )

		//	console.log(optCols)

			var optionInterface = sprintf( '<div class="opt-columns row fix"> %s </div>', colOut )

			return sprintf( '<form class="form-%1$s-%2$s form-scope-%2$s opt-area opt-form" data-sid="%1$s" data-scope="%2$s">%3$s</form>', that.sid, tabKey, optionInterface )


		}

		, optValue: function( scope, key, index, subkey ){

			var that = this
			, 	pageData = $.pl.data
			,	sectionData = $.pl.data.list
			,	index = index || false
			, 	subkey = subkey || false
			, 	value = ''

			// global settings are always related to 'global'
			if (that.config.mode == 'settings' || that.config.mode == 'panel' || scope == 'global_setting'){

				scope = 'global'

				// Set option value
				if( pageData[ 'global' ] && pageData[ 'global' ][ 'settings' ] && pageData[ 'global' ][ 'settings' ][ key ]){
					value = pl_html_input( pageData[ 'global' ][ 'settings' ][ key ] )
				}

			} else if( sectionData[ that.uniqueID ] && sectionData[ that.uniqueID ][ key ]){

				value = pl_html_input( sectionData[ that.uniqueID ][ key ] )

			}


			if( value != '' && index && subkey ){

				if( value[index] && value[index][subkey] ){
					value = pl_html_input(value[index][subkey])
				} else
					value = ''

			}

			return value


		}

		, optName: function( scope, key, type ){

			if(o.type == 'check'){

			} else {
				return sprintf('%s[%s]', that.uniqueID,  key )
			}

		}

		, addOptionObjectMeta: function( tabIndex, o, optLevel, parent ) {

			var that = this
			,	oNew = o

			oNew.classes = o.classes || ''

			if( optLevel == 3 ){
				oNew.name = sprintf('%s[%s][%s][%s]', that.uniqueID, parent.key, parent.itemNumber, o.key )
				oNew.value =  that.optValue( tabIndex, parent.key, parent.itemNumber, o.key )
				oNew.inputID = sprintf('%s_%s_%s', parent.key, parent.itemNumber, o.key )
			} else if( o.scope == 'global' ){

				oNew.name = sprintf('settings[%s]', o.key )
				oNew.value =  that.optValue( 'global_setting', o.key )
				oNew.inputID = o.key

			} else {
				oNew.name = sprintf('%s[%s]', that.uniqueID, o.key )
				oNew.value =  that.optValue( tabIndex, o.key )
				oNew.inputID = o.key
			}

			return oNew

		}

		, addHiddenInput: function( key, itemNumber ){
			var that = this
			return sprintf( '<input type="hidden" class="lstn dont-change" id="%s_%s_showitem" name="%s[%s][%s][showitem]" value="1" />', key, itemNumber, that.uniqueID, key, itemNumber)
		}

		, optEngine: function( tabIndex, o, optLevel, parent ) {

			var that = this
			, 	oHTML = ''
			, 	scope = (that.config.mode == 'settings' || that.config.mode == 'panel') ? 'global' : tabIndex
			, 	level = optLevel || 1
			,	optLabel = o.label || o.title
			,	sel = sprintf('[data-clone="%s"] [data-sync="%s"]', that.uniqueID, o.key)
			,	syncType = (o.type != 'multi' && $(sel).length > 0) ? 'exchange' : 'refresh'
			,	syncTooltip = (syncType == 'refresh') ? $.pl.lang("Refresh for preview.") : $.pl.lang("Syncs with element.")
			,	syncIcon = (syncType == 'refresh') ? 'refresh' : 'exchange'
			,	optDefault = o.default || ''
			,	parent = parent || {}

			o = that.addOptionObjectMeta( tabIndex, o, optLevel, parent )


			if( o.scope == 'global')
				optLabel += ' ( <i class="icon icon-globe" title="Global Setting"></i> Global Option )'

		//	o.classes = o.classes || ''
			//o.label = o.label || o.title

			if( o.type != 'edit_post' && o.type != 'link' && o.type != 'action_button' ){
				optLabel += sprintf(' <span data-key="%s" class="pl-help-text btn btn-mini pl-tooltip sync-btn-%s" title="%s"><i class="icon icon-%s"></i></span>', o.key, syncType, syncTooltip, syncIcon)
			}

			if( o.type == 'multi' ){
				if(o.opts){
					$.each( o.opts , function(index, osub) {

						oHTML += that.optEngine(tabIndex, osub, 2) // recursive

					})
				}

			}

			else if( o.type == 'accordion' ){

				// option value should be an array, so foreach

				var optionArray = ( typeof(o.value) == 'object' || typeof(o.value) == 'array' ) ? o.value : false
				,	opts_cnt = o.opts_cnt || 3

				if( ! optionArray ) {
					optionArray = new Array()
					for ( var i = 0; i < opts_cnt; i++ ){
						optionArray.push([]);
					}
				}

				var	itemType = o.post_type || 'Item'
				, 	itemNumber = 1
				, 	totalNum = optionArray.length || Object.keys(optionArray).length
				, 	removeShow = ( totalNum <= 1 ) ? 'display: none;' : ''

				oHTML += sprintf("<div class='opt-accordion toolbox-sortable'>")

				$.each( optionArray, function( ind, vals ){


					o.itemNumber = 'item'+itemNumber

					oHTML += sprintf("<div class='opt-group' data-item-num='%s'><h4 class='opt-name'><span class='bar-title'>%s %s</span> <span class='btn btn-mini remove-item' style='%s'><i class='icon icon-remove'></i></span></h4><div class='opt-accordion-opts'>", itemNumber, itemType, itemNumber, removeShow )

					if( o.opts ){
						$.each( o.opts , function(index, osub) {


							oHTML += that.optEngine(tabIndex, osub, 3, o) // recursive array

						})
					}

					// adds a hidden input set to true, so that the item doesn't disappear
					oHTML += that.addHiddenInput( o.key, o.itemNumber )

					oHTML += sprintf("</div></div>")

					itemNumber++
				})

				oHTML += sprintf("</div><div class='accordion-tools'><span class='btn btn-mini add-accordion-item' data-uid='' data-scope='' data-key=''><i class='icon icon-plus-sign'></i> Add %s</span></div>", itemType)

			}

			else if ( o.type == 'button_link' ){

				var buttonOpts = [
					{type: 'text', key: o.key, label: 'URL', }
					,	{type: 'text', key: o.key+'_text', label: 'Text'}
					,	{type: 'select_button', key: o.key+'_style', label: 'Style'}
				]

				oHTML += '<div class="button-link option-group">'

				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )

				$.each( buttonOpts , function(index, osub) {

					oHTML += that.optEngine(tabIndex, osub, level, parent) // recursive

				})

				oHTML += '</div>'

			}

			else if( o.type == 'disabled' ){ }

			else if( o.type == 'divider' ){ oHTML += '<hr />' }

			else if( o.type == 'color' ){

				var prepend = '<span class="btn add-on trigger-color"> <i class="icon icon-tint"></i> </span>'
				,	colorVal = (o.value != '') ? o.value : optDefault
				,	cssCompile = o.compile || ""


				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )
				oHTML += sprintf('<div class="input-prepend">%4$s<input type="text" id="%1$s" name="%3$s" class="lstn lstn-css pl-colorpicker color-%1$s" data-var="%5$s" value="%2$s" /></div>', o.inputID, o.value, o.name, prepend, cssCompile )

			}

			else if( o.type == 'image_upload' ){

			  	var imgSize = o.imgsize || 200
				,	size = imgSize + 'px'
				,	sizeMode = o.sizemode || 'width'
				,	remove = sprintf('<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">%s</a>', $.pl.lang("Remove") )
				,	thm = (o.value != '') ? sprintf('<div class="img-wrap"><img src="%s" /></div>', o.value) : ''
				,	has_alt = o.has_alt || false

				oHTML += '<div class="upload-box image-uploader">'

				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )

				oHTML += '<div class="uploader-input">'



					oHTML += sprintf('<input id="%1$s" name="%2$s" type="text" class="lstn text-input upload-input" placeholder="" value="%3$s" />', o.inputID, o.name, o.value )

					that.optValue( tabIndex, parent.key, parent.itemNumber, o.key )



					var attach_key = o.key + "_attach_id"
					,	oAttach = that.addOptionObjectMeta( tabIndex, {key: attach_key}, optLevel, parent )
					,	attach_value =  oAttach.value
					,	attach_name = (optLevel == 3) ? sprintf('%s[%s][%s][%s]', that.uniqueID, parent.key, parent.itemNumber, attach_key ) : sprintf('%s[%s]', that.uniqueID, attach_key )

				//	console.log(oAttach)

					oHTML += sprintf('<input id="%1$s" name="%2$s" type="hidden" class="lstn hidden-input" value="%3$s" />', attach_key, attach_name, attach_value)

					oHTML += sprintf('<div id="upload-%1$s" class="fineupload upload-%1$s fileupload-new" data-provides="fileupload"></div>', o.inputID)


				oHTML += '</div>'

				oHTML += sprintf( '<div class="opt-upload-thumb-%s opt-upload-thumb" >%s</div>', o.key, pl_do_shortcode(thm) );
			if( has_alt ){
				var alt = o.name.replace(']', '_alt]')
				,	id = o.inputID + '_alt'
				,	data = $.pl.data.list
				,	name = o.name.substring(0,7)
				,	opt_data = data[name]
				,	img_alt = ''
				if(plIsset(data[name]) && plIsset(data[name][id])) {
					img_alt = data[name][id]
				}
				oHTML += sprintf('<label for="%s">Image alt/title text</label>', id)
				oHTML += sprintf('<input id="%1$s" name="%2$s" type="text" class="lstn" placeholder="%4$s" value="%3$s" />', id, alt, img_alt, img_alt)
			}

				oHTML += '</div>'

			}

			else if( o.type == 'media_select_video' ){




				oHTML += '<div class="video-upload-inputs option-group">'


				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )


				oHTML +=  that.addVideoOption( o.value, o.inputID, o.name, 'Video Format 1')

				o2 = that.addOptionObjectMeta( tabIndex, {key: o.key+'_2'}, level, parent )

				oHTML +=  that.addVideoOption( o2.value, o2.inputID, o2.name, 'Video Format 2')

				oHTML += sprintf('<div class="opt-ref"><a href="#" class="btn btn-info btn-mini btn-ref"><i class="icon icon-info-sign"></i> %s</a><div class="help-block">%s</div></div>', $.pl.lang("About HTML5 Video"), $.pl.lang("Different browsers have different ways of handling html5 videos.<br />At the time of testing the best way to get cross browser support is to use an mp4 AND an ogv file.<br />mp4 = MPEG 4 files with H264 video codec and AAC audio<br />ogv = Ogg files with Theora video codec and Vorbis audio"))


				oHTML += '</div>'
			}

			// Text Options
			else if( o.type == 'text' || o.type == 'text_small' ){

				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )

				if( o.type == 'text_small' )
					o.classes += ' pl-text-small'

				var place = o.place || ""

				oHTML += sprintf('<input id="%1$s" name="%2$s" type="text" class="%4$s lstn" placeholder="%5$s" value="%3$s" />', o.inputID, o.name, o.value, o.classes, place)

			}

			else if( o.type == 'textarea' ){

				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )
				oHTML += sprintf('<textarea id="%s" name="%s" class="%s type-textarea lstn" >%s</textarea>', o.inputID, o.name, o.classes, o.value )

			}

			else if( o.type == 'select_menu' ){

				var select_opts = ''
				,	menus = $.pl.config.menus
				,	configure = $.pl.config.urls.menus

				if($.pl.config.menus){
					$.each($.pl.config.menus, function(skey, s){
						var selected = (o.value == s.term_id) ? 'selected' : ''

						select_opts += sprintf('<option value="%s" %s >%s</option>', s.term_id, selected, s.name)
					})
				}

				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )
				oHTML += sprintf('<select id="%s" name="%s" class="lstn"><option value="">%s</option>%s</select>', o.inputID, o.name, $.pl.lang("Default"), select_opts)

				oHTML += sprintf('<br/><a href="%s" class="btn btn-mini" ><i class="icon icon-edit"></i> %s</a>', configure, $.pl.lang( "Configure Menus") )
			}

			else if( o.type == 'action_button' ){

				oHTML += sprintf('<a href="#" data-action="%s" class="btn settings-action %s" >%s</a>', o.key, o.classes, optLabel )

			}

			else if( o.type == 'edit_post' ){
				var editLink = $.pl.config.urls.editPost

				oHTML += sprintf('<a href="%s" class="btn %s" >%s</a>', editLink, o.classes, optLabel )

			}

			else if( o.type == 'link' ){

				var target = o.target || '_blank'
				,	tab_link = o.tab_link || ''
				,	stab_link = o.stab_link || ''

				oHTML += sprintf('<div><a href="%s" class="btn %s" target="%s" data-tab-link="%s" data-stab-link="%s">%s</a></div>', o.url, o.classes, target, tab_link, stab_link, optLabel )

			}

			// Checkbox Options
			else if ( o.type == 'check' ) {

				var checked = (!o.value || o.value == 0 || o.value == '') ? '' : 'checked'
				,	toggleValue = (checked == 'checked') ? 1 : 0
				,	aux = sprintf('<input name="%s" class="checkbox-toggle" type="hidden" value="%s" />', o.name, toggleValue )


				var stdCheck =  sprintf('<label class="checkbox check-standard" >%s<input id="%s" class="checkbox-input lstn" type="checkbox" %s>%s</label>', aux, o.inputID, checked, optLabel )



				oHTML +=  sprintf('<div class="checkbox-group scope-%s checkgroup-%s" data-checkgroup="%s">%s</div>', scope, o.key, o.key, stdCheck )

			}

			// Select Options
			else if (
				o.type == 'select'
				|| o.type == 'count_select'
				|| o.type == 'count_select_same'
				|| o.type == 'select_same'
				|| o.type == 'select_taxonomy'
				|| o.type == 'select_wp_tax'
				|| o.type == 'select_icon'
				|| o.type == 'select_animation'
				|| o.type == 'select_multi'
				|| o.type == 'select_button'
				|| o.type == 'select_theme'
				|| o.type == 'select_padding'
				|| o.type == 'select_imagesizes'
			){


				var select_opts = (o.type != 'select_multi') ? sprintf( '<option value="" >&mdash; %s &mdash;</option>', $.pl.lang( "SELECT" ))  : ''

				if ( o.type == 'count_select' || o.type == 'count_select_same' ) {

					var cnt_start = parseInt(o.count_start) || 0
					,	cnt_num = parseInt(o.count_number) || 10
					,	cnt_multiple = parseInt(o.count_mult) || 1
					,	suffix = o.suffix || ''
					,	key_suffix = ( o.type == 'count_select_same' ) ? o.suffix : ''

					o.opts = {}

					for ( i = cnt_start; i <= cnt_num; i+=cnt_multiple ) {
						o.opts[ i+key_suffix ] = { name: i+suffix }
					}
				}


				if(o.type == 'select_wp_tax'){

					var taxes = $.pl.config.taxes
					o.opts = {}
					$.each(taxes, function(key, s){
						o.opts[ s ] = {name: s}
					})

				} else if(o.type == 'select_padding'){

					var icons = $.pl.config.icons
					o.opts = {}
					for(i = 0; i <= 200; i+=20)
						o.opts[ i ] = {name: i + ' px'}

				} else if(o.type == 'select_icon'){

					var icons = $.pl.config.icons

					o.opts = {}
					$.each(icons, function(key, s){
						o.opts[ s ] = {name: s}
					})
				} else if( o.type == 'select_animation' ){

					var anims = $.pl.config.animations

					o.opts = {}
					$.each(anims, function(key, s){
						o.opts[ key ] = {name: s}
					})

				} else if( o.type == 'select_button' ){

					var btns = $.pl.config.btns

					o.opts = {}
					$.each(btns, function(key, s){

						// we cant use a string of '0' as a default key here, as it stops the option engine from using a default :/
						if( '0' == key )
							key = ''

						o.opts[ key ] = {name: s}
					})

				}	else if( o.type == 'select_theme' ){

					var themes = $.pl.config.themes

					o.opts = {}
					$.each(themes, function(key, s){
						o.opts[ key ] = {name: s}
					})

				}else if( o.type == 'select_imagesizes' ){

						var sizes = $.pl.config.imgSizes

						o.opts = {}
						$.each(sizes, function(key, s){
							o.opts[ s ] = {name: s}
						})



					}

				if(o.opts){

					$.each(o.opts, function(key, s){

						var optValue = (o.type == 'select_same') ? s : key
						,	optName = (o.type == 'select_same') ? s : s.name
						,	selected = ''

						// Multi Select
						if(typeof o.value == 'object'){
							var selected = ''
							$.each(o.value, function(k, val){
								if(optValue == val)
									selected = 'selected'
							})

						} else {

							if(o.value != '')
								var selected = (o.value == optValue) ? 'selected' : ''
							else if( plIsset(o.default) )
								var selected = (o.default == optValue) ? 'selected' : ''

						}



						select_opts += sprintf('<option value="%s" %s >%s</option>', optValue, selected, optName)

					})
				}


				var multi = (o.type == 'select_multi') ? 'multiple' : ''


				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )
				oHTML += sprintf('<select id="%s" name="%s" class="%s lstn" data-type="%s" %s>%s</select>', o.inputID, o.name, o.classes, o.type, multi, select_opts)

				if( o.type == 'select_icon' ) {
					oHTML += sprintf('&nbsp;&nbsp;<i class="icon icon-preview icon-2x icon-%s" id="preview-icon" data-name="%s" style=""></i>', o.value, o.name )
				}

				if(o.type == 'select_taxonomy' && o.post_type){

					oHTML += sprintf(
						'<div style="margin-bottom: 10px;"><a href="%sedit.php?post_type=%s" target="_blank" class="btn btn-mini btn-info"><i class="icon icon-edit"></i> %s</a></div>',
						$.pl.config.urls.adminURL,
						o.post_type,
						$.pl.lang("Edit Sets")
					)
				}

				if ( ! o.ref && o.type == 'select_imagesizes' ){
					oHTML += sprintf('<div class="opt-ref"><a href="#" class="btn btn-info btn-mini btn-ref"><i class="icon icon-info-sign"></i> %s</a><div class="help-block">%s</div></div>', $.pl.lang("About Image Sizes"), $.pl.lang("Select which registered thumbnail size to use for the images. To add new sizes see: <a href='http://codex.wordpress.org/Function_Reference/add_image_size'>The Codex</a>"))
				}

			}

			else if( o.type == 'type' || o.type == 'fonts' ){

				var select_opts = ''

				if($.pl.config.fonts){
					$.each($.pl.config.fonts, function(skey, s){
						var google = (s.google) ? ' G' : ''
						, 	webSafe = (s.web_safe) ? ' *' : ''
						, 	uri	= (s.google) ? s.gfont_uri : ''
						,	selected = (o.value == skey) ? 'selected' : ''

						select_opts += sprintf('<option data-family=\'%s\' data-gfont=\'%s\' value="%s" %s >%s%s%s</option>', s.family, uri, skey, selected, s.name, google, webSafe)
					})
				}

				oHTML += sprintf('<label for="%s">%s</label>', o.inputID, optLabel )
				oHTML += sprintf('<select id="%s" name="%s" class="font-selector lstn"><option value="">&mdash; %s &mdash;</option>%s</select>', o.inputID, o.name, $.pl.lang("Select Font"), select_opts)

				oHTML += sprintf('<label for="preview-%s">%s</label>', o.key, $.pl.lang("Font Preview") )
				oHTML += sprintf('<textarea class="type-preview" id="preview-%s" style="">%s.</textarea>', o.key, $.pl.lang( "The quick brown fox jumps over the lazy dog" ) )
			}

			else if( o.type == 'template' ){
				oHTML += o.template
			}

			else if( o.type == 'help'  || o.type == 'help_important' ){


			} else {

				oHTML += sprintf('<div class="needed">%s %s</div>', o.type, $.pl.lang( "Type Still Needed" ) )

			}

			// Add help block
			if ( o.help ){

				var beforeHelp = ( o.type == 'help'  || o.type == 'help_important' ) ? sprintf('<label for="%s">%s</label>', o.inputID, o.label ) : ''

				oHTML += sprintf('<div class="help-block %s">%s %s</div>',  o.type, beforeHelp, o.help)

			}




			// Add help block
			if ( o.ref )
				oHTML += sprintf('<div class="opt-ref"><a href="#" class="btn btn-info btn-mini btn-ref"><i class="icon icon-info-sign"></i> %s</a><div class="help-block">%s</div></div>', $.pl.lang("More Info"),o.ref)

			if(level == 2 || level == 3)
				return sprintf('<div class="input-wrap">%s</div>', oHTML)
			else
				return oHTML

		}

		, addVideoOption: function( inputValue, inputID, inputName, inputLabel){

			var theOption = ''

			theOption += '<div class="upload-box media-select-video">'

			theOption += sprintf('<label for="%s">%s</label>', inputID, inputLabel )

			theOption += sprintf('<input id="%1$s" name="%2$s" type="text" class="lstn text-input upload-input" placeholder="" value="%3$s" />', inputID, inputName, inputValue )

			theOption += '<a class="btn btn-mini btn-primary pl-load-media-lib" data-mimetype="video"><i class="icon icon-edit"></i> Select</a> '
			theOption += sprintf(' <a class="btn btn-mini" href="%s"><i class="icon icon-upload"></i> Upload</a> <div class="btn  btn-mini rmv-upload"><i class="icon icon-remove"></i></div>', $.pl.config.urls.addMedia)


			theOption += '</div>'

			return theOption
		}

		, runScriptEngine: function ( tabIndex, opts ) {

			var that = this



			$.each(opts, function(index, o){

				that.scriptEngine(tabIndex, o)
			})

		}

		, onceOffScripts: function() {

			var that = this

			// Settings Actions
			$(".settings-action").on("click.settingsAction", function(e) {

				e.preventDefault()

				var btn = $(this)
				, 	theAction = btn.data('action')

				if( theAction == 'reset_global' || theAction == 'reset_local' || theAction == 'reset_type' || theAction == 'reset_global_child' ){

					if( theAction == 'reset_global' )
						var context = $.pl.lang("global site options")
					else if ( theAction == 'reset_type' )
						var context = $.pl.lang("post type options")
					else
						var context = $.pl.lang("local page options")

					var confirmText = sprintf( $.pl.lang("<h3>Are you sure?</h3><p>This will reset <strong>%s</strong> to their defaults.<br/>(Once reset, this will still need to be published live.)</p>"), context)

					,	page_tpl_import = $('[data-scope="importexport"] #page_tpl_import').attr('checked') || 'undefined'
					,	global_import = $('[data-scope="importexport"] #global_import').attr('checked') || 'undefined'
					,	type_import = $('[data-scope="importexport"] #type_import').attr('checked') || 'undefined'
					,	page_tpl_ = ('checked' == page_tpl_import ) ? $.pl.lang("<span class='btn btn-mini btn-info'>Page Templates</span>&nbsp;"): ''
					,	global_ = ('checked' == global_import ) ? $.pl.lang("<span class='btn btn-mini btn-info'>Global Options</span>&nbsp;"): ''
					,	type_ = ('checked' == type_import ) ? $.pl.lang("<span class='btn btn-mini btn-info'>Type Options</span>"): ''
					,	savingText = $.pl.lang("Resetting Options")
					,	refreshText = $.pl.lang("Successfully Reset. Refreshing page")

					if( theAction == 'reset_global_child' ) {

						var confirmText = sprintf( $.pl.lang("<h3>Are you sure?</h3><p>Importing this file will replace the following settings.<br /><strong>%s%s%s</strong></p>"), page_tpl_, global_,type_ )
						,	savingText = $.pl.lang("Importing From Child Theme")
 						,	refreshText = $.pl.lang("Successfully Imported. Refreshing page")
					}

					var args = {
							mode: 'settings'
						,	run: theAction
						,	confirm: true
						,	confirmText: confirmText
						,	savingText: savingText
						,	refresh: true
						,	refreshText: refreshText
						, 	log: true
						,	page_tpl_import: page_tpl_import
						,	global_import: global_import
						,	type_import: type_import

					}

			//		console.log(theAction)

					var response = $.plAJAX.run( args )

				}


				if( theAction == 'reset_cache') {
					var args = {
							mode: 'settings'
						,	run: theAction
						,	confirm: false
						,	confirmText: confirmText
						,	savingText: $.pl.lang("Flushing Caches")
						,	refresh: false
						,	refreshText: $.pl.lang("Success! Refreshing page")
						, 	log: true
					}
					var response = $.plAJAX.run( args )
				}


				if( theAction == 'opt_dump' ){

					var formDataObject = $('[data-scope="importexport"]').formParams()
					var dump = formDataObject.publish_config || false
					var confirmText = $.pl.lang("<h3>Are you sure?</h3><p>This will write all settings to a config file in your child theme named pl-config.json</p>")

					if(dump) {

						var args = {
								mode: 'settings'
							,	run: 'exporter'
							,	confirm: dump
							,	confirmText: confirmText
							,	savingText: $.pl.lang("Exporting Options")
							,	refresh: false
							,	refreshText: ''
							, 	log: true
							,	formData: JSON.stringify( formDataObject )
						}
						var response = $.plAJAX.run( args )


					} else if( ! dump) {
						// need to make a special url here...

						var export_global = formDataObject.export_global || false
						var templates = formDataObject.templates || false
						var export_types = formDataObject.export_types || false
						var url = $.pl.config.urls.siteURL + '?pl_exp'

						var endpoint = ''

						if( export_global ) {
							endpoint = endpoint + '&export_global=1'
						}
						if( templates ) {

							plPrint(templates)
							var tpls = []
							$.each( templates, function(key, value){
								if(value) {
									tpls.push(key)
								}
							})
							var tplsSlug = tpls.join('|') || false
							if(tplsSlug) {
								endpoint = endpoint + '&templates=' + tplsSlug
							}
						}
						if( export_types ) {
							endpoint = endpoint + '&export_types=1'
						}
						if(endpoint) {
							plPrint(url + endpoint)
							pl_url_refresh(url + endpoint)
						}
					}

				}
			})

			$('.checklist-tool').on('click', function (e) {
				e.preventDefault();
				var action = $(this).data('action')
				,	field = $(this).closest('fieldset')

				if(action == 'checkall'){

					field.find(':checkbox').prop('checked', true)

				} else if (action == 'uncheckall'){

					field.find(':checkbox').prop('checked', false)

				}

		    })


			$('.opt-name .remove-item').on('click', function (e) {



				var accord = $(this).closest('.opt-accordion')

				if( accord.find('.opt-group').length <= 2){
					accord.find('.remove-item').hide()
				}

				$(this).closest('.opt-group').remove()

				that.updateAccordion( accord )

			//	accord.find('.lstn').first().trigger('blur')

		    })

			$('.add-accordion-item').on('click', function (e) {

				var theOpt = $(this).closest('.opt-box')
				, 	theAccordion = theOpt.find('.opt-accordion')

				theNew = theOpt.find('.opt-group').first().clone( true )

				theNew.find('.bar-title').html('New Item')
				theNew.find('.ui-icon').remove()
				theNew.find('.lstn:not(.dont-change)').val('')
				theNew.find('.remove-item').show()
				theNew.find('.img-wrap').remove()

				// add to accordion
				theAccordion.append( theNew )

				theAccordion.accordion("destroy").accordion({
					header: ".opt-name"
					,	collapsible: true
					,	active: false
				})

				// Work around til we get a better image uploader script.
				// Can't figure out how to reinitialize so that it works
				theNew
					.find('.fineupload')
					.html( sprintf('<div class="help-block">%s</div>',$.pl.lang("Refresh Page for Image Upload Button")) )

				// change the name stuff
				// relight UI stuff

				that.updateAccordion( theAccordion )

				$('.lstn').off('keyup.optlstn blur.optlstn change.optlstn paste.optlstn')

				that.setBinding()



		    })



			$('#fileupload').fileupload({
				url: ajaxurl
				, dataType: 'json'
				, formData: {}
				, add: function(e, data){
					var toolBoxOpen = $.toolbox('open')


					$.toolbox('hide')
					var page_tpl_import = ('checked' == $('[data-scope="importexport"] #page_tpl_import').attr('checked') ) ? sprintf( '<span class="btn btn-mini btn-info">%s</span>&nbsp;', $.pl.lang("Page Templates") ) : ''
					, global_import = ('checked' == $('[data-scope="importexport"] #global_import').attr('checked') ) ? sprintf( '<span class="btn btn-mini btn-info">%s</span>&nbsp;', $.pl.lang("Global Options") ): ''
					, type_import = ('checked' == $('[data-scope="importexport"] #type_import').attr('checked') ) ? sprintf( '<span class="btn btn-mini btn-info">%s</span>', $.pl.lang("Type Options") ): ''

					bootbox.confirm(
						sprintf( $.pl.lang("<h3>Are you sure?</h3><p>Importing this file will replace the following settings.<br /><strong>%s%s%s</strong></p>"), page_tpl_import, global_import,type_import )
						, function( result ){

							if(result == true){

								data.submit()

							} else if( toolBoxOpen ){

								$('body').toolbox('show')

							}

					})

				}
				, complete: function (response) {

					plPrint(response)
					var result = $.parseJSON(response.responseText)
					,	error = result.import_error || false

				if( error ) {
					window.onbeforeunload = null

					var txt = sprintf( '<h3>%s</h3>%s%s',
					sprintf('<h3>%s</h3>', $.pl.lang("Import Failed!") ),
					sprintf( '%s %s', $.pl.lang("Looks like you tried to upload"), error ),
					sprintf( '<br />The proper file naming format has to look like this<br />pl-config_2014-02-27_20-00-51.json' )
					 )
					bootbox.confirm( txt )
				} else {
						window.onbeforeunload = null
						bootbox.dialog( $.pl.lang("<h3>Settings Imported</h3>") )
						var url = $.pl.config.urls.siteURL
						pl_url_refresh(url, 2000)
				}

				//	window.onbeforeunload = null
				//	bootbox.dialog( $.pl.lang("<h3>Settings Imported</h3>") )
				//	var url = $.pl.config.siteURL
				//	pl_url_refresh(url, 2000)
				}
			})

			$('#fileupload').bind('fileuploadsubmit', function (e, data) {

			    data.formData = {
					action: 'upload_config_file'
					, mode: 'fileupload'
					, refresh: true
					, refreshText: $.pl.lang("Imported Settings")
					, savingText: $.pl.lang("Importing")
					, run: 'upload_config'
					, page_tpl_import: $('[data-scope="importexport"] #page_tpl_import').attr('checked')
					, global_import: $('[data-scope="importexport"] #global_import').attr('checked')
					, type_import: $('[data-scope="importexport"] #type_import').attr('checked')
				}
				return true

			})


			// Color picker buttons
			$('.trigger-color').on('click', function(){
				$(this)
					.next()
					.find('input')
					.focus()
			})

			// Font previewing
			$('.font-selector, .font-weight').on('change', function(){

				var selector = $(this).closest('.opt').find('.font-selector')
				that.loadFontPreview( selector )

			})


			$('select').change(function(){
				var type = $(this).attr('data-type') || false
				if ( type == 'select_icon' ) {
					var name = $(this).attr('name')
					,	new_class = 'icon icon-preview icon-2x icon-' + $(this).val();
					$(sprintf('i[data-name="%s"]',name)).removeAttr('class')
					$(sprintf('i[data-name="%s"]',name)).addClass( new_class )
				}
			})

			$('.font-selector, .font-style').on('change', function(){

				var selector = $(this).closest('.opt').find('.font-selector')
				that.loadFontPreview( selector )

			})

			// Image Uploader
			$('.upload-input').on('change', function(){

				var val = $(this).val()
				,	closestOpt = $(this).closest('.opt')

				if(val){
					closestOpt.find('.rmv-upload').fadeIn()
				} else {
				//	closestOpt.find('.upload-thumb').fadeOut()
					closestOpt.find('.rmv-upload').fadeOut()
				}

			})

			$('.pl-load-media-lib').on('click', function(){

				if( $(this).data('mimetype') == 'video' )
					var mediaFrame = $.pl.config.urls.mediaLibraryVideo
				else
					var mediaFrame = $.pl.config.urls.mediaLibrary

				var theInput = $(this).closest('.upload-box').find('.upload-input')
				, 	optionID = theInput.attr('id')
				,	mediaFrame = mediaFrame + '&oid=' + optionID

				$.pl.iframeSelector = optionID

				$.toolbox('hide')

				bootbox.dialog(
					sprintf('<iframe src="%s"></iframe>', mediaFrame)
					, [ ]
					, {
						animate: false
						, classes: 'modal-large'
						, backdrop: true
					}
				)



				$('.bootbox').on('hidden.mediaDialog', function () {

					$.toolbox('show')


					theInput.trigger('blur').closest('.ui-accordion').accordion('refresh')

					$('.bootbox').off('hidden.mediaDialog')

				})



			})

			$('.rmv-upload').on('click', function(){

				$(this).closest('.upload-box')
					.find('.upload-input')
						.val('').trigger('blur')
					.end()
					.find('.opt-upload-thumb')
						.fadeOut()
					.end()
					.find('.lstn')
						.first().trigger('blur')

				that.reloadOptionLayout( $(this) )

			})

			// Tooltips inside of options
			$('.pl-tooltip')
				.tooltip({placement: 'top'})

			// Syncing buttons
			$('.sync-btn-exchange').on('click', function(e){

				e.preventDefault()

				var btn = $(this)
				,	key = btn.data('key')
				,	sel = sprintf('[data-clone="%s"] [data-sync="%s"]', that.uniqueID, key)
				,	el = $( sel )
				, 	offTop = el.offset().top - 120


				// Add Actions
				btn.find('i').addClass('icon icon-spin')
				el.removeClass('stop-focus').addClass('pl-focus')

				// Remove Actions
				setTimeout(function () {
				    el.addClass('stop-focus')
					btn.find('i').removeClass('icon icon-spin')
				}, 1000);

				// Scroll Page
				jQuery('html,body').animate({scrollTop: offTop}, 500);


			})

			$('.sync-btn-refresh').on('click', function(e){

				e.preventDefault()

				var $that = $(this)

				$that.find('i').addClass('icon icon-spin')
				window.onbeforeunload = null

				plCallWhenSet( 'saving', function(){

					location.reload()

				}, true )



			})

			$( '.btn-refresh' ).on('click.saveButton', function(){

				$(this).find('i').addClass('icon icon-spin')

				window.onbeforeunload = null
				location.reload()

			})


			// Reference Help Toggle
			$('.btn-ref').on('click.ref', function(){
				var closestRef = $(this).closest('.opt-ref')
				,	closestHelp = closestRef.find('.help-block')

				if(closestRef.hasClass('ref-open')){
					closestRef.removeClass('ref-open')
					closestHelp.hide()
				} else {
					closestRef.addClass('ref-open')
					closestHelp.show()
				}

				that.reloadOptionLayout( closestRef )
			})
		}

		, reloadOptionLayout: function( element ){
			element.closest('.isotope').isotope( 'reLayout' )
			element.closest('.opt-box').find('.opt-accordion').accordion('refresh')
		}

		, loadFontPreview: function( selector ) {

			var	key = selector.attr('id')
			,	selectOpt = selector.find('option:selected')
			, 	fam = selectOpt.data('family')
			, 	uri	= selectOpt.data('gfont')
			, 	ggl	= (uri != '') ? true : false
			, 	loader = 'loader'+key
			, 	weight = selector.closest('.opt').find('.font-weight').val()
			, 	weight = (weight) ? weight : 'normal'
			, 	style = selector.closest('.opt').find('.font-style').val()
			, 	style = (style) ? style : ''

			if(uri) {
				if(ggl){
					if( $('#'+loader).length != 0 )
						$('#'+loader).attr('href', uri)
					else
						$('head').append( sprintf('<link rel="stylesheet" id="%s" href="%s" />', loader, uri) )

				}
			} else {
				$('#'+loader).remove()
			}

			selector
				.next()
				.next()
				.css('font-family', fam)
				.css('font-weight', weight)
		}

		, scriptEngine: function( tabIndex, o, optLevel, parent ) {

			var that = this
			,	optLevel = optLevel || 1
			,	parent = parent || {}

		//	o = that.addOptionObjectMeta( tabIndex, o, optLevel, parent )
			//console.log(o)

			if( optLevel == 3 ){
				o.inputID = sprintf('%s_%s_%s', parent.key, parent.itemNumber, o.key )
			}

			// Multiple Options
			if( o.type == 'multi' ){

				if(o.opts){
					$.each( o.opts , function(index, osub) {

						that.scriptEngine(tabIndex, osub, 2, o) // recursive

					})
				}

			}

			else if( o.type == 'accordion' ){

				// option value should be an array, so foreach

				var optionArray = ( typeof(o.value) == 'object' || typeof(o.value) == 'array' ) ? o.value : [[],[],[]]
				, 	itemNumber = 1


				$.each( optionArray, function( ind, vals ){

					o.itemNumber = 'item'+itemNumber

					if( o.opts ){
						$.each( o.opts , function(index, osub) {


							that.scriptEngine(tabIndex, osub, 3, o) // recursive array

						})
					}
					itemNumber++
				})


			}

			else if( o.type == 'color' ){

				var dflt = ( isset( o.default ) ) ? o.default : ''

				dflt = dflt.replace('#', '')

				$( '.color-'+o.inputID ).colorpicker({
					color: dflt
					, allowNull: true
					, onClose: function(color, inst){
						$(this).trigger('blur') // fire to set page data
					}
				})

			}
			else if(  o.type == 'type' ||  o.type == 'fonts' ){


				that.loadFontPreview( $( sprintf('#%s.font-selector', o.inputID) ) )

			}

			else if( o.type == 'image_upload' ){

				that.theImageUploader( '.fineupload', o.sizelimit, o.extension )
			}

		}

		, theImageUploader: function( inputSelector, sizeLimit, extension ){

				var selector = inputSelector || '.fineupload'
				, 	sizeLimit = sizeLimit || 2097152 // 2M
				,	extension = extension || null
				,	allowedExtensions = ['jpeg', 'jpg', 'gif', 'png', 'ico', 'svg']

				$( selector ).fineUploader({
					request: {
						endpoint: ajaxurl
						, 	params: {
								action: 'pl_up_image'
								,	scope: 'global'
							}
					}
					,	multiple: false
					,	validation: {
							allowedExtensions: allowedExtensions,
							sizeLimit: sizeLimit
						}
					,	text: {
							uploadButton: sprintf( '<i class="icon icon-upload"></i> %s', $.pl.lang("Upload") )
						}
					// , 	debug: true
					,	template: '<div class="qq-uploader span12">' +
					                      '<pre class="qq-upload-drop-area span12 hidden"><span>{dragZoneText}</span></pre>' +
					                      sprintf( '<div class="qq-upload-button btn btn-primary btn-mini" style="width: auto;">{uploadButtonText}</div> <div class="pl-load-media-lib btn btn-mini" >%s</div>  <div class="btn  btn-mini rmv-upload"><i class="icon icon-remove"></i></div>', $.pl.lang("Library") ) +
					                      '<span class="qq-drop-processing"><span>{dropProcessingText}</span><span class="icon icon-spinner icon-spin spin-fast"></span></span>' +
					                      '<ul class="qq-upload-list" style="margin-top: 10px; text-align: center;"></ul>' +
					                    '</div>'

				}).on('complete', function(event, id, fileName, response) {

					var optBox = $(this).closest('.upload-box')

						if (response.success) {
							var theThumb = optBox.find('.opt-upload-thumb')
							, 	imgStyle = theThumb.data('imgstyle')
							, 	imgURL = pl_do_shortcode(response.url)

							theThumb.fadeIn().html( sprintf('<div class="img-wrap"><img src="%s" style="%s"/></div>', imgURL, imgStyle ))

							optBox.find('.text-input').val(response.url).change()

							optBox.find('.hidden-input').val(response.attach_id).change()

							optBox.find('.lstn').first().trigger('blur')

							optBox.imagesLoaded( function(){
								optBox.closest('.isotope').isotope( 'reLayout' )
								optBox.closest('.opt-box').find('.opt-accordion').accordion('refresh')
							})

						}
				})
		}

	}



}(window.jQuery);
