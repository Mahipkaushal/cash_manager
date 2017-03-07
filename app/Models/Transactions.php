<?php

namespace App\Models;

use App\Models\Model;
use App\Models\User;
use App\Models\Tolist;

class Transactions extends Model {

    protected $user;
    protected $tolist;
    protected $distributor;
    protected $thisUser;
    protected $thisUserId;
    protected $approvalRequired;
    protected $container;

	public function __construct($container) {
        $this->container = $container;

        $this->user = new User($this->container);
        $this->tolist = new Tolist($this->container);
        $this->distributor = new Distributor($this->container);

        $this->thisUser = $this->container->user;
        $this->thisUserId = $this->container->user['user_id'];
        $this->approvalRequired = $this->container->user['approval_required'];
    }

    public function insertTransaction($data = array()) {
        if($data) {
            switch((int)$data['to_type']) {
                case 1:
                    if($data['from'] != $this->container->cashbox_id) {

                        $this->internalPocketTransfer($data);

                    } else if($data['from'] == $this->container->cashbox_id) {

                        $data['to_type'] = 2;
                        $this->cashboxToInternalPocketTransfer($data);

                    }
                    break;

                case 3: 
                    $this->internalPocketToCashboxTransfer($data);
                    break;

                case 4:
                    $this->internalPocketToExternalPocketTransfer($data);
                    break;

                case 5:
                    $this->internalPocketExpense($data);
                    break;

            }

            return true;
        } else {
            return false;
        }
    }

    public function internalPocketTransfer($data = array()) {
        if($data) {
            $cash_transactions_id = 0;

            $cash_transaction_master_id = $this->generateMasterTransactionId(0, 1);

            /*------------------------- Debit Internal Pocket [Start] ---------------------*/
            $trans_data = array();
            $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
            $trans_data['flow_type'] = 1;
            $trans_data['transaction_type'] = $data['to_type'];
            $trans_data['flow_type_id'] = 15;
            $trans_data['transaction_from'] = $data['from'];
            $trans_data['transaction_to'] = $data['to'];
            $trans_data['amount'] = $data['amount'];
            $trans_data['remark'] = $data['comment'];
            $trans_data['transaction_date'] = $data['date'];

            $cash_transactions_id = $this->addNewTransaction($trans_data);

            $pocketData = array();
            $pocketData['user_id'] = $data['from'];
            $pocketData['amount'] = (-1 * $data['amount']);
            $pocketData['cash_transactions_id'] = $cash_transactions_id;
            $pocketData['transaction_type'] = $data['to_type'];
            $pocketData['approval_status'] = 2;
            $pocketData['added_by'] = $this->thisUserId;

            $debit_pocket_history_id = $this->user->updateUserPocket($pocketData);
            /*------------------------- Debit Internal Pocket [End] ---------------------*/

            /*------------------------- Credit Internal Pocket [Start] ---------------------*/
            $trans_data = array();
            $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
            $trans_data['flow_type'] = 0;
            $trans_data['transaction_type'] = $data['to_type'];            
            $trans_data['flow_type_id'] = 15;
            $trans_data['transaction_from'] = $data['from'];
            $trans_data['transaction_to'] = $data['to'];
            $trans_data['amount'] = $data['amount'];
            $trans_data['remark'] = $data['comment'];
            $trans_data['transaction_date'] = $data['date'];

            $cash_transactions_id = $this->addNewTransaction($trans_data);

            $pocketData = array();
            $pocketData['user_id'] = $data['to'];
            $pocketData['amount'] = $data['amount'];
            $pocketData['cash_transactions_id'] = $cash_transactions_id;
            $pocketData['approval_status'] = 2;
            $pocketData['added_by'] = $this->thisUserId;

            $credit_pocket_history_id = $this->user->updateUserPocket($pocketData);

            //Send Sms to notify user
            $senderUserDetail = $this->user->getUser($data['from']);
            $receiverUserDetail = $this->user->getUser($data['to']);
            if($receiverUserDetail) {
                $username = $receiverUserDetail['username'];
                $telephone = $receiverUserDetail['telephone'];
                $msg = 'Dear ' . $username . ', Your pocket credited with Rs. ' . number_format($data['amount'], 0) . ' by ' . $senderUserDetail['username'] . '. Your current pocket balance is Rs. ' . number_format($receiverUserDetail['pocket'], 0);
                $this->sendSMS($telephone, $msg);
            }
            /*------------------------- Credit Internal Pocket [End] ---------------------*/


        } else {
            return false;
        }
    }

