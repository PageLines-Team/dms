!function ($) {

	// --> Initialize
	$(document).ready(function() {

		$.plWizard.init()

	})

	$.plWizard = {

		init: function( ){
			
			$.toolbox('hide')
			
			// alright, lets run this thing.
				/*
				the json config obj.
				name: the class given to the element where you want the wiztip to appear
				bgcolor: the background color of the wiztip
				color: the color of the wiztip text
				text: the text inside the wiztip
				time: if automatic tour, then this is the time in ms for this step
				position: the position of the tip. Possible values are
					TL	top left
					TR  top right
					BL  bottom left
					BR  bottom right
					LT  left top
					LB  left bottom
					RT  right top
					RB  right bottom
					T   top
					R   right
					B   bottom
					L   left
				 */
				var config = [
					{
						"name" 		: ".btn-pagelines-home",
						"position"	: "BL",
						"text"		: "<strong>Welcome to the Tour!</strong><br/> Hello! Welcome to the PageLines DMS tour. First you may want to read the user guide as it provides a lot of context for these tools.<br/><br/><a class='btn btn-mini btn-primary' href='http://www.pagelines.com/user-guide' target='_blank'>Read User Guide First &raquo;</a><br/><br/> <strong>Already read it?</strong> Cool, use the tour controls on the right side of the page and let's do your walk through!",
						beforeSend	: function(){
							$.toolbox('hide')
							$('body').removeClass('drag-drop-editing width-resize')
							
							
						}
					},
					
					{
						"name" 		: ".btn-pagelines-home",
						"position"	: "BL",
						"text"		: "<strong>DMS Toolbar</strong><br/>This is the DMS toolbar, let's get started with it's basic editing buttons.",
						beforeSend	: function(){
							$.toolbox('hide')
							$('body').removeClass('drag-drop-editing width-resize')
						}
					},
					{
						"name" 		: ".btn-toggle-grid",
						"position"	: "BL",
						'to'		: 600,
						"text"		: "<strong>This is your site preview button.</strong><br/> Click it to show (or hide) PageLines drag and drop tools. This allows you to quickly see your site as it will appear to visitors.<br/><br/><strong>Tip:</strong> You can also use the keyboard shortcut alt+A.",
						beforeSend	: function(){
							$('.btn-toggle-grid').trigger('click')
						}
					},
					{
						"name" 		: ".btn-publish",
						"position"	: "BR",
						"text"		: "<strong>Publish Button</strong><br/><br/> <strong>Draft vs Live:</strong> DMS has two modes: draft and live. All settings are first saved as 'draft' to prevent you showing incomplete changes to visitors. Click the publish button to make 'draft' settings 'live' and show them to visitors. Note: Publish is not required to preview changes.<br/><br/><strong>Tip:</strong> You can also use the keyboard shortcut alt+S.",
						beforeSend	: function(){
						}
					},
					{
						"name" 		: ".btn-state",
						"position"	: "BL",
						"text"		: "<strong>Change Tool</strong><br/> This button keeps track if you make changes that aren't live yet. You can also use it to roll changes back to the last published state.",
						beforeSend	: function(){
						}
					},
					{
						"name" 		: ".el-pl-toggle",
						"position"	: "BL",
						"text"		: "<strong>Close/Deactivation Tool</strong><br/> Use this button to close the toolbox, then deactivate the editor completely.<br/><br/><strong>Tip:</strong> Deactivating the editor is useful for testing performance, since none of the editing scripts are loaded, it's much faster.<br/><br/><strong>Tip:</strong> Use the keyboard shortcut [esc] to close the toolbox when open.",
						beforeSend	: function(){
							$.toolbox('hide')
						}
					},
					{
						"name" 		: ".el-add-new",
						"text"		: "<strong>The Toolbox</strong><br/> Some actions, like clicking this button, open up what we call the 'toolbox'.",
						"position"	: "BL",
						'to'		: 600,
						beforeSend	: function(){
							$('body').removeClass('resize-hover')
							$('.btn-add-new').trigger('click')
						}
					},
					{
						"name" 		: ".resizer-handle",
						"text"		: "<strong>Resize Toolbox</strong><br/> Resize the toolbox by dragging the top edge when it's open.",
						"position"	: "B",
						beforeSend	: function(){
							$('.resizer-handle').addClass('resizing')
							$('.btn-add-new').trigger('click')
						}
					},
					{
						"name" 		: ".tab-add_section",
						"text"		: "<strong>Add New Sections</strong><br/> Use this panel to drag new section to the page.",
						"position"	: "BL",
						'to'		: 600,
						beforeSend	: function(){
							$('.resizer-handle').removeClass('resizing')
							$('.tab-add_section a').trigger('click')
						}
					},
					{
						"name" 		: ".tab-components",
						"text"		: "<strong>Filter Sections</strong><br/> Filter your sections using this nav.",
						"position"	: "BL",
						'to'		: 600,
						beforeSend	: function(){
							$('.btn-add-new').trigger('click')
							$('.tab-components a').trigger('click')
						}
					},
					{
						"name" 		: ".x-item[data-object='PLNavBar']",
						"text"		: "<strong>Add Section By Dragging</strong><br/> To add a section just drag it to the page. Note: Content sections must be dragged to an area that supports them. <br/><br/> <strong>Tip:</strong> In this panel you can click the section icon for more information.",
						"position"	: "BL",
						'to'		: 600,
						beforeSend	: function(){
							$('.btn-add-new').trigger('click')
							$(".x-item[data-object='PLNavBar']").trigger('click')
						}
					},
					{
						"name" 		: ".el-section-controls",
						"text"		: "<strong>Section Controls</strong><br/> When you hover over sections their controls appear. Sections in the content area (like this one) are called <strong>content sections</strong> and they are based on a <strong>12 column grid</strong>.",
						"position"	: "TL", 
						'to'		: 600,
						beforeSend	: function(){
							
							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('body')
								.find('.level1')
								.first()
								.addClass('section-hover')
								.find('.pl-section-controls')
								.first()
								.addClass('el-section-controls')
						}
					},
					
					{
						"name" 		: ".el-section-controls .section-size",
						"text"		: "<strong>Edit Column Width</strong><br/> Adjust the width of the section in columns.",
						"position"	: "TL", 
						beforeSend	: function(){
							
							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('body')
								.find('.level1:visible')
								.first()
								.addClass('section-hover')
								.find('.pl-section-controls')
								.addClass('el-section-controls')
						}
					},
					{
						"name" 		: ".el-section-controls .offset-size",
						"text"		: "<strong>Edit Column Offset</strong><br/> Adjust number of columns you want to offset the section. Note: It must be less than 12 columns wide (full width).",
						"position"	: "TL", 
						beforeSend	: function(){
							
							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('body')
								.find('.level1')
								.first()
								.addClass('section-hover')
								.find('.pl-section-controls')
								.addClass('el-section-controls')
						}
					},
					{
						"name" 		: ".el-section-controls .section-start-row",
						"text"		: "<strong>Force To New Row</strong><br/> Force this section to a new row. This is useful if you're using complicated multi-section layouts.",
						"position"	: "TL", 
						beforeSend	: function(){
							
							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('body')
								.find('.level1')
								.first()
								.addClass('section-hover')
								.find('.pl-section-controls')
								.addClass('el-section-controls')
						}
					},
					{
						"name" 		: ".el-section-controls .section-delete",
						"text"		: "<strong>Delete Section</strong><br/> This permanently deletes the section and its settings.",
						"position"	: "TR", 
						beforeSend	: function(){
							
							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('body')
								.find('.level1')
								.first()
								.addClass('section-hover')
								.find('.pl-section-controls')
								.addClass('el-section-controls')
						}
					},
					{
						"name" 		: ".el-section-controls .section-clone",
						"text"		: "<strong>Clone Section</strong><br/> Duplicate the section and its settings with one click!",
						"position"	: "TR", 
						beforeSend	: function(){
							
							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('body')
								.find('.level1')
								.first()
								.addClass('section-hover')
								.find('.pl-section-controls')
								.addClass('el-section-controls')
						}
					},
					{
						"name" 		: ".el-section-controls .section-edit",
						"text"		: "<strong>Edit Section Settings</strong><br/> Click this and the sections settings are loaded in the toolbox below. Set up the section there.<br/><br/> Note: some setting changes require page refresh to view, others may sync with the page.",
						"position"	: "TR", 
						'to'		: 500,
						beforeSend	: function(){

							$('body').addClass('drag-drop-editing width-resize')

							$('body')
								.find('.level1')
								.first()
								.addClass('section-hover')
								.find('.pl-section-controls')
								.addClass('el-section-controls')

							$('.el-section-controls .section-edit')
								.trigger('click')
						}
					},
					
					{
						"name" 		: ".area-control-hover",
						"text"		: "<strong>Full Width Sections</strong><br/> Sections that are the entire width of the page are called full-width sections. <br/><br/> Their controls are on the top left of each full width area and pop out on hover.<br/><br/>A special case, <strong>canvas sections</strong> are full width sections with sections nested inside.",
						"position"	: "TL", 
						'to'		: 500,
						beforeSend	: function(){

							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('.section-hover').removeClass('section-hover')
							
							$('.template-region-wrap')
								.find('.pl-area')
								.first()
								.find('.pl-area-controls')
									.addClass('area-control-hover')
						}
					},
					{
						"name" 		: ".area-control-hover .area-reorder",
						"text"		: "<strong>Reorder Full Width Section</strong><br/> Notice how you can't set widths for full width sections. You can drag them up and down though by dragging this handy button.",
						"position"	: "TL", 
						beforeSend	: function(){

							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('.template-region-wrap')
								.find('.pl-area')
								.first()
								.find('.pl-area-controls')
									.addClass('area-control-hover')
						}
					},
					{
						"name" 		: ".area-control-hover .area-save",
						"text"		: "<strong>Custom User Sections</strong><br/> You can save full width sections as custom sections using this button. This allows you to store and reuse this section as well as its nested sections and all their settings.",
						"position"	: "TL", 
						beforeSend	: function(){

							$('body').addClass('drag-drop-editing width-resize')
							
							$.toolbox('hide')
							
							$('.template-region-wrap')
								.find('.pl-area')
								.first()
								.find('.pl-area-controls')
									.addClass('area-control-hover')
						}
					},
					{
						"name" 		: ".template-region-wrap .btn-region",
						"text"		: "<strong>Regions: Template</strong><br/> Every page has various section regions that behave differently. This is the template region, which is the only region that changes from page to page by default. <br/><br/><strong>Tip:</strong> When using page templates, this area is locked to that template until you click this button to unlock it.",
						"position"	: "TL", 
						beforeSend	: function(){

							$('body').addClass('drag-drop-editing width-resize')
							$('.area-control-hover').removeClass('area-control-hover')
						}
					},
					{
						"name" 		: ".pl-fixed-top .btn-region",
						"text"		: "<strong>Regions: Fixed, Header, Footer</strong><br/> The header, footer, and fixed regions are on every page by default.",
						"position"	: "TL", 
						beforeSend	: function(){

							$('body').addClass('drag-drop-editing width-resize')
							
						}
					},
					{
						"name" 		: ".ui-resizable-demo",
						"text"		: "<strong>Layout Width</strong><br/> Adjust the layout width using the handles on either side of the content area. This change applies globally by default.<br/><br/><strong>Tip:</strong> You can select different layout modes in your settings.",
						"position"	: "LT", 
						beforeSend	: function(){

							$('body').addClass('drag-drop-editing width-resize resize-hover')
							
							$('.template-region-wrap')
								.find('.ui-resizable-w')
								.first()
								.addClass('ui-resizable-demo')
							
						}
					},
					
					{
						"name" 		: ".btn-page-setup",
						"text"		: "<strong>Page Setup and Templates</strong><br/> Use this panel to save and apply page templates. You can also use it to view page information.",
						'to'		: 600,
						"position"	: "BL",
						beforeSend	: function(){
							$(".btn-page-setup").trigger('click')
						}
					},
					{
						"name" 		: ".btn-settings",
						"text"		: "<strong>Front End Settings</strong><br/> Here is where you set all your global settings and preferences for your front end.",
						"position"	: "BL",
						beforeSend	: function(){
							$(".btn-settings").trigger('click')
						}
					},
					{
						"name" 		: ".tab-typography",
						"text"		: "<strong>Site Typography</strong><br/> Set up your site typography here.",
						"position"	: "BL",
						beforeSend	: function(){
							$(".btn-settings").trigger('click')
							$(".tab-typography a").trigger('click')
						}
					},
					{
						"name" 		: ".tab-color_control",
						"text"		: "<strong>Site Colors</strong><br/> Set up your site colors here.",
						"position"	: "BL",
						beforeSend	: function(){
							$(".btn-settings").trigger('click')
							$(".tab-color_control a").trigger('click')
						}
					},
					{
						"name" 		: ".tab-importexport",
						"text"		: "<strong>Import/Export</strong><br/> Here you can export your site configuration including templates and settings. Then import these same settings on another install.",
						"position"	: "BL",
						beforeSend	: function(){
							$(".btn-settings").trigger('click')
							$(".tab-importexport a").trigger('click')
						}
					},
					{
						"name" 		: ".tab-social_media",
						"text"		: "<strong>More Settings</strong><br/> There are a bunch of other point and click settings to configure and play with as well.",
						"position"	: "BL",
						beforeSend	: function(){
							$(".btn-settings").trigger('click')
							$(".tab-social_media a").trigger('click')
						}
					},
					{
						"name" 		: ".btn-pl-design",
						"text"		: "<strong>Custom LESS/CSS</strong><br/> Standard options not cutting it? Use this panel to customize using CSS/LESS.<br/><br/><strong>Tip:</strong> You can preview your changes live by hitting cmd/ctrl+return.<br/><br/><strong>Note:</strong> If you create an error in customization your site may break. We've provided a fallback in the admin for fixing this.",
						"position"	: "BL",
						beforeSend	: function(){
							$(".btn-pl-design").trigger('click')
						}
					},
					{
						"name" 		: ".mm-holder",
						"text"		: "<strong>Mobile Menu</strong><br/> This is your mobile menu. On mobile devices users will get an alternative nav that triggers this with one click.",
						"position"	: "RT",
						'to'		: 600,
						beforeSend	: function(){
							$.toolbox('hide')
							$(".pl-mobile-menu").addClass('show-menu')
							$(".site-wrap").addClass('show-mobile-menu')
						}
					},
					{
						"name" 		: ".tab-layout",
						"text"		: "<strong>Mobile Menu Setup</strong><br/> Configure the mobile menu in this tab.",
						"position"	: "BL",
						'to'		: 600,
						beforeSend	: function(){
							$(".pl-mobile-menu").removeClass('show-menu')
							$(".site-wrap").removeClass('show-mobile-menu')
							$(".btn-settings").trigger('click')
							$(".tab-layout a").trigger('click')
						}
					},
					{
						"name" 		: ".btn-pagelines-home",
						"position"	: "BL",
						'to'		: 600,
						"text"		: "<strong>Notes On Performance</strong><br/><br/><strong>Why Chrome?</strong><br/> PageLines uses advanced Javascript to allow rich editing. We require Chrome for editing as it provides you the best possible experience and workflow.<br/><br/><strong>Editor vs Live Speed</strong><br/> The editor requires many scripts that are NOT loaded when visitors view your site. For them, your site will always be optimized for speed. To edit faster, you may want to consider high quality hosting.",
						beforeSend	: function(){
							$.toolbox('hide')
						}
					},
					{
						"name" 		: ".btn-pagelines-help",
						"position"	: "BL",
						'to'		: 600,
						"text"		: "<strong>More Questions?</strong><br/>Please join the community, we can't wait to help you out!",
						beforeSend	: function(){
							$(".btn-pagelines-help").trigger('click')
						
						}
					},
					{
						"name" 		: ".btn-pagelines-home",
						"position"	: "BL",
						'to'		: 600,
						"text"		: "<strong>That's It!</strong><br/> Congrats! You finished the tour. Hopefully now you know everything you need to get started. <br/><br/>We missing something? Please <a class='link' href='mailto:hello@pagelines.com'>email us</a> and let us know.",
						beforeSend	: function(){
							$.toolbox('hide')
						
						}
					},
					

				],
				//define if steps should change automatically
				autoplay	= false,
				//timeout for the step
				showtime,
				//current step of the tour
				step		= 0,
				//total number of steps
				total_steps	= config.length;
					
				//show the tour controls
				showControls();
				
				/*
				we can restart or stop the tour,
				and also navigate through the steps
				 */
				$('#activatetour').live('click',startTour);
				$('#canceltour').live('click',endTour);
				$('#endtour').live('click',endTour);
				$('#restarttour').live('click',restartTour);
				$('#nextstep').live('click',nextStep);
				$('#prevstep').live('click',prevStep);
				
				function startTour(){
					$('#activatetour').parent().remove();
					$('#endtour,#restarttour').show();
					if(!autoplay && total_steps > 1)
						$('.tour-nav').show();
					//showOverlay();
					nextStep();
				}
				
				function nextStep(){
					if(!autoplay){
						if(step > 0)
							$('#prevstep').removeClass('disabled').show()
						else
							$('#prevstep').addClass('disabled')
						if(step == total_steps-1)
							$('#nextstep').addClass('disabled')
						else
							$('#nextstep').removeClass('disabled').show()
					}	
					if(step >= total_steps){
						//if last step then end tour
						endTour();
						return false;
					}
					++step;
					showWiztip();
				}
				
				function prevStep(){
					if(!autoplay){
						if(step > 2)
							$('#prevstep').removeClass('disabled').show()
						else
							$('#prevstep').addClass('disabled');
						if(step == total_steps)
							$('#nextstep').removeClass('disabled').show()
					}		
					if(step <= 1)
						return false;
					--step;
					showWiztip();
				}
				
				function endTour(){
					step = 0;
					if(autoplay) clearTimeout(showtime);
					removeWiztip();
					hideControls();
					hideOverlay();
					
					$(window).off('scroll.wizTipScroll')
				}
				
				function restartTour(){
					step = 0;
					if(autoplay) clearTimeout(showtime);
					nextStep();
				}
				
				function elementOrParentIsFixed(element) {
				    var $element = $(element);
				    var $checkElements = $element.add($element.parents());
				    var isFixed = false;
				    $checkElements.each(function(){
				        if ($(this).css("position") === "fixed") {
				            isFixed = true;
				            return false;
				        }
				    });
				    return isFixed;
				}
				
				function showWiztip(){
					//remove current wiztip
					//removeWiztip();
					
					var step_config		= config[step-1];
					var timeDelay		= ( plIsset(step_config.to) ) ? step_config.to : 0
					var totalItems 		= config.length
					$('.tourcontrol-title').html('Viewing '+step+' of '+totalItems)
					
					if ( $.isFunction( step_config.beforeSend ) )
						step_config.beforeSend.call( this )
						
					var $elem			= $( step_config.name );
					
					var moveOnScroll	= ( elementOrParentIsFixed( $elem ) ) ? true : false
						
					// timeout needed for some callback actions
					setTimeout(function(){
				
						
						
					
						if(autoplay)
							showtime	= setTimeout(nextStep,step_config.time);
					
					
						
					
						if( step == 1 ){
							removeWiztip();
							var bgcolor 		= step_config.bgcolor;
							var color	 		= step_config.color;

							var $wiztip		= $('<div>',{
								id			: 'tour_wiztip',
								class 		: 'wiztip',
							}).css({
								'display'			: 'none'
							});

							//position the wiztip correctly:

							//append the wiztip but hide it
							$('BODY').prepend($wiztip);	
						
						} else {
							$wiztip = $('.wiztip')
						}
						
					
					
						$wiztip
							.html('<p>'+step_config.text+'</p><span class="wiztip_arrow"></span>')

						setTheTipPosition( step_config, $elem, $wiztip )
						
						$(window).resize(function(){
							setTheTipPosition( step_config, $elem, $wiztip )
						})
						
						if( moveOnScroll ){
							$(window).off('scroll.wizTipScroll')
							$(window).on('scroll.wizTipScroll', function(){
								setTheTipPosition( step_config, $elem, $wiztip )
							})
						} else 
							$(window).off('scroll.wizTipScroll')
						
					}, timeDelay) // end timeout
					
					
					
					
					
				}
				
				function setTheTipPosition( step_config, $elem, $wiztip ){
					//the css properties the wiztip should have
					var properties		= {};
					
					var tip_position 	= step_config.position;
					
					
					//get some info of the element
					var e_w				= $elem.outerWidth();
					var e_h				= $elem.outerHeight();
					var e_l				= $elem.offset().left;
					var e_t				= $elem.offset().top;
					var e_p				= ( elementOrParentIsFixed( $elem ) ) ? true : false
					
					switch(tip_position){
						case 'TL'	:
							properties = {
								'left'	: e_l,
								'top'	: e_t + e_h + 12 + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_TL');
							break;
						case 'TR'	:
							properties = {
								'left'	: e_l + e_w - $wiztip.width() + 'px',
								'top'	: e_t + e_h + 12 + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_TR');
							break;
						case 'BL'	:
							properties = {
								'left'	: e_l + 'px',
								'top'	: e_t - $wiztip.height() - 12 + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_BL');
							break;
						case 'BR'	:
							properties = {
								'left'	: e_l + e_w - $wiztip.width() + 'px',
								'top'	: e_t - $wiztip.height() - 12 + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_BR');
							break;
						case 'LT'	:
							properties = {
								'left'	: e_l + e_w + 'px',
								'top'	: e_t + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_LT');
							break;
						case 'LB'	:
							properties = {
								'left'	: e_l + e_w + 'px',
								'top'	: e_t + e_h - $wiztip.height() + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_LB');
							break;
						case 'RT'	:
							properties = {
								'left'	: e_l - $wiztip.width() + 'px',
								'top'	: e_t + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_RT');
							break;
						case 'RB'	:
							properties = {
								'left'	: e_l - $wiztip.width() + 'px',
								'top'	: e_t + e_h - $wiztip.height() + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_RB');
							break;
						case 'T'	:
							properties = {
								'left'	: e_l + e_w/2 - $wiztip.width()/2 + 'px',
								'top'	: e_t + e_h + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_T');
							break;
						case 'R'	:
							properties = {
								'left'	: e_l - $wiztip.width() + 'px',
								'top'	: e_t + e_h/2 - $wiztip.height()/2 + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_R');
							break;
						case 'B'	:
							properties = {
								'left'	: e_l + e_w/2 - $wiztip.width()/2 + 'px',
								'top'	: e_t - $wiztip.height() - 12 + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_B');
							break;
						case 'L'	:
							properties = {
								'left'	: e_l + e_w  + 'px',
								'top'	: e_t + e_h/2 - $wiztip.height()/2 + 'px'
							};
							$wiztip.find('span.wiztip_arrow').removeClass().addClass('wiztip_arrow wiztip_arrow_L');
							break;
					}
					
					
					
					// if the element is fixed
					// if( e_p ){
					// 						var sTop = $(window).scrollTop()
					// 						,	newTop = e_t - sTop
					// 						console.log('new top'+e_t)
					// 						$.extend( properties, {'position': 'fixed', 'top': e_t} )
					// 					}
						
					
					
					/*
					if the element is not in the viewport
					we scroll to it before displaying the wiztip
					 */
					var w_t	= $(window).scrollTop();
					var w_b = $(window).scrollTop() + $(window).height();
					//get the boundaries of the element + wiztip
					var b_t = parseFloat(properties.top,10);
					
					if(e_t < b_t)
						b_t = e_t;
					
					var b_b = parseFloat(properties.top,10) + $wiztip.height();
					if((e_t + e_h) > b_b)
						b_b = e_t + e_h;
						
					
					if( ! e_p && ( (b_t < w_t || b_t > w_b) || (b_b < w_t || b_b > w_b) ) ){
						$('html, body').stop()
						.animate({scrollTop: b_t - 100}, 500, 'easeInOutExpo', function(){
							//need to reset the timeout because of the animation delay
							if(autoplay){
								clearTimeout(showtime);
								showtime = setTimeout(nextStep,step_config.time);
							}
							//show the new wiztip
							$wiztip.css(properties).show();
						});
					}
					else
					//show the new wiztip
						$wiztip.css(properties).show();
				}
				
				
				
				function removeWiztip(){
					$('#tour_wiztip').remove();
				}
				
				function showControls(){
					/*
					we can restart or stop the tour,
					and also navigate through the steps
					 */
					var $tourcontrols  = '<div id="tourcontrols" class="tourcontrols">';
					$tourcontrols += '<p class="tourcontrol-title">Getting Started with PageLines?</p>';
					$tourcontrols += '<p><span class="btn btn-primary btn-large" id="activatetour"><i class="icon icon-magic"></i> Start the tour</span></p>';
						if(!autoplay){
							$tourcontrols += '<div class="tour-nav" style="display: none;"><span class="btn btn-primary disabled" id="prevstep"><i class="icon icon-caret-left"></i> Previous</span>';
							$tourcontrols += '&nbsp;&nbsp;<span class="btn btn-primary disabled" id="nextstep" >Next <i class="icon icon-caret-right"></i></span></div>';
						}
						$tourcontrols += '<a id="restarttour" style="display:none;">Restart the tour</span>';
						$tourcontrols += '<a id="endtour" style="display:none;">End the tour</a>';
						$tourcontrols += '<span class="close-tour" id="canceltour"><i class="icon icon-remove"></i></span>';
					$tourcontrols += '</div>';
					
					$('BODY').prepend($tourcontrols);
					$('#tourcontrols').animate({'right':'30px'},500);
				}
				
				function hideControls(){
					$('#tourcontrols').remove();
				}
				
				function showOverlay(){
					var $overlay	= '<div id="tour_overlay" class="overlay"></div>';
					$('BODY').prepend($overlay);
				}
				
				function hideOverlay(){
					$('#tour_overlay').remove();
				}
			
		}

	}

}(window.jQuery);