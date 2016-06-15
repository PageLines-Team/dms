<?php
/*
	Section: PostNav
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: Post Navigation - Shows titles for next and previous post.
	Class Name: PageLinesPostNav
	Workswith: main-single
	Cloning: true
	Failswith: pagelines_special_pages()
	Filter: component
	Isolate: single
	Loading: active
*/

class PageLinesPostNav extends PageLinesSection {

	function section_persistent(){
		add_filter('previous_post_link', array($this, 'add_btn_class' ) );
		add_filter('next_post_link', array($this, 'add_btn_class') );
	
	}

	function add_btn_class( $out ) {
		$out = str_replace( '<a', '<a class="btn btn-mini"', $out );
	    return $out;
	}
	
   function section_template() {

		pagelines_register_hook( 'pagelines_section_before_postnav' ); // Hook ?>
		<div class="post-nav fix">
			<span class="previous"><?php previous_post_link('%link', '<i class="icon icon-angle-left"></i> %title') ?></span>
			<span class="next"><?php next_post_link('%link', '%title <i class="icon icon-angle-right"></i>') ?></span>
		</div>
<?php 	pagelines_register_hook( 'pagelines_section_after_postnav' ); // Hook

	}
}