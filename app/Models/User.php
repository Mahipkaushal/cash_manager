<?php

namespace App\Models;

use App\Models\Model;

class User extends Model {

    public function login($data = array()) {
    	if($data) {

    		$sql = "SELECT * FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->container->db->escape($data['username']) . "' AND password = '" . $this->container->db->escape(md5($data['password'])) . "' AND status = 1";

    		$query = $this->container->db->query($sql);

    		if($query->row) {
    			return $query->row;
    		} else {
    			return false;
    		}
    	} else {
    		return false;
    	}
    }

    public function getUser($user_id = 0) {
        if($user_id) {

            $sql = "SELECT";

            $sql .= " u.user_id";
            $sql .= ", u.username";
            $sql .= ", u.telephone";
            $sql .= ", u.user_group_id";
            $sql .= ", u.pocket";
            $sql .= ", cm.approval_required";
            $sql .= ", cm.cashbox_access";
            $sql .= ", cm.permission_type";

            $sql .= " FROM `" . DB_PREFIX . "user` u";
            $sql .= " LEFT JOIN `" . DB_PREFIX . "cash_manager_app_access_level` cm ON(cm.user_group_id = u.user_group_id)";
            $sql .= " WHERE";            
            $sql .= " u.user_id > 0";

            $sql .= " AND u.user_id = '" . (int)$user_id . "'";

            $query = $this->container->db->query($sql);

            if($query->row) {
                return $query->row;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getUserByToken($token = false) {
        if($token) {

            $sql = "SELECT";

            $sql .= " uad.user_id";

            $sql .= " FROM `" . DB_PREFIX . "user_app_data` uad";
            
            $sql .= " WHERE";
            $sql .= " uad.status = 1";            
            $sql .= " AND uad.app_id = '" . APP_ID . "'";

            $sql .= " AND uad.token = '" . $this->container->db->escape($token) . "'";

            $query = $this->container->db->query($sql);

            if($query->row && $query->row['user_id'] > 0) {
                return $this->getUser($query->row['user_id']);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function validateToken($token = false) {
        if($token) {

            $sql = "SELECT";

            $sql .= " uad.user_id";

            $sql .= " FROM `" . DB_PREFIX . "user_app_data` uad";
            $sql .= " LEFT JOIN `" . DB_PREFIX . "user` u ON(u.user_id = uad.user_id)";
            $sql .= " WHERE";
            $sql .= " uad.token = '" . $this->container->db->escape($token) . "'";
            $sql .= " AND uad.status = 1";
            $sql .= " AND uad.app_id = '" . APP_ID . "'";
            $sql .= " AND u.cash_access = 1";

            $query = $this->container->db->query($sql);
            if($query->row) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function updateToken($token, $user_id) {
        
        $this->deleteToken($user_id);

        $sql = "INSERT INTO `" . DB_PREFIX . "user_app_data` SET app_id = '" . APP_ID . "', user_id = '" . $user_id . "', token = '" . $this->container->db->escape($token) . "', date_added = '" . REAL_TIME . "'";

        $this->container->db->query($sql);

    }

    public function deleteToken($user_id) {

        $sql = "UPDATE `" . DB_PREFIX . "user_app_data` SET status = '0', date_modified = '" . REAL_TIME . "' WHERE status = '1' AND app_id = '" . APP_ID . "' AND user_id = '" . $user_id . "'";

        $this->container->db->query($sql);

    }

    public function getUsers() {

        $sql = "SELECT";

        $sql .= " u.user_id";
        $sql .= ", u.username";

        $sql .= " FROM `" . DB_PREFIX . "user` u";
        $sql .= " WHERE";            
        $sql .= " u.status = 1";

        $sql .= " ORDER BY u.username";

        $query = $this->container->db->query($sql);

        if($query->rows) {
            return $query->rows;
        } else {
            return false;
        }

    }

    public function getUserPocket($user_id = 0) {
        if($user_id) {
            $sql = "SELECT";

            $sql .= " u.pocket";

            $sql .= " FROM `" . DB_PREFIX . "user` u";

            $sql .= " WHERE u.user_id = '" . (int)$user_id . "'";

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

    public function updateUserPocket($data = array()) {
        if($data) {            
            if(isset($data['user_id']) && (int)$data['user_id'] > 0) {
                $user_id = (int)$data['user_id'];
            } else {
                $user_id = (int)$this->container->user['user_id'];
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

            
            $current_pocket_value = $this->getUserPocket($user_id);
            $new_pocket_value = $current_pocket_value + $amount;

            $sql = "UPDATE `" . DB_PREFIX . "user` SET pocket = '" . $new_pocket_value . "' WHERE user_id = '" . $user_id . "'";
            $this->container->db->query($sql);

            $sql = "INSERT INTO `" . DB_PREFIX . "user_pocket_history` SET user_id = '" . $user_id . "', cash_transactions_id = '" . $cash_transactions_id . "', transaction_type = '" . (int)$transaction_type . "', previous_amount = '" . $current_pocket_value . "', amount = '" . $amount . "', balance_amount = '" . $new_pocket_value . "', approval_status = '" . $approval_status . "', added_by = '" . $added_by . "', date_added = '" . REAL_TIME . "'";

            $this->container->db->query($sql);

            return $this->container->db->getLastId();

        } else {
            return false;
        }
    }
    
}
