<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Context;


trait ContextVars
{
    protected $vars = [];

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setVar($name, $value)
    {

        $this->vars[$this->prepareVarName($name)] = $value;
    }

    protected function getVar($name)
    {
        return isset($this->vars[$this->prepareVarName($name)]) ? $this->vars[$this->prepareVarName($name)] : null;
    }

    private function prepareVarName($name)
    {
        return strtolower(str_replace(' ', '_', str_replace('  ', ' ', $name)));
    }

    /**
     * @param string $param
     *
     * @return bool|int|null|string
     */
    protected function injectVars($param)
    {
        if (!is_string($param) || !preg_match_all('/\{([^\}\{]+)\}/', $param, $matches)) {
            return $this->getCorrectType($param);
        }

        foreach ($matches[1] as $index => $match) {
            $key = $this->prepareVarName($match);

            if (!isset($this->vars[$key])) {
                continue;
            }

            $param = str_replace(sprintf('{%s}', $match), $this->vars[$key], $param);
        }

        return $this->getCorrectType($param);
    }

    /**
     * @param string $string
     *
     * @return bool|int|null|string
     */
    protected function getCorrectType($string)
    {
        if (!is_string($string)) {
            return $string;
        }

        switch (strtolower($string)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null';
                return null;
        }

        if (is_numeric($string)) {
            return (int)$string;
        }

        return $string;
    }
}