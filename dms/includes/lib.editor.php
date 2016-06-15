<?php

// get all region slugs in editor
function pl_editor_regions(){

	$regions = array(
		'fixed', 'header', 'footer', 'template'
	);

	return $regions;

}

/*
 *	Get index value in array, does shortcodes or default
 */
function pl_array_get( $key, $array, $default = false ){

	if( isset( $array[$key] ) && $array[$key] != '' )
		$val = $array[$key];
	else
		$val = $default;

	return do_shortcode( $val );
}

/*
 *	Editor functions - Always loaded
 */

function pl_has_editor(){

	return (class_exists('PageLinesTemplateHandler')) ? true : false;

}


// Function to be used w/ compabibility mode to de
function pl_deprecate_v2(){

	if(pl_setting('enable_v2'))
		return false;
	else
		return true;

}


function pl_use_editor(){
	return true;
}

function pl_less_dev(){
	if( defined( 'PL_LESS_DEV' ) && PL_LESS_DEV )
		return false;
	else
		return false;

}

function pl_has_dms_plugin(){

	if( class_exists( 'DMSPluginPro' ) )
		return true;
	else
		return false;
}

function pl_is_pro(){
	return apply_filters( 'pl_is_pro', false );
}

function pl_pro_text(){
	return apply_filters( 'pl_pro_text', '' );
}

function pl_pro_disable_class(){
	return apply_filters( 'pl_pro_disable_class', 'hidden' );
}

function pl_is_activated(){
	return apply_filters( 'pl_is_activated', false );
}

// Process old function type to new format
function process_to_new_option_format( $old_options ){

	$new_options = array();

	foreach($old_options as $key => $o){

		if($o['type'] == 'multi_option' || $o['type'] == 'text_multi'){

			$sub_options = array();
			foreach($o['selectvalues'] as $sub_key => $sub_o){
				$sub_options[ ] = process_old_opt($sub_key, $sub_o, $o);
			}
			$new_options[ ] = array(
				'type' 	=> 'multi',
				'title'	=> $o['title'],
				'opts'	=> $sub_options
			);
		} else {
			$new_options[ ] = process_old_opt($key, $o);
		}

	}

	return $new_options;
}

function process_old_opt( $key, $old, $otop = array()){

	if(isset($otop['type']) && $otop['type'] == 'text_multi')
		$old['type'] = 'text';

	$defaults = array(
        'type' 			=> 'check',
		'title'			=> '',
		'inputlabel'	=> '',
		'exp'			=> '',
		'shortexp'		=> '',
		'count_start'	=> 0,
		'count_number'	=> '',
		'selectvalues'	=> array(),
		'taxonomy_id'	=> '',
		'post_type'		=> '',
		'span'			=> 1,
		'col'			=> 1,
		'default'		=> '',
	);

	$old = wp_parse_args($old, $defaults);

	$exp = ($old['exp'] == '' && $old['shortexp'] != '') ? $old['shortexp'] : $old['exp'];

	if($old['type'] == 'text_small'){
		$type = 'text';
	} elseif($old['type'] == 'colorpicker'){
		$type = 'color';
	} elseif($old['type'] == 'check_multi'){
		$type = 'multi';

		foreach($old['selectvalues'] as $key => &$info){
			$info['type'] = 'check';
		}
	} else
		$type = $old['type'];

	$new = array(
		'key'			=> ( !isset($old['key']) ) ? $key : $old['key'],
		'title'			=> $old['title'],
		'label'			=> ( !isset($old['label']) && isset($old['inputlabel'])) ? $old['inputlabel'] : $old['label'],
		'type'			=> $type,
		'help'			=> $exp,
		'opts'			=> ( !isset($old['opts']) && isset($old['selectvalues'])) ? $old['selectvalues'] : $old['opts'],
		'span'			=> $old['span'],
		'col'			=> $old['col']
	);

	if ( isset( $old['scope'] ) )
		$new['scope'] = $old['scope'];

	if ( isset( $old['template'] ) )
		$new['template'] = $old['template'];

	if($old['type'] == 'count_select'){
		$new['count_start'] = $old['count_start'];
		$new['count_number'] = $old['count_number'];
	}

	if($old['taxonomy_id'] != ''){
		$new['taxonomy_id'] = $old['taxonomy_id'];
	}

	if($old['post_type'] != '')
		$new['post_type'] = $old['post_type'];

	if($old['default'] != '')
		$new['default'] = $old['default'];

	return $new;
}

