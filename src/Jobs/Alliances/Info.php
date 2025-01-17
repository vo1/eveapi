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

namespace Seat\Eveapi\Jobs\Alliances;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Mapping\Alliances\InfoMapping;
use Seat\Eveapi\Models\Alliances\Alliance;

/**
 * Class Info.
 *
 * @package Seat\Eveapi\Jobs\Alliances
 */
class Info extends EsiBase
{
    /**
     * @var int
     */
    private $alliance_id;

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/alliances/{alliance_id}/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var array
     */
    protected $tags = ['alliance'];

    /**
     * Info constructor.
     *
     * @param  int  $alliance_id
     */
    public function __construct(int $alliance_id)
    {
        $this->alliance_id = $alliance_id;

        array_push($this->tags, $alliance_id);
    }

    /**
     * Handle the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {

        $info = $this->retrieve([
            'alliance_id' => $this->alliance_id,
        ]);

        if ($info->isCachedLoad() && Alliance::find($this->alliance_id))
            return;

        $model = Alliance::firstOrNew([
            'alliance_id' => $this->alliance_id,
        ]);

        InfoMapping::make($model, $info, [
            'alliance_id' => function () {
                return $this->alliance_id;
            },
        ])->save();
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \Carbon\Carbon
     */
    public function retryUntil()
    {
        return now()->addHours(12);
    }
}
