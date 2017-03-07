<?php

namespace App\Models;

use App\Models\Model;

class TransactionHistory extends Model {

	public function getTransactions($filter = array()) {

		$sql = "SELECT p.user_pocket_history_id, p.cash_transactions_id, p.transaction_type, p.amount, p.approval_status, p.date_added, ctt.name as transaction_type_name, c.transaction_from, c.transaction_to, c.flow_type_id, f.name as flow_type_name";

		$sql .= " FROM `" . DB_PREFIX . "user_pocket_history` p";		
		$sql .= " LEFT JOIN `" . DB_PREFIX . "cash_transaction_types` ctt ON(ctt.cash_transaction_type_id = p.transaction_type)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "cash_transactions_record` c ON(c.cash_transaction_id = p.cash_transactions_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "flow_type` f ON(f.flow_type_id = c.flow_type_id)";

		$sql .= " WHERE p.user_id = '" . (int)$this->container->user['user_id'] . "'";
		$sql .= " AND p.status = 1";
		$sql .= " AND p.cash_transactions_id > 0";
		$sql .= " AND p.approval_status = 2";

		if(isset($filter['filter_from'])) {
			$sql .= " AND c.transaction_from = '" . (int)$filter['filter_from'] . "'";
		}

		if(isset($filter['filter_to'])) {
			if(isset($filter['filter_transaction_type'])) {
				$sql .= " AND c.transaction_type = '" . (int)$filter['filter_transaction_type'] . "'";
			}
			$sql .= " AND c.transaction_to = '" . (int)$filter['filter_to'] . "'";
		}

		if(isset($filter['filter_flow_type'])) {
			$sql .= " AND c.flow_type = '" . (int)$filter['filter_flow_type'] . "'";
		}

		if(isset($filter['filter_date_from']) && isset($filter['filter_date_to'])) {
			$sql .= " AND DATE(p.date_added) >= DATE('" . $filter['filter_date_from'] . "')";
			$sql .= " AND DATE(p.date_added) <= DATE('" . $filter['filter_date_to'] . "')";
		} else if(isset($filter['filter_date_from'])) {
			$sql .= " AND DATE(p.date_added) = DATE('" . $filter['filter_date_from'] . "')";
		}

		$sql .= " ORDER BY p.date_added DESC";

		if(isset($filter['start']) && isset($filter['limit'])) {
			if($filter['start'] < 0) {
				$filter['start'] = 0;
			}
			if($filter['limit'] < 0) {
				$filter['limit'] = 10;
			}

			$sql .= " LIMIT " . $filter['start'] . ", " . $filter['limit'];
		}

		$query = $this->container->db->query($sql);

		if($query->rows) {
			return $query->rows;
		} else {
			return false;
		}

	}

	public function getTotalTransactions($filter = array()) {

		$sql = "SELECT COUNT(p.user_pocket_history_id) as total";

		$sql .= " FROM `" . DB_PREFIX . "user_pocket_history` p";		
		$sql .= " LEFT JOIN `" . DB_PREFIX . "cash_transaction_types` ctt ON(ctt.cash_transaction_type_id = p.transaction_type)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "cash_transactions_record` c ON(c.cash_transaction_id = p.cash_transactions_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "flow_type` f ON(f.flow_type_id = c.flow_type_id)";

		$sql .= " WHERE p.user_id = '" . (int)$this->container->user['user_id'] . "'";
		$sql .= " AND p.status = 1";
		$sql .= " AND p.cash_transactions_id > 0";
		$sql .= " AND p.approval_status = 2";

		if(isset($filter['filter_from'])) {
			$sql .= " AND c.transaction_from = '" . (int)$filter['filter_from'] . "'";
		}

		if(isset($filter['filter_to'])) {
			if(isset($filter['filter_transaction_type'])) {
				$sql .= " AND c.transaction_type = '" . (int)$filter['filter_transaction_type'] . "'";
			}
			$sql .= " AND c.transaction_to = '" . (int)$filter['filter_to'] . "'";
		}

		if(isset($filter['filter_flow_type'])) {
			$sql .= " AND c.flow_type = '" . (int)$filter['filter_flow_type'] . "'";
		}

		if(isset($filter['filter_date_from']) && isset($filter['filter_date_to'])) {
			$sql .= " AND DATE(p.date_added) >= DATE('" . $filter['filter_date_from'] . "')";
			$sql .= " AND DATE(p.date_added) <= DATE('" . $filter['filter_date_to'] . "')";
		} else if(isset($filter['filter_date_from'])) {
			$sql .= " AND DATE(p.date_added) = DATE('" . $filter['filter_date_from'] . "')";
		}

		$query = $this->container->db->query($sql);

		if($query->row) {
			return $query->row['total'];
		} else {
			return 0;
		}

	}

	public function getTransactionRejectionReason($user_pocket_history_id = 0) {
		
		$sql = "SELECT a.reject_reason FROM `" . DB_PREFIX . "cash_transaction_approval` a WHERE pocket_history_id = '" . (int)$user_pocket_history_id . "'";
		$query = $this->container->db->query($sql);

		if($query->row) {
			return $query->row['reject_reason'];
		} else {
			return false;
		}

	}

}