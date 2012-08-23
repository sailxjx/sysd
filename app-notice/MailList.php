<?php

/**
 * Document: MailList
 * Created on: 2012-8-22, 16:16:47
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailList extends Base {

	protected function main() {
		print_r(Redis_Key::mailId());
	}

}