    public function cashboxToInternalPocketTransfer($data = array()) {
        if($data) {
            $cash_transactions_id = 0;

            $cash_transaction_master_id = $this->generateMasterTransactionId(0, 1);

            /*------------------------- CashBox Debit [Start] ---------------------*/
            $trans_data = array();
            $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
            $trans_data['flow_type'] = 1;
            $trans_data['transaction_type'] = $data['to_type'];
            $trans_data['flow_type_id'] = 22;
            $trans_data['transaction_from'] = $data['from'];
            $trans_data['transaction_to'] = $data['to'];
            $trans_data['amount'] = $data['amount'];
            $trans_data['remark'] = $data['comment'];
            $trans_data['transaction_date'] = $data['date'];

            $cash_transactions_id = $this->addNewTransaction($trans_data);

            $cashboxData = array();
            $cashboxData['amount'] = (-1 * $data['amount']);
            $cashboxData['cash_transactions_id'] = $cash_transactions_id;
            $cashboxData['transaction_type'] = $data['to_type'];
            $cashboxData['approval_status'] = 2;
            $cashboxData['added_by'] = $this->thisUserId;

            $debit_pocket_history_id = $this->updateCashBox($cashboxData);
            /*------------------------- CashBox Debit [End] ---------------------*/

            /*------------------------- Credit Internal Pocket [Start] ---------------------*/
            $trans_data = array();
            $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
            $trans_data['flow_type'] = 0;
            $trans_data['transaction_type'] = $data['to_type'];
            $trans_data['flow_type_id'] = 22;
            $trans_data['transaction_from'] = $data['from'];
            $trans_data['transaction_to'] = $data['to'];
            $trans_data['amount'] = $data['amount'];
            $trans_data['remark'] = $data['comment'];
            $trans_data['transaction_date'] = $data['date'];

            $cash_transactions_id = $this->addNewTransaction($trans_data);

            $pocketData = array();
            $pocketData['user_id'] = $data['to'];
            $pocketData['amount'] = $data['amount'];
            $pocketData['cash_transactions_id'] = $cash_transactions_id;
            $pocketData['transaction_type'] = $data['to_type'];
            $pocketData['approval_status'] = 2;
            $pocketData['added_by'] = $this->thisUserId;

            $credit_pocket_history_id = $this->user->updateUserPocket($pocketData);

            //Send Sms to notify user
            $senderUserDetail = array(
                'user_id'   =>  $this->container->cashbox_id,
                'username'  =>  'CashBox'
            );
            $receiverUserDetail = $this->user->getUser($data['to']);
            if($receiverUserDetail) {
                $username = $receiverUserDetail['username'];
                $telephone = $receiverUserDetail['telephone'];
                $msg = 'Dear ' . $username . ', Your pocket credited with Rs. ' . number_format($data['amount'], 0) . ' by ' . $senderUserDetail['username'] . '. Your current pocket balance is Rs. ' . number_format($receiverUserDetail['pocket'], 0);
                $this->sendSMS($telephone, $msg);
            }
            /*------------------------- Credit Internal Pocket [End] ---------------------*/

        } else {
            return false;
        }
    }

    public function internalPocketToCashboxTransfer($data = array()) {
        if($data) {
            $cash_transactions_id = 0;

            $cash_transaction_master_id = $this->generateMasterTransactionId(0, 1);

            /*------------------------- Debit Internal Pocket [Start] ---------------------*/
            $trans_data = array();
            $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
            $trans_data['flow_type'] = 1;
            $trans_data['transaction_type'] = $data['to_type'];
            $trans_data['flow_type_id'] = 18;
            $trans_data['transaction_from'] = $data['from'];
            $trans_data['transaction_to'] = $data['to'];
            $trans_data['amount'] = $data['amount'];
            $trans_data['remark'] = $data['comment'];
            $trans_data['transaction_date'] = $data['date'];

            $cash_transactions_id = $this->addNewTransaction($trans_data);

            $pocketData = array();
            $pocketData['user_id'] = $data['from'];
            $pocketData['amount'] = (-1 * $data['amount']);
            $pocketData['cash_transactions_id'] = $cash_transactions_id;
            $pocketData['transaction_type'] = $data['to_type'];
            $pocketData['approval_status'] = 2;
            $pocketData['added_by'] = $this->thisUserId;

            $debit_pocket_history_id = $this->user->updateUserPocket($pocketData);
            /*------------------------- Debit Internal Pocket [End] ---------------------*/

            /*------------------------- CashBox Credit [Start] ---------------------*/
            $trans_data = array();
            $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
            $trans_data['flow_type'] = 0;
            $trans_data['transaction_type'] = $data['to_type'];
            $trans_data['flow_type_id'] = 18;
            $trans_data['transaction_from'] = $data['from'];
            $trans_data['transaction_to'] = $data['to'];
            $trans_data['amount'] = $data['amount'];
            $trans_data['remark'] = $data['comment'];
            $trans_data['transaction_date'] = $data['date'];

            $cash_transactions_id = $this->addNewTransaction($trans_data);

            $cashboxData = array();
            $cashboxData['amount'] = $data['amount'];
            $cashboxData['cash_transactions_id'] = $cash_transactions_id;
            $cashboxData['transaction_type'] = $data['to_type'];
            $cashboxData['approval_status'] = 2;
            $cashboxData['added_by'] = $this->thisUserId;

            $credit_pocket_history_id = $this->updateCashBox($cashboxData);
            /*------------------------- CashBox Credit [End] ---------------------*/

        } else {
            return false;
        }
    }

