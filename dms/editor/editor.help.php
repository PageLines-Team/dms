<?php 


class EditorHelpPanel {


	function __construct( ){


		add_filter('pl_toolbar_config', array( $this, 'toolbar'));
	

	}
	
	
	function scripts(){
	
	}

	function toolbar( $toolbar ){
		
		$toolbar['pagelines-help'] = array(
			'name'	=> __( '', 'pagelines' ),
			'icon'	=> 'icon-question-circle',
			'vtype'	=> 'btn',
			'pos'	=> 180,
			'panel'	=> array(
				
				'heading2'	=> __( "Support &amp; Help", 'pagelines' ),
				'resources'	=> array(
						'name'	=> __( 'Resources', 'pagelines' ),
						'call'	=> array( $this, 'help_resources'),
						'icon'	=> 'icon-thumbs-up',
					),
			)

		);

		return $toolbar;
	}
	
	function help_resources(){
		$tour = pl_add_query_arg(array('pl-view-tour' => 1)); 
		?>
		<div class="row">
			<div class="span2">
				<a class="big-icon-button" href="<?php echo $tour;?>">
				<div class="the-icon"><i class="icon icon-magic"></i></div>
				<div class="the-text">View Interactive Tour <i class="icon icon-angle-right"></i>
</div></a>
			</div>
			<div class="span2">
				<a class="big-icon-button" href="http://www.pagelines.com/quick-start/" target="_blank">
				<div class="the-icon"><i class="icon icon-book"></i></div>
				<div class="the-text">Quick Start Guide <i class="icon icon-angle-right"></i>
</div></a>
			</div>
			<div class="span2">
				<a class="big-icon-button" href="http://answers.pagelines.com/" target="_blank">
				<div class="the-icon"><i class="icon icon-support"></i></div>
				<div class="the-text">Customization Answers <i class="icon icon-angle-right"></i>
</div></a>
			</div>
			<div class="span2">
				<a class="big-icon-button" href="http://pagelines.com/docs/DMS" target="_blank">
				<div class="the-icon"><i class="icon icon-files-o"></i></div>
				<div class="the-text">Installation Docs <i class="icon icon-angle-right"></i>
</div></a>
			</div>
			<div class="span2">
				<a class="big-icon-button" href="http://forum.pagelines.com/" target="_blank">
				<div class="the-icon"><i class="icon icon-comments"></i></div>
				<div class="the-text">Technical Support Forums <i class="icon icon-angle-right"></i>
</div></a>
			</div>
		</div>
		<?php 
	}

}
