<?php
/*
	Section: Column
	Class Name: PLColumn
	Filter: layout
	Loading: active
*/

class PLColumn extends PageLinesSection {


	function section_opts(){

		$options = array();

		
		$options[] = array(

			'key'			=> 'boxed_columns',
			'type' 			=> 'check',
			'col'			=> 2,
			'label' 	=> __( 'Make Boxed Column?', 'pagelines' ),
			'help' 		=> __( 'Adds a border around column for better visual organization.', 'pagelines' ),
			

		);
		
	
		
		return $options;
	}


	function section_template() {

		$boxed_class = ( $this->opt('boxed_columns') ) ? 'column-boxed pl-border' : '';
		?>
		<div class="pl-sortable-column pl-sortable-area editor-row <?php echo $boxed_class;?>">

			<?php

			echo render_nested_sections( $this->meta['content'], 2 );

			?>
			<span class="pl-column-forcer">&nbsp;</span>
		</div>
	<?php

	}

}