    public function internalPocketToExternalPocketTransfer($data = array()) {
        if($data) {
            $cash_transactions_id = 0;
            $approval_status = 1;

            if(!$this->approvalRequired) {
            	$cash_transaction_master_id = $this->generateMasterTransactionId(0, 1);
                $approval_status = 2;
            }

            /*------------------------- Debit Internal Pocket [Start] ---------------------*/
            if(!$this->approvalRequired) {
                $trans_data = array();
                $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
                $trans_data['flow_type'] = 1;
                $trans_data['transaction_type'] = $data['to_type'];
                $trans_data['flow_type_id'] = 20;
                $trans_data['transaction_from'] = $data['from'];
                $trans_data['transaction_to'] = $data['to'];
                $trans_data['amount'] = $data['amount'];
                $trans_data['remark'] = $data['comment'];
                $trans_data['transaction_date'] = $data['date'];

                $cash_transactions_id = $this->addNewTransaction($trans_data);
            }

            $pocketData = array();
            $pocketData['user_id'] = $data['from'];
            $pocketData['amount'] = (-1 * $data['amount']);
            $pocketData['cash_transactions_id'] = $cash_transactions_id;
            $pocketData['transaction_type'] = $data['to_type'];
            $pocketData['approval_status'] = $approval_status;
            $pocketData['added_by'] = $this->thisUserId;

            $debit_pocket_history_id = $this->user->updateUserPocket($pocketData);

            /*------------------------- Debit Internal Pocket [End] ---------------------*/

            /*------------------------- Credit External Pocket [Start] ---------------------*/
            if(!$this->approvalRequired) {
                $trans_data = array();
                $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
                $trans_data['flow_type'] = 0;                
                $trans_data['transaction_type'] = $data['to_type'];
                $trans_data['flow_type_id'] = 20;
                $trans_data['transaction_from'] = $data['from'];
                $trans_data['transaction_to'] = $data['to'];
                $trans_data['amount'] = $data['amount'];
                $trans_data['remark'] = $data['comment'];
                $trans_data['transaction_date'] = $data['date'];

                $cash_transactions_id = $this->addNewTransaction($trans_data);

                $pocketData = array();
                $pocketData['distributor_id'] = $data['to'];
                $pocketData['amount'] = $data['amount'];
                $pocketData['cash_transactions_id'] = $cash_transactions_id;
                $pocketData['transaction_type'] = $data['to_type'];
                $pocketData['transaction_from'] = $data['from'];
                $pocketData['approval_status'] = 2;
                $pocketData['added_by'] = $this->thisUserId;

                $credit_pocket_history_id = $this->distributor->updateDistributorPocket($pocketData);

                //Send Sms to notify distributor
                //$this->sendSMS();
            }
            /*------------------------- Credit External Pocket [End] ---------------------*/

            if($this->approvalRequired) {
                $approvalData = array();
                $approvalData['pocket_history_id'] = $debit_pocket_history_id;
                $approvalData['transaction_from'] = $data['from'];
                $approvalData['transaction_to'] = $data['to'];
                $approvalData['transaction_type'] = $data['to_type'];
                $approvalData['amount'] = $data['amount'];
                $approvalData['transaction_date'] = $data['date'];
                $approvalData['data'] = $data;
                $approvalData['comment'] = $data['comment'];
                $this->addForApproval($approvalData);
            }

        } else {
            return false;
        }
    }

