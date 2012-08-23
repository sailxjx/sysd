<?php

/**
 * Document: MailList
 * Created on: 2012-8-22, 16:16:47
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailList extends Base {

	protected function main() {
		Queue_Mail::getIns()->wait(10, 100)->error(20, 10)->wait(array(
			1 => 10,
			2 => 10
		))->push();
		print_r(Queue_Mail::getIns());exit;
	}

}
