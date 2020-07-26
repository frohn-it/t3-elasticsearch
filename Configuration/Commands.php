<?php
declare(strict_types=1);

return [
    'elastic:list' => [
        'class' => \BeFlo\T3Elasticsearch\Command\ListCommand::class,
    ],
    'elastic:index:start' => [
        'class' => \BeFlo\T3Elasticsearch\Command\IndexStartCommand::class,
    ],
    'elastic:index:create' => [
        'class' => \BeFlo\T3Elasticsearch\Command\IndexCreateCommand::class,
    ],
];
