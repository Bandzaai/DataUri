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

use Bandzaai\DataUri\Exception\FileNotFoundException;
use Bandzaai\DataUri\Exception\InvalidArgumentException;
use Bandzaai\DataUri\Exception\InvalidDataException;

/**
 * DataUri is a class to manage datauri data.
 *
 * @author Breogán Esculta
 * @author Bandzaai team
 * @license http://opensource.org/licenses/MIT MIT
 */
class DataUri
{

    /**
     * Build from a string.
     *
     * @param string $dataUri            
     * @param int $len            
     * @param bool $strict            
     * @throws InvalidArgumentException
     * @throws InvalidDataException
     * @return \Bandzaai\DataUri\Builder
     */
    public static function fromString(string $dataUri, int $mode = Builder::TAGLEN, bool $strict = false): Builder
    {
        // Initialize vars
        $dataParams = $matches = [];
        $isBase64 = false;
        
        // Check is string match with the scheme regexp
        if (! preg_match(Builder::SCHEME_REGEXP, $dataUri, $matches)) {
            throw new InvalidArgumentException('Could not parse the URL scheme');
        }
        
        // Extract params from matches.
        list ($input, $mimeType, $params, $rawData) = $matches;
        
        // Has params.
        if (! empty($params)) {
            foreach (explode(';', $params) as $param) {
                if (strstr($param, '=')) {
                    $param = explode('=', $param);
                    $dataParams[array_shift($param)] = array_pop($param);
                } elseif ($param === Builder::BASE_64) {
                    $isBase64 = true;
                }
            }
        }
        
        // Decode data if raw data if the input scheme has the parameter "base64"
        if (($isBase64 && ! $rawData = base64_decode($rawData, $strict))) {
            throw new InvalidDataException('Base64 decoding failed');
        }
        // Is not base 64 encoded
        if ($isBase64 === false) {
            $rawData = rawurldecode($rawData);
        }
        
        // Instantiate a new Builder
        $dataURI = new Builder($rawData, $mimeType, $dataParams, $strict, $mode);
        $dataURI->setBinaryData($isBase64);
        
        return $dataURI;
    }

    /**
     * Build from a filename.
     *
     * @param string $filename            
     * @param bool $strict            
     * @param int $mode            
     * @throws FileNotFoundException
     * @return Builder
     */
    public static function fromFile(string $filename, bool $strict = false, int $mode = Builder::TAGLEN): Builder
    {
        // Check if file exists.
        if (! file_exists($filename)) {
            throw new FileNotFoundException(sprintf('%s file does not exist', $filename));
        }
        
        // Get mimetype info.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filename);
        finfo_close($finfo);
        
        // Get file contents.
        $data = file_get_contents($filename);
        
        // Return ne Builder instance
        return new Builder($data, $mimeType, [], $strict, $mode);
    }

    /**
     * Build from an URL.
     *
     * @param string $url            
     * @param bool $strict            
     * @param int $mode            
     * @throws \RuntimeException
     * @throws FileNotFoundException
     * @return Builder
     */
    public static function fromUrl(string $url, bool $strict = false, int $mode = Builder::TAGLEN): Builder
    {
        if (! extension_loaded('curl')) {
            throw new \RuntimeException('This method requires the CURL extension.');
        }
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            curl_close($ch);
            throw new FileNotFoundException(sprintf('%s file does not exist or the remote server does not respond.', $url));
        }
        
        $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        $dataURI = new Builder($data, $mimeType, [], $strict, $mode);
        
        return $dataURI;
    }
}
