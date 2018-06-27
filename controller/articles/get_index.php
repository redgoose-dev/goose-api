<?php
namespace Core;

if (!defined('__GOOSE__')) exit();

/**
 * get articles
 *
 * url params
 * - @param int nest
 *
 * @var Goose $this
 */

// set where
$where = '';

// output
Controller::index($this, 'article', $where);
