<?php

namespace App\Controllers\Expense;

use App\Controllers\Controller;
use App\Models\User;
use App\Models\Distributor;
use App\Models\Tolist;
use App\Models\ApprovalHistory;
use Slim\Views\Twig as View;

class CashTransactionApprovalsController extends Controller {

	protected $user;
	protected $distributor;
	protected $tolist;
	protected $approvals;
	protected $limit = 5;

    public function getCashTransactionApprovalPage($request, $response) {

    	$json = array();

		if(!$request->getAttribute('error')) {
			$this->user = new User($this);
			$this->distributor = new Distributor($this);
			$this->tolist = new Tolist($this);
			$this->approvals = new ApprovalHistory($this);

			$filter_froms = $this->getFromList();
			$filter_tos = $this->getToList();
			$filters = [
				'filter_tos'	=>	$filter_tos,
				'filter_froms'	=>	$filter_froms
			];

			$data = array();
			$data['start'] = 0;
			$data['limit'] = $this->limit;

			$approvals = array();
			$approvals_result = $this->approvals->getApprovals($data);
			$approvals_total = $this->approvals->getTotalApprovals($data);

			if($approvals_result) {
				$froms = $this->tolist->getFroms();
				$tos = $this->tolist->getTos();

				foreach($approvals_result as $approval) {
					$approval_status = '';
					$rejection_reason = '';
					$transaction_from = '-';
					$transaction_to = '-';

					$amount = $approval['amount'];
					
					if($approval['approval_status'] == 1) {
						$approval_status = '<span class="color_yellow"><i class="fa fa-hand-paper-o" aria-hidden="true"></i> Pending for Approval!';
					} else if($approval['approval_status'] == 2) {
						$approval_status = '<span class="color_green"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Approved!';
					} else if($approval['approval_status'] == 3) {
						$approval_status = '<span class="color_red"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i> Rejected!';
						$reason = $this->approvals->getTransactionRejectionReason($approval['cash_transaction_approval_id']);
						if($reason) {
							$rejection_reason = '<div class="row right"><div class="rejection_reason">' . $reason . '</div></div>';
						}
					}

					if(isset($approval['transaction_from']) && isset($froms[$approval['transaction_type'] . ':' . $approval['transaction_from']])) {
						$transaction_from = $froms[$approval['transaction_type'] . ':' . $approval['transaction_from']];
					}
					if(isset($approval['transaction_to']) && isset($tos[$approval['transaction_type'] . ":" . $approval['transaction_to']])) {
						$transaction_to = $tos[$approval['transaction_type'] . ":" . $approval['transaction_to']];
					}

					if($transaction_from == $this->container->user['username'] && $approval['transaction_from'] == $this->container->user['user_id']) {
						$transaction_from = 'You';
					}

					if($transaction_to == $this->container->user['username'] && $approval['transaction_to'] == $this->container->user['user_id']) {
						$transaction_to = 'You';
					}

					$approvals[] = array(
						'cash_transaction_approval_id'		=>	$approval['cash_transaction_approval_id'],
						'amount'							=>	'<span><i class="fa fa-rupee"></i>' . $amount . '</span>',
						'date_added'						=>	date('d M Y h:i a', strtotime($approval['date_added'])),
						'transaction_date'					=>	date('d M Y', strtotime($approval['transaction_date'])),
						'transaction_category'				=>	$approval['transaction_type_name'],
						'approval_status'					=>	$approval_status,
						'rejection_reason'					=>	$rejection_reason,
						'transaction_from'					=>	$transaction_from,
						'transaction_to'					=>	$transaction_to
					);
				}
			}

			$next_page = 0;
			$next_start = $approvals_total;
			if($approvals_total > ($data['start'] + $data['limit'])) {
				$next_page = abs($approvals_total - ($data['start'] + $data['limit']));
				$next_start = $this->limit;
			}

			$approvals_vars = [
				'approvals'				=>	$approvals,
				'next_page'				=>	$next_page,
				'next_start'			=>	$next_start,
			];
	        
	        $vars = [
	            'page'	=> [
	            	'title'			=>	'Approvals',
	            	'filter'		=>	$this->view->fetch('partials/approvalFilter.twig', $filters),
	            	'approvals'		=>	$this->view->fetch('partials/approvalHistory.twig', $approvals_vars)
	            ],
	        ];
	        
	        $json['html'] = $this->view->fetch('expense/cashTransactionApprovals.twig', $vars);
	    } else {
	    	$json['error']['code'] = 401;
	    	$json['error']['message'] = $request->getAttribute('message');
	    }
        
        return json_encode($json);
    }

