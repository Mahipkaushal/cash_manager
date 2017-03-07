<?php

namespace App\Controllers\Expense;

use App\Controllers\Controller;
use App\Models\User;
use App\Models\Distributor;
use App\Models\Tolist;
use App\Models\TransactionHistory;
use Slim\Views\Twig as View;

class CashTransactionsController extends Controller {

	protected $user;
	protected $distributor;
	protected $tolist;
	protected $transactions;
	protected $limit = 5;

    public function getCashTransactions($request, $response) {

    	$json = array();

		if(!$request->getAttribute('error')) {
			$this->user = new User($this);
			$this->distributor = new Distributor($this);
			$this->tolist = new Tolist($this);
			$this->transactions = new TransactionHistory($this);

			$filter_froms = $this->getFromList();
			$filter_tos = $this->getToList();
			$filters = [
				'filter_tos'	=>	$filter_tos,
				'filter_froms'	=>	$filter_froms
			];			

			$data = array();
			$data['start'] = 0;
			$data['limit'] = $this->limit;

			$transactions = array();
			$transactions_result = $this->transactions->getTransactions($data);
			$transactions_total = $this->transactions->getTotalTransactions($data);

			if($transactions_result) {
				$froms = $this->tolist->getFroms();
				$tos = $this->tolist->getTos();

				foreach($transactions_result as $txn) {
					$approval_status = '';
					$rejection_reason = '';
					$transaction_from = '-';
					$transaction_to = '-';

					$amount = $txn['amount'];
					if($amount < 0) {
						$amount = '<span class="color_red"><i class="fa fa-rupee" aria-hidden="true"></i> ' . number_format($amount, 2) . '</span>';
					} else {
						$amount = '<span class="color_green"><i class="fa fa-rupee" aria-hidden="true"></i> ' . number_format($amount, 2) . '</span>';
					}
					if($txn['approval_status'] == 1) {
						$approval_status = '<span class="color_yellow"><i class="fa fa-hand-paper-o" aria-hidden="true"></i> Pending for Approval!';
					} else if($txn['approval_status'] == 2) {
						$approval_status = '<span class="color_green"><i class="fa fa-check" aria-hidden="true"></i> Success!';
					} else if($txn['approval_status'] == 3) {
						$approval_status = '<span class="color_red"><i class="fa fa-remove" aria-hidden="true"></i> Rejected!';
						$reason = $this->transactions->getTransactionRejectionReason($txn['user_pocket_history_id']);
						if($reason) {
							$rejection_reason = '<div class="row bold"><div class="rejection_reason">' . $reason . '</div></div>';
						}
					}

					if(isset($txn['transaction_from']) && isset($froms[$txn['transaction_type'] . ':' . $txn['transaction_from']])) {
						$transaction_from = $froms[$txn['transaction_type'] . ':' . $txn['transaction_from']];
					}
					if(isset($txn['transaction_to']) && isset($tos[$txn['transaction_type'] . ":" . $txn['transaction_to']])) {
						$transaction_to = $tos[$txn['transaction_type'] . ":" . $txn['transaction_to']];
					}

					if($transaction_from == $this->container->user['username'] && $txn['transaction_from'] == $this->container->user['user_id']) {
						$transaction_from = 'You';
					}

					if($transaction_to == $this->container->user['username'] && $txn['transaction_to'] == $this->container->user['user_id']) {
						$transaction_to = 'You';
					}

					$transactions[] = array(
						'user_pocket_history_id'		=>	$txn['user_pocket_history_id'],
						'amount'						=>	$amount,
						'date'							=>	date('d M Y h:i a', strtotime($txn['date_added'])),
						'transaction_category'			=>	$txn['transaction_type_name'],
						'approval_status'				=>	$approval_status,
						'rejection_reason'				=>	$rejection_reason,
						'transaction_from'				=>	$transaction_from,
						'transaction_to'				=>	$transaction_to
					);
				}
			}

			$next_page = 0;
			$next_start = $transactions_total;
			if($transactions_total > ($data['start'] + $data['limit'])) {
				$next_page = abs($transactions_total - ($data['start'] + $data['limit']));
				$next_start = $this->limit;
			}

			$transactions_vars = [
				'transactions'			=>	$transactions,
				'next_page'				=>	$next_page,
				'next_start'			=>	$next_start,
			];
	        
	        $vars = [
	            'page'	=> [
	            	'title'			=>	'Transactions',
	            	'filter'		=>	$this->view->fetch('partials/filter.twig', $filters),
	            	'transactions'	=>	$this->view->fetch('partials/transactionHistory.twig', $transactions_vars)
	            ],
	        ];
	        
	        $json['html'] = $this->view->fetch('expense/cashTransactions.twig', $vars);
	    } else {
	    	$json['error']['code'] = 401;
	    	$json['error']['message'] = $request->getAttribute('message');
	    }
        
        return json_encode($json);
    }

