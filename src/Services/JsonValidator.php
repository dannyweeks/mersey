<?php


namespace Weeks\Mersey\Services;


use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

class JsonValidator
{
    protected $schema;

    protected $json;

    protected $errors;

    public function __construct($schema)
    {

        $this->schema = $schema;
    }

    public function validate($json)
    {
        // Get the schema and data as objects
        $retriever = new UriRetriever;
        $schema = $retriever->retrieve('file://' . realpath($this->schema));
        // Validate
        $validator = new Validator();
        $validator->check($json, $schema);

        $this->errors = $validator->getErrors();

        return $validator->isValid();
    }

    /**
     * @param mixed $schema
     * @return $this
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @param $json
     * @return $this
     */
    public function getJson($json)
    {
        $this->json = $json;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

}