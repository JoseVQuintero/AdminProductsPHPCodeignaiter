<?php

namespace Config;

use App\Filters\JWTAuthenticationFilter;
use App\Filters\JWTCronAuthenticationFilter;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf' => CSRF::class,
        'toolbar' => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'auth'          => JWTAuthenticationFilter::class,
        'authcron'      => JWTCronAuthenticationFilter::class,
        'isLoggedIn' 	=> \App\Filters\Authentication::class,
		'isGranted' 	=> \App\Filters\Authorization::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array
     */

    public $globals = [
		'before' => [
			//'honeypot',
			//'csrf',
			'isLoggedIn'	=> ['except' => ['/', 'cron/*', 'api/*', 'auth/*', 'authcron/*','getLogin', 'register']],
			'isGranted' 	=> ['except' => ['/', 'cron/*', 'api/*', 'auth/*', 'authcron/*','getLogin', 'register', 'blocked', 'home', 'Welcome/*']],
		],
		'after'  => [
			'toolbar',
			//'honeypot',
		],
	];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['csrf', 'throttle']
     *
     * @var array
     */
    public $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array
     */
    public $filters = [        
        'auth' => [
            'before' => [
                //'client',
                //'manufacturer/*',
                //'manufacturer',
                'pricefile/*',
                'pricefile',
                'storage/*',
                'storage',
            ],
            'after'  => [
                'client',
                // 'honeypot',
            ],
        ]
    ];
}
