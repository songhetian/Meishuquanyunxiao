<?php

namespace components;

use Yii;
use vakorovin\datetimepicker\Datetimepicker;
use vakorovin\datetimepicker\Assets;
use yii\helpers\Html;
use yii\web\View;

class Datetime extends  Datetimepicker {

	public function run()
    {
		Assets::register($this->getView());

		if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }

		$options = "";
		if (!empty($this->options)) {
			$options .= "{\n";
			foreach ((array) $this->options as $key => $value) {
                if (is_array($value)) {
                    $values = [];
                    foreach ($value as $_key => $_value) {
                        if (is_int($_value) || is_float($_value)) {
                            $values[] = $_value; // хотя по факту не нужно, используется только для передачи дат
                        } else {
                            $values[] = "'{$_value}'";
                        }
                    }
                    $value = "[" . implode(', ', $values) . "]";
                    $options .= "    {$key}: {$value},\n";
                } elseif (is_int($value) || is_float($value)) {
                    $options .= "    {$key}: {$value},\n";
                } else {
                	if($key != 'aria-required')
                    $options .= "    {$key}: '{$value}',\n";
                }
            }
			$options .= "}\n";
		}

		$JavaScript = "jQuery('";
		$JavaScript .= '#'.$this->options['id'];
		$JavaScript .= "').datetimepicker({$options});";

		$this->getView()->registerJs($JavaScript, View::POS_END);
	}
}