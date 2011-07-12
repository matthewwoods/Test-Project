window.Wygwam;


(function($) {


Wygwam = function(id, config, defer) {

	config = (Wygwam.configs[config] || Wygwam.configs['default']);

	if (! defer) {
		CKEDITOR.replace(id, config);
	}
	else {
		var width = (config.width ? config.width.toString() : '100%'),
			height = (config.height ? config.height.toString() : '200');

		if (width.match(/\d$/)) width += 'px';
		if (height.match(/\d$/)) height += 'px';

		var css = (config.contentsCss ? config.contentsCss : Wygwam.themeUrl+'lib/ckeditor/contents.css'),
			html = '<link rel="stylesheet" type="text/css" href="'+css+'" />'
			     + '<style type="text/css">* { cursor: pointer !important; }</style>';

		var $textarea = $('#'+id).hide();

		html += $textarea.val();

		Wygwam.defers[id] = {
			config: config,
			html: html
		};

		var src = Wygwam.themeUrl+'scripts/defer.html?'+id,
			$iframe = $('<iframe class="wygwam" style="width:'+width+'; height:'+height+';" frameborder="0" src="'+src+'" />').insertAfter($textarea);

		Wygwam.defers[id].$iframe = $iframe;
	}
};


Wygwam.configs = {};
Wygwam.defers = {};


Wygwam.initDeferedField = function(id) {
	var data = Wygwam.defers[id];
	data.$iframe.remove();
	CKEDITOR.replace(id, data.config);
};


})(jQuery);
