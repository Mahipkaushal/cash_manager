var $login_xhr = null;
var $home_xhr = null;
var $cashManger_xhr = null;
var $cashTransactions_xhr = null;
var $validateToken_xhr = null;
var $logout_xhr = null;
var $subSearch_xhr = null;
var $insertTransaction_xhr = null;
var $getPocketHistory_xhr = null;
var $cashTransactionApprovals_xhr = null;
var $getApprovalHistory_xhr = null;


var getLoginScreen = function() {
    $login_xhr = $.ajax({
        url: $baseUrl + '/auth/login',
        type: 'GET',
        dataType: 'json',
        complete: function() {
            hideOverlayLoader();
        },
        success: function(json) {
            if(json['html']) {
                $(document).find('div[name="container"]').html(json['html']);                
            }
        },
        error: function ($login_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $login_xhr.statusText + "\r\n" + $login_xhr.responseText);
            console.log(thrownError + "\r\n" + $login_xhr.statusText + "\r\n" + $login_xhr.responseText);
        }
    });
}

var getHomeScreen = function() {
    $home_xhr = $.ajax({
        url: $baseUrl + '/expense/home',
        type: 'GET',
        dataType: 'json',
        complete: function() {
            hideOverlayLoader();
        },
        success: function(json) {
            if(json['html']) {
                $(document).find('div[name="container"]').html(json['html']);
                $appPageContainer = $(document).find('#app #content .app-pages');
                setUsername();
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        },
        error: function ($home_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $home_xhr.statusText + "\r\n" + $home_xhr.responseText);
            console.log(thrownError + "\r\n" + $home_xhr.statusText + "\r\n" + $home_xhr.responseText);
        }
    });
}

var getCashManager = function() {
    $cashManger_xhr = $.ajax({
        url: $baseUrl + '/expense/cashManager',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            showPageLoader();
        },
        complete: function() {
            hidePageLoader();
        },
        success: function(json) {
            if(json['html']) {                
                $appPageContainer.html(json['html']);
                showAppPage();
            }
            if(json['search_array']) {
                $searchArray = json['search_array'];
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        },
        error: function ($cashManger_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $cashManger_xhr.statusText + "\r\n" + $cashManger_xhr.responseText);
            console.log(thrownError + "\r\n" + $cashManger_xhr.statusText + "\r\n" + $cashManger_xhr.responseText);
        }
    });
}

var getCashTransactions = function() {
    $cashTransactions_xhr = $.ajax({
        url: $baseUrl + '/expense/cashTransactions',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            showPageLoader();
        },
        complete: function() {
            hidePageLoader();
        },
        success: function(json) {
            if(json['html']) {                
                $appPageContainer.html(json['html']);
                showAppPage();
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        },
        error: function ($cashTransactions_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $cashTransactions_xhr.statusText + "\r\n" + $cashTransactions_xhr.responseText);
            console.log(thrownError + "\r\n" + $cashTransactions_xhr.statusText + "\r\n" + $cashTransactions_xhr.responseText);
        }
    });
}

var validateToken = function() {
    $validateToken_xhr = $.ajax({
        url: $baseUrl + '/auth/validateToken',
        type: 'GET',
        dataType: 'json',
        success: function(json) {
            if(json['success']) {
                setUpAjax();
                getHomeScreen();               
            } else {
                $username = '';
                $token = '';
                clearLocalStorage();
                setUpAjax();
                removeUsername();
                getLoginScreen();
            }
        },
        error: function ($validateToken_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $validateToken_xhr.statusText + "\r\n" + $validateToken_xhr.responseText);
            console.log(thrownError + "\r\n" + $validateToken_xhr.statusText + "\r\n" + $validateToken_xhr.responseText);
        }
    });
}

var logout= function() {
    $logout_xhr = $.ajax({
        url: $baseUrl + '/expense/logout',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            showPageLoader();
        },
        complete: function() {
            hidePageLoader();
            $username = '';
            $token = '';
            clearLocalStorage();
            setUpAjax();
            removeUsername();
            getLoginScreen();
        },
        success: function(json) {
            if(json['success']) {
                //displayMessage(json['success'], 'success');
            }
        },
        error: function ($logout_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $logout_xhr.statusText + "\r\n" + $logout_xhr.responseText);
            console.log(thrownError + "\r\n" + $logout_xhr.statusText + "\r\n" + $logout_xhr.responseText);
        }
    });
}

