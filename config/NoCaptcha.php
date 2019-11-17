<?php

return [
	'secret'            => env('NOCAPTCHA_SECRET'),
	'sitekey'           => env('NOCAPTCHA_SITEKEY'),
	'server-get-config' => TRUE,
	'options'           => [
		'timeout' => 30,
	],
];
