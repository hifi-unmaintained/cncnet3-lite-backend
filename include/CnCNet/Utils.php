<?php

/*
 * Copyright (c) 2011 Toni Spets <toni.spets@iki.fi>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

class CnCNet_Utils
{
    static function v4_peers2bin($peers)
    {
        $ret = '';

        foreach ($peers as $peer) {
            list($ip, $port) = explode(':', $peer);
            $ret .= pack('Nn', ip2long($ip), $port);
        }

        return $ret;
    }

    // Base32 implementation
    //
    // Copyright 2010 Google Inc.
    // Author: Markus Gutschke
    //
    // Licensed under the Apache License, Version 2.0 (the "License");
    // you may not use this file except in compliance with the License.
    // You may obtain a copy of the License at
    //
    //      http://www.apache.org/licenses/LICENSE-2.0
    //
    // Unless required by applicable law or agreed to in writing, software
    // distributed under the License is distributed on an "AS IS" BASIS,
    // WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    // See the License for the specific language governing permissions and
    // limitations under the License.

    /* converted to PHP by Toni Spets for the CnCNet project, original at http://code.google.com/p/google-authenticator/source/browse/libpam/base32.c */
    static function base32_encode($data)
    {
        $a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        $result = null;
        $length = strlen($data);

        if ($length > 0) {
            $buffer = ord($data[0]);
            $next = 1;
            $bitsLeft = 8;

            while ($bitsLeft > 0 || $next < $length) {
                if ($bitsLeft < 5) {
                    if ($next < $length) {
                        $buffer <<= 8;
                        $buffer |= ord($data[$next++]) & 0xFF;
                        $bitsLeft += 8;
                    } else {
                        $pad = 5 - $bitsLeft;
                        $buffer <<= $pad;
                        $bitsLeft += $pad;
                    }
                }
                $index = 0x1F & ($buffer >> ($bitsLeft - 5));
                $bitsLeft -= 5;
                $result .= $a[$index];
            }
        }

        return $result;
    }
}