function pl_create_id( $string ){

	if( ! empty($string) ){
		$string = str_replace( ' ', '_', trim( strtolower( $string ) ) );
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
	} else
		$string = pl_new_clone_id();

	return ( ! is_int($string) ) ? $string : 's'.$string;
}

function pl_new_clone_id(){
	return 'u' . substr(uniqid(), -5);
}


function pl_create_int_from_string( $str ){

	return (int) substr( preg_replace("/[^0-9,.]/", "", md5( $str )), -6);
}


/*
 * Lets document utility functions
 */
function pl_add_query_arg( $args ) {

	global $wp;
	$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	return add_query_arg( $args, $current_url );
}

/*
 * This function recursively converts an multi dimensional array into a multi layer object
 * Needed for json conversion in < php 5.2
 */
function pl_arrays_to_objects( array $array ) {

	$objects = new stdClass;

	if( is_array($array) ){
		foreach ( $array as $key => $val ) {

			if($key === ''){
				$key = 0;
			}

	        if ( is_array( $val ) && !empty( $val )) {


				$objects->{$key} = pl_arrays_to_objects( $val );

	        } else {

	            $objects->{$key} = $val;

	        }
	    }

	}

    return $objects;
}

function pl_animation_array(){
	$animations = array(
		'no-anim'			=> __( 'No Animation', 'pagelines' ),
		'pla-fade'			=> __( 'Fade', 'pagelines' ),
		'pla-scale'			=> __( 'Scale', 'pagelines' ),
		'pla-from-left'		=> __( 'From Left', 'pagelines' ),
		'pla-from-right'	=> __( 'From Right', 'pagelines' ),
		'pla-from-bottom'	=> __( 'From Bottom', 'pagelines' ),
		'pla-from-top'		=> __( 'From Top', 'pagelines' ),
	);

	return $animations;
}

function pl_get_all_taxonomies(){
	$args = array(
	  'public'   => true,

	);
	return get_taxonomies( $args,'names');
}

