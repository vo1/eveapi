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

namespace Seat\Eveapi\Jobs\PlanetaryInteraction\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Mapping\Structures\CustomsOfficeMapping;
use Seat\Eveapi\Models\PlanetaryInteraction\CorporationCustomsOffice;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class CustomsOffices.
 *
 * @package Seat\Eveapi\Jobs\Corporation
 */
class CustomsOffices extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/customs_offices/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-planets.read_customs_offices.v1';

    /**
     * @var array
     */
    protected $roles = ['Director'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'pi'];

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $known_structures;

    /**
     * CustomsOffices constructor.
     *
     * @param  int  $corporation_id
     * @param  \Seat\Eveapi\Models\RefreshToken  $token
     */
    public function __construct(int $corporation_id, RefreshToken $token)
    {
        $this->known_structures = collect();

        parent::__construct($corporation_id, $token);
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        while (true) {

            $customs_offices = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($customs_offices->isCachedLoad() &&
                CorporationCustomsOffice::where('corporation_id', $this->getCorporationId())->count() > 0)
                return;

            collect($customs_offices)->each(function ($customs_office) {

                $model = CorporationCustomsOffice::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'office_id'      => $customs_office->office_id,
                ]);

                CustomsOfficeMapping::make($model, $customs_office, [
                    'corporation_id' => function () {
                        return $this->getCorporationId();
                    },
                ])->save();

                $this->known_structures->push($customs_office->office_id);

            });

            if (! $this->nextPage($customs_offices->pages))
                break;
        }

        // Cleanup customs offices that were not in the response.
        CorporationCustomsOffice::where('corporation_id', $this->getCorporationId())
            ->whereNotIn('office_id', $this->known_structures->flatten()->all())
            ->delete();
    }
}
