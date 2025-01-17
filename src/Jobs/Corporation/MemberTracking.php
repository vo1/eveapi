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

namespace Seat\Eveapi\Jobs\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Models\Corporation\CorporationMemberTracking;

/**
 * Class MemberTracking.
 *
 * @package Seat\Eveapi\Jobs\Corporation
 */
class MemberTracking extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/membertracking/';

    /**
     * @var string
     */
    protected $version = 'v2';

    /**
     * @var string
     */
    protected $scope = 'esi-corporations.track_members.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'member'];

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $members = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        if ($members->isCachedLoad() &&
            CorporationMemberTracking::where('corporation_id', $this->getCorporationId())->count() > 0)
            return;

        collect($members)->each(function ($member) {

            CorporationMemberTracking::firstOrNew([
                'corporation_id' => $this->getCorporationId(),
                'character_id'   => $member->character_id,
            ])->fill([
                'start_date'   => property_exists($member, 'start_date') ?
                    carbon($member->start_date) : null,
                'base_id'      => $member->base_id ?? null,
                'logon_date'   => property_exists($member, 'logon_date') ?
                    carbon($member->logon_date) : null,
                'logoff_date'  => property_exists($member, 'logoff_date') ?
                    carbon($member->logoff_date) : null,
                'location_id'  => $member->location_id ?? null,
                'ship_type_id' => $member->ship_type_id ?? null,
            ])->save();
        });

        CorporationMemberTracking::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('character_id', collect($members)->pluck('character_id')->all())
            ->delete();
    }
}
