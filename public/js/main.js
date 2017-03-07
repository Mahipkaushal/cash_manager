
$(document).ready(function() {
    $overlay = $(document).find('.overlay');
    $pageOverlay = $(document).find('.pageOverlay');
    showOverlayLoader();
    checkLocalStorage();
    $user = getUser();
    if($user === false) {
    	getLoginScreen();
    } else {
        $username = $user.username;
        $token = $user.token;
        setUpAjax();
        validateToken();
    }
    $(document).on('click', '.menu_btn', function() {
        showMenu();
    });
    $(document).on('click', '.side-menu-container .close', function() {
        hideMenu();
    });
    $(document).on('focus', '.form .to_field', function() {
        $elem = $(this).parent().parent().children('.search-container');
        showSearchPage($elem);
    });
    $(document).on('click', '.form .search-container .head .back_btn', function() {
        $elem = $(this).parent().parent().parent();
        hideSearchPage($elem);
    });
    $(document).on('click', '#cash_manager .head .reset_btn', function() {
        getCashManager();
    });
    $(document).on('click', '#cash_transactions .head .filter_btn', function() {
        $elem = $('#cash_transactions').find('.filter-container');        
        showFilterPage($elem);
    });
    $(document).on('click', '#cash_transaction_approvals .head .filter_btn', function() {
        $elem = $('#cash_transaction_approvals').find('.filter-container');        
        showFilterPage($elem);
    });
    $(document).on('click', '#cash_transactions .head .back_btn', function() {
        $elem = $('#cash_transactions').find('.filter-container');
        hideFilterPage($elem);
    });
    $(document).on('click', '#cash_transaction_approvals .head .back_btn', function() {
        $elem = $('#cash_transaction_approvals').find('.filter-container');
        hideFilterPage($elem);
    });
    $(document).on('click', '.form .search-container .head .done_btn', function() {
        removeSubSearchResultText();

        $searchContainer = $(this).parent().parent().parent();
        $searchResults = $searchContainer.find('.body .search_results_body .search_results .result');

        $selected = false;
        $sub_selected = false;
        $id = '';
        $text = '';
        $description_required = 0;
        $searchResults.each(function(){
            $selected = $(this).children('input[name="result"]').prop('checked');
            if($selected === true) {
                $id = $(this).children('input[name="result"]').val();
                $text = $(this).attr('data-text');
                if(typeof $(this).attr('data-description-required') !== 'undefined' && $(this).attr('data-description-required') == 1) {
                    $subSearchContainer = $searchContainer.find('.sub-search-container');
                    $description_required = 1;
                    $sub_selected = getSubSearchResult($subSearchContainer);
                } else {
                    $sub_selected = true;
                }
                return false;
            }
        });
        if($selected === false) {
            alert('Please select person!');
        } else if($sub_selected === false) {
            alert('Please select or enter description!');
        } else {
            $searchContainer.parent().find('input[name="to_id"]').val($id);
            $searchContainer.parent().find('input[name="to"]').val($text);
            $searchContainer.parent().find('input[name="to_description_required"]').val($description_required);
            hideSearchPage($searchContainer);
        }
    });
    $(document).on('click', '.search-container .body .search_results_body .search_results input[name="result"]', function(e) {
        if($(this).prop('checked') === true) {
            $text = $(this).parent().children('label.radio').text();
            $id = $(this).val();
            $searchBody = $(this).closest('.body');
            $searchInput = $searchBody.find('input[name="search"]');

            $searchInput.val($text);
            $searchInput.attr('disabled', true);
            $searchBody.find('.deselect').show();

            if(typeof $(this).parent().attr('data-description-required') !== 'undefined' && $(this).parent().attr('data-description-required') == 1) {
                $searchBody.find('.search_results_body').slideUp('fast');

                $searchBody.find('.sub_search_results').html('');
                $searchBody.find('.sub-search-container').show();

                $searchBody.find('input[name="sub_search"]').focus();

                $searchBody.find('input[name="result_search_id"]').val($id);
            } else {
                $searchBody.find('input[name="result_search_id"]').val('');
            }
        }
    });
    $(document).on('click', '.search-container .body .search_term .deselect', function() {
        $searchBody = $(this).closest('.body');
        $searchInput = $searchBody.find('input[name="search"]');

        $searchBody.find('.sub-search-container').hide();
        $searchBody.find('.search_results_body').slideDown('fast');

        populateSearchList();
        $searchInput.val('');
        $searchInput.attr('disabled', false);
        $searchInput.focus();
        $(this).hide();
    });

    $(document).on('click', '.sub-search-container .sub_search_results_body .sub_search_results input[name="sub_result"]', function(e) {
        if($(this).prop('checked') === true) {

            $text = $(this).parent().attr('data-text');
            $id = $(this).val();
            $subSearchBody = $(this).closest('.sub-search-container');
            $subSearchInput = $subSearchBody.find('input[name="sub_search"]');

            $subSearchInput.val($text);
        }
    });

    $(document).on('click', '#cash_manager .submit_btn', function() {
        showPageLoader();
        $form = $('#cash_manager .body');
        if(validateForm($form) === true) {            
            $formData = $('#cash_manager .body input, #cash_manager .body select, #cash_manager .body textarea');
            submitCashForm($formData);
        }
    });

    $(document).on('click', '#cash_transactions .more_btn', function() {
        $next_start = $(this).attr('data-next-start');
        $url = $baseUrl + '/expense/cashTransactions?next=' + $next_start;        
        getPocketHistory($url);
    });

    $(document).on('click', '#cash_transactions .filter-container .filter_apply_btn', function() {
        $('#cash_transactions .body #histories').html('');        
        $elem = $('#cash_transactions').find('.filter-container');
        hideFilterPage($elem);

        $url = $baseUrl + '/expense/cashTransactions';        
        getPocketHistory($url);
    });

    $(document).on('click', '#cash_transaction_approvals .more_btn', function() {
        $next_start = $(this).attr('data-next-start');
        $url = $baseUrl + '/expense/cashTransactionApprovals?next=' + $next_start;        
        getApprovalHistory($url);
    });

    $(document).on('click', '#cash_transaction_approvals .filter-container .filter_apply_btn', function() {
        $('#cash_transaction_approvals .body #approvals').html('');        
        $elem = $('#cash_transaction_approvals').find('.filter-container');
        hideFilterPage($elem);

        $url = $baseUrl + '/expense/cashTransactionApprovals';        
        getApprovalHistory($url);
    });

});