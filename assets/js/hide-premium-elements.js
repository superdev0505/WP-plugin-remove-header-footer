
function vgplFindFooter(contentColumns, $header) {
	var $footer = vgplFindHeader('.copyright, #footer, .site-info, .site-footer, .footer-credits, #colophon');

	var contentColumnEnd = contentColumns.$inmediateParent.offset().top + contentColumns.$inmediateParent.height();

	var $possibleSecondaryFooterElements = jQuery('.widget, .sidebar, .footer, aside');

	if ($header && $header.length) {
		var $possibleSecondaryFooterElementsB = $header.siblings();
		jQuery.each($possibleSecondaryFooterElementsB, function (index, element) {
			$possibleSecondaryFooterElements = $possibleSecondaryFooterElements.add(element);
		});
	}

	var $secondaryFooterElements = $possibleSecondaryFooterElements.filter(function () {
		var $element = jQuery(this);

		return $element.offset().top > contentColumnEnd;
	});

	if (!$footer || !$footer.length) {
		$footer = jQuery();
	}

	jQuery.each($secondaryFooterElements, function (index, element) {
		$footer = $footer.add(element);
	});

	return $footer;
}
function vgplFindSecondaryHeader($header, contentColumns) {
	if (!contentColumns.$parent || !contentColumns.$parent.length) {
		return false;
	}
	var $headerSiblings = $header.siblings();
	var parentTopPosition = contentColumns.$parent.offset().top;

	var secondaryHeader = [];

	$headerSiblings.each(function () {
		var siblingBottomPosition = jQuery(this).offset().top + jQuery(this).height();

		if (siblingBottomPosition < parentTopPosition) {
			secondaryHeader.push(jQuery(this));
		}
	});

	var $secondaryHeader = jQuery();
	jQuery.each(secondaryHeader, function (index, element) {
		$secondaryHeader = $secondaryHeader.add(element);
	});

	return $secondaryHeader;

}
function vgplFindHeader(selector, contentColumns) {
	if (!selector) {
		var selector = '.logo, #logo, .menu-item, .site-title, .site-branding, .custom-header, .site-header, #masthead, .header';
	}
	if (typeof selector === 'string') {
		var $headerElements = jQuery('body').find(selector);
	} else {
		var $headerElements = selector;
	}
	var $header, $headerParent;

	if ($headerElements.length) {
		var $firstHeaderElement = $headerElements.first();
		var firstHeaderElementHeight = $firstHeaderElement.height();
		var $headerParents = $firstHeaderElement.parents();

		$headerParents.each(function (index, element) {
			var $headerElementParent = jQuery(this);
			var headerElementParentHeight = $headerElementParent.height();
			var headerElementParentSelector = $headerElementParent.getSelector(true);
			var bodySelector = jQuery('body').getSelector(true);

			if (headerElementParentSelector == bodySelector && index == 0) {
				$header = $firstHeaderElement;
				$headerParent = jQuery('body');
				return false;
			} else {

				if (firstHeaderElementHeight <= headerElementParentHeight && headerElementParentHeight < (jQuery('body').height() / 2)) {
					$header = jQuery(this);
				} else {
					$headerParent = jQuery(this);

					if (!$header) {
						$header = $firstHeaderElement;
					}
					return false;
				}
			}

		});

	}
	return $header;
}


jQuery('body').on('vgplInit', function () {

	var $marker = jQuery('.vg-page-layout-placeholder');

	if (!$marker.length) {
		return;
	}
	var contentColumns = window.vgplContentColumns;

	var hideHeader = $marker.data('hide-header');
	var hideFooter = $marker.data('hide-footer');

	var headerSelector = $marker.data('header-selector');
	var footerSelector = $marker.data('footer-selector');

	var removeSpace = $marker.data('remove-space');


	// Hide header
	$header = false;
	if (hideHeader) {
		$header = false;
		if (headerSelector) {
			$header = jQuery(headerSelector);
		}
		if (!$header || !$header.length) {
			$header = vgplFindHeader(null, contentColumns);
		}
		if ($header && $header.length) {
			$header.hide();

			$secondaryHeader = vgplFindSecondaryHeader($header, contentColumns);
			if ($secondaryHeader && $secondaryHeader.length) {
				$secondaryHeader.hide();
			}
		}
	}
	// Hide footer
	if (hideFooter) {
		$footer = false;
		if (footerSelector) {
			$footer = jQuery(footerSelector);
		}
		if (!$footer || !$footer.length) {
			$footer = vgplFindFooter(contentColumns, $header);
		}
		if ($footer && $footer.length) {
			$footer.hide();
		}
	}
	// Remove space
	if (removeSpace) {
		$content = false;
		$content = jQuery(removeSpace);
		if ($content && $content.length) {
			$content.css("padding", "0");
			$content.css("margin", "0");
		}
	}
});