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
                        // La validation a échoué, mais l'erreur est déjà ajoutée dans la méthode
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

    public function getFirstError($field) {
        return isset($this->errors[$field]) && !empty($this->errors[$field]) ? $this->errors[$field][0] : null;
    }

    public function getAllErrors() {
        $allErrors = [];
        foreach ($this->errors as $field => $errors) {
            foreach ($errors as $error) {
                $allErrors[] = $error;
            }
        }
        return $allErrors;
    }

    protected function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
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

    protected function validateInteger($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "Le champ {$field} doit être un nombre entier.");
            return false;
        }
        return true;
    }

    protected function validateBetween($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        $min = (float) $parameters[0];
        $max = (float) $parameters[1];

        if (!is_numeric($value) || $value < $min || $value > $max) {
            $this->addError($field, "Le champ {$field} doit être un nombre entre {$min} et {$max}.");
            return false;
        }
        return true;
    }

    protected function validateIn($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        if (!in_array($value, $parameters)) {
            $this->addError($field, "Le champ {$field} doit être l'une des valeurs suivantes: " . implode(', ', $parameters) . ".");
            return false;
        }
        return true;
    }

    protected function validateRegex($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        $pattern = $parameters[0];

        if (!preg_match($pattern, $value)) {
            $this->addError($field, "Le champ {$field} n'est pas au format attendu.");
            return false;
        }
        return true;
    }

    protected function validateUrl($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "Le champ {$field} doit être une URL valide.");
            return false;
        }
        return true;
    }

    protected function validateUnique($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        $table = $parameters[0];
        $column = $parameters[1] ?? $field;
        $exceptId = $parameters[2] ?? null;
        $exceptColumn = $parameters[3] ?? 'id';

        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];

        if ($exceptId !== null) {
            $sql .= " AND {$exceptColumn} != ?";
            $params[] = $exceptId;
        }

        $result = App::$app->db->fetch($sql, $params);

        if ($result && $result['count'] > 0) {
            $this->addError($field, "La valeur du champ {$field} est déjà utilisée.");
            return false;
        }
        return true;
    }

    protected function validateStrongPassword($field, $value, $parameters, $data) {
        if (empty($value)) {
            return true;
        }

        $minLength = $parameters[0] ?? 8;
        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{" . $minLength . ",}$/";

        if (!preg_match($pattern, $value)) {
            $this->addError($field, "Le mot de passe doit contenir au moins {$minLength} caractères, dont au moins une lettre majuscule, une lettre minuscule et un chiffre.");
            return false;
        }
        return true;
    }
}