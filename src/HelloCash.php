<?php

namespace drsdre\HelloCash;

use drsdre\HelloCash\Requests\Account;
use drsdre\HelloCash\Requests\Airtime;
use drsdre\HelloCash\Requests\Connection;
use drsdre\HelloCash\Requests\Invoice;
use drsdre\HelloCash\Requests\Transfer;

class HelloCash {
    final public function accounts(): Account {
		return new Account();
	}

    final public function airtime(): Airtime {
		return new Airtime();
	}

    final public function connections(): Connection {
		return new Connection();
	}

    final public function invoices(): Invoice {
		return new Invoice();
	}

	final public function transfers(): Transfer {
		return new Transfer();
	}
}
