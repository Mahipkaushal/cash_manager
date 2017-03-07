var validateLoginForm = function() {
    $isValid = false;
    $username = $(document).find('input[name="username"]').val();
    $password = $(document).find('input[name="password"]').val();
    if($username.replace(/ /g,'').length <= 0) {
        alert("Please enter Username");
    } else if($password.replace(/ /g,'').length <= 0) {
        alert("Please enter Password");
    } else {
        $isValid = true;
    }

    return $isValid;
}

var sendLogin = function() {
    $remember = $('#remember_me').is(':checked');
    $data = $('#form input[type="text"], #form input[type="password"], #form input[type="hidden"], #form input[type="checkbox"]:checked');
    $.ajax({
        url: $baseUrl + '/auth/login',
        type: 'POST',
        data: $data,
        dataType: 'json',
        beforeSend: function() {
            showPageLoader();
        },
        complete: function() {
            hidePageLoader();
        },
        success: function(json) {
            if(json['success']) {
                showOverlayLoader();
                //displayMessage(json['success'], 'success');
            }
            if(json['user']) {
                $username = json['user']['username'];
                $token = json['user']['token'];
                if($remember === true) {
                    saveInLocalStorage();
                }
                setUpAjax();
                getHomeScreen();
            }
            if(json['error']) {
                displayMessage(json['error'], 'error');
            }
        } 
    });
}

var login = function() {
    if(validateLoginForm() === true) {
        sendLogin();
    }
}