<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FrizusRequestFormHelper
{
    public $arResult;

    public function __construct($arResult)
    {
        $this->arResult = &$arResult;
    }

    public function open()
    {
        echo '<form method="POST" enctype="multipart/form-data">';
        echo bitrix_sessid_post();
    }

    public function close()
    {
        echo '</form>';
    }

    public function showCsrfErrors()
    {
        if (isset($this->arResult['ERRORS']['csrf'])) {
            echo '<div class="invalid-feedback d-block csrf-error">' .
                nl2br(htmlspecialcharsbx(implode("\n", $this->arResult['ERRORS']['csrf']))) .
                '</div>';
        }
    }

    public function showMailErrors()
    {
        if (isset($this->arResult['SEND_MAIL_ERRORS'])) {
            echo '<div class="invalid-feedback d-block send-mail-error">' .
                nl2br(htmlspecialcharsbx(implode("\n", $this->arResult['SEND_MAIL_ERRORS']))) .
                '</div>';
        }
    }

    public function text($name, $options)
    {
        $id = 'field-' . str_replace('_', '-', $name);
        $oldValue = $this->arResult['OLD_VALUES'][$name] ?? null;
        $haveErrors = isset($this->arResult['ERRORS'][$name]);

        echo '<div class="form-group">';
        echo '<label for="' . $id . '">' . $options['label'] . '</label>';
        echo '<input 
                type="text"
                name="' . $name . '"
                class="form-control' . ($haveErrors ? ' is-invalid' : '') . '"
                id="' . $id . '"';
        if (isset($oldValue)) {
            $escValue = htmlspecialcharsbx($oldValue, ENT_COMPAT, false);
            echo ' value="' . $escValue . '"';
        }
        if ($options['required'] ?? false) {
            echo ' required';
        }
        echo '>';
        $this->showErrors($name);
        echo '</div>';
    }

    public function showErrors($name, $forceShow = false)
    {
        if (isset($this->arResult['ERRORS'][$name])) {
            echo '<div class="invalid-feedback' . ($forceShow ? ' d-block' : '') . '">' .
                nl2br(htmlspecialcharsbx(implode("\n", $this->arResult['ERRORS'][$name]))) .
                '</div>';
        }
    }

    public function radio($name, $options)
    {
        echo '<div class="mb-2">' . $options['label'] . '</div>';
        echo '<div class="form-group">';

        $haveErrors = isset($this->arResult['ERRORS'][$name]);
        $oldValue = $this->arResult['OLD_VALUES'][$name] ?? null;
        $maxCursor = count($options['values']) - 1;
        foreach ($options['values'] as $cursor => $value) {
            $id = 'field-' . str_replace('_', '-', $name) . '-' . $cursor;
            $escValue = htmlspecialcharsbx($value, ENT_COMPAT, false);
            echo '<div class="form-check">';
            echo '<input
                    class="form-check-input' . ($haveErrors ? ' is-invalid' : '') . '"
                    type="radio"
                    name="' . $name . '"
                    id="' . $id . '"
                    ' . ($haveErrors ? ' class="is-invalid"' : '') . '
                    value="' . $escValue . '"';
            if (isset($oldValue) && ($oldValue === $value)) {
                echo ' checked';
            }
            if ($options['required'] ?? false) {
                echo ' required';
            }
            echo '>';
            echo '<label class="form-check-label" for="' . $id . '">' . $escValue . '</label>';
            if ($cursor === $maxCursor) {
                $this->showErrors($name);
            }
            echo '</div>';
        }
        if (empty($options['values'])) {
            $this->showErrors($name, true);
        }

        echo '</div>';
    }

    public function select($name, $options)
    {
        $id = 'field-' . str_replace('_', '-', $name);
        $oldValue = $this->arResult['OLD_VALUES'][$name] ?? null;
        $haveErrors = isset($this->arResult['ERRORS'][$name]);

        echo '<div class="form-group">';
        echo '<label for="' . $id . '">' . $options['label'] . '</label>';
        echo '<select
                class="form-control' . ($haveErrors ? ' is-invalid' : '') . '" 
                name="' . $name . '"
                id="' . $id . '"';
        if ($options['required'] ?? false) {
            echo ' required';
        }
        echo '>';
        echo '<option value="">' . $options['defaultText'] . '</option>';
        foreach ($options['values'] as $cursor => $value) {
            $escValue = htmlspecialcharsbx($value, ENT_COMPAT, false);

            echo '<option';
            if (isset($oldValue) && ($oldValue === $value)) {
                echo ' selected';
            }
            echo '>';
            echo $escValue . '</option>';
        }
        echo '</select>';
        $this->showErrors($name);
        echo '</div>';
    }

    public function file($name, $options)
    {
        $id = 'field-' . str_replace('_', '-', $name);
        $oldValue = $this->arResult['OLD_VALUES'][$name] ?? null;
        $haveErrors = isset($this->arResult['ERRORS'][$name]);
        $inputName = ($options['multiple'] ?? false) ? ($name . '[]') : $name;

        echo '<div class="form-group">';
        echo '<label for="' . $id . '">' . $options['label'] . '</label>';
        echo '<br>';
        echo '<input 
                type="file"
                name="' . $inputName . '"
                ' . ($haveErrors ? ' class="is-invalid"' : '') . '
                id="' . $id . '"';
        if ($options['multiple'] ?? false) {
            echo ' multiple';
        }
        if ($options['required'] ?? false) {
            echo ' required';
        }
        echo '>';
        $this->showErrors($name);
        echo '</div>';
    }

    public function textarea($name, $options)
    {
        $id = 'field-' . str_replace('_', '-', $name);
        $oldValue = $this->arResult['OLD_VALUES'][$name] ?? null;
        $haveErrors = isset($this->arResult['ERRORS'][$name]);

        echo '<div class="form-group">';
        echo '<label for="' . $id . '">' . $options['label'] . '</label>';
        echo '<textarea
                class="form-control"
                name="' . $name . '"
                ' . ($haveErrors ? ' class="is-invalid"' : '') . '
                id="' . $id . '"';
        if ($options['required'] ?? false) {
            echo ' required';
        }
        echo '>';
        if (isset($oldValue)) {
            $escValue = htmlentities($oldValue, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
            echo "\n" . $escValue;
        }
        echo '</textarea>';
        $this->showErrors($name);
        echo '</div>';
    }

    public function submit($submit)
    {
        echo '<div class="form-group">';
        echo '<input class="btn btn-primary" type="submit" value="' . $submit . '">';
        echo '</div>';
    }
}