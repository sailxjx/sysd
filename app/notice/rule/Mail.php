<?php

/**
 * Document: Mail
 * Created on: 2012-8-27, 15:47:13
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Rule_Mail extends Rule_Rule {

	protected function getDelNumRKey() {
		return Redis_Key::mailRedel();
	}

}
