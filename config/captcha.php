<?php

return [
	'default' => [
		'length'     => 4,
		'width'	     => 300,
		'height'     => 38,
		'quality'    => 100,
		'angle'      => 12,
		'blur'       => 1,
		'contrast'   => 32,
		'lines'      => 6,
		'bgImage'    => true,
		'bgColor'    => '#000',
		'fontColors' => ['#b74b47', '#f4645f', '#f9b5b2'],
	],
	
	'flat'   => [
		'length'    => 6,
		'width'     => 160,
		'height'    => 46,
		'quality'   => 90,
		'lines'     => 6,
		'bgImage'   => false,
		'bgColor'   => '#ecf2f4',
		'fontColors'=> ['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
		'contrast'  => -5,
	],
	
	'mini'   => [
		'length'    => 3,
		'width'     => 60,
		'height'    => 32,
	],
	
	'inverse'   => [
		'length'    => 5,
		'width'     => 120,
		'height'    => 36,
		'quality'   => 90,
		'sensitive' => true,
		'angle'     => 12,
		'sharpen'   => 10,
		'blur'      => 2,
		'invert'    => true,
		'contrast'  => -5,
	],
];