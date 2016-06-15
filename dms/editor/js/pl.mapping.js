!function ($) {

	$.plMapping = {

       	getCurrentMap: function() {

			var that = this
			,	map = {}
			, 	templateClass = 'custom-template'

			$('.pl-region').each( function(regionIndex, o) {

				var region = $(this).data('region')
				,	regionSet = that.getRegionMapping( $(this) )
				, 	regionConfig = {}

				if( $(this).hasClass( templateClass ) && plIsset( $(this).data( templateClass ) ) ) {
					regionConfig.ctemplate = $(this).data( templateClass )
				} 
			
				regionConfig.map = regionSet.map
				
				map[region] = regionConfig
			})

			
			return map

		}
		
		, getRegionMapping: function( region ){
			var that = this
			,	areasList = []
			,	settingConfig = []
			,	scope = ( region.data('region') == 'template') ? 'local' : 'global'
			,	templateClass = 'custom-section'
			
			region.find('.pl-area').each( function(areaIndex, o2) {
				
				var areaSet = that.getAreaMapping( $(this) )
			
				if( $(this).hasClass( templateClass )  && plIsset( $(this).data( templateClass ) ) ){
					
					areaSet.map.ctemplate = $(this).data( templateClass )
					
				} 
					
				areasList.push( areaSet.map )
				
				settingConfig.push( areaSet.settings )
				

			})
			
				
			var	set = {
					map: areasList
					, settings: settingConfig
				}
				
			return set
		}
		
		, getAreaMapping: function( area ){
			
			var that = this
			,	areaContent	= []
			,	settings = {}
			,	scope = ( area.parents(".template-region-wrap").length == 1 ) ? 'local' : 'global'
			,	UID = area.data('clone')

			area.find('.pl-section.level1').each( function(sectionIndex, o3) {

				var section = $(this)
				,	sectionsTemplate = section.data('template') || ''

				if( sectionsTemplate != "" ){

					$.merge( areaContent, sectionsTemplate )

				} else {
					sectionSet = that.sectionConfig( section, scope )
					
					settings = $.extend( {}, settings, sectionSet.settings )
					
					areaContent.push( sectionSet.map )

				}

			})

			if( plIsset( $.pl.data[ scope ][ UID ] ) )
				settings[ UID ] = $.pl.data[ scope ][ UID ]

			var UID = area.data('clone')
			,	map = {
						name: area.data('name') || ''
					,	class: area.data('class') || ''
					,	id: area.attr('id') || ''
					, 	object: area.data('object') || ''
					, 	sid: area.data('sid') || ''
					,  	clone: UID || 0
					,	content: areaContent
				}
			,	set = {
					map: map
					, settings: settings
				}
			
			return set
			
		}

		, sectionConfig: function( section, scope ){

			var that = this
			,	map = {}
			,	settings = {}
			, 	UID = section.data('clone')

			map.object 	= section.data('object')
			map.clone 	= UID
			map.sid 	= section.data('sid')

			map.span 	= that.getColumnSize( section )[ 4 ]
			map.offset 	= $.plMapping.getOffsetSize( section )[ 3 ]
			map.newrow 	= (section.hasClass('force-start-row')) ? 'true' : 'false'
			map.content = []


			// Recursion
			section.find( '.pl-section.level2' ).each( function() {

				var recur = that.sectionConfig( $(this), scope )
				
				settings = $.extend( {}, settings, recur.settings )
				
				map.content.push( recur.map )

			})

			if( plIsset( $.pl.data[ scope ][ UID ] ) )
				settings[ UID ] = $.pl.data[ scope ][ UID ]
			
			var set = {
				map: map
				, settings: settings
			}

			return set

		}

		, getOffsetSize: function( column, defaultValue ) {

			var that = this
			,	max = 12
			,	sizes = that.getColumnSize( column )
			,	avail = max - sizes[4]
			,	data = []

			for( i = 0; i <= 12; i++){

					next = ( i == avail ) ? 0 : i+1

					prev = ( i == 0 ) ? avail : i-1

					if(column.hasClass("offset"+i))
						data = new Array("offset"+i, "offset"+next, "offset"+prev, i)

			}

			if(data.length === 0 || defaultValue)
				return new Array("offset0", "offset0", "offset0", 0)
			else
				return data

		}


		, getColumnSize: function(column, defaultValue) {

			if (column.hasClass("span12") || defaultValue) //full-width
				return new Array("span12", "span1", "span11", "12/12", 12)

		    else if (column.hasClass("span11")) //five-sixth
		        return new Array("span11", "span12", "span10", "11/12", 11)

			else if (column.hasClass("span10")) //five-sixth
		        return new Array("span10", "span11", "span9", "10/12", 10)

			else if (column.hasClass("span9")) //three-fourth
				return new Array("span9", "span10", "span8", "9/12", 9)

			else if (column.hasClass("span8")) //two-third
				return new Array("span8", "span9", "span7", "8/12", 8)

			else if (column.hasClass("span7"))
				return new Array("span7", "span8", "span6", "7/12", 7)

			else if (column.hasClass("span6")) //one-half
				return new Array("span6", "span7", "span5", "6/12", 6)

			else if (column.hasClass("span5"))
				return new Array("span5", "span6", "span4", "5/12", 5)

			else if (column.hasClass("span4")) // one-third
				return new Array("span4", "span5", "span3", "4/12", 4)

			else if (column.hasClass("span3")) // one-fourth
				return new Array("span3", "span4", "span2", "3/12", 3)

		    else if (column.hasClass("span2")) // one-sixth
		        return new Array("span2", "span3", "span1", "2/12", 2)

			else if (column.hasClass("span1")) // one-twelth?
			        return new Array("span1", "span2", "span12", "1/12", 1)
			else
				return false

		}

	}

}(window.jQuery);