function pl_icon_array(){

	$icons = array(
		'500px',
		'adjust',
		'adn',
		'align-center',
		'align-justify',
		'align-left',
		'align-right',
		'amazon',
		'ambulance',
		'anchor',
		'android',
		'angellist',
		'angle-double-down',
		'angle-double-left',
		'angle-double-right',
		'angle-double-up',
		'angle-down',
		'angle-left',
		'angle-right',
		'angle-up',
		'apple',
		'archive',
		'area-chart',
		'arrow-circle-down',
		'arrow-circle-left',
		'arrow-circle-o-down',
		'arrow-circle-o-left',
		'arrow-circle-o-right',
		'arrow-circle-o-up',
		'arrow-circle-right',
		'arrow-circle-up',
		'arrow-down',
		'arrow-left',
		'arrow-right',
		'arrow-up',
		'arrows',
		'arrows-alt',
		'arrows-h',
		'arrows-v',
		'asterisk',
		'at',
		'automobile',
		'backward',
		'balance-scale',
		'ban',
		'bank',
		'bar-chart',
		'bar-chart-o',
		'barcode',
		'bars',
		'battery-0',
		'battery-1',
		'battery-2',
		'battery-3',
		'battery-4',
		'battery-empty',
		'battery-full',
		'battery-half',
		'battery-quarter',
		'battery-three-quarters',
		'bed',
		'beer',
		'behance',
		'behance-square',
		'bell',
		'bell-o',
		'bell-slash',
		'bell-slash-o',
		'bicycle',
		'binoculars',
		'birthday-cake',
		'bitbucket',
		'bitbucket-square',
		'bitcoin',
		'black-tie',
		'bold',
		'bolt',
		'bomb',
		'book',
		'bookmark',
		'bookmark-o',
		'briefcase',
		'btc',
		'bug',
		'building',
		'building-o',
		'bullhorn',
		'bullseye',
		'bus',
		'buysellads',
		'cab',
		'calculator',
		'calendar',
		'calendar-check-o',
		'calendar-minus-o',
		'calendar-o',
		'calendar-plus-o',
		'calendar-times-o',
		'camera',
		'camera-retro',
		'car',
		'caret-down',
		'caret-left',
		'caret-right',
		'caret-square-o-down',
		'caret-square-o-left',
		'caret-square-o-right',
		'caret-square-o-up',
		'caret-up',
		'cart-arrow-down',
		'cart-plus',
		'cc',
		'cc-amex',
		'cc-diners-club',
		'cc-discover',
		'cc-jcb',
		'cc-mastercard',
		'cc-paypal',
		'cc-stripe',
		'cc-visa',
		'certificate',
		'chain',
		'chain-broken',
		'check',
		'check-circle',
		'check-circle-o',
		'check-square',
		'check-square-o',
		'chevron-circle-down',
		'chevron-circle-left',
		'chevron-circle-right',
		'chevron-circle-up',
		'chevron-down',
		'chevron-left',
		'chevron-right',
		'chevron-up',
		'child',
		'chrome',
		'circle',
		'circle-o',
		'circle-o-notch',
		'circle-thin',
		'clipboard',
		'clock-o',
		'clone',
		'close',
		'cloud',
		'cloud-download',
		'cloud-upload',
		'cny',
		'code',
		'code-fork',
		'codepen',
		'coffee',
		'cog',
		'cogs',
		'columns',
		'comment',
		'comment-o',
		'commenting',
		'commenting-o',
		'comments',
		'comments-o',
		'compass',
		'compress',
		'connectdevelop',
		'contao',
		'copy',
		'copyright',
		'creative-commons',
		'credit-card',
		'crop',
		'crosshairs',
		'css3',
		'cube',
		'cubes',
		'cut',
		'cutlery',
		'dashboard',
		'dashcube',
		'database',
		'dedent',
		'delicious',
		'desktop',
		'deviantart',
		'diamond',
		'digg',
		'dollar',
		'dot-circle-o',
		'download',
		'dribbble',
		'dropbox',
		'drupal',
		'edit',
		'eject',
		'ellipsis-h',
		'ellipsis-v',
		'empire',
		'envelope',
		'envelope-o',
		'envelope-square',
		'eraser',
		'eur',
		'euro',
		'exchange',
		'exclamation',
		'exclamation-circle',
		'exclamation-triangle',
		'expand',
		'expeditedssl',
		'external-link',
		'external-link-square',
		'eye',
		'eye-slash',
		'eyedropper',
		'facebook',
		'facebook-f',
		'facebook-official',
		'facebook-square',
		'fast-backward',
		'fast-forward',
		'fax',
		'feed',
		'female',
		'fighter-jet',
		'file',
		'file-archive-o',
		'file-audio-o',
		'file-code-o',
		'file-excel-o',
		'file-image-o',
		'file-movie-o',
		'file-o',
		'file-pdf-o',
		'file-photo-o',
		'file-picture-o',
		'file-powerpoint-o',
		'file-sound-o',
		'file-text',
		'file-text-o',
		'file-video-o',
		'file-word-o',
		'file-zip-o',
		'files-o',
		'film',
		'filter',
		'fire',
		'fire-extinguisher',
		'firefox',
		'flag',
		'flag-checkered',
		'flag-o',
		'flash',
		'flask',
		'flickr',
		'floppy-o',
		'folder',
		'folder-o',
		'folder-open',
		'folder-open-o',
		'font',
		'fonticons',
		'forumbee',
		'forward',
		'foursquare',
		'frown-o',
		'futbol-o',
		'gamepad',
		'gavel',
		'gbp',
		'ge',
		'gear',
		'gears',
		'genderless',
		'get-pocket',
		'gg',
		'gg-circle',
		'gift',
		'git',
		'git-square',
		'github',
		'github-alt',
		'github-square',
		'gittip',
		'glass',
		'globe',
		'google',
		'google-plus',
		'google-plus-square',
		'google-wallet',
		'graduation-cap',
		'gratipay',
		'group',
		'h-square',
		'hacker-news',
		'hand-grab-o',
		'hand-lizard-o',
		'hand-o-down',
		'hand-o-left',
		'hand-o-right',
		'hand-o-up',
		'hand-paper-o',
		'hand-peace-o',
		'hand-pointer-o',
		'hand-rock-o',
		'hand-scissors-o',
		'hand-spock-o',
		'hand-stop-o',
		'hdd-o',
		'header',
		'headphones',
		'heart',
		'heart-o',
		'heartbeat',
		'history',
		'home',
		'hospital-o',
		'hotel',
		'hourglass',
		'hourglass-1',
		'hourglass-2',
		'hourglass-3',
		'hourglass-end',
		'hourglass-half',
		'hourglass-o',
		'hourglass-start',
		'houzz',
		'html5',
		'i-cursor',
		'ils',
		'image',
		'inbox',
		'indent',
		'industry',
		'info',
		'info-circle',
		'inr',
		'instagram',
		'institution',
		'internet-explorer',
		'intersex',
		'ioxhost',
		'italic',
		'joomla',
		'jpy',
		'jsfiddle',
		'key',
		'keyboard-o',
		'krw',
		'language',
		'laptop',
		'lastfm',
		'lastfm-square',
		'leaf',
		'leanpub',
		'legal',
		'lemon-o',
		'level-down',
		'level-up',
		'life-bouy',
		'life-buoy',
		'life-ring',
		'life-saver',
		'lightbulb-o',
		'line-chart',
		'link',
		'linkedin',
		'linkedin-square',
		'linux',
		'list',
		'list-alt',
		'list-ol',
		'list-ul',
		'location-arrow',
		'lock',
		'long-arrow-down',
		'long-arrow-left',
		'long-arrow-right',
		'long-arrow-up',
		'magic',
		'magnet',
		'mail-forward',
		'mail-reply',
		'mail-reply-all',
		'male',
		'map',
		'map-marker',
		'map-o',
		'map-pin',
		'map-signs',
		'mars',
		'mars-double',
		'mars-stroke',
		'mars-stroke-h',
		'mars-stroke-v',
		'maxcdn',
		'meanpath',
		'medium',
		'medkit',
		'meh-o',
		'mercury',
		'microphone',
		'microphone-slash',
		'minus',
		'minus-circle',
		'minus-square',
		'minus-square-o',
		'mobile',
		'mobile-phone',
		'money',
		'moon-o',
		'mortar-board',
		'motorcycle',
		'mouse-pointer',
		'music',
		'navicon',
		'neuter',
		'newspaper-o',
		'object-group',
		'object-ungroup',
		'odnoklassniki',
		'odnoklassniki-square',
		'opencart',
		'openid',
		'opera',
		'optin-monster',
		'outdent',
		'pagelines',
		'paint-brush',
		'paper-plane',
		'paper-plane-o',
		'paperclip',
		'paragraph',
		'paste',
		'pause',
		'paw',
		'paypal',
		'pencil',
		'pencil-square',
		'pencil-square-o',
		'phone',
		'phone-square',
		'photo',
		'picture-o',
		'pie-chart',
		'pied-piper',
		'pied-piper-alt',
		'pinterest',
		'pinterest-p',
		'pinterest-square',
		'plane',
		'play',
		'play-circle',
		'play-circle-o',
		'plug',
		'plus',
		'plus-circle',
		'plus-square',
		'plus-square-o',
		'power-off',
		'print',
		'puzzle-piece',
		'qq',
		'qrcode',
		'question',
		'question-circle',
		'quote-left',
		'quote-right',
		'ra',
		'random',
		'rebel',
		'recycle',
		'reddit',
		'reddit-square',
		'refresh',
		'registered',
		'remove',
		'renren',
		'reorder',
		'repeat',
		'reply',
		'reply-all',
		'retweet',
		'rmb',
		'road',
		'rocket',
		'rotate-left',
		'rotate-right',
		'rouble',
		'rss',
		'rss-square',
		'rub',
		'ruble',
		'rupee',
		'safari',
		'save',
		'scissors',
		'search',
		'search-minus',
		'search-plus',
		'sellsy',
		'send',
		'send-o',
		'server',
		'share',
		'share-alt',
		'share-alt-square',
		'share-square',
		'share-square-o',
		'shekel',
		'sheqel',
		'shield',
		'ship',
		'shirtsinbulk',
		'shopping-cart',
		'sign-in',
		'sign-out',
		'signal',
		'simplybuilt',
		'sitemap',
		'skyatlas',
		'skype',
		'slack',
		'sliders',
		'slideshare',
		'smile-o',
		'soccer-ball-o',
		'sort',
		'sort-alpha-asc',
		'sort-alpha-desc',
		'sort-amount-asc',
		'sort-amount-desc',
		'sort-asc',
		'sort-desc',
		'sort-down',
		'sort-numeric-asc',
		'sort-numeric-desc',
		'sort-up',
		'soundcloud',
		'space-shuttle',
		'spinner',
		'spoon',
		'spotify',
		'square',
		'square-o',
		'stack-exchange',
		'stack-overflow',
		'star',
		'star-half',
		'star-half-empty',
		'star-half-full',
		'star-half-o',
		'star-o',
		'steam',
		'steam-square',
		'step-backward',
		'step-forward',
		'stethoscope',
		'sticky-note',
		'sticky-note-o',
		'stop',
		'street-view',
		'strikethrough',
		'stumbleupon',
		'stumbleupon-circle',
		'subscript',
		'subway',
		'suitcase',
		'sun-o',
		'superscript',
		'support',
		'table',
		'tablet',
		'tachometer',
		'tag',
		'tags',
		'tasks',
		'taxi',
		'television',
		'tencent-weibo',
		'terminal',
		'text-height',
		'text-width',
		'th',
		'th-large',
		'th-list',
		'thumb-tack',
		'thumbs-down',
		'thumbs-o-down',
		'thumbs-o-up',
		'thumbs-up',
		'ticket',
		'times',
		'times-circle',
		'times-circle-o',
		'tint',
		'toggle-down',
		'toggle-left',
		'toggle-off',
		'toggle-on',
		'toggle-right',
		'toggle-up',
		'trademark',
		'train',
		'transgender',
		'transgender-alt',
		'trash',
		'trash-o',
		'tree',
		'trello',
		'tripadvisor',
		'trophy',
		'truck',
		'try',
		'tty',
		'tumblr',
		'tumblr-square',
		'turkish-lira',
		'tv',
		'twitch',
		'twitter',
		'twitter-square',
		'umbrella',
		'underline',
		'undo',
		'university',
		'unlink',
		'unlock',
		'unlock-alt',
		'unsorted',
		'upload',
		'usd',
		'user',
		'user-md',
		'user-plus',
		'user-secret',
		'user-times',
		'users',
		'venus',
		'venus-double',
		'venus-mars',
		'viacoin',
		'video-camera',
		'vimeo',
		'vimeo-square',
		'vine',
		'vk',
		'volume-down',
		'volume-off',
		'volume-up',
		'warning',
		'wechat',
		'weibo',
		'weixin',
		'whatsapp',
		'wheelchair',
		'wifi',
		'wikipedia-w',
		'windows',
		'won',
		'wordpress',
		'wrench',
		'xing',
		'xing-square',
		'y-combinator',
		'y-combinator-square',
		'yahoo',
		'yc',
		'yc-square',
		'yelp',
		'yen',
		'youtube',
		'youtube-play',
		'youtube-square',
	);
	asort($icons);

	$icons = array_values($icons);

	return apply_filters( 'pl_icon_array', $icons );
}

