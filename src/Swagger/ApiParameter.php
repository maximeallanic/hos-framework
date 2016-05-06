<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 19/04/16
 * Time: 14:10
 */

namespace Hos\Swagger;


use Hos\ExceptionExt;
use Symfony\Component\Validator\Constraints\Regex;

class ApiParameter
{
    CONST API_PARAM = "/^(?<param>{(?:(?!}).)+})?(?:\s?(?<description>.+))/";
    private $parameter;
    private $doc;
    private $type;

    /**
     * ApiParameter constructor.
     * @param \Zend_Reflection_Parameter $parameter
     */
    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }

    private function getDoc() {
        if (!$this->doc && strlen($this->parameter->getDeclaringFunction()->getDocComment()) > 0) {
            /** @var \Zend_Reflection_Docblock_Tag_Param $doc */
            foreach ($this->parameter->getDeclaringFunction()->getDocblock()->getTags('param') as $doc) {
                $this->parseDoc($doc);
            }
        }
        return $this->doc;
    }

    /**
     * @param \Zend_Reflection_Docblock_Tag_Param $doc
     */
    private function parseDoc($doc) {
        if (get_class($doc) == "Zend_Reflection_Docblock_Tag_Param"
            && $doc->getVariableName() == ("$".$this->parameter->getName())) {

            $this->type = $doc->getType();

            if (preg_match(self::API_PARAM, $doc->getDescription(), $doc)) {
                $this->doc = $doc;

                if (isset($this->doc['param']))
                    $this->doc['param'] = json_decode($this->doc['param'], true);
            }
        }
    }

    public function getApiName() {
        return $this->parameter->getName();
    }

    private function getDescription() {
        $doc = $this->getDoc();
        if ($doc && isset($doc['description']))
            return $doc['description'];
        return false;
    }

    private function getParam($name) {
        $doc = $this->getDoc();
        if ($doc && isset($doc['param'])) {
            return isset($doc['param'][$name]) ? $doc['param'][$name] : false;
        }
        return false;
    }

    public function getValueFromRequest() {
        switch ($this->getParam('type')) {
            case 'path':
                $data = Request::getPaths();
                break;
            case 'body':
                $data = Request::getBody();
                break;
            case 'formData':
                $data = Request::getForms();
                break;
            case 'header':
                $data = Request::getHeaders();
                break;
            default:
                $data = Request::getQueries();
                break;
        }
        if (!isset($data[$this->parameter->getName()])) {
            if (!$this->parameter->isOptional())
                throw new ExceptionExt('api.no_parameter_in_query');
            else
                return $this->parameter->getDefaultValue();
        }
        return $data[$this->parameter->getName()];
    }

    public function generateDoc() {
        return [
            "name" => $this->parameter->getName(),
            "description" => $this->getDescription() ?: "",
            "type" => $this->getParam('type') ?: "string",
            "paramType" => $this->getParam('type') ?: 'query',
            "required" => !$this->parameter->isOptional()
        ];
    }
}