    public function getCashTransactionApprovals($request, $response) {

    	$json = array();

    	if(!$request->getAttribute('error')) {
    		$this->user = new User($this);
			$this->distributor = new Distributor($this);
			$this->tolist = new Tolist($this);
    		$this->approvals = new ApprovalHistory($this);

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

    		if($request->getParam('filter_approval_status') && $request->getParam('filter_approval_status') != '*') {
    			$data['filter_approval_status'] = (int)$request->getParam('filter_approval_status');		
    		}

    		if($request->getParam('filter_date_from') && !empty($request->getParam('filter_date_from'))) {
    			$data['filter_date_from'] = date('Y-m-d', strtotime($request->getParam('filter_date_from')));
    			if($request->getParam('filter_date_to') && !empty($request->getParam('filter_date_to'))) {
	    			$data['filter_date_to'] = date('Y-m-d', strtotime($request->getParam('filter_date_to')));
	    		}
    		}

			$data['start'] = $next;
			$data['limit'] = $this->limit;

			$approvals = array();
			$approvals_result = $this->approvals->getApprovals($data);
			$approvals_total = $this->approvals->getTotalApprovals($data);

			if($approvals_result) {

				$froms = $this->tolist->getFroms();
				$tos = $this->tolist->getTos();

				foreach($approvals_result as $approval) {
					$approval_status = '';
					$rejection_reason = '';
					$transaction_from = '-';
					$transaction_to = '-';

					$amount = $approval['amount'];
					
					if($approval['approval_status'] == 1) {
						$approval_status = '<span class="color_yellow"><i class="fa fa-hand-paper-o" aria-hidden="true"></i> Pending for Approval!';
					} else if($approval['approval_status'] == 2) {
						$approval_status = '<span class="color_green"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Approved!';
					} else if($approval['approval_status'] == 3) {
						$approval_status = '<span class="color_red"><i class="fa fa-thumbs-o-down" aria-hidden="true"></i> Rejected!';
						$reason = $this->approvals->getTransactionRejectionReason($approval['cash_transaction_approval_id']);
						if($reason) {
							$rejection_reason = '<div class="row right"><div class="rejection_reason">' . $reason . '</div></div>';
						}
					}

					if(isset($approval['transaction_from']) && isset($froms[$approval['transaction_type'] . ':' . $approval['transaction_from']])) {
						$transaction_from = $froms[$approval['transaction_type'] . ':' . $approval['transaction_from']];
					}
					if(isset($approval['transaction_to']) && isset($tos[$approval['transaction_type'] . ":" . $approval['transaction_to']])) {
						$transaction_to = $tos[$approval['transaction_type'] . ":" . $approval['transaction_to']];
					}

					if($transaction_from == $this->container->user['username'] && $approval['transaction_from'] == $this->container->user['user_id']) {
						$transaction_from = 'You';
					}

					if($transaction_to == $this->container->user['username'] && $approval['transaction_to'] == $this->container->user['user_id']) {
						$transaction_to = 'You';
					}

					$approvals[] = array(
						'cash_transaction_approval_id'		=>	$approval['cash_transaction_approval_id'],
						'amount'							=>	'<span><i class="fa fa-rupee"></i>' . $amount . '</span>',
						'date_added'						=>	date('d M Y h:i a', strtotime($approval['date_added'])),
						'transaction_date'					=>	date('d M Y', strtotime($approval['transaction_date'])),
						'transaction_category'				=>	$approval['transaction_type_name'],
						'approval_status'					=>	$approval_status,
						'rejection_reason'					=>	$rejection_reason,
						'transaction_from'					=>	$transaction_from,
						'transaction_to'					=>	$transaction_to
					);
				}
			}

			$next_page = 0;
			$next_start = $approvals_total;
			if($approvals_total > ($data['start'] + $data['limit'])) {
				$next_page = abs($approvals_total - ($data['start'] + $data['limit']));
				$next_start = $next + $this->limit;
			}

			$approvals_vars = [
				'approvals'				=>	$approvals,
				'next_page'				=>	$next_page,
				'next_start'			=>	$next_start,
			];

			$json['html'] = $this->view->fetch('partials/approvalHistory.twig', $approvals_vars);

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