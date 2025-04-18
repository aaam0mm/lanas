<?php
class FormValidator {

    protected $errors = [];

    protected function set_error( string $field, string $error ) : void {

        $this->errors[] = [
            "selector" => $field,
            "error" => $error
        ];

    }

    public function get_errors() : array {
        return $this->errors;
    }

}