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

namespace Seat\Eveapi\Jobs\Industry\Corporation\Mining;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Mapping\Industry\ExtractionMapping;
use Seat\Eveapi\Models\Industry\CorporationIndustryMiningExtraction;

/**
 * Class Extractions.
 *
 * @package Seat\Eveapi\Jobs\Industry\Corporation\Mining
 */
class Extractions extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporation/{corporation_id}/mining/extractions/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-industry.read_corporation_mining.v1';

    /**
     * @var array
     */
    protected $roles = ['Station_Manager'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'industry', 'structure'];

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $mining_extractions = $this->retrieve([
            'corporation_id' => $this->getCorporationId(),
        ]);

        if ($mining_extractions->isCachedLoad() &&
            CorporationIndustryMiningExtraction::where('corporation_id', $this->getCorporationId())->count() > 0)
            return;

        collect($mining_extractions)->each(function ($extraction) {

            $model = CorporationIndustryMiningExtraction::firstOrNew([
                'moon_id' => $extraction->moon_id,
                'extraction_start_time' => $extraction->extraction_start_time,
            ]);

            ExtractionMapping::make($model, $extraction, [
                'corporation_id' => function () {
                    return $this->getCorporationId();
                },
            ])->save();
        });
    }
}
