function plItemScope( item ){

	return ( item.parents(".template-region-wrap").length == 1 ) ? 'local' : 'global'
}


function plCallWhenSet( flag, callback, flip ){
	
	var flip = flip || false
	,	flagVal = (flip) ? !jQuery.pl.flags[flag] : jQuery.pl.flags[flag]

	
	plPrint(flag)
	plPrint(flagVal)
	
	if( ! flagVal ){
		setTimeout(function() {
		    plCallWhenSet( flag, callback, flip )
		}, 150)
		
	} else {
		plPrint('call function')
		callback.call( this )
	}
}

function plUniqueID( length ) {
	var length = length || 6
	
  // Math.random should be unique because of its seeding algorithm.
  // Convert it to base 36 (numbers + letters), and grab the first 9 characters
  // after the decimal.
  return 'u'+Math.random().toString(36).substr(2, length);
}

/* Data cleanup and handling
 * ============================================= */
function pl_html_input( text ) {
	
	if( typeof text != 'string')
		return text
	else 	
		return jQuery.trim( pl_htmlEntities( pl_stripSlashes( pl_urldecode( text ) ) ) )
}	

function getURLParameter(name) {
    return decodeURI(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
    );
}

function pl_stripSlashes (str) {

  return (str + '').replace(/\\(.?)/g, function (s, n1) {
    switch (n1) {
    case '\\':
      return '\\';
    case '0':
      return '\u0000';
    case '':
      return '';
    default:
      return n1;
    }
  });
}

function pl_htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function isset () {

  var a = arguments,
    l = a.length,
    i = 0,
    undef;

  if (l === 0) {
    throw new Error('Empty isset');
  }

  while (i !== l) {
    if (a[i] === undef || a[i] === null) {
      return false;
    }
    i++;
  }
  return true;
}

function basename (path, suffix) {

  var b = path.replace(/^.*[\/\\]/g, '');

  if (typeof(suffix) == 'string' && b.substr(b.length - suffix.length) == suffix) {
    b = b.substr(0, b.length - suffix.length);
  }

  return b;
}

/* Simple Shortcode System
 * =============================================
 */
function pl_do_shortcode(opt) {
	
	/* If we are not a string, bail out! */
	if( 'string' !== typeof opt || ! opt.length )
		return opt

	var match = opt.match( /\[([^\]]*)/ ) || false
	var shortcode = (match) ? match[1] : false
	
	if(!shortcode)
		return opt
		
	switch(shortcode) {
		case 'pl_child_url':
			opt = opt.replace(/\[pl_child_url\]/g, jQuery.pl.config.urls.ChildStyleSheetURL) // should link to child theme root
		case 'pl_parent_url':
			opt = opt.replace(/\[pl_parent_url\]/g, jQuery.pl.config.urls.CoreURL) // should always be dms root
		case 'pl_site_url':
			opt = opt.replace(/\[pl_site_url\]/g, jQuery.pl.config.urls.siteURL) // site root url
		case 'pl_theme_url':
			opt = opt.replace(/\[pl_theme_url\]/g, jQuery.pl.config.urls.ParentStyleSheetURL) // parent theme
	}
	return opt
}


/* Page refresh function with optional timeout.
 * =============================================
 */
function pl_url_refresh(url,timeout){
	if( !url )
		var url = window.location.href;
	if(!timeout)
		var timeout = 0
	setTimeout(function() {
	  window.location.href = url;
	}, timeout);
}

jQuery.fn.getInputType = function(){ return this[0].tagName == "INPUT" ? jQuery(this[0]).attr("type").toLowerCase() : this[0].tagName.toLowerCase(); }

function localStorageSpace() {
	var allStrings = '';
	for(var key in window.localStorage){
		if(window.localStorage.hasOwnProperty(key)){
			allStrings += window.localStorage[key]
		}
	}
	return allStrings ? 3 + ((allStrings.length*16)/(8*1024)).toFixed(2) + ' KB' : 'Empty (0 KB)';
}

function pl_urldecode(str) {
	return unescape(str)
}

function strpos(haystack, needle, offset) {
  //  discuss at: http://phpjs.org/functions/strpos/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Daniel Esteban
  //   example 1: strpos('Kevin van Zonneveld', 'e', 5);
  //   returns 1: 14

  var i = (haystack + '')
    .indexOf(needle, (offset || 0));
  return i === -1 ? false : i;
}

function GetQueryStringParams(sParam) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        }
    }
}