function pl_button_classes(){
	$array = array(
		''			 		=> 'Default',
		'btn-link-color'	=> 'Link Color',
		'btn-ol-white'		=> 'Outline White',
		'btn-ol-black'		=> 'Outline Black',
		'btn-ol-link'		=> 'Outline Link',
		'btn-primary'		=> 'Dark Blue',
		'btn-info'			=> 'Light Blue',
		'btn-success'		=> 'Green',
		'btn-warning'		=> 'Orange',
		'btn-important'		=> 'Red',
		'btn-inverse'		=> 'Black',
	);
	return $array;
}

function pl_theme_classes(){
	$array = array(
		''			 	=> 'Default',
		'pl-trans'		=> 'No Background',
		'pl-contrast'	=> 'Contrast BG',
		'pl-black'		=> 'Black Background, white text',
		'pl-grey'		=> 'Dark Grey Background, White Text',
		'pl-white'		=> 'White Background, Black Text',
		'pl-dark-img'	=> 'Black Background, White Text w Shadow',
		'pl-light-img'	=> 'White Background, Black Text w Shadow',
		'pl-base'		=> 'Base Color Background',
	);
	return apply_filters( 'pl_theme_classes', $array );
}

function pl_get_area_classes( $section, $set = array(), $namespace = false ){

	$namespace = ( $namespace ) ? $namespace : $section->id;

	if( 'navbar' == $namespace )
		$namespace = 'navbar_area';

	$class = array(
		'theme'		=> $section->opt($namespace.'_theme'),
		'scroll'	=> $section->opt($namespace.'_scroll'),
		'video'		=> ( $section->opt($namespace.'_video') ) ? 'bg-video-canvas' : '',
		'repeat'	=> ( $section->opt($namespace.'_repeat') ) ? 'pl-bg-repeat' : 'pl-bg-cover'
	);

	$class = wp_parse_args( $set, $class );

	return join(' ', $class);

}

