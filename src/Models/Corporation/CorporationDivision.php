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

namespace Seat\Eveapi\Models\Corporation;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Wallet\CorporationWalletBalance;
use Seat\Eveapi\Traits\HasCompositePrimaryKey;

/**
 * Class CorporationDivision.
 *
 * @package Seat\Eveapi\Models\Corporation
 */
class CorporationDivision extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * @var array
     */
    protected $primaryKey = ['corporation_id', 'type', 'division'];

    /**
     * @param $value
     * @return string
     */
    public function getNameAttribute($value)
    {

        if (is_null($value))
            return 'Master Wallet';

        return $value;
    }

    /**
     * @return float
     */
    public function getBalanceAttribute()
    {

        $balance = null;

        if ($this->type == 'wallet')
            $balance = CorporationWalletBalance::where('corporation_id', $this->corporation_id)
                ->where('division', $this->division)
                ->select('balance')
                ->first();

        return is_null($balance) ? 0.0 : $balance->balance;
    }
}