var subSearch = function($data) {
    
    if($subSearch_xhr != null) {
        $subSearch_xhr.abort();
    }

    $subSearch_xhr = $.ajax({
        url: $baseUrl + '/common/subSearch',
        type: 'GET',
        data: $data,
        dataType: 'json',
        beforeSend: function() {
            $('.sub-search-container .sub_search_results_body .sub_search_results').html('<div class="bar bar_color_grey bar_animate"></div>');
        },
        complete: function() {

        },
        success: function(json) {
            if(json['results']) {                
                $subSearchArray = json['results'];
                populateSubSearchList();
            }
            if(json['no_result']) {
                $subSearchArray = [];
                populateSubSearchList();
            }
        }
    });

}

var submitCashForm = function($data) {
    $insertTransaction_xhr = $.ajax({
        url: $baseUrl + '/expense/submitTransaction',
        type: 'POST',
        data: $data,
        dataType: 'json',
        beforeSend: function() {

        },
        complete: function() {
            hidePageLoader();
        },
        success: function(json) {
            if(json['success']) {                
                alert(json['success']);
                getCashManager();
            }
            if(json['pocket']) {
                updatePocketValue(json['pocket']);
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        },
        error: function ($insertTransaction_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $insertTransaction_xhr.statusText + "\r\n" + $insertTransaction_xhr.responseText);
            console.log(thrownError + "\r\n" + $insertTransaction_xhr.statusText + "\r\n" + $insertTransaction_xhr.responseText);
        }
    });
}

var getPocketHistory = function($url) {
    if($getPocketHistory_xhr != null) {
        $getPocketHistory_xhr.abort();
    }
    $data = $('#cash_transactions_filter input[type="text"], #cash_transactions_filter input[type="hidden"], #cash_transactions_filter input[type="checkbox"]:checked, #cash_transactions_filter input[type="radio"]:checked, #cash_transactions_filter select, #cash_transactions_filter textarea');
    $getPocketHistory_xhr = $.ajax({
        url: $url,
        type: 'POST',
        data: $data,
        dataType: 'json',
        beforeSend: function() {
            $('#cash_transactions #histories .button.more_btn').remove();
            $('#cash_transactions #histories .more_btn_container').append('<div class="wait center_screen center"><div class="bar bar_color_blue bar_animate"></div></div>');
        },
        complete: function() {
            
        },
        success: function(json) {
            if(json['html']) {
                $('#cash_transactions #histories .more_btn_container').remove();          
                $('#cash_transactions #histories').append(json['html']);
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        }
    });
}

var getCashTransactionApprovals = function() {
    $cashTransactionApprovals_xhr = $.ajax({
        url: $baseUrl + '/expense/cashTransactionApprovals',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            showPageLoader();
        },
        complete: function() {
            hidePageLoader();
        },
        success: function(json) {
            if(json['html']) {                
                $appPageContainer.html(json['html']);
                showAppPage();
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        },
        error: function ($cashTransactionApprovals_xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + $cashTransactionApprovals_xhr.statusText + "\r\n" + $cashTransactionApprovals_xhr.responseText);
            console.log(thrownError + "\r\n" + $cashTransactionApprovals_xhr.statusText + "\r\n" + $cashTransactionApprovals_xhr.responseText);
        }
    });
}

var getApprovalHistory = function($url) {
    if($getApprovalHistory_xhr != null) {
        $getApprovalHistory_xhr.abort();
    }
    $data = $('#cash_transaction_approvals_filter input[type="text"], #cash_transaction_approvals_filter input[type="hidden"], #cash_transaction_approvals_filter input[type="checkbox"]:checked, #cash_transaction_approvals_filter input[type="radio"]:checked, #cash_transaction_approvals_filter select, #cash_transaction_approvals_filter textarea');
    $getApprovalHistory_xhr = $.ajax({
        url: $url,
        type: 'POST',
        data: $data,
        dataType: 'json',
        beforeSend: function() {
            $('#cash_transaction_approvals #approvals .button.more_btn').remove();
            $('#cash_transaction_approvals #approvals .more_btn_container').append('<div class="wait center_screen center"><div class="bar bar_color_blue bar_animate"></div></div>');
        },
        complete: function() {
            
        },
        success: function(json) {
            if(json['html']) {
                $('#cash_transaction_approvals #approvals .more_btn_container').remove();          
                $('#cash_transaction_approvals #approvals').append(json['html']);
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        }
    });
}