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

class CnCNet_Api
{
    protected $game;
    protected $player;
    protected $ip;

    const DEFAULT_PORT  = 8054;
    const INTERVAL      = 2; /* ping interval (minutes) */
    const TIMEOUT       = 5; /* timeout if ping is not received (minutes) */

    function __construct()
    {
        $this->game = new CnCNet_Game();
        $this->player = new CnCNet_Player();
        if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $_SERVER['REMOTE_ADDR'], $m)) {
            $this->ip = $m[1];
        }
    }

    public function launch ($protocol, $port = self::DEFAULT_PORT)
    {
        if ($this->ping($protocol, $port)) {
            $ips = array();
            $select = $this->player->select()
                                   ->join('games', 'games.id = players.game_id', '')
                                   ->where('games.protocol = ?', $protocol)
                                   ->where('logout IS NULL')
                                   ->where('active > ?', date('Y-m-d H:i:s', strtotime(sprintf('-%d minutes', self::TIMEOUT))));
            $db = $select->getAdapter();
            $select->where(sprintf('NOT (ip = %s AND port = %s)', $db->quote($this->ip), $db->quote($port)));

            foreach ($select as $row) {
                $ips[] = $row->ip . ($row->port != self::DEFAULT_PORT ? ':' . $row->port : '');
            }

            /* need this for it to work at all */
            $ips[] = 'latejoin';

            return array(
                'url'       => $protocol.'://@'.base64_encode(implode(',', $ips)),
                'interval'  => self::INTERVAL
            );
        }

        return false;
    }

    public function ping ($protocol, $port = self::DEFAULT_PORT)
    {
        $row = $this->player->select()->join('games', 'games.id = players.game_id', '')->where('ip = ?', $this->ip)->where('port = ?', $port)->where('games.protocol = ?', $protocol)->fetchRow();
        if ($row) {
            $row->active = date('Y-m-d H:i:s');
            $row->logout = NULL;
            $row->save();
            return true;
        } else {
            $game = $this->game->select()->where('protocol = ?', $protocol)->fetchRow();
            if ($game) {
                $this->player->insert(array(
                    'game_id'   => $game->id,
                    'ip'        => $this->ip,
                    'port'      => $port,
                    'active'    => date('Y-m-d H:i:s')
                ));
                return array('interval' => self::INTERVAL);
            }
        }
        return false;
    }

    public function logout ($protocol, $port = self::DEFAULT_PORT)
    {
        $row = $this->player->select()->join('games', 'games.id = players.game_id', '')->where('ip = ?', $this->ip)->where('port = ?', $port)->where('games.protocol = ?', $protocol)->where('logout IS NULL')->fetchRow();
        if ($row) {
            $row->logout = date('Y-m-d H:i:s');
            $row->save();
            return true;
        }

        return false;
    }
}
