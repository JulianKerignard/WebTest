<?php
namespace App\Core;

class Validator {
    protected $errors = [];

    public function validate($data, $rules) {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            if (!is_array($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }

            foreach ($fieldRules as $rule) {
                $parameters = [];

                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $parameters = explode(',', $paramStr);
                }

                $methodName = 'validate' . ucfirst($rule);

                if (method_exists($this, $methodName)) {
                    $value = $data[$field] ?? null;

                    if (!$this->$methodName($field, $value, $parameters, $data)) {
                        // Validation failed
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getError($field) {
        return $this->errors[$field] ?? [];
    }

    protected function addError($field, $message) {
        $this->errors[$field][] = $message;
    }

    // Validation rules
    protected function validateRequired($field, $value, $parameters, $data) {
        if ($value === null || $value === '') {
            $this->addError($field, "Le champ {$field} est obligatoire.");
            return false;
        }
        return true;
    }

    protected function validateEmail($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "Le champ {$field} doit être une adresse email valide.");
            return false;
        }
        return true;
    }

    protected function validateMin($field, $value, $parameters, $data) {
        $min = (int) $parameters[0];

        if (strlen($value) < $min) {
            $this->addError($field, "Le champ {$field} doit contenir au moins {$min} caractères.");
            return false;
        }
        return true;
    }

    protected function validateMax($field, $value, $parameters, $data) {
        $max = (int) $parameters[0];

        if (strlen($value) > $max) {
            $this->addError($field, "Le champ {$field} ne doit pas dépasser {$max} caractères.");
            return false;
        }
        return true;
    }

    protected function validateConfirmed($field, $value, $parameters, $data) {
        $confirmField = $field . '_confirmation';

        if (!isset($data[$confirmField]) || $value !== $data[$confirmField]) {
            $this->addError($field, "La confirmation du champ {$field} ne correspond pas.");
            return false;
        }
        return true;
    }

    protected function validateDate($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        $format = $parameters[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, $value);

        if (!$date || $date->format($format) !== $value) {
            $this->addError($field, "Le champ {$field} doit être une date valide au format {$format}.");
            return false;
        }
        return true;
    }

    protected function validateNumeric($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        if (!is_numeric($value)) {
            $this->addError($field, "Le champ {$field} doit être un nombre.");
            return false;
        }
        return true;
    }
}