function pl_get_area_styles( $section, $namespace = false ){

	$namespace = ( $namespace ) ? $namespace : $section->id;

	$bg = $section->opt($namespace.'_background');

	$color = $section->opt($namespace.'_color');

	$color_enable = $section->opt($namespace.'_color_enable');

	$style = array(
		'background' => ( $bg ) ? sprintf('background-image: url(%s);', $bg) : '',
		'color'		=> ( $color_enable ) ? sprintf('background-color: %s;', pl_hash($color)) : '',
	);

	return $style;


}

function pl_standard_video_bg( $section, $namespace = false ){

	$namespace = ( $namespace ) ? $namespace : $section->id;
	$video = '';
	if( $section->opt( $namespace.'_video') ){

		$videos = pl_get_video_sources( array( $section->opt( $namespace.'_video'), $section->opt( $namespace.'_video_2') ) );
		$video = sprintf(
			'<div class="bg-video-viewport"><video poster="%s" class="bg-video" autoplay loop>%s</video></div>',
			pl_transparent_image(),
			$videos
		);

		return $video;
	} else
		return '';

}

function pl_get_background_options( $section, $column = 3 ){

	$namespace = $section->id;

	$options = array(
		'title' => __( 'Background Options', 'pagelines' ),
		'type'	=> 'multi',
		'col'	=> $column,
		'opts'	=> array(
			array(
				'key'			=> $namespace.'_background',
				'type' 			=> 'image_upload',
				'label' 		=> __( 'Background Image', 'pagelines' ),
			),
			array(
				'key'			=> $namespace.'_repeat',
				'type' 			=> 'check',
				'label' 		=> __( 'Repeat Background?', 'pagelines' ),
			),
			array(
				'key'			=> $namespace.'_theme',
				'type' 			=> 'select_theme',
				'label' 		=> __( 'Background Theme', 'pagelines' ),
			),
			array(
				'key'			=> $namespace.'_video',
				'type' 			=> 'media_select_video',
				'label' 		=> __( 'Background Video', 'pagelines' ),
			),
			array(
				'key'			=> $namespace.'_color_enable',
				'type' 			=> 'check',
				'label' 		=> __( 'Background Color Enable', 'pagelines' ),
			),
			array(
				'key'			=> $namespace.'_color',
				'type' 			=> 'color',
				'label' 		=> __( 'Background Color', 'pagelines' ),
			),
		)
	);


	return $options;
}


