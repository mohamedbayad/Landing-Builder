<?php

return [
    'categories' => [
        'element',
        'integration',
        'enhancement',
        'template',
    ],

    'permissions' => [
        'analytics',
        'tracking',
        'storage',
        'http',
        'forms',
        'payments',
        'crm',
        'templates',
        'ui',
    ],

    'hooks' => [
        'editor.init',
        'editor.ready',
        'editor.destroy',
        'page.create',
        'page.load',
        'page.save',
        'page.publish',
        'page.render',
        'element.add',
        'element.remove',
        'element.update',
        'element.select',
        'element.click',
        'inspector.open',
        'inspector.update',
        'inspector.close',
        'toolbar.register',
        'panel.register',
        'menu.register',
        'form.submit',
        'form.validate',
        'form.success',
        'form.error',
        'user.login',
        'user.save_page',
        'user.publish_page',
    ],

    'rate_limits' => [
        'http_requests_per_minute' => 100,
        'storage_ops_per_minute' => 1000,
    ],
];

