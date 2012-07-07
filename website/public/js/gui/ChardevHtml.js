var ChardevHtml = {
	getInfo: function( html ) {
		var info = Dom.create('a', {'class': 'info', 'href': 'javascript:'});
		
		Listener.add( info, 'mouseover', Tooltip.showMovable, Tooltip, [html]);
		Listener.add( info, 'mouseout', Tooltip.hide, Tooltip, []);
		Listener.add( info, 'mousemove', Tooltip.move, Tooltip, []);
		
		return info;
	},
	createWithInfo: function( tag, obj, infoHtml) {
		return Dom.append(Dom.create(tag, obj), ChardevHtml.getInfo( infoHtml ));
	},
	addTooltip: function( node, html ) {
		if( typeof node === 'string' ) {
			node = document.getElementById(node);
		}
		Listener.add( node, 'mousemove', Tooltip.move, Tooltip, [] );
		Listener.add( node, 'mouseout', Tooltip.hide, Tooltip, [] );
		Listener.add( node, 'mouseover', Tooltip.showMovable, Tooltip, [html] );
	},
	shadow: function( node, text ) {
		var s = Dom.createAt( node, 'span', {});
		s.innerHTML = "<span style='position: absolute'>" + text + "<span style='color: #808080; position: absolute; top: -1px; left: 1px;'>"+text+"</span></span>";
	},
	buttonLightStyleFor: function( node ) {
		Tools.jsCssClassHandler( node, { 
			'default': "button button_light li_filter_search_btn", 
			'focus': "button_light_focus", 
			'hover': "button_light_hover"
		});
	},
	addInfo: function( node, html ) {
		if( typeof node === 'string' ) {
			node = document.getElementById(node);
		}
		Dom.append( node, ChardevHtml.getInfo(html));
	}
};

if( ! window['ChardevHtml'] ) {
	window['ChardevHtml'] = {
			'getInfo': ChardevHtml.getInfo,
			'createWithInfo': ChardevHtml.createWithInfo,
			'addTooltip': ChardevHtml.addTooltip,
			'shadow': ChardevHtml.shadow,
			'buttonLightStyleFor': ChardevHtml.buttonLightStyleFor,
			'addInfo': ChardevHtml.addInfo
	};
}