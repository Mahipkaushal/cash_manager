<?php

namespace App\Models;

use App\Models\Model;

class Distributor extends Model {

	public function getDistributors($data = array()) {
		$sql = "SELECT";

        $sql .= " d.distributor_id";
        $sql .= ", d.name";
        $sql .= ", d.company";
        $sql .= ", d.address_1";
        $sql .= ", d.area";
        $sql .= ", d.city";
        $sql .= ", d.telephone";
        $sql .= ", d.pocket_status";
        $sql .= ", d.pocket";

        $sql .= " FROM `" . DB_PREFIX . "distributor` d";

        $sql .= " WHERE";
        $sql .= " d.distributor_id > 0";

        if(isset($data['distributor_id'])) {
        	$sql .= " AND d.distributor_id = '" . (int)$data['distributor_id'] . "'";
    	}
        
        if(isset($data['pocket_status'])) {
        	$sql .= " AND d.pocket_status = '" . (int)$data['pocket_status'] . "'";
    	}
        $sql .= " ORDER BY d.name";

        $query = $this->container->db->query($sql);

        if($query->rows) {
            return $query->rows;
        } else {
            return false;
        }
	}

    public function getDistributorPocket($distributor_id = 0) {
        if($distributor_id) {
            $sql = "SELECT";

            $sql .= " d.pocket";

            $sql .= " FROM `" . DB_PREFIX . "distributor` d";

            $sql .= " WHERE d.distributor_id = '" . (int)$distributor_id . "'";

            $query = $this->container->db->query($sql);

            if($query->row) {
                return $query->row['pocket'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function updateDistributorPocket($data = array()) {
        if($data) {            
            if(isset($data['distributor_id']) && (int)$data['distributor_id'] > 0) {
                $distributor_id = (int)$data['distributor_id'];
            } else {
                return false;
            }
            if(isset($data['cash_transactions_id']) && (int)$data['cash_transactions_id'] > 0) {
                $cash_transactions_id = (int)$data['cash_transactions_id'];
            } else {
                $cash_transactions_id = 0;
            }
            if(isset($data['amount']) && $data['amount'] != 0) {
                $amount = $data['amount'];
            } else {
                $amount = 0;
            }
            if(isset($data['transaction_type'])) {
                $transaction_type = (int)$data['transaction_type'];
            } else {
                $transaction_type = 0;
            }
            if(isset($data['transaction_from'])) {
                $transaction_from = (int)$data['transaction_from'];
            } else {
                $transaction_from = 0;
            }
            if(isset($data['transaction_to'])) {
                $transaction_to = (int)$data['transaction_to'];
            } else {
                $transaction_to = 0;
            }
            if(isset($data['approval_status'])) {
                $approval_status = (int)$data['approval_status'];
            } else {
                $approval_status = 0;
            }
            if(isset($data['added_by']) && (int)$data['added_by'] > 0) {
                $added_by = (int)$data['added_by'];
            } else {
                $added_by = $this->thisUserId;
            }

            $current_pocket_value = $this->getDistributorPocket($distributor_id);
            $new_pocket_value = $current_pocket_value + $amount;

            $sql = "UPDATE `" . DB_PREFIX . "distributor` SET pocket = '" . $new_pocket_value . "' WHERE distributor_id = '" . (int)$distributor_id . "'";
            $this->container->db->query($sql);

            $sql = "INSERT INTO `" . DB_PREFIX . "distributor_pocket_history` SET distributor_id = '" . (int)$distributor_id . "', cash_transactions_id = '" . $cash_transactions_id . "', transaction_type = '" . (int)$transaction_type . "', transaction_from = '" . $transaction_from . "', transaction_to = '" . $transaction_to . "', previous_amount = '" . $current_pocket_value . "', amount = '" . $amount . "', balance_amount = '" . $new_pocket_value . "', approval_status = '" . $approval_status . "', user_id = '" . $added_by . "', date_added = '" . REAL_TIME . "'";

            $this->container->db->query($sql);

            return $this->container->db->getLastId();

        } else {
            return false;
        }
    }
}