<?php

namespace Rezzza\SecurityBundle\Request\Obfuscator;

use Rezzza\SecurityBundle\Exception\ObfuscateBadPatternException;

/**
 * RequestObfuscator
 *
 * @uses ObfuscatorInterface
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class RequestObfuscator implements ObfuscatorInterface
{
    CONST TOKEN_REPLACE = 'X';
    CONST TOKEN_ALL = '*';

    /**
     * {@inheritdoc}
     */
    public function obfuscate(array $data, array $obfuscatedPatterns)
    {
        foreach ($obfuscatedPatterns as $key => $pattern) {
            if (isset($data[$key])) {
                $data[$key] = $this->obfuscateContentWithPattern($data[$key], $pattern);
            }
        }

        return $data;
    }

    private function obfuscateContentWithPattern($content, $pattern)
    {
        if (!is_array($content)) {
            return is_scalar($content) ? $this->obfuscateContent($content) : null;
        }

        if ($pattern === self::TOKEN_ALL) {
            return self::TOKEN_REPLACE;
        }

        $patterns    = (array) $pattern;
        foreach ($patterns as $pattern) {
            $keys = array_map(function($v) {
                return str_replace(']', '', $v);
            }, explode('[', $pattern));

            $pattern = array_shift($keys);

            if (array_key_exists($pattern, $content)) {
                if (count($keys) === 0) {
                    $content[$pattern] = $this->obfuscateContent($content[$pattern]);
                } else {
                    $newPattern = array_shift($keys);
                    foreach ($keys as $key) {
                        $newPattern .= sprintf('[%s]', $key);
                    }
                    $content[$pattern] = $this->obfuscateContentWithPattern($content[$pattern], $newPattern);
                }
            }
        }

        return $content;
    }

    private function obfuscateContent($content)
    {
        return is_scalar($content) ? str_repeat(self::TOKEN_REPLACE, strlen($content)) : self::TOKEN_REPLACE;
    }
}
