<?php

namespace Weeks\Mersey\Services;

use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Weeks\Mersey\Components\Schema;

class JsonValidator
{
    /**
     * The schema to validate against
     *
     * @var Schema
     */
    protected $schema;

    /**
     * Validate the given json against the schema.
     *
     * @param string $json
     * @param null   $schema
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function validate($json, $schema = null)
    {
        // Get the schema and data as objects
        $retriever = new UriRetriever;
        $schema = $schema ?: $retriever->retrieve('file://' . realpath($this->schema->resolve()));

        // Validate
        $validator = new Validator();
        $validator->check($json, $schema);

        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            $messages = collect($errors)
                ->transform(function ($error) {
                    return $this->formatErrorMessage($error);
                })->implode("\n");

            throw new \Exception($messages);
        }

        return true;
    }

    /**
     * Set the schema
     *
     * @param Schema $schema
     *
     * @return $this
     */
    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Get the ordinal version of a number.
     *
     * @param $number
     *
     * @return string
     */
    protected function ordinal($number)
    {
        // Special case "teenth"
        if (($number / 10) % 10 != 1) {
            // Handle 1st, 2nd, 3rd
            switch ($number % 10) {
                case 1:
                    return $number . 'st';
                case 2:
                    return $number . 'nd';
                case 3:
                    return $number . 'rd';
            }
        }

        // Everything else is "nth"
        return $number . 'th';
    }

    /**
     * Format the error message
     *
     * @param $error
     *
     * @return string
     */
    protected function formatErrorMessage($error)
    {
        $message = '%2$s error. %1$s. Located: [%4$s %3$s]';
        $parts = explode('.', $error['property']);

        $fileName = str_replace('-schema', '', $this->schema->getFileName());
        $type = str_singular(str_replace('.json', '', $fileName));

        $data = [
            'info'     => $error['message'],
            'file'     => $fileName,
            'type'     => $type,
            $type      => $this->ordinal($parts[0] + 1),
            'property' => $parts[1],
        ];

        $totalParts = count($parts);

        /**
         * If there was 3 parts to the property then a project
         * was involved with the error
         */
        if ($totalParts >= 3) {
            $data['property'] = $parts[2];

            list($data['section_2_type'], $data['project']) = $this->getSectionData($parts[1]);

            $message .= '->[%6$s %7$s]';

            /**
             * If there was 4 parts to the property then a script
             * was involved with the error
             */
            if ($totalParts >= 4) {
                $data['property'] = $parts[3];

                list($data['section_3_type'], $data['script']) = $this->getSectionData($parts[2]);
                $message .= '->[%8$s %9$s]';
            }
        }

        if ($error['constraint'] == 'type') {
            $replacement = [' but the \'' . $data['property'] . '\' property should be $1', ''];
            $data['info'] = preg_replace(['/, but (an?)/', '/ is required/'], $replacement, $data['info']);
        }

        return vsprintf($message . '.', $data);
    }

    /**
     * Extract the data from the error
     *
     * @param $part
     *
     * @return array
     */
    protected function getSectionData($part)
    {
        $section = [];
        preg_match_all("/(.*)\[(\d+)\]/", $part, $matches);
        $section[] = str_singular($matches[1][0]);
        $section[] = $this->ordinal($matches[2][0] + 1);

        return $section;
    }
}