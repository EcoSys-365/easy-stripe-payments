<?php return array(
    'root' => array(
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => null,
        'name' => 'ecosys365/easy-stripe-payments',
        'dev' => true,
    ),
    'versions' => array(
        'ecosys365/easy-stripe-payments' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => null,
            'dev_requirement' => false,
        ),
        'stripe/stripe-php' => array(
            'pretty_version' => 'v18.0.0',
            'version' => '18.0.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'reference' => '70d6c286f0eca002b60ccd62afa7140c43c14bbb',
            'dev_requirement' => false,
        ),
    ),
);