    public function internalPocketExpense($data = array()) {
        if($data) {
            $cash_transactions_id = 0;
            $approval_status = 1;

            if(!$this->approvalRequired) {
            	$cash_transaction_master_id = $this->generateMasterTransactionId(0, 1);
                $approval_status = 2;
            }

            /*------------------------- Debit Internal Pocket [Start] ---------------------*/
            if(!$this->approvalRequired) {
                $trans_data = array();
                $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
                $trans_data['flow_type'] = 1;
                $trans_data['transaction_type'] = $data['to_type'];
                $trans_data['flow_type_id'] = 1;
                $trans_data['transaction_from'] = $data['from'];
                $trans_data['transaction_to'] = $data['to'];
                $trans_data['amount'] = $data['amount'];
                $trans_data['remark'] = $data['comment'];
                $trans_data['transaction_date'] = $data['date'];

                $cash_transactions_id = $this->addNewTransaction($trans_data);
            }

            $pocketData = array();
            $pocketData['user_id'] = $data['from'];
            $pocketData['amount'] = (-1 * $data['amount']);
            $pocketData['cash_transactions_id'] = $cash_transactions_id;
            $pocketData['transaction_type'] = $data['to_type'];
            $pocketData['approval_status'] = $approval_status;
            $pocketData['added_by'] = $this->thisUserId;

            $debit_pocket_history_id = $this->user->updateUserPocket($pocketData);

            /*------------------------- Debit Internal Pocket [End] ---------------------*/

            /*------------------------- Record Pocket Expense [Start] ---------------------*/
            if(!$this->approvalRequired) {
                $trans_data = array();
                $trans_data['cash_transaction_master_id'] = $cash_transaction_master_id;
                $trans_data['flow_type'] = 0;
                $trans_data['transaction_type'] = $data['to_type'];
                $trans_data['flow_type_id'] = 1;
                $trans_data['transaction_from'] = $data['from'];
                $trans_data['transaction_to'] = $data['to'];
                $trans_data['amount'] = $data['amount'];
                $trans_data['remark'] = $data['comment'];
                $trans_data['transaction_date'] = $data['date'];

                $cash_transactions_id = $this->addNewTransaction($trans_data);

                $expenseData = array();
                $expenseData['user_id'] = $data['from'];
                $expenseData['cash_transactions_id'] = $cash_transactions_id;
                $expenseData['transaction_type'] = $data['to_type'];
                $expenseData['cash_manager_app_to_options_id'] = $data['to'];
                if(isset($data['to_description_id'])) {
                    $expenseData['cash_manager_app_to_options_variant_id'] = $data['to_description_id'];
                } else {
                    $expenseData['cash_manager_app_to_options_variant_id'] = 0;
                }
                $expenseData['amount'] = $data['amount'];
                $expenseData['transaction_date'] = $data['date'];
                $expenseData['added_by'] = $this->thisUserId;

                $user_expense_id = $this->addNewExpense($expenseData);                
            }
            /*------------------------- Record Pocket Expense [End] ---------------------*/

            if($this->approvalRequired) {
                $approvalData = array();
                $approvalData['pocket_history_id'] = $debit_pocket_history_id;
                $approvalData['transaction_from'] = $data['from'];
                $approvalData['transaction_to'] = $data['to'];
                $approvalData['transaction_type'] = $data['to_type'];
                $approvalData['amount'] = $data['amount'];
                $approvalData['transaction_date'] = $data['date'];
                $approvalData['data'] = $data;
                $approvalData['comment'] = $data['comment'];
                $this->addForApproval($approvalData);
            }

        } else {
            return false;
        }
    }

    public function addNewTransaction($data = array()) {
        if($data) {

        	if(isset($data['cash_transaction_master_id'])) {
        		$cash_transaction_master_id = $data['cash_transaction_master_id'];
        	} else {
        		$cash_transaction_master_id = 0;
        	}

            $sql = "INSERT INTO `" . DB_PREFIX . "cash_transactions_record` SET";

            $sql .= " cash_transaction_master_id = '" . (int)$cash_transaction_master_id . "'";
            $sql .= ", flow_type = '" . (int)$data['flow_type'] . "'";
            $sql .= ", transaction_type = '" . (int)$data['transaction_type'] . "'";
            $sql .= ", flow_type_id = '" . (int)$data['flow_type_id'] . "'";
            $sql .= ", transaction_from = '" . (int)$data['transaction_from'] . "'";
            $sql .= ", transaction_to = '" . (int)$data['transaction_to'] . "'";
            $sql .= ", amount = '" . $data['amount'] . "'";
            $sql .= ", remark = '" . $this->container->db->escape($data['remark']) . "'";
            $sql .= ", transaction_date = '" . $data['transaction_date'] . "'";
            $sql .= ", user_id = '" . (int)$this->thisUserId . "'";
            $sql .= ", real_date = '" . REAL_TIME . "'";

            $this->container->db->query($sql);

            $cash_transactions_id = $this->container->db->getLastId();

            return $cash_transactions_id;

        } else {
            return false;
        }
    }

