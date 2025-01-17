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

namespace Seat\Eveapi\Jobs\Industry\Corporation;

use Seat\Eveapi\Jobs\AbstractAuthCorporationJob;
use Seat\Eveapi\Mapping\Industry\JobMapping;
use Seat\Eveapi\Models\Industry\CorporationIndustryJob;

/**
 * Class Jobs.
 *
 * @package Seat\Eveapi\Jobs\Industry\Corporation
 */
class Jobs extends AbstractAuthCorporationJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/corporations/{corporation_id}/industry/jobs/';

    /**
     * @var array
     */
    protected $query_string = [
        'include_completed' => true,
    ];

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-industry.read_corporation_jobs.v1';

    /**
     * @var array
     */
    protected $roles = ['Factory_Manager'];

    /**
     * @var array
     */
    protected $tags = ['corporation', 'industry'];

    /**
     * @var int
     */
    protected $page = 1;

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

            $industry_jobs = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            if ($industry_jobs->isCachedLoad() &&
                CorporationIndustryJob::where('corporation_id', $this->getCorporationId())->count() > 0)
                return;

            collect($industry_jobs)->each(function ($job) {

                $model = CorporationIndustryJob::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'job_id'         => $job->job_id,
                ]);

                JobMapping::make($model, $job, [
                    'corporation_id' => function () {
                        return $this->getCorporationId();
                    },
                    'location_id' => function () use ($job) {
                        return $job->location_id;
                    },
                ])->save();
            });

            if (! $this->nextPage($industry_jobs->pages))
                return;
        }
    }
}
