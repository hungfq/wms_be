<?php

namespace App\Validators;

use App\Exceptions\ValidationHttpException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

abstract class AbstractValidator
{
    protected $_isTransformMsg = false;

    abstract protected function rules($params = []);

    /**
     * @param $input
     * @param array $params
     * @return mixed
     */
    public function validate($input, $params = [])
    {
        $validator = Validator::make($input, $this->rules($params), $this->transformMessages($params));

        if ($validator->fails()) {
            $messages = $validator->errors()->getMessages();

            throw new ValidationHttpException($messages);
        }

        return $validator->validated();
    }

    /**
     * @return array
     */
    protected function messages($params = [])
    {
        return [];
    }

    /**
     * @param array $params
     * @return array
     */
    private function transformMessages(array $params)
    {
        $messages = $this->messages($params);

        if (empty($messages)) {
            return [];
        }

        if ($this instanceof TranslateErrorMessage) {
            $this->_isTransformMsg = $this->isTranslateMessage();
        }

        if (!$this->_isTransformMsg) {
            return $messages;
        }

        return Arr::dot($messages);

    }
}