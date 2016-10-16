$(window).ready(function(){
	var format					= $('#format');
	if (format.length) {
		var buttons				= $('#editor input[type=button].field'),
		controls				= $('#editor input[type=button].control'),
		preview					= $('#preview ul'),
		formatted				= $('#preview address'),
		options					= $('#preview input[type=checkbox]'),
		form					= $('#editor'),
		poboxLabel				= $('#pobox-label input'),
		
		/**
		 * Onblur-Handler
		 */
		blur					= function(e) {
			this.caret			= $(this).caret();
		},
		/**
		 * Onfocus-Handler
		 */
		focus					= function(e) {
			this.caret			= null;
		},
		/**
		 * Format an example address / name
		 */
		example							= function(e) {
			if (e || (format[0]._value != format[0].value)) {
				format[0]._value		= format[0].value;
				var pobox				= poboxLabel.length ? poboxLabel[0].value : '';
				for (var o = 0, options	= form[0].elements['preview[]'], ol = options.length, activeOptions = []; o < ol; ++o) {
					if (options[o].checked) {
						activeOptions.push(options[o].value);
					}
				}
				var params		= {
					'options'	: activeOptions,
					'format'	: format[0].value,
					'pobox'		: pobox
				}
				formatted.load(preview.attr('data-url'), params);
			}
		}
		/**
		 * Update all buttons after a textarea change
		 */
		change					= function(e) {
			var oneofs					= {};
			for (var b = 0, bl = buttons.length, v = this.value; b < bl; ++b) {
				var bt					= buttons[b].name,
				regexp					= new RegExp('<<' + bt + '>>'),
				match					= !!v.match(regexp);
				buttons[b].disabled		= match,
				oneof					= $(buttons[b]).attr('data-one-of');
				if (oneof) {
					oneofs[oneof]		= oneofs[oneof] || match;
				}
			}
			for (var oneof in oneofs) {
				$('input[data-one-of=' + oneof + ']').each(function(){
					$(this)[oneofs[oneof] ? 'addClass' : 'removeClass']('contained');
				});
			}
			if (!!e) {
				example();
			}
		}
		/**
		 * Insert a button value
		 */	
		insert							= function(e) {
			var value					= format[0].value,
			insert						= '<<'+this.name+'>>';
			if (format[0].caret) {
				format[0].value			= value.substring(0, format[0].caret.start) + insert + value.substr(format[0].caret.end);
				format.caret(format[0].caret.start + insert.length, format[0].caret.start + insert.length);
			} else {
				format[0].value			= value + insert;
				format.caret(format[0].value.length, format[0].value.length);
			}
			format[0].caret				= null;
			change.apply(format[0], [e]);
		},
		/**
		 * Insert a control
		 */	
		control							= function(e) {
			var value					= format[0].value,
			type						= $(this).attr('data-control-type'),
			insert						= (type == 'wrap') ? this.name.split('?').join(format[0].caret ? format[0].caret.text : '') : this.name,
			start						= (type == 'wrap') ? this.name.indexOf('?') : insert.length,
			length						= ((type == 'wrap') && format[0].caret) ? format[0].caret.text.length : 0;
			if (format[0].caret) {
				format[0].value			= value.substring(0, format[0].caret.start) + insert + value.substr(format[0].caret.end);
				format.caret(format[0].caret.start + start, format[0].caret.start + start + length);
			} else {
				format[0].value			= value + insert;
				format.caret(format[0].value.length, format[0].value.length);
			}
			format[0].caret				= null;
			example();
		}
		
		format[0].focus();
		format.caret(format[0].value.length, format[0].value.length);
		format.blur(blur).focus(focus).change(change).mouseout(change);
		format[0]._value				= format[0].value;
		
		// Arm all buttons & controls
		buttons.click(insert);
		controls.click(control);
		
		change.call(format[0]);
		
		$('#favourites li').click(function(e) {
			format[0].value				= $('div.favourite-format', this).text();
			change.apply(format[0], [e]);
		});
		
		poboxLabel.change(example);
		options.change(example);
		$('#align').change(function(){ this.form.submit(); })
	}
	
	// Arm all region tables
	$('form.region-category').each(function(){
		this.checkboxes			= $('input[type=checkbox]', this),
		selectAll				= $('input[type=button][name=select-all]', this),
		unselectAll				= $('input[type=button][name=unselect-all]', this);
		this.checkboxes.each(function(){ $(this).closest('tr').click($.proxy(function(){ this.checked = !this.checked; }, this )); });
		selectAll.click(function(){ this.form.checkboxes.each(function(){ this.checked = true; })});
		unselectAll.click(function(){ this.form.checkboxes.each(function(){ this.checked = false; })});
	});
	
	// Arm all language and romanization dropdowns
	$('select.romanization, select.language, select.script').change(function(e) {
		this.form.elements['priority'].value = $(this).attr('data-priority');
		this.form.submit();
	});
})