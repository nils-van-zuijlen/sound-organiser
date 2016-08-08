<?php

namespace Xif\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class XifUserBundle extends Bundle {
	public function getParent() {
		return 'FOSUserBundle';
	}
}
