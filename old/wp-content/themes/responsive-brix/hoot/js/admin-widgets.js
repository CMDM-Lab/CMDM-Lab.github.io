(function( $ ) {
	"use strict";

	/*** Icon Picker ***/

	$.fn.hootWidgetIconPicker = function() {
		return this.each(function() {

			var $self       = $(this),
				$picker_box = $self.siblings('.hoot-of-icon-picker-box'),
				$button     = $self.siblings('.hoot-of-icon-picked'),
				$preview    = $button.children('i'),
				$icons      = $picker_box.find('i');

			$button.on( "click", function() {
				$picker_box.toggle();
			});

			$icons.on( "click", function() {
				var iconvalue = $(this).data('value');
				$icons.removeClass('selected');
				var selected = ( ! $(this).hasClass('cmb-icon-none') ) ? 'selected' : '';
				$(this).addClass(selected);
				$preview.removeClass().addClass('fa ' + selected + ' ' + iconvalue );
				$self.val(iconvalue);
				$picker_box.toggle();
			});

		});
	};
	$(document).on('click', function(event) {
		// If this is not inside .hoot-of-icon-picker-box or .hoot-of-icon-picked
		if (!$(event.target).closest('.hoot-of-icon-picker-box').length
			&&
			!$(event.target).closest('.hoot-of-icon-picked').length ) {
			$('.hoot-of-icon-picker-box').hide();
		}
	});

	/*** Setup Widget ***/

	$.fn.hootSetupWidget = function() {

		var setupAdd = function( $container, widgetClass, dynamic ){
			// Add Group Item
			$container.find('.hoot-widget-field-group-add').each( function() {
				var $addGroup   = $(this),
					$itemList   = $addGroup.siblings('.hoot-widget-field-group-items'),
					groupID     = $addGroup.parent('.hoot-widget-field-group').data('id'),
					newItemHtml = window.hoot_widget_helper[widgetClass][groupID];

				$addGroup.on( "click", function() {
					var iterator = parseInt( $(this).data('iterator') );
					iterator++;
					$(this).data('iterator', iterator);
					var newItem = newItemHtml.trim().replace(/975318642/g, iterator);

					var $newItem = $(newItem);
					setupToggle( $newItem );
					setupRemove( $newItem );
					$newItem.find('.hoot-of-icon').hootWidgetIconPicker();
					//init( $newItem, widgetClass, true ); //@todo
					$itemList.append($newItem);
				});
			});
		};

		var setupToggle = function( $container ) {
			// Make groups collapsible
			$container.find('.hoot-widget-field-group-item-top').on( "click", function() {
				$(this).siblings('.hoot-widget-field-group-item-form').toggle();
			});
		};

		var setupRemove = function( $container ) {
			// Make group items removable
			$container.find('.hoot-widget-field-remove').on( "click", function() {
				$(this).closest('.hoot-widget-field-group-item').remove();
			});
		};

		return this.each( function(i, el) {
			var $self       = $(el),
				widgetClass = $self.data('class');

			// Skip this if we've already set up the form, or if template widget
			if ( $('body').hasClass('widgets-php') ) {
				if ( $self.data('hoot-form-setup') === true ) return true;
				//if ( $self.closest('.widget').attr('id').indexOf("__i__") > -1 ) return true;
				//if ( $self.closest('#widgets-left').length > 0 ) return true;
				if ( !$self.is(':visible') ) return true;
			}

			$self.find('.hoot-of-icon').hootWidgetIconPicker();

			setupAdd( $self, widgetClass, false );
			setupToggle( $self );
			setupRemove( $self );

			// All done.
			$self.trigger('hootwidgetformsetup').data('hoot-form-setup', true);
		});

	};

	/*** Initialize Stuff ***/

	// Initialize existing hoot forms
	$('.hoot-widget-form').hootSetupWidget();

	// When we click on a widget top or drag an instance to a widget area
	$('.widgets-holder-wrap').on('click', '.widget:has(.hoot-widget-form) .widget-top', function(){
		var $$ = $(this).closest('.widget').find('.hoot-widget-form');
		setTimeout( function(){ $$.hootSetupWidget(); }, 200);
	});

}(jQuery));