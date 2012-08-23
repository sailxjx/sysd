<?php

/**
 * Document: MailList
 * Created on: 2012-8-22, 16:16:47
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class MailList extends Base {

	protected function main() {
		Log_Log::getIns()->del(2);
		print_r(Redis_Key::mailid(array('id' => '101a')));
	}

}
