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
namespace Bandzaai\DataUri\Exception;

/**
 *
 * @author Breogán Esculta
 * @author Bandzaai Team
 * @license http://opensource.org/licenses/MIT MIT
 */
class TooLongDataException extends \Exception
{

    /**
     * Data length.
     *
     * @var int
     */
    protected $length;

    /**
     * Constructor.
     *
     * @param string $message            
     * @param int $length            
     */
    public function __construct($message = null, int $length)
    {
        parent::__construct($message);
        $this->length = $length;
    }

    /**
     * Get the data length.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }
}
