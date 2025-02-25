<?php
if (!defined('ACTIVE')) {
    define('ACTIVE', 1);
}
if (!defined('INACTIVE')) {
    define('INACTIVE', 0);
}
if (!defined('ITEM_PER_PAGE')) {
    define('ITEM_PER_PAGE', 20);
}
if (!defined('DELETED')) {
    define('DELETED', 1);
}
if (!defined('DEFAULT_DELETED_AT')) {
    define('DEFAULT_DELETED_AT', '1999-01-01 00:00:00');
}

if (!defined('SORT_DEFAULT_COLUMN')) {
    define('SORT_DEFAULT_COLUMN', 'updated_at');
}

if (!defined('SORT_DEFAULT_VALUE')) {
    define('SORT_DEFAULT_VALUE', 'DESC');
}
