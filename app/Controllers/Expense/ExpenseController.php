<?php

namespace App\Controllers\Expense;

use App\Controllers\Controller;
use App\Models\User;
use App\Models\Distributor;
use App\Models\Tolist;
use App\Models\Transactions;
use Slim\Views\Twig as View;

class ExpenseController extends Controller {

	protected $user;
	protected $distributor;
	protected $tolist;
	protected $transactions;

	public function getHome($request, $response) {
		
		$json = array();

		if(!$request->getAttribute('error')){
			$user = $request->getAttribute('user');
			$pocket_value = $user['pocket'];
			$class_indicator = '';
			if($pocket_value > 0) {
				$class_indicator = ' color_green';
			} else if($pocket_value < 0) {
				$class_indicator = ' color_red';
			}
	        $vars = [
	            'user'	=> [
	            	'user_id'	=>	$user['user_id'],
	            	'username'	=>	$user['username'],
	            	'token'		=>	$request->getAttribute('token')
	            ],
	            'pocket'	=>	[
	            	'value'		=>	number_format($pocket_value, 2),
	            	'indicator'	=>	$class_indicator
	            ],
	        ];
	        $json['html'] = $this->view->fetch('expense/home.twig', $vars);
	    } else {
	    	$json['error']['code'] = 401;
	    	$json['error']['message'] = $request->getAttribute('message');
	    }
        
        return json_encode($json);
    }

    public function getCashManager($request, $response) {
		
		$json = array();

		if(!$request->getAttribute('error')){
			$thisUser = $request->getAttribute('user');

			$this->user = new User($this);
			$this->distributor = new Distributor($this);
			$this->tolist = new Tolist($this);

			$user_results = $this->user->getUsers();

			$froms = array();
			$froms = $this->getFromList($user_results, $thisUser);
			$tos = $this->getToList($user_results, $thisUser);

			$dates = $this->getDates();

	        $vars = [
	            'page'	=> [
	            	'title'		=>	'Cash Manager',
	            	'search'	=>	$this->view->fetch('partials/search.twig', [])
	            ],
	            'dates'	=>	$dates,
	            'froms'	=>	$froms
	        ];


	        $json['search_array'] = $tos;

	        $json['html'] = $this->view->fetch('expense/cashManager.twig', $vars);

	    } else {
	    	$json['error']['code'] = 401;
	    	$json['error']['message'] = $request->getAttribute('message');
	    }
        
        return json_encode($json);
    }

    public function insertTransaction($request, $response) {

    	$this->transactions = new Transactions($this);

		$json = array();
		$data = array();

		if(!$request->getAttribute('error')){
			$to_type = 0;

			$user = $request->getAttribute('user');

			$postData = $request->getParams();
			$errors = array();

			if(isset($postData['from']) && !empty($postData['from'])) {
				$data['from'] = $postData['from'];

				if($data['from'] == $this->cashbox_id) {
					if(!$user['cashbox_access']) {
						$errors[] = 'You don\'t have permission to access Cashbox!';
					}
				}
			} else {
				$data['from'] = $user['user_id'];
			}

			if(isset($postData['amount']) && !empty($postData['amount']) && is_numeric($postData['amount']) && $postData['amount'] > 0) {
				$data['amount'] = $postData['amount'];
			} else {
				$errors[] = 'Invalid Amount!';
			}

			if(isset($postData['to_id']) && !empty($postData['to_id'])) {
				$to_parts = explode(":", $postData['to_id']);
				if(isset($to_parts[0])) {
					$data['to_type'] = $to_parts[0];
					$to_type = $to_parts[0];

					if($to_type == 3) {
						if(!$user['cashbox_access']) {
							$errors[] = 'You don\'t have permission to access Cashbox!';
						}
					} else if($data['from'] == $this->cashbox_id && $to_type != 1) {
						$errors[] = 'Cashbox can only be used for internal pocket transfer!';
					}

				} else {
					$errors[] = 'Invalid To Type parameter!';
				}

				if(isset($to_parts[1])) {
					$data['to'] = $to_parts[1];
				} else {
					$errors[] = 'Invalid To parameter!';
				}
			} else {
				$errors[] = 'To parameter is missing!';
			}

			if(isset($postData['to_description_required']) && (int)$postData['to_description_required'] == 1) {
				if(isset($postData['to_description_id'])) {
					$data['to_description_id'] = $postData['to_description_id'];
				} else {
					$data['to_description_id'] = 0;
				}
				if(isset($postData['to_description']) && !empty($postData['to_description'])) {
					$data['to_description'] = $postData['to_description'];
				} else {
					$errors[] = 'To Description parameter is missing!';
				}
			}

			if(isset($postData['comment'])) {
				$data['comment'] = $postData['comment'];
			} else {
				$data['comment'] = '';
			}

			if(isset($postData['date']) && !empty($postData['date'])) {
				$data['date'] = date('Y-m-d', strtotime($postData['date']));
			} else {
				$data['date'] = date('Y-m-d', strtotime(REAL_TIME));
			}

			if($data['from'] == $data['to']) {
				$errors[] = 'From and To field cannot be same!';
			}
			
			$data['user_id'] = $user['user_id'];

			if(!$errors) {

				$this->user = new User($this);

				if(isset($postData['to_description_required']) && (int)$postData['to_description_required'] == 1 && $data['to_description_id'] == 0) {
					$newToVariant = array();
					$newToVariant['cash_manager_app_to_options_id'] = $data['to'];
					$newToVariant['name'] = ucfirst($data['to_description']);
					$newToVariant['status'] = 1;

					$this->tolist = new Tolist($this);

					$cash_manager_app_to_options_variant_id = $this->tolist->insertToOptionVariant($newToVariant);

					$data['to_description_id'] = $cash_manager_app_to_options_variant_id;
				}

				$result = $this->transactions->insertTransaction($data);

				if($result) {
					$pocket_value = $this->user->getUserPocket($user['user_id']);
					if($pocket_value) {
						$class_indicator = '';
						if($pocket_value > 0) {
							$class_indicator = ' color_green';
						} else if($pocket_value < 0) {
							$class_indicator = ' color_red';
						}
						$json['pocket']['value'] = number_format($pocket_value, 2);
						$json['pocket']['indicator'] = $class_indicator;
					}

					$json['post_data'] = $data;

		        	$json['success'] = 'Request submitted successfully!';
		        } else {
		        	$json['error']['code'] = 301;
	    			$json['error']['message'] = "Something went wrong!\nPlease try again";
		        }
	        } else {
	        	$error = "Please correct the following error(s) and try again:\n\n";
	        	foreach($errors as $key => $value) {
	        		$error .= ($key + 1) . ": " . $value . "\n";
	        	}
	        	
	        	$json['error']['code'] = 201;
	    		$json['error']['message'] = $error;
	        }
	    } else {
	    	$json['error']['code'] = 401;
	    	$json['error']['message'] = $request->getAttribute('message');
	    }
        
        return json_encode($json);

    }

