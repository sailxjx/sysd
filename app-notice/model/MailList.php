<?php

/**
 * Document: MailList
 * Created on: 2012-8-22, 16:26:44
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Model_MailList extends Model_List {

	public function __call($name, $arguments) {
		return true;
	}

}
