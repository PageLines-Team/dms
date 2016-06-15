<?php


class PageLinesRegions {

	function __construct(){



		$this->url = PL_PARENT_URL . '/editor';
	}


	function region_start( $region ){

		printf(
			'<div class="pl-region-bar area-tag"><a class="btn-region tt-top">%s</a></div>',
			$region
		);

	}
	



}