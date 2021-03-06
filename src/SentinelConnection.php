<?php

namespace pyurin\yii\redisHa;

class SentinelConnection {

	public $hostname;

	public $port;

	public $connectionTimeout;

	public $unixSocket;

	protected $_socket;

	protected function open () {
		if ($this->_socket !== null) {
			return;
		}
		$connection = ($this->unixSocket ?  : $this->hostname . ':' . $this->port);
		\Yii::trace('Opening redis DB connection: ' . $connection, __METHOD__);
		$this->_socket = @stream_socket_client($this->unixSocket ? 'unix://' . $this->unixSocket : 'tcp://' . $this->hostname . ':' . $this->port, $errorNumber, $errorDescription, $this->connectionTimeout ? $this->connectionTimeout : ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);
		if ($this->_socket) {
			return true;
		} else {
			$this->_socket = false;
			return false;
		}
	}

	function getMaster () {
		if ($this->open()) {
			return Helper::executeCommand('sentinel', [
					'get-master-addr-by-name',
					'mymaster'
			], $this->_socket);
		} else {
			return false;
		}
	}
}