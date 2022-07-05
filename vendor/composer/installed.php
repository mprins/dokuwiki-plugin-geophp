<?php return array(
    'root' => array(
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'type' => 'plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => '38b433d62fd7ed4e56e24355a5d2ec67274233d9',
        'name' => 'mprins/dokuwiki-plugin-geophp',
        'dev' => true,
    ),
    'versions' => array(
        'funiq/geophp' => array(
            'pretty_version' => 'v2.0.0',
            'version' => '2.0.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../funiq/geophp',
            'aliases' => array(),
            'reference' => '42ba83cda286f9b76cf6da2e149e2cb36cefc6e8',
            'dev_requirement' => false,
        ),
        'mprins/dokuwiki-plugin-geophp' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'type' => 'plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => '38b433d62fd7ed4e56e24355a5d2ec67274233d9',
            'dev_requirement' => false,
        ),
        'roave/security-advisories' => array(
            'pretty_version' => 'dev-latest',
            'version' => 'dev-latest',
            'type' => 'metapackage',
            'install_path' => NULL,
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'reference' => '7a8c86df136ffe6bd7bc4655d15629a87e5bd022',
            'dev_requirement' => true,
        ),
    ),
);
