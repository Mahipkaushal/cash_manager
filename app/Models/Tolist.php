<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;
use App\Models\Distributor;

class Tolist extends Model {

    protected $user;
    protected $distributor;

	public function getToOptions() {
		$sql = "SELECT";

        $sql .= " cm.cash_manager_app_to_options_id";
        $sql .= ", cm.name";
        $sql .= ", cm.description_required";

        $sql .= " FROM `" . DB_PREFIX . "cash_manager_app_to_options` cm";

        $sql .= " WHERE";
        $sql .= " cm.cash_manager_app_to_options_id > 0";
        $sql .= " AND cm.app_id = '" . APP_ID . "'";
        $sql .= " AND cm.status = 1";

        $sql .= " ORDER BY cm.name";

        $query = $this->container->db->query($sql);

        if($query->rows) {
            return $query->rows;
        } else {
            return false;
        }
	}

	public function getToOptionVariant($data = array()) {
		$sql = "SELECT";

        $sql .= " cmv.cash_manager_app_to_options_variant_id";
        $sql .= ", cmv.name";

        $sql .= " FROM `" . DB_PREFIX . "cash_manager_app_to_options_variant` cmv";

        $sql .= " WHERE";
        $sql .= " cmv.cash_manager_app_to_options_variant_id > 0";
        $sql .= " AND cmv.status = 1";

        if(isset($data['cash_manager_app_to_options_id'])) {
            $sql .= " AND cmv.cash_manager_app_to_options_id = '" . (int)$data['cash_manager_app_to_options_id'] . "'";
        }

        if (isset($data['name']) && !empty($data['name'])) {
            $sql .= " AND (";

            if (!empty($data['name'])) {
                $implode = array();
                $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $this->container->db->escape(strtolower($data['name'])))));
                foreach ($words as $word) {
                    $implode[] = "LCASE(cmv.name) LIKE '%" . $this->container->db->escape($word) . "%'";
                }
                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }
            $sql .= ")";
        }

        $sql .= " ORDER BY cmv.name";

        $query = $this->container->db->query($sql);

        if($query->rows) {
            return $query->rows;
        } else {
            return false;
        }
	}

    public function insertToOptionVariant($data = array()) {
        if($data) {
            $sql = "INSERT INTO `" . DB_PREFIX . "cash_manager_app_to_options_variant` SET";

            $sql .= " cash_manager_app_to_options_id = '" . (int)$data['cash_manager_app_to_options_id'] . "'";
            $sql .= ", name = '" . $this->container->db->escape($data['name']) . "'";
            $sql .= ", status = '" . (int)$data['status'] . "'";
            $sql .= ", user_id = '" . (int)$this->container->user['user_id'] . "'";
            $sql .= ", date_added = '" . REAL_TIME . "'";

            $this->container->db->query($sql);

            return $this->container->db->getLastId();
        }
    }

    public function getFroms() {
        $this->user = new User($this->container);
        $this->distributor = new Distributor($this->container);

        $cashbox_id = $this->container->cashbox_id;        

        $froms = array();

        $users = $this->user->getUsers();
        if($users) {
            foreach($users as $u) {
                $froms[1 . ':' . $u['user_id']] = $u['username'];
                $froms[4 . ':' . $u['user_id']] = $u['username'];
                $froms[5 . ':' . $u['user_id']] = $u['username'];
                $froms[7 . ':' . $u['user_id']] = $u['username'];               
            }
        }   

        $froms[2 . ':' . $cashbox_id] = 'Cash Box';
        $froms[3 . ':' . $cashbox_id] = 'Cash Box';

        $froms[6 . ':' . 0] = 'System';

        $distributors = $this->distributor->getDistributors();
        if($distributors) {
            foreach ($distributors as $d) {
                $froms[8 . ':' . $d['distributor_id']] = $d['name'];
            }
        }

        $customTos = $this->getToOptions();
        if($customTos){
            foreach ($customTos as $c) {
                $froms[9 . ':' . $c['cash_manager_app_to_options_id']] = $c['name'];
            }
        }

        return $froms;
    }

    public function getTos() {
        $this->user = new User($this->container);
        $this->distributor = new Distributor($this->container);

        $cashbox_id = $this->container->cashbox_id;

        $tos = array();

        $users = $this->user->getUsers();
        if($users) {
            foreach($users as $u) {
                $tos[1 . ':' . $u['user_id']] = $u['username'];
                $tos[2 . ':' . $u['user_id']] = $u['username'];
                $tos[6 . ':' . $u['user_id']] = $u['username'];
                $tos[8 . ':' . $u['user_id']] = $u['username'];
                $tos[9 . ':' . $u['user_id']] = $u['username'];
            }
        }
        
        $tos[3 . ':' . $cashbox_id] = 'Cash Box';

        $distributors = $this->distributor->getDistributors();
        if($distributors) {
            foreach ($distributors as $d) {
                $tos[4 . ':' . $d['distributor_id']] = $d['name'];
            }
        }

        $customTos = $this->getToOptions();
        if($customTos){
            foreach ($customTos as $c) {
                $tos[5 . ':' . $c['cash_manager_app_to_options_id']] = $c['name'];
            }
        }

        $tos[7 . ':' . 0] = 'System';

        return $tos;
    }

}