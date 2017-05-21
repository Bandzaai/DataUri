<?php

/**
 * Copyright (c) 2017 Bandzaai
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
namespace Bandzaai\DataUri;

use Bandzaai\DataUri\Exception\FileExistsException;
use Bandzaai\DataUri\Exception\TooLongDataException;

/**
 * Builder for datauri scheme representations.
 *
 * @author Breogán Esculta <breo.esculta@bandzaai.com>
 * @author Bandzaai Team
 * @license http://opensource.org/licenses/MIT MIT
 */
class Builder
{

    /**
     * Regexp for datauri scheme.
     *
     * @var string
     */
    const SCHEME_REGEXP = '/data:([a-zA-Z0-9\/+]+)([a-zA-Z0-9-_;=.+]+)?,(.*)/';

    /**
     * LITLEN mode (1024).
     *
     * @var integer
     */
    const LITLEN = 0;

    /**
     * ATTSPLEN mode (2100).
     *
     * @var integer
     */
    const ATTSPLEN = 1;

    /**
     * TAGLEN mode (2100).
     *
     * @var integer
     */
    const TAGLEN = 2;

    /**
     * ATTSLEN limit.
     *
     * @var int
     */
    const ATTS_TAG_LIMIT = 2100;

    /**
     * LITLEN limit.
     *
     * @var integer
     */
    const LIT_LIMIT = 1024;

    /**
     * Base64 encode prefix.
     *
     * @var string
     */
    const BASE_64 = 'base64';

    /**
     * File data
     *
     * @var string
     */
    protected $data;

    /**
     * File mime type
     *
     * @var string
     */
    protected $mimeType;

    /**
     * Parameters provided in data uri.
     *
     * @var Array
     */
    protected $parameters;

    /**
     * Indicates if data is binaty data.
     *
     * @var boolean
     */
    protected $isBinaryData = false;

    /**
     * Constructor.
     *
     * @param string $data            
     * @param string $mimeType            
     * @param array $parameters            
     * @param bool $strict            
     * @param int $mode            
     */
    public function __construct(string $data, string $mimeType = null, array $parameters = [], bool $strict = false, int $mode = self::TAGLEN)
    {
        $this->data = $data;
        $this->mimeType = $mimeType;
        $this->parameters = $parameters;
        
        $this->setup($mode, $strict);
    }

    /**
     * Setup.
     *
     * @param int $mode            
     * @param bool $strict            
     * @throws TooLongDataException
     */
    private function setup(int $mode, bool $strict)
    {
        if ($strict && $mode === self::LITLEN && strlen($this->data) > self::LIT_LIMIT) {
            throw new TooLongDataException('Too long data', strlen($this->data));
        } elseif ($strict && strlen($this->data) > self::ATTS_TAG_LIMIT) {
            throw new TooLongDataException('Too long data', strlen($this->data));
        }
        
        if (null === $this->mimeType) {
            $this->mimeType = 'text/plain';
            $this->addParameters('charset', 'US-ASCII');
        }
        
        $this->isBinaryData = strpos($this->mimeType, 'text/') !== 0;
    }

    /**
     * Get data uri data.
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Get mime type.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Get data uri parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Indicate if data is binary data.
     *
     * @return boolean
     */
    public function isBinaryData(): bool
    {
        return $this->isBinaryData;
    }

    /**
     * Set if data is binary data.
     *
     * @param bool $boolean            
     * @return Builder
     */
    public function setBinaryData(bool $boolean): Builder
    {
        $this->isBinaryData = (boolean) $boolean;
        return $this;
    }

    /**
     * Add a parameter to data uri definition.
     *
     * @param string $paramName            
     * @param string $paramValue            
     * @return \Bandzaai\DataUri\Builder
     */
    public function addParameters(string $paramName, string $paramValue): Builder
    {
        $this->parameters[$paramName] = $paramValue;
        return $this;
    }

    /**
     * Write image to disk.
     *
     * @param string $filename            
     * @param bool $overwrite            
     * @throws FileExistsException
     * @return bool
     */
    public function writeToDisk(string $filename, bool $overwrite = false): bool
    {
        if (file_exists($filename) && $overwrite === false) {
            throw new FileExistsException('File ' . $filename . ' already exists.');
        }
        
        return file_put_contents($filename, $this->data) ? true : false;
    }

    /**
     * Returns the data uri string.
     *
     * @return string
     */
    public function getDataUriString(): string
    {
        $parameters = $base64 = '';
        
        if (0 !== count($params = $this->getParameters())) {
            foreach ($params as $paramName => $paramValue) {
                $parameters .= sprintf(';%s=%s', $paramName, $paramValue);
            }
        }
        
        if ($this->isBinaryData()) {
            $base64 = sprintf(';%s', self::BASE_64);
            $data = base64_encode($this->getData());
        } else {
            $data = rawurlencode($this->getData());
        }
        
        return sprintf('data:%s%s%s,%s', $this->getMimeType(), $parameters, $base64, $data);
    }
}
