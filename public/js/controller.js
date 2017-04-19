var $overlay = '';
var $overlayLoader = '<div class="overlay_loader"><img src="images/hourglass.png" alt="Loading..." /></div>';
var $pageLoader = '<div class="loading">Loading&#8230;</div>';
var $username = '';
var $token = '';
var $appPageContainer = null;
var $searchArray = [];
var $subSearchArray = [];
var $errors = [];

var showOverlayLoader = function() {
    $overlay.html($overlayLoader);
    $overlay.fadeIn('fast');
}

var hideOverlayLoader = function() {
	setTimeout(function() {
	    $overlay.fadeOut('slow', function() {
	        $overlay.html('');
	    });
	}, 500);
}

var showPageLoader = function() {
    $pageOverlay.html($pageLoader);
    $pageOverlay.show();
}

var hidePageLoader = function() {
    $pageOverlay.fadeOut('slow', function() {
        $pageOverlay.html('');
    });
}

var showAppPage = function() {
	$appPageContainer.show();
}

var hideAppPage = function() {
	$appPageContainer.hide();
	$appPageContainer.html('');
}

var showSearchPage = function($elem) {	
	$intx = 1;
	$inty = 1;
	if($elem.hasClass('linear-transition')) {
		$intx = 100;
		$inty = 350;
	}
	$elem.show();
    setTimeout(function() {
        $elem.addClass('active');
        setTimeout(function() {
        	$elem.children('.head').addClass('active');
        	$elem.children('.body').find('input[name="search"]').focus();
        },$inty);
    }, $intx);

    populateSearchList();
}

var hideSearchPage = function($elem) {
	$elem.children('.head').removeClass('active');
	$elem.removeClass('active');
	$elem.hide();
	$elem.find('.body .search_term input[name="search"]').val('');
	$elem.find('.body .search_term input[name="search"]').attr('disabled', false);
	$elem.find('.body .search_term .deselect').hide();
	$elem.find('.search_results_body').show();
	$elem.find('.search_results_body .search_results').html('');
	$elem.find('.sub-search-container').hide();
	$elem.find('.sub-search-container .sub_search_term input[name="sub_search"]').val('');
	$elem.find('.sub-search-container .sub_search_results_body .sub_search_results').html('');
}

var showFilterPage = function($elem) {	
	$intx = 1;
	$inty = 1;
	if($elem.hasClass('linear-transition')) {
		$intx = 100;
		$inty = 350;
	}
	$elem.show();
    setTimeout(function() {
        $elem.addClass('active');
        setTimeout(function() {
        	$elem.children('.head').addClass('active');
        },$inty);
    }, $intx);
}

var hideFilterPage = function($elem) {
	$elem.children('.head').removeClass('active');
	$elem.removeClass('active');
	$elem.hide();
}

var setUpAjax = function() {
	$.ajaxSetup({
        cors: true,
        headers: {
            "X-Auth-Token": $token
        }
	});
}

var displayMessage = function($msg, $type) {
	if($type == 'error') {
		alert('Error: ' + $msg['code'] + '- ' + $msg['message']);
	} else if($type == 'success') {
		alert('Success! ' + $msg);
	} else {
		alert($msg);
	}
}

var setUsername = function() {
	$('header .header .user').html('Hi <span class="username">' + $username + '</span>!');
}

var removeUsername = function() {
	$('header .header .user').html('');
}

var showMenu = function() {
	$(document).find('.side-menu-container').addClass('active');
}

var hideMenu = function() {
	$(document).find('.side-menu-container').removeClass('active');
}

var addSubSearchResultText = function($id, $text, $container) {
	$html = '';
	$html += '<div class="to_description_block">';
		$html += '<label class="form-labels" for="to_description">To Description:</label>';
		$html += '<input type="hidden" class="required" data-field-type="text" data-field-name="To Description" name="to_description_id" value="' + $id + '" />';
		$html += '<input type="text" name="to_description" id="to_description" class="form-controls to_description_field" disabled="disabled" readonly="true" onfocus="$(this).blur();" value="' + $text + '" />';
	$html += '</div>';
	$container.append($html);
}

var removeSubSearchResultText = function() {
	$(document).find('.to_description_block').remove();
}

var getSubSearchResult = function($subSearchContainer) {
	$optionFlag = false;
	$textFlag = false;
	$subSearchResults = $subSearchContainer.find('.sub_search_results_body .sub_search_results .sub-result');
	$subSearchResults.each(function() {
		$optionFlag = $(this).children('input[name="sub_result"]').prop('checked');
        if($optionFlag === true) {
            $sub_id = $(this).children('input[name="sub_result"]').val();
            $sub_text = $(this).attr('data-text');            
            return false;
        }
	});

	if($optionFlag === false) {
		$subSearchText = $.trim($subSearchContainer.find('input[name="sub_search"]').val());
		if($subSearchText.replace(/ +/g, ' ').length > 0) {
			$textFlag = true;
			$sub_id = 0;
			$sub_text = $subSearchText;
		}
	}

	if($optionFlag === true || $textFlag === true) {
		$container = $subSearchContainer.closest('.have_search').children('.form-input-container');
	    addSubSearchResultText($sub_id, $sub_text, $container);

	    return true;
	} else {
		return false;
	}
}

var isNumberKey = function(event) {
	var keyCode = window.event ? event.keyCode : event.which;
    if (keyCode < 48 || keyCode > 57) {
        if (keyCode != 0 && keyCode != 8 && keyCode != 13 && keyCode != 46 && !event.ctrlKey) {
            event.preventDefault();
        }
    }
}

var validateForm = function($container) {
	$errors = [];
	$container.find('.required').each(function() {
		$type = $(this).attr('data-field-type');
		$field = $(this).attr('data-field-name');
		$val = $(this).val();
		switch($type) {
			case 'select':
				if($val == '*' || $val == '') {
					$errors.push('Select ' + $field);
				}
				break;
			case 'number':
				if(isNaN($val) || $val <= 0 || $val.replace(/\s/g, '').length <= 0) {
					$errors.push('Enter valid ' + $field);
				}
				break;
			case 'text':
				if($val.replace(/\s/g, '').length <= 0) {
					$errors.push('Enter valid ' + $field);
				}
				break;
		}
	});

	if($errors.length > 0) {
		$error = "Please correct the following error(s) and try again:\n\n"
		$.each($errors, function($i,$val) {
			$error += (parseInt($i) + 1) + ": " + $val + "\n"; 
		});

		hidePageLoader();
		displayMessage($error, 'warning');
		return false;
	}
	return true;
}

var updatePocketValue = function($pocket) {
	$(document).find('.pocket-value').removeClassRegex(/^color_/)
	$(document).find('.pocket-value span').html($pocket.value);
	$(document).find('.pocket-value').addClass($pocket.indicator);
}

$.fn.removeClassRegex = function(regex) {
	return $(this).removeClass(function(index, classes) {
		return classes.split(/\s+/).filter(function(c) {
			return regex.test(c);
		}).join(' ');
	});
};