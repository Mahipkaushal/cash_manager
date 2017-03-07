<?php

namespace App\Library;

class DB {
	private $driver;

	public function __construct($driver) {
		if ($driver) {
			$this->driver = $driver;
		} else {
			exit('Error: Could not load database driver type ' . $driver . '!');
		}
	}

	public function query($sql) {
		return $this->driver->query($sql);
	}

	public function escape($value) {
		return $this->driver->escape($value);
	}

	public function countAffected() {
		return $this->driver->countAffected();
	}

	public function getLastId() {
		return $this->driver->getLastId();
	}
}