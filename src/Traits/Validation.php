<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use App\Http\Resources\ApiResource;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait Validation {

    use ValidatesRequests;

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @overwrite default `validate` function in Controller
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $messages = $this->__getFormattedMessages($messages);
        $customAttributes = $this->__getFormattedAttributes($rules);

        return $this->getValidationFactory()->make(
            $request->all(),
            $rules,
            $messages,
            $customAttributes
        )->validate();
    }

    function __validationFail($validator, ?array $debug = null)
    {
        $messages = $validator;
        if(is_a($validator, Validator::class)){
            $debug = $validator->getMessageBag()->toArray();
            $messages = $debug[array_keys($debug)[0]][0] ?? $debug;
        }
        return new ApiResource($this->__api($debug, false, $messages, 0));
    }

    function __getFormattedAttributes(array $rules)
    {
        return array_combine(array_keys($rules), array_map(function($val) {
            preg_match('/.*(?=\.)/', $val, $matches);
            $match = $matches[0] ?? $val;
            $word = str_replace('.*', '', $match);
            $singular = Str::singular($word);
            $formatted = str_replace($word, $singular, $val);
            return ucfirst(  str_replace(".*.", "'s ", $formatted));
        }, str_replace('_', ' ', array_keys($rules))));
    }

    function __getFormattedMessages(?array $custom = null, ?bool $replace = false)
    {
        $attributes = [
            "required" => ":attribute is required.",
            "exists" => ":attribute is invalid.",
            "required_with" => ":attribute is required when :values is not empty.",
            "min" => [
                'numeric' => ":attribute must be at least :min.",
                'file' => ":attribute must be at least :min kilobytes.",
                'string' => ":attribute must be at least :min characters.",
                'array' => ":attribute must have at least :min items.",
            ],
            "max" => [
                'numeric' => ":attribute must not be greater than :max.",
                'file' => ":attribute must not be greater than :max kilobytes.",
                'string' => ":attribute must not be greater than :max characters.",
                'array' => ":attribute must not have more than :max items.",
            ],
            "between" => ":attribute field's value is not between :min - :max.",
            "password" => ":attribute is incorrect.",
            "confirmed" => ":attribute confirmation does not match."
        ];

        if(!empty($custom)){
            if($replace){
                $attributes = $custom;
            }else{
                $attributes = array_merge($attributes, $custom);
            }
        }

        return $attributes;
    }

}