    public function addForApproval($data = array()) {
        if($data) {

            $sql = "INSERT INTO `" . DB_PREFIX . "cash_transaction_approval` SET";
            $sql .= " pocket_history_id = '" . (int)$data['pocket_history_id'] . "'";
            $sql .= ", transaction_from = '" . (int)$data['transaction_from'] . "'";
            $sql .= ", transaction_to = '" . (int)$data['transaction_to'] . "'";
            $sql .= ", transaction_type = '" . (int)$data['transaction_type'] . "'";
            $sql .= ", amount = '" . $data['amount'] . "'";
            $sql .= ", transaction_date = '" . $data['transaction_date'] . "'";
            $sql .= ", data = '" . serialize($data['data']) . "'";
            $sql .= ", comment = '" . $this->container->db->escape($data['comment']) . "'";
            $sql .= ", user_id = '" . $this->thisUserId . "'";
            $sql .= ", date_added = '" . REAL_TIME . "'";
            $sql .= ", date_modified = '" . REAL_TIME . "'";

            $this->container->db->query($sql);

        } else {
            return false;
        }
    }

    public function addNewExpense($data = array()) {
        if($data) {

            $sql = "INSERT INTO `" . DB_PREFIX . "user_expenses` SET";
            $sql .= " user_id = '" . (int)$data['user_id'] . "'";
            $sql .= ", cash_transactions_id = '" . (int)$data['cash_transactions_id'] . "'";
            $sql .= ", transaction_type = '" . (int)$data['transaction_type'] . "'";
            $sql .= ", cash_manager_app_to_options_id = '" . (int)$data['cash_manager_app_to_options_id'] . "'";
            $sql .= ", cash_manager_app_to_options_variant_id = '" . (int)$data['cash_manager_app_to_options_variant_id'] . "'";
            $sql .= ", amount = '" . $data['amount'] . "'";
            $sql .= ", transaction_date = '" . $data['transaction_date'] . "'";
            $sql .= ", added_by = '" . (int)$data['added_by'] . "'";
            $sql .= ", date_added = '" . REAL_TIME . "'";

            $this->container->db->query($sql);

            return $this->container->db->getLastId();

        } else {
            return false;
        }
    }

    public function generateMasterTransactionId($referrence_id = 0, $cash_transaction_from_type = 0) {
    	$sql = 'INSERT INTO `' . DB_PREFIX . "cash_transaction_master_record` SET cash_transaction_from_type = '" . (int)$cash_transaction_from_type . "', referrence_id = '" . $referrence_id . "', user_id = '" . (int)$this->container->user['user_id'] . "', date_added = '" . REAL_TIME . "'";
    	$this->container->db->query($sql);

    	return $this->container->db->getLastId();
    }

    public function sendSMS($telephone, $msg) {
        $mobileNumber = $telephone;
        $senderId = "TLMILL";
        $message = urlencode($msg);
        $route = "4";
        $sms = new \App\Library\Sms;
        $sms->setSender($senderId);
        $sms->setMobile($mobileNumber);
        $sms->setRoute($route);
        $sms->setMessage($message);
        $sms->sendSMS();
    }

    public function getCashBox() {
        $sql = "SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = 'tailmill_cash_box'";
        $query = $this->container->db->query($sql);
        if($query->row) {
            return $query->row['value'];
        } else {
            return 0;
        }
    }

    public function updateCashBox($data = array()) {
        if($data) {

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
            if(isset($data['to_type'])) {
                $transaction_type = (int)$data['to_type'];
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

            if($amount != 0) {

                $current_cashbox_value = $this->getCashBox();
                $new_cashbox_value = $current_cashbox_value + $amount;

                $sql = "UPDATE `" . DB_PREFIX . "setting` SET value = '" . $new_cashbox_value . "' WHERE `key` = 'tailmill_cash_box'";
                $this->container->db->query($sql);

                $sql = "INSERT INTO `" . DB_PREFIX . "user_pocket_history` SET user_id = '" . $this->container->cashbox_id . "', cash_transactions_id = '" . $cash_transactions_id . "', transaction_type = '" . (int)$transaction_type . "', previous_amount = '" . $current_cashbox_value . "', amount = '" . $amount . "', balance_amount = '" . $new_cashbox_value . "', approval_status = '" . $approval_status . "', added_by = '" . $added_by . "', date_added = '" . REAL_TIME . "'";

                $this->container->db->query($sql);

                return $this->container->db->getLastId();

            } else {
                return false;
            }

        }
    }

}