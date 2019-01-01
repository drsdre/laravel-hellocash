<?php

namespace drsdre\HelloCash;

use drsdre\HelloCash\Requests\Account;
use drsdre\HelloCash\Requests\Airtime;
use drsdre\HelloCash\Requests\Connection;
use drsdre\HelloCash\Requests\Invoice;
use drsdre\HelloCash\Requests\Transfer;

class HelloCash {
	public function accounts() {
		return new Account();
	}

	public function airtime() {
		return new Airtime();
	}

	public function connections() {
		return new Connection();
	}

	public function invoices() {
		return new Invoice();
	}

	public function transfers() {
		return new Transfer();
	}
}