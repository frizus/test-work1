<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Bitrix\Main\Config\Option;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FrizusRequestForm extends CBitrixComponent
{
    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        foreach (['FIELD_CATEGORY', 'FIELD_APPLICATION_TYPE', 'FIELD_STORAGE', 'FIELD_BRAND'] as $paramName) {
            if (!isset($arParams[$paramName]) || !is_array($arParams[$paramName])) {
                $arParams[$paramName] = [];
            } else {
                $new = [];
                foreach ($arParams[$paramName] as $value) {
                    if (is_string($value) && ($value !== '')) {
                        $new[] = $value;
                    }
                }
                $arParams[$paramName] = $new;
            }
        }

        return $arParams;
    }

    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        if (!isset($this->arParams['~RECIPIENT_EMAIL']) ||
            ($this->arParams['~RECIPIENT_EMAIL'] === '')
        ) {
            echo $this->getName() . ': Не указана почта получателя.';
            return;
        }

        $this->arResult['ERRORS'] = [];
        $this->arResult['ORDER_LIST_FIELDS'] = ['brand', 'name', 'quantity', 'packaging', 'client'];

        if ($this->request->getRequestMethod() === 'POST') {
            $this->formSendAction();
        } else {
            $this->showFormAction();
        }
    }

    /**
     * @return void
     */
    protected function formSendAction()
    {
        $this->arResult['OLD_VALUES'] = $this->validateFields();

        if ($this->haveValidationErrors()) {
            $this->showForm();
        } else {
            $this->arResult['SENT'] = $this->sendMail();
            $this->showForm();
        }
    }

    /**
     * @return array
     */
    protected function validateFields()
    {
        if (!check_bitrix_sessid()) {
            $this->arResult['ERRORS']['csrf'][] = 'Отправьте форму заново.';
        }

        $post = $this->request->getPostList()->toArray();
        $files = $this->request->getFileList()->toArray();
        $fields = [];
        foreach (['title', 'category', 'application_type', 'storage', 'order_list', 'comment'] as $fieldName) {
            if (array_key_exists($fieldName, $post)) {
                $fields[$fieldName] = $post[$fieldName];
            }
        }
        if (array_key_exists('file', $files)) {
            $fields['file'] = $files['file'];
        }

        if (!array_key_exists('title', $fields) ||
            !is_string($fields['title']) ||
            ($fields['title'] === '') ||
            (trim($fields['title']) === '')
        ) {
            $this->arResult['ERRORS']['title'][] = 'Поле Заголовок заявки обязательно для заполнения.';
        }

        if (!array_key_exists('category', $fields) ||
            !is_string($fields['category']) ||
            ($fields['category'] === '') ||
            !in_array(htmlspecialcharsback($fields['category']), $this->arParams['~FIELD_CATEGORY'], true)
        ) {
            $fields['category'] = null;
            $this->arResult['ERRORS']['category'][] = 'Поле Категория обязательно для заполнения.';
        }

        if (!array_key_exists('application_type', $fields) ||
            !is_string($fields['application_type']) ||
            ($fields['application_type'] === '') ||
            !in_array(htmlspecialcharsback($fields['application_type']), $this->arParams['~FIELD_APPLICATION_TYPE'], true)
        ) {
            $fields['application_type'] = null;
            $this->arResult['ERRORS']['application_type'][] = 'Поле Вид заявки обязательно для заполнения.';
        }

        if (array_key_exists('storage', $fields) &&
            (
                !is_string($fields['storage']) ||
                (
                    ($fields['storage'] !== '') &&
                    !in_array(htmlspecialcharsback($fields['storage']), $this->arParams['~FIELD_STORAGE'])
                )
            )
        ) {
            $fields['storage'] = null;
            $this->arResult['ERRORS']['storage'][] = 'Некорректное значение поля Склад поставки.';
        }

        if (array_key_exists('order_list', $fields)) {
            $orderListErrorText = 'Некорректное значение Состава заявки.';

            if (!is_array($fields['order_list'])) {
                $this->arResult['ERRORS']['order_list'][] = $orderListErrorText;
            } else {
                $index = 0;
                $reindex = false;
                $fieldsCount = count($this->arResult['ORDER_LIST_FIELDS']);
                foreach ($fields['order_list'] as $cursor => $values) {
                    if ((filter_var($cursor, FILTER_VALIDATE_INT) === false) ||
                        !is_array($values) ||
                        (count($values) !== $fieldsCount)
                    ) {
                        $this->arResult['ERRORS']['order_list'][] = $orderListErrorText;
                        break;
                    }

                    if ($cursor != $index) {
                        $reindex = true;
                    }

                    $notEmpty = false;
                    foreach ($this->arResult['ORDER_LIST_FIELDS'] as $fieldName) {
                        if (!array_key_exists($fieldName, $values) ||
                            !is_string($values[$fieldName])
                        ) {
                            $this->arResult['ERRORS']['order_list'][] = $orderListErrorText;
                            break 2;
                        }

                        if ($fieldName === 'brand') {
                            if (($values[$fieldName] !== '') &&
                                !in_array(htmlspecialcharsback($values[$fieldName]), $this->arParams['~FIELD_BRAND'], true)
                            ) {
                                $this->arResult['ERRORS']['order_list'][] = $orderListErrorText;
                                break 2;
                            }
                        }

                        if ($values[$fieldName] !== '') {
                            $notEmpty = true;
                        }
                    }

                    if (!$notEmpty) {
                        unset($fields['order_list'][$cursor]);
                        $reindex = true;
                    }
                }


                if (isset($this->arResult['ERRORS']['order_list'])) {
                    $fields['order_list'] = null;
                } else {
                    if (empty($fields['order_list'])) {
                        $fields['order_list'] = null;
                    } elseif ($reindex) {
                        $fields['order_list'] = array_values($fields['order_list']);
                    }
                }
            }
        }

        if (array_key_exists('file', $fields) &&
            (
                !is_array($fields['file']) ||
                !is_array($fields['file']['name']) ||
                !is_array($fields['file']['tmp_name'])
            )
        ) {
            $fields['file'] = null;
            $this->arResult['ERRORS']['file'][] = 'Некорректное значение поля файлов.';
        }

        if (array_key_exists('comment', $fields)) {
            if (!is_string($fields['comment'])) {
                $this->arResult['ERRORS']['comment'][] = 'Некорректное значение поля Комментарий.';
            }
        }

        return $fields;
    }

    /**
     * @return bool
     */
    public function haveValidationErrors()
    {
        return !empty($this->arResult['ERRORS']);
    }

    /**
     * @return void
     */
    protected function showForm()
    {
        $this->includeComponentTemplate();
    }

    /**
     * @return bool|void
     */
    protected function sendMail()
    {
        require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            /*
            $mail->IsSMTP();
            $mail->Host = "ssl://smtp.yandex.ru";
            $mail->SMTPAuth = true;
            $mail->Username = '';
            $mail->Password = '';
            $mail->Port = 465;
            */

            $siteName = Option::get('main', 'site_name');
            if (!isset($siteName) || ($siteName === '')) {
                $siteName = $_SERVER['HTTP_HOST'];
            }
            $from = Option::get('main', 'email_from', 'noreply@' . $_SERVER['HTTP_HOST']);

            $mail->setFrom($from, $siteName);
            $mail->addAddress($this->arParams['~RECIPIENT_EMAIL']);

            $files = $this->arResult['OLD_VALUES']['file'];
            if (isset($files) && !empty($files['tmp_name'])) {
                foreach ($files['tmp_name'] as $cursor => $file) {
                    if (is_file($file)) {
                        $mail->addAttachment($file, $files['name'][$cursor]);
                    }
                }
            }

            $mail->isHTML(true);
            $mail->Subject = 'Новая заявка с ' . $siteName;
            $mail->Body = $this->getMailHtml($siteName);

            if (!$mail->send()) {
                $this->arResult['SEND_MAIL_ERRORS'][] = 'Не удалось отправить письмо, попробуйте позже. Если проблема не исчезнет, свяжитесь с администратором сайта.';
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->arResult['SEND_MAIL_ERRORS'][] = 'Не удалось отправить письмо, попробуйте позже. Если проблема не исчезнет, свяжитесь с администратором сайта.';
            $this->arResult['SEND_MAIL_ERRORS'][] = $mail->ErrorInfo;
            return false;
        }
    }

    /**
     * @param $siteName
     * @return string
     */
    protected function getMailHtml($siteName)
    {
        $s = '';
        $s .= '<h1>Новая заявка</h1>';
        $s .= '<p>';
        $s .= '<strong>Заголовок заявки</strong>: ' . $this->arResult['OLD_VALUES']['title'] . '<br>';
        $s .= '<strong>Категория</strong>: ' . $this->arResult['OLD_VALUES']['category'] . '<br>';
        $s .= '<strong>Вид заявки</strong>: ' . $this->arResult['OLD_VALUES']['application_type'] . '<br>';
        $s .= '<strong>Склад поставки</strong>: ';
        if (isset($this->arResult['OLD_VALUES']['storage']) && ($this->arResult['OLD_VALUES']['storage'] !== '')) {
            $s .= $this->arResult['OLD_VALUES']['storage'];
        } else {
            $s .= '<i>не указан</i>';
        }
        $s .= '</p>';

        $s .= '<h2>Состав заявки</h2>';
        if (isset($this->arResult['OLD_VALUES']['order_list'])) {
            $labels = [
                'brand' => 'Бренд',
                'name' => 'Наименование',
                'quantity' => 'Количество',
                'packaging' => 'Фасовка',
                'client' => 'Клиент',
            ];


            $s .= '<table border="1" cellspacing="0" cellpadding="10">';
            $s .= '<thead>';
            $s .= '<tr>';
            foreach ($labels as $label) {
                $s .= '<th>' . $label . '</th>';
            }
            $s .= '</tr>';
            $s .= '</thead>';
            $s .= '<tbody>';
            foreach ($this->arResult['OLD_VALUES']['order_list'] as $values) {
                $s .= '<tr>';
                foreach ($this->arResult['ORDER_LIST_FIELDS'] as $key) {
                    $text = null;
                    if ($key === 'brand') {
                        if ($values[$key] === '') {
                            $text = '<i>Не указан</i>';
                        }
                    }
                    if (!isset($text)) {
                        $text = $values[$key];
                    }
                    $s .= '<td>' . $text . '</td>';
                }
                $s .= '</tr>';
            }
            $s .= '</tbody>';
            $s .= '</table>';
        } else {
            $s .= '<p><i>Не заполнен.</i></p>';
        }

        $s .= '<br>';
        $s .= '<strong>Комментарий</strong>: ';
        if (isset($this->arResult['OLD_VALUES']['comment']) && ($this->arResult['OLD_VALUES']['comment'] !== '')) {
            $s .= nl2br($this->arResult['OLD_VALUES']['comment']);
        } else {
            $s .= '<i>не указан</i>';
        }

        $s .= '</p>';

        $s .= '<br><br><br>';
        $s .= 'Письмо отправлено с ' . $siteName;

        return $s;
    }

    /**
     * @return void
     */
    protected function showFormAction()
    {
        $this->showForm();
    }
}