    public function logout($request, $response) {
    	$json = array();

    	if(!$request->getAttribute('error')){
    		
    		$user = $request->getAttribute('user');
    		$user_id = $user['user_id'];
        	$this->token->deleteToken($user_id);

        	$json['success'] = 'Logout Successful!';
        }

        return json_encode($json);
    }

    protected function getDates() {
    	$dates = array();
    	$dates[] = array(
			'value'		=>	date('Y-m-d', strtotime(REAL_TIME . '-2 day')),
			'text'		=>	date('d-M-Y', strtotime(REAL_TIME . '-2 day')),
			'selected'	=>	''
		);
		$dates[] = array(
			'value'		=>	date('Y-m-d', strtotime(REAL_TIME . '-1 day')),
			'text'		=>	date('d-M-Y', strtotime(REAL_TIME . '-1 day')) . ' (Yesterday)',
			'selected'	=>	''
		);
		$dates[] = array(
			'value'		=>	date('Y-m-d', strtotime(REAL_TIME)),
			'text'		=>	date('d-M-Y', strtotime(REAL_TIME)) . ' (Today)',
			'selected'	=>	' selected="selected"'
		);
		return $dates;
    }

    protected function getFromList($users, $user) {
    	$froms = array();

		$froms[] = array(
			'user_id'	=>	$user['user_id'],
			'username'	=>	$user['username'],
			'selected'	=>	' selected="selected"'
		);

		if($user['permission_type'] == 1) {
	    	if($users) {
				foreach($users as $u) {
					if($user['user_id'] != $u['user_id']) {
						$froms[] = array(
							'user_id'	=>	$u['user_id'],
							'username'	=>	$u['username'],
							'selected'	=>	''
						);
					}
				}
			}
		}

		if($user['cashbox_access'] == 1) {
			$froms[] = array(
				'user_id'	=>	1000000,
				'username'	=>	'Cashbox',
				'selected'	=>	''
			);
		}

		return $froms;
    }

    protected function getToList($users, $user) {
    	$tos = array();

    	if($users) {
			foreach($users as $u) {
				if($user['user_id'] != $u['user_id']) {
					$tos[] = array(
						'id'	=>	1 . ':' . $u['user_id'],
						'text'	=>	'-' . $u['username']
					);
				}
			}
		}

		if($user['permission_type'] == 1) {
			$tos[] = array(
				'id'	=>	1 . ':' . $user['user_id'],
				'text'	=>	'-' . $user['username']
			);
		}

		if($user['cashbox_access'] == 1) {
			$tos[] = array(
				'id'	=>	3 . ':' . 1000000,
				'text'	=>	'Cashbox'
			);
		}

		$filter = array();
		$filter['pocket_status'] = 1;
		$distributors = $this->distributor->getDistributors($filter);

		if($distributors) {
			foreach ($distributors as $d) {
				$tos[] = array(
					'id'	=>	4 . ':' . $d['distributor_id'],
					'text'	=>	$d['name']
				);
			}
		}

		$customTos = $this->tolist->getToOptions();

		if($customTos){
			foreach ($customTos as $c) {
				$tos[] = array(
					'id'					=>	5 . ':' . $c['cash_manager_app_to_options_id'],
					'text'					=>	$c['name'],
					'description_required'	=>	$c['description_required']
				);
			}
		}

		return $tos;
    }

}