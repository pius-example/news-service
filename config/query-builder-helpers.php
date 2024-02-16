<?php

return [

    /*
     * Here you can change default suffixes which will be appended to the
     * query filter names that are exposed to consumers of your application.
     */
    'suffixes' => [
        'equal' => '',
        'greater' => '_gt',
        'greater_or_equal' => '_gte',
        'less' => '_lt',
        'less_or_equal' => '_lte',
        'contain' => '_like',
        'start_with' => '_llike',
        'end_with' => '_rlike',
        'empty' => '_empty',
        'not' => '_not',
    ],

    /*
     * This option defines which 'like' operator is used for searching queries. Some
     * databases support other operators. For example, if you use Postgresql you
     * may want to change it to ILIKE to allow case-insensitive searches.
     */
    'like_operator' => 'ILIKE',
];
