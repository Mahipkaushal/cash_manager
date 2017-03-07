var populateSearchList = function() {
	$searchContainer = $('.search-container .body .search_results_body .search_results');
	$searchContainer.html('');

	$.each($searchArray, function($key, $value) {
        $description_required = 0;
        if(typeof $value.description_required !== 'undefined') {
            $description_required = $value.description_required;
        }
        $row_id = $value.id.replace(/:/g , "-");
        $text = $value.text;
        $row = '';
        $row += '<label class="form-labels result elements_3d row-' + $row_id + '" for="radio-' + $value.id + '" data-text="' + $value.text + '" data-description-required="' + $description_required + '">';
        $row += '	<input type="radio" class="radio-hidden" name="result" id="radio-' + $value.id + '" value="' + $value.id + '" />';
        $row += '	<label class="radio elements_block" for="radio-' + $value.id + '">';
        $row += 		$value.text;
        $row += '	</label>';
		$row += '</label>';

		$searchContainer.append($row);
    });
}

var populateSubSearchList = function() {
    $subSearchContainer = $('.sub-search-container .sub_search_results_body .sub_search_results');
    $subSearchContainer.html('');

    $.each($subSearchArray, function($key, $value) {
        $row_id = $value.id;
        $text = $value.text;
        $row = '';
        $row += '<label class="form-labels sub-result elements_3d sub-row-' + $row_id + '" for="sub-radio-' + $value.id + '" data-text="' + $value.text + '" >';
        $row += '   <input type="radio" class="radio-hidden" name="sub_result" id="sub-radio-' + $value.id + '" value="' + $value.id + '" />';
        $row += '   <label class="radio elements_block" for="sub-radio-' + $value.id + '">';
        $row +=         $value.text;
        $row += '   </label>';
        $row += '</label>';

        $subSearchContainer.append($row);
    });
}

$(document).on('keyup', '.search-container .body .search_term input[name="search"]', function() {
	populateSearchList();
    $val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

    $searchContainer = $(this).closest('.body').find('.search_results_body .search_results');
    
    $.each($searchArray, function($key, $value) {
        $text = $value.text.replace(/\s+/g, ' ').toLowerCase();

        $row_id = $value.id.replace(/:/g , "-");
        
        if($text.indexOf($val) > -1) {
        	$searchContainer.find('.row-' + $row_id).show();
        } else {
        	$searchContainer.find('.row-' + $row_id).hide();
        }

    });
});

$(document).on('keyup', '.search-container .sub-search-container .sub_search_term input[name="sub_search"]', function(e) {
    if((e.which <= 90 && e.which >= 48) || e.which == 8) {
        $text = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
        if($text.length > 0) {
            $subSearchForm = $(this).parent();
            $data = $subSearchForm.children('input');
            subSearch($data);
        } else {
            $subSearchArray = [];
            populateSubSearchList();
        }
    }
});

$(document).on('click', '.search-container .sub-search-container .sub_search_term .sub_search_btn', function(e) {
    $text = $.trim($(this).parent().children('input[name="sub_search"]').val()).replace(/ +/g, ' ').toLowerCase();
    if($text.length > 0) {
        $subSearchForm = $(this).parent();
        $data = $subSearchForm.children('input');
        subSearch($data);
    } else {
        $subSearchArray = [];
        populateSubSearchList();
    }
});