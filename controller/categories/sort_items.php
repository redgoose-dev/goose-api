<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * sort turn category
 *
 * @var Goose $this
 */

try
{
  // check post values
  Util::checkExistValue($_POST, [ 'nest_srl', 'srls' ]);

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem((object)[
    'model' => $this->model,
    'table' => 'nests',
    'srl' => (int)$_POST['nest_srl'],
  ]);

  // set srls
  $srls = explode(',', $_POST['srls']);

  // update db
  foreach ($srls as $k=>$v)
  {
    $this->model->edit((object)[
      'table' => 'categories',
      'where' => 'nest_srl='.(int)$_POST['nest_srl'].' and srl='.$v,
      'data' => [ 'turn='.$k ],
    ]);
  }

  // set output
  $output = (object)[];
  $output->code = 200;

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