    public function getTransactions($request, $response) {

    	$json = array();

    	if(!$request->getAttribute('error')) {
    		$this->user = new User($this);
			$this->distributor = new Distributor($this);
			$this->tolist = new Tolist($this);
    		$this->transactions = new TransactionHistory($this);

    		$next = 0;
    		if($request->getParam('next')) {
    			$next = $request->getParam('next');
    		}

    		$data = array();

    		if($request->getParam('filter_from') && $request->getParam('filter_from') != '*') {
    			$data['filter_from'] = $request->getParam('filter_from');
    		}

    		if($request->getParam('filter_to') && $request->getParam('filter_to') != '*') {
    			$filter_to = explode(":", $request->getParam('filter_to'));
    			$data['filter_transaction_type'] = $filter_to[0];
    			$data['filter_to'] = $filter_to[1];
    		}

    		if($request->getParam('filter_flow_type') && $request->getParam('filter_flow_type') != '*') {
    			$filter_flow_type = (int)$request->getParam('filter_flow_type');
    			if($filter_flow_type == 1) {
    				$data['filter_flow_type'] = 0;
    			} else if($filter_flow_type == 2) {
    				$data['filter_flow_type'] = 1;
    			}    			
    		}

    		if($request->getParam('filter_date_from') && !empty($request->getParam('filter_date_from'))) {
    			$data['filter_date_from'] = date('Y-m-d', strtotime($request->getParam('filter_date_from')));
    			if($request->getParam('filter_date_to') && !empty($request->getParam('filter_date_to'))) {
	    			$data['filter_date_to'] = date('Y-m-d', strtotime($request->getParam('filter_date_to')));
	    		}
    		}

			$data['start'] = $next;
			$data['limit'] = $this->limit;

			$transactions = array();

			$transactions_result = $this->transactions->getTransactions($data);
			$transactions_total = $this->transactions->getTotalTransactions($data);

			if($transactions_result) {

				$froms = $this->tolist->getFroms();
				$tos = $this->tolist->getTos();

				foreach($transactions_result as $txn) {
					$approval_status = '';
					$rejection_reason = '';
					$transaction_from = '-';
					$transaction_to = '-';

					$amount = $txn['amount'];
					if($amount < 0) {
						$amount = '<span class="color_red"><i class="fa fa-rupee" aria-hidden="true"></i> ' . number_format($amount, 2) . '</span>';
					} else {
						$amount = '<span class="color_green"><i class="fa fa-rupee" aria-hidden="true"></i> ' . number_format($amount, 2) . '</span>';
					}
					if($txn['approval_status'] == 1) {
						$approval_status = '<span class="color_yellow"><i class="fa fa-hand-paper-o" aria-hidden="true"></i> Pending for Approval!';
					} else if($txn['approval_status'] == 2) {
						$approval_status = '<span class="color_green"><i class="fa fa-check" aria-hidden="true"></i> Success!';
					} else if($txn['approval_status'] == 3) {
						$approval_status = '<span class="color_red"><i class="fa fa-remove" aria-hidden="true"></i> Rejected!';
						$reason = $this->transactions->getTransactionRejectionReason($txn['user_pocket_history_id']);
						if($reason) {
							$rejection_reason = '<div class="row bold"><div class="rejection_reason">' . $reason . '</div></div>';
						}
					}

					if(isset($txn['transaction_from']) && isset($froms[$txn['transaction_type'] . ':' . $txn['transaction_from']])) {
						$transaction_from = $froms[$txn['transaction_type'] . ':' . $txn['transaction_from']];
					}
					if(isset($txn['transaction_to']) && isset($tos[$txn['transaction_type'] . ":" . $txn['transaction_to']])) {
						$transaction_to = $tos[$txn['transaction_type'] . ":" . $txn['transaction_to']];
					}

					if($transaction_from == $this->container->user['username'] && $txn['transaction_from'] == $this->container->user['user_id']) {
						$transaction_from = 'You';
					}

					if($transaction_to == $this->container->user['username'] && $txn['transaction_to'] == $this->container->user['user_id']) {
						$transaction_to = 'You';
					}

					$transactions[] = array(
						'user_pocket_history_id'		=>	$txn['user_pocket_history_id'],
						'amount'						=>	$amount,
						'date'							=>	date('d M Y h:i a', strtotime($txn['date_added'])),
						'transaction_category'			=>	$txn['transaction_type_name'],
						'approval_status'				=>	$approval_status,
						'rejection_reason'				=>	$rejection_reason,
						'transaction_from'				=>	$transaction_from,
						'transaction_to'				=>	$transaction_to
					);
				}
			}

			$next_page = 0;
			$next_start = $transactions_total;
			if($transactions_total > ($data['start'] + $data['limit'])) {
				$next_page = abs($transactions_total - ($data['start'] + $data['limit']));
				$next_start = $next + $this->limit;
			}

			$transactions_vars = [
				'transactions'			=>	$transactions,
				'next_page'				=>	$next_page,
				'next_start'			=>	$next_start,
			];

			$json['html'] = $this->view->fetch('partials/transactionHistory.twig', $transactions_vars);

    	} else {
	    	$json['error']['code'] = 401;
	    	$json['error']['message'] = $request->getAttribute('message');
	    }
        
        return json_encode($json);

    }

    protected function getFromList() {
    	$froms = array();

    	$users = $this->user->getUsers();

    	if($users) {
			foreach($users as $u) {
				$froms[] = array(
					'user_id'	=>	$u['user_id'],
					'username'	=>	$u['username']
				);
			}
		}

		$froms[] = array(
			'user_id'	=>	1000000,
			'username'	=>	'Cashbox'
		);

		return $froms;
    }

    protected function getToList() {
    	$tos = array();

    	$users = $this->user->getUsers();

    	if($users) {
			foreach($users as $u) {
				$tos[] = array(
					'id'	=>	1 . ':' . $u['user_id'],
					'text'	=>	'-' . $u['username']
				);
			}
		}

		if($users) {
			foreach($users as $u) {
				$tos[] = array(
					'id'	=>	2 . ':' . $u['user_id'],
					'text'	=>	'-' . $u['username']
				);
			}
		}

		$tos[] = array(
			'id'	=>	2 . ':' . 1000000,
			'text'	=>	'Cashbox'
		);

		$tos[] = array(
			'id'	=>	3 . ':' . 1000000,
			'text'	=>	'Cashbox'
		);

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