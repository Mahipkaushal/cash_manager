var $localStorage = false;
var checkLocalStorage = function() {
	if (typeof(Storage) !== 'undefined') {
		$localStorage = true;
		return true;
	} else {
		return false;
	}	
}

var saveInLocalStorage = function() {
	if($localStorage === true) {
		if (localStorage.getItem('authData') !== null) {
			localStorage.removeItem('authData');
		}

		var $auth = new Object();
		$auth.username = $username;
		$auth.token = $token;
		
		localStorage.setItem('authData', JSON.stringify($auth));
	}
}

var clearLocalStorage = function() {
	if($localStorage === true) {
		if (localStorage.getItem('authData') !== null) {
			localStorage.removeItem('authData');
		}
	}
}

var getUser = function() {
	if($localStorage === true) {
		if (localStorage.getItem('authData') !== null) {
			return JSON.parse(localStorage.getItem('authData'));
		} else {
			return false;	
		}
	} else {
		return false;
	}
}