function pl_get_post_type_options( ){


	$opts = array(
			array(
				'key'			=> 'post_type',
				'type' 			=> 'select',
				'opts'			=> pl_get_thumb_post_types( false ),
				'label' 	=> __( 'Select Post Type', 'pagelines' ),
			),
			array(
				'key'			=> 'post_total',
				'type' 			=> 'count_select',
				'count_start'	=> 5,
				'count_number'	=> 20,
				'default'		=> 10,
				'label' 		=> __( 'Total Posts Loaded', 'pagelines' ),
			),
			array(
				'key'		=> 'post_sort',
				'type'		=> 'select',
				'label'		=> __( 'Element Sorting', 'pagelines' ),
				'default'	=> 'DESC',
				'opts'			=> array(
					'DESC'		=> array('name' => __( 'Date Descending (default)', 'pagelines' ) ),
					'ASC'		=> array('name' => __( 'Date Ascending', 'pagelines' ) ),
					'rand'		=> array('name'	=> __( 'Random', 'pagelines' ) )
				)
			),
			array(
				'key'			=> 'meta_key',
				'type' 			=> 'text_small',
				'label' 	=> __( 'Meta Key', 'pagelines' ),
			),
			array(
				'key'			=> 'meta_value',
				'type' 			=> 'text_small',
				'label' 	=> __( 'Meta Key Value', 'pagelines' ),
				'help'		=> __( 'Select only posts which have a certain meta key and corresponding meta value. Useful for featured posts, or similar.', 'pagelines' ),
			),
		);


	return $opts;
}



