<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Jobs\Character;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\Character\CharacterStats;

/**
 * Class Stats.
 *
 * @package Seat\Eveapi\Jobs\Character
 */
class Stats extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/characters/{character_id}/stats/';

    /**
     * @var int
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-characterstats.read.v1';

    /**
     * @var array
     */
    protected $tags = ['character'];

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

        $stats = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        if ($stats->isCachedLoad() &&
            CharacterStats::where('character_id', $this->getCharacterId())->count() > 0)
            return;

        // Process each years aggregate
        collect($stats)->each(function ($aggregate) {

            // Separate stats by categories
            foreach (['character', 'combat', 'industry', 'inventory', 'isk', 'market',
                'mining', 'module', 'orbital', 'pve', 'social', 'travel', ] as $category) {

                CharacterStats::firstOrCreate([
                    'character_id' => $this->getCharacterId(),
                    'year'         => $aggregate->year,
                    'category'     => $category,
                    'stats'        => isset($aggregate->$category) ?
                        json_encode($aggregate->$category) : null,
                ]);
            }
        });
    }
}
