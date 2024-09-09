<?php

namespace validation;

class Validator
{
    /**
     * @param $user
     * @return array
     */
    public function validate($user): array
    {
     $errors = [];
     if (empty($user['name'])) {
         $errors['name'] = "Name can't be blank";
     }
     if (empty($user['email'])) {
         $errors['email'] = "Email can't be blank";
     }
     return $errors;
    }
}