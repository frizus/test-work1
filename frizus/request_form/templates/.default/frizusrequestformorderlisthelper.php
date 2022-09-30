<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FrizusRequestFormOrderListHelper
{
    /**
     * @var FrizusRequestFormHelper
     */
    protected $form;

    protected $arResult;

    public function __construct(FrizusRequestFormHelper $form)
    {
        $this->form = $form;
        $this->arResult = &$this->form->arResult;
    }

    public function orderList($name, $options)
    {
        $this->header($name, $options);

        $oldValue = $this->arResult['OLD_VALUES'][$name] ?? null;
        if (isset($oldValue)) {
            foreach ($oldValue as $cursor => $values) {
                $this->row($cursor, $values, $name, $options);
            }
        } else {
            $values = array_combine($this->arResult['ORDER_LIST_FIELDS'], array_fill(0, count($this->arResult['ORDER_LIST_FIELDS']), null));
            $this->row(0, $values, $name, $options);
        }

        $this->footer($name, $options);
    }

    protected function header($name, $options)
    {
        echo '<div class="mb-3">';
        echo '<div class="mb-2">Состав заявки</div>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-sm table-striped mb-0 order-list">';
        echo '<thead>';
        echo '<tr class="text-center">';
        foreach ($options['fields'] as $name) {
            echo '<td>' . ($options['labels'][$name] ?? $name) . '</td>';
        }
        echo '<td></td>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
    }

    protected function row($cursor, $values, $name, $options)
    {
        echo '<tr class="order-item">';
        foreach ($this->arResult['ORDER_LIST_FIELDS'] as $key) {
            $value = $values[$key];
            $inputName = $name . '[' . $cursor . '][' . $key . ']';
            if ($key === 'brand') {
                $this->selectCell($inputName, [
                    'values' => $options['brandValues'],
                    'defaultText' => $options['brandDefaultText'],
                ], $value);
            } else {
                $this->textCell($inputName, $value);
            }
        }
        $this->actionsCell();
        echo '</tr>';
    }

    protected function selectCell($name, $options, $selectedValue)
    {
        echo '<td>';
        echo '<select class="form-control form-control-sm" name="' . $name . '">';
        echo '<option value="">' . $options['defaultText'] . '</option>';
        foreach ($options['values'] as $cursor => $value) {
            $escValue = htmlspecialcharsbx($value, ENT_COMPAT, false);
            echo '<option';
            if (isset($selectedValue) && ($selectedValue === $value)) {
                echo ' selected';
            }
            echo '>';
            echo $escValue . '</option>';
        }
        echo '</select>';
        echo '</td>';
    }

    protected function textCell($name, $value)
    {
        echo '<td>';
        echo '<input class="form-control form-control-sm" type="text" name="' . $name . '"';
        if (isset($value)) {
            $escValue = htmlspecialcharsbx($value, ENT_COMPAT, false);
            echo ' value="' . $escValue . '"';
        }
        echo '>';
        echo '</td>';
    }

    protected function actionsCell()
    {
        echo '<td class="actions">';

        echo '<a href="javascript:void(0)" class="add text-primary" title="Добавить еще">';
        echo '
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
</svg>';
        echo '</a>';

        echo '<a href="javascript:void(0)" class="delete text-danger" title="Удалить">';
        echo '
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
</svg>';
        echo '</a>';

        echo '</td>';
    }

    protected function footer($name, $options)
    {
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        $this->showErrors($name);
        echo '</div>';
    }

    public function showErrors($name)
    {
        if (isset($this->arResult['ERRORS'][$name])) {
            echo '<div class="invalid-feedback d-block">' .
                nl2br(htmlspecialcharsbx(implode("\n", $this->arResult['ERRORS'][$name]))) .
                '</div>';
        }
    }
}