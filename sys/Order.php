<?php

/**
 * Created by PhpStorm.
 * User: karmadon
 * Date: 27.01.16
 * Time: 14:09
 */
class Order
{
    public
        $htmlHeader = '',
        $output = '';

    private
        $id,
        $userID,
        $clientID,
        $clientName,
        $typeID,
        $typeName,
        $typeColor,
        $typeInSum,
        $typeOutSum,
        $speedID,
        $speedName,
        $speedColor,
        $speedInSum,
        $speedOutSum,
        $stateID,
        $stateName,
        $stateColor,
        $optionsID,
        $optionsName,
        $optionsInSum,
        $optionsOutSum,
        $dateIncome,
        $dateEnd,
        $dateClose,
        $totalIn,
        $totalOut,
        $objectFilled,
        $action,
        $postData;

    public function __construct($action, $postData)
    {
        $this->userID = 1;                  /// WARNING

        $this->objectFilled = 0;
        $this->action = $action;
        $this->postData = $postData;

        $this->getAction($action);
    }

    private function getAction($action)
    {
        switch ($action) {
            case 'new': {
                $this->newRecordHandler();
                break;
            }
            case 'list': {
                $this->listRecordHandler();
                break;
            }
            case 'view': {
                $this->viewRecordHandler();
                break;
            }
            default: {

                //Echo "<script> location.replace('/dashboard/view/'); </script>";
            }
        }
    }

    private function newRecordHandler()
    {

        if (!$this->postData) {
            $this->htmlHeader = 'Новый заказ';
            $this->output = $this->showForm();
        } else {
            $this->htmlHeader = 'Заказ добавлен';
            echo 'hello from daata';
        }
    }

    private function listRecordHandler()
    {

        if (!$this->postData) {
            $this->htmlHeader = 'Все заказы';
            $this->output = $this->showOrders();
        } else {
            $this->htmlHeader = 'Фильтрованные заказы';
            echo 'hello from daata';
        }
    }

    private function viewRecordHandler()
    {

        if (!$this->postData) {
            $this->htmlHeader = 'Все заказы';
            //$this->output = $this->showOrders();
        } else {
            $this->htmlHeader = 'Данные о заказе';
            echo 'hello from daata';
        }
    }

    private function showForm()
    {
        $html = <<<HTML
<div class="panel panel-success">
    <div class="panel-heading">
        Данные нового заказа
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="container">
                <div class="stepwizard ">
                    <div class="stepwizard-row setup-panel">
                        <div class="stepwizard-step">
                            <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
                            <p>Данные клиента</p>
                        </div>
                        <div class="stepwizard-step">
                            <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
                            <p>Данные заказа</p>
                        </div>
                        <div class="stepwizard-step">
                            <a href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
                            <p>Дополнительно</p>
                        </div>
                    </div>
                </div>

                <form role="form" action="" method="post" id="form" name="formmae">
                    <div class="row setup-content" id="step-1">

                        <div class="col-md-12">
                            <h3> Данные клиента</h3>
                            <div class="form-group has-success">
                                <label>Имя клиента</label>
                                <input class="form-control" maxlength="200" type="text" required="required"
                                       placeholder="Имя, Фамилия, год рождения итд" name="clientName" id="name">
                                <p class="help-block">Так будет отодражаться везде и поиск тоже.</p>
                            </div>

                            <div class="form-group">
                                <label>КонтрАгент</label>
                                <div class="form-inline">
                                    <select class="form-control" name="clientParent" id="parent">
                                        <option value="1">"Пингвин"</option>
                                        <option value="2">"АЛАнТур"</option>
                                        <option value="3">Клиент с "Улицы"</option>
                                        <option value="4">Московский Приходской цирк благородных девиц</option>
                                    </select>
                                    <button class="form-control">Новый контрагент</button>
                                    <p class="help-block">Нужно выбрать "от кого этот клиент".</p>
                                </div>

                            </div>

                            <button class="btn btn-primary nextBtn btn-lg pull-right" type="button">Далее</button>
                        </div>

                    </div>
                    <div class="row setup-content" id="step-2">

                        <div class="col-md-12">
                            <h3> Данные документа</h3>

                            <div class="form-group">
                                <label>Документ</label>

                                <select class="form-control" name="docType" id="type">
                                    <option value="1">Взорслый загранпаспорт</option>
                                    <option value="2">Детский Загранпаспорт</option>
                                    <option value="3">Взорслый БИО загранпаспорт</option>
                                    <option value="4">Детский БИО Загранпаспорт</option>
                                </select>

                                <p class="help-block">Выберите тип документа.</p>
                            </div>

                            <div class="form-group">
                                <label>Срочность</label>

                                <select class="form-control" name="docSpeed" id="speed">
                                    <option value="1">СРОЧНЫЙ</option>
                                    <option value="2">Не срочный</option>

                                </select>

                                <p class="help-block">Выберите тип документа.</p>
                            </div>


                            <button class="btn btn-primary nextBtn btn-lg pull-right" type="button">Далее</button>
                        </div>

                    </div>
                    <div class="row setup-content" id="step-3">

                        <div class="col-md-12">
                            <h3> Дополнительные данные</h3>


                            <div class="form-group has-warning">
                                <label>Номер Телефона</label>
                                <input class="form-control" placeholder="+38 (000) 000-00-00" name="clientPhone"
                                       id="phone">
                                <p class="help-block">Это не обязательно если клиента ведет контрАгент.</p>
                            </div>

                            <div class="form-group">
                                <label>Дополнительные опции</label>

                                <select class="form-control" name="docOptions" id="options">
                                    <option value="1">Нет дополонительных опций</option>
                                    <option value="2">Второй действующий</option>
                                    <option value="3">Второй действующий СРОЧНЫЙ</option>

                                </select>

                                <p class="help-block">Выберите тип документа.</p>
                            </div>


                            <button class="btn btn-success btn-lg pull-right" type="submit">Готово</button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
<!-- /.col-lg-12 -->

HTML;
        return $html;
    }