function get_sidebar_select(){


	global $wp_registered_sidebars;
	$allsidebars = $wp_registered_sidebars;
	ksort($allsidebars);

	$sidebar_select = array();
	foreach($allsidebars as $key => $sb){

		$sidebar_select[ $sb['id'] ] = array( 'name' => $sb['name'] );
	}

	return $sidebar_select;
}

function pl_count_sidebar_widgets( $sidebar_id ){

	$total_widgets = wp_get_sidebars_widgets();

	if(isset($total_widgets[ $sidebar_id ]))
		return count( $total_widgets[ $sidebar_id ] );
	else
		return false;
}

function pl_enqueue_script(  $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ){

	global $wp_scripts;

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

function pl_add_theme_tab( $array ){

	global $pl_user_theme_tabs;

	if(!isset($pl_user_theme_tabs) || !is_array($pl_user_theme_tabs))
		$pl_user_theme_tabs = array();


	$pl_user_theme_tabs = array_merge($array, $pl_user_theme_tabs);



}




function pl_blank_template( $name = '' ){
	if ( current_user_can( 'edit_theme_options' ) )
		return sprintf('<div class="blank-section-template pl-editor-only"><strong>%s</strong> is hidden or returned no output.</div>', $name);
	else
		return '';

}


function pl_shortcodize_url( $full_url ){
	$url = str_replace(home_url(), '[pl_site_url]', $full_url);

	return $url;
}

function pl_get_image_sizes() {
	$sizes = get_intermediate_image_sizes();
	$sizes[] = 'full';
	return $sizes;
}

function pl_check_updater_exists() {
	$path = sprintf( '%s/pagelines-updater/pagelines-updater.php', WP_PLUGIN_DIR );
	return ( is_file( $path ) ) ? true : false;
}
