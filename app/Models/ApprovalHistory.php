<?php

namespace App\Models;

use App\Models\Model;

class ApprovalHistory extends Model {

	public function getApprovals($filter = array()) {

		$sql = "SELECT a.cash_transaction_approval_id, a.pocket_history_id, a.transaction_from, a.transaction_to, a.transaction_type, a.amount, a.transaction_date, a.approval_status, a.reject_reason, a.approved_by, a.comment, a.date_added, ctt.name as transaction_type_name";

		$sql .= " FROM `" . DB_PREFIX . "cash_transaction_approval` a";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "cash_transaction_types` ctt ON(ctt.cash_transaction_type_id = a.transaction_type)";

		$sql .= " WHERE a.cash_transaction_approval_id > 0";
		$sql .= " AND a.user_id = '" . (int)$this->container->user['user_id'] . "'";

		if(isset($filter['filter_from'])) {
			$sql .= " AND a.transaction_from = '" . (int)$filter['filter_from'] . "'";
		}

		if(isset($filter['filter_to'])) {
			if(isset($filter['filter_transaction_type'])) {
				$sql .= " AND a.transaction_type = '" . (int)$filter['filter_transaction_type'] . "'";
			}
			$sql .= " AND a.transaction_to = '" . (int)$filter['filter_to'] . "'";
		}

		if(isset($filter['filter_approval_status'])) {
			$sql .= " AND a.approval_status = '" . (int)$filter['filter_approval_status'] . "'";
		}

		if(isset($filter['filter_date_from']) && isset($filter['filter_date_to'])) {
			$sql .= " AND DATE(a.transaction_date) >= DATE('" . $filter['filter_date_from'] . "')";
			$sql .= " AND DATE(a.transaction_date) <= DATE('" . $filter['filter_date_to'] . "')";
		} else if(isset($filter['filter_date_from'])) {
			$sql .= " AND DATE(a.transaction_date) = DATE('" . $filter['filter_date_from'] . "')";
		}

		$sql .= " ORDER BY a.date_added DESC";

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

	public function getTotalApprovals($filter = array()) {

		$sql = "SELECT COUNT(a.cash_transaction_approval_id) as count";

		$sql .= " FROM `" . DB_PREFIX . "cash_transaction_approval` a";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "cash_transaction_types` ctt ON(ctt.cash_transaction_type_id = a.transaction_type)";

		$sql .= " WHERE a.cash_transaction_approval_id > 0";
		$sql .= " AND a.user_id = '" . (int)$this->container->user['user_id'] . "'";

		if(isset($filter['filter_from'])) {
			$sql .= " AND a.transaction_from = '" . (int)$filter['filter_from'] . "'";
		}

		if(isset($filter['filter_to'])) {
			if(isset($filter['filter_transaction_type'])) {
				$sql .= " AND a.transaction_type = '" . (int)$filter['filter_transaction_type'] . "'";
			}
			$sql .= " AND a.transaction_to = '" . (int)$filter['filter_to'] . "'";
		}

		if(isset($filter['filter_approval_status'])) {
			$sql .= " AND a.approval_status = '" . (int)$filter['filter_approval_status'] . "'";
		}

		if(isset($filter['filter_date_from']) && isset($filter['filter_date_to'])) {
			$sql .= " AND DATE(a.transaction_date) >= DATE('" . $filter['filter_date_from'] . "')";
			$sql .= " AND DATE(a.transaction_date) <= DATE('" . $filter['filter_date_to'] . "')";
		} else if(isset($filter['filter_date_from'])) {
			$sql .= " AND DATE(a.transaction_date) = DATE('" . $filter['filter_date_from'] . "')";
		}

		$query = $this->container->db->query($sql);

		if($query->row) {
			return $query->row['count'];
		} else {
			return 0;
		}
	}

	public function getTransactionRejectionReason($cash_transaction_approval_id = 0) {
		
		$sql = "SELECT a.reject_reason FROM `" . DB_PREFIX . "cash_transaction_approval` a WHERE a.cash_transaction_approval_id = '" . (int)$cash_transaction_approval_id . "'";
		$query = $this->container->db->query($sql);

		if($query->row) {
			return $query->row['reject_reason'];
		} else {
			return false;
		}

	}

}