    private function showOrders()
    {

        $db_object = new Db();
        $sqlQuery = "SELECT ID from orders WHERE USER_ID = '$this->userID'";

        $db_object->query($sqlQuery);

        $tableRow = '';

        foreach ($db_object->last_result as $new) {
            $tableRow .= $this->createHTMLTableRow($new->ID);
        }


        $html = <<<HTML

  <table class="table">
    <thead>
      <tr>
        <th>№</th>
        <th>Дата</th>
        <th>Клиент</th>
        <th>Тип документа</th>
        <th>Срочность</th>
        <th>Состояние</th>
        <th>Дополнительно</th>
        <th>Сообщения</th>
        <th>Деньги</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
$tableRow
    </tbody>
  </table>
</div>

HTML;

        return $html;

    }

    private function showOrderDetail()
    {

        $db_object = new Db();
        $sqlQuery = "SELECT ID from orders WHERE USER_ID = '$this->userID'";

        $db_object->query($sqlQuery);

        $tableRow = '';

        foreach ($db_object->last_result as $new) {
            $tableRow .= $this->createHTMLTableRow($new->ID);
        }


        $html = <<<HTML

  <table class="table">
    <thead>
      <tr>
        <th>№</th>
        <th>Дата</th>
        <th>Клиент</th>
        <th>Тип документа</th>
        <th>Срочность</th>
        <th>Состояние</th>
        <th>Дополнительно</th>
        <th>Сообщения</th>
        <th>Деньги</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
$tableRow
    </tbody>
  </table>
</div>

HTML;

        return $html;

    }

    private function createHTMLTableRow($orderID)
    {
        $this->fillObject($orderID);

        $tableRow = '
        <tr class="' . $this->stateColor . '">
        <td>' . $this->id . '</td>
        <td>' . $this->dateIncome . '</td>
        <td>' . $this->clientName . '</td>
        <td><span class="label label-' . $this->typeColor . '">' . $this->typeName . '</span></td>
        <td><span class="label label-' . $this->speedColor . '">' . $this->speedName . '</span></td>
        <td><span class="label label-' . $this->stateColor . '">' . $this->stateName . '</span></td>
        <td>' . $this->optionsName . '</td>
        <td><span class="label label-' . $this->stateColor . '"><a  data-toggle="modal" data-target="#myModal-' . $this->id . '">0</a></span></td>
        <td>' . $this->totalIn . '/' . $this->totalOut . ' Грн.</td>
        <td>' . $this->totalOut . '</td>
        </tr>';


        return $tableRow;

    }

    private function fillObject($orderID)
    {
        $db_object = new Db();

        $sqlQuery = "SELECT
  ord.ID           AS ORDER_ID,
  ord.INCLOME_DATE AS INCOME_DATE,
  ord.END_DATE AS END_DATE,
  ord.CLOSE_DATE AS CLOSE_DATE,
  cli.CLIENT_NAME  AS CLIENT,
  cli.ID  AS CLIENT_ID,
  typ.ID    AS TYPE_ID,
  typ.TYPE_NAME    AS TYPE,
  typ.TYPE_COLOR   AS TYPE_COLOR,
  spd.ID   AS SPEED_ID,
  spd.SPEED_NAME   AS SPEED,
  spd.SPEED_COLOR  AS SPEED_COLOR,
  stt.ID   AS STATE_ID,
  stt.STATE_NAME   AS STATE,
  stt.STATE_COLOR  AS STATE_COLOR,
  opt.ID     AS OPTIONS_ID,
  opt.OPT_NAME     AS OPTIONS,
    typ.TYPE_IN_SUM   AS TYPE_IN_SUM,
  typ.TYPE_OUT_SUM   AS TYPE_OUT_SUM,
  spd.SPEED_ADD_IN_SUM   AS SPEED_ADD_IN_SUM,
  spd.SPEED_ADD_OUT_SUM   AS SPEED_ADD_OUT_SUM,
  opt.OPT_IN_SUM     AS OPT_IN_SUM,
  opt.OPT_OUT_SUM     AS OPT_OUT_SUM,
  (typ.TYPE_IN_SUM +spd.SPEED_ADD_IN_SUM+opt.OPT_IN_SUM) AS TOTAL_IN,
  (typ.TYPE_OUT_SUM +spd.SPEED_ADD_OUT_SUM+opt.OPT_OUT_SUM) AS TOTAL_OUT
FROM orders ord
  INNER JOIN (SELECT
                ID,
                CLIENT_NAME
              FROM clients
              WHERE USER_ID = '$this->userID') cli ON ord.CLIENT_ID = cli.ID
  INNER JOIN (SELECT
                ID,
                TYPE_NAME,
                TYPE_COLOR,
                                TYPE_IN_SUM,
                TYPE_OUT_SUM
              FROM order_type
              WHERE USER_ID = '$this->userID') typ ON ord.ORDER_TYPE_ID = typ.ID
  INNER JOIN (SELECT
                ID,
                SPEED_NAME,
                SPEED_COLOR,
                  SPEED_ADD_IN_SUM,
                SPEED_ADD_OUT_SUM
              FROM order_speed
              WHERE USER_ID = '$this->userID') spd ON ord.ORDER_SPEED_ID = spd.ID
  INNER JOIN (SELECT
                ID,
                STATE_NAME,
                STATE_COLOR
              FROM order_state
              WHERE USER_ID = '$this->userID') stt ON ord.ORDER_STATE_ID = stt.ID
  INNER JOIN (SELECT
                ID,
                OPT_NAME,
                OPT_IN_SUM,
                OPT_OUT_SUM
              FROM order_options
              WHERE USER_ID = '$this->userID') opt ON ord.ORDER_OPTIONS_ID = opt.ID
WHERE ord.USER_ID = '$this->userID' AND ord.ID = '$orderID'";

        $db_object->query($sqlQuery);


        $result = (array)$db_object->last_result[0];

        $this->id = $result['ORDER_ID'];
        $this->clientID = $result['CLIENT_ID'];
        $this->clientName = $result['CLIENT'];
        $this->typeID = $result['TYPE_ID'];
        $this->typeName = $result['TYPE'];
        $this->typeColor = $result['TYPE_COLOR'];
        $this->typeInSum = $result['TYPE_IN_SUM'];
        $this->typeOutSum = $result['TYPE_OUT_SUM'];
        $this->speedID = $result['SPEED_ID'];
        $this->speedName = $result['SPEED'];
        $this->speedColor = $result['SPEED_COLOR'];
        $this->speedInSum = $result['SPEED_ADD_IN_SUM'];
        $this->speedOutSum = $result['SPEED_ADD_OUT_SUM'];
        $this->stateID = $result['STATE_ID'];
        $this->stateName = $result['STATE'];
        $this->stateColor = $result['STATE_COLOR'];
        $this->optionsID = $result['OPTIONS_ID'];
        $this->optionsName = $result['OPTIONS'];
        $this->optionsInSum = $result['OPT_IN_SUM'];
        $this->optionsOutSum = $result['OPT_OUT_SUM'];
        $this->dateIncome = $result['INCOME_DATE'];
        $this->dateEnd = $result['END_DATE'];
        $this->dateClose = $result['CLOSE_DATE'];
        $this->totalIn = $result['TOTAL_IN'];
        $this->totalOut = $result['TOTAL_OUT'];
        $this->objectFilled = 1;